<?php

namespace App\Services\Template;

use ArrayAccess;

class MustacheEngine
{
    /**
     * Render a Mustache template with a data context.
     *
     * Note: Unlike HTML-oriented mustache engines, this engine does NOT
     * HTML-escape tags by default, because it is primarily used for LLM prompts
     * where characters like &, <, >, ", ' must remain literal plain text.
     * Both {{ tag }} and {{{ tag }}} render raw literal text.
     */
    public static function render(string $template, array $context): string
    {
        $engine = new static();
        return $engine->parse($template, [$context]);
    }

    /**
     * Parse template with a stack of contexts.
     */
    protected function parse(string $template, array $contextStack): string
    {
        $output = '';
        $offset = 0;
        $length = strlen($template);

        while ($offset < $length) {
            $tagOpen = strpos($template, '{{', $offset);

            if ($tagOpen === false) {
                $output .= substr($template, $offset);
                break;
            }

            // Append plain text before tag
            $output .= substr($template, $offset, $tagOpen - $offset);

            // Check if triple mustache {{{ tag }}}
            $isTriple = (substr($template, $tagOpen, 3) === '{{{');
            $closeToken = $isTriple ? '}}}' : '}}';
            $tagOpenLen = $isTriple ? 3 : 2;

            $tagClose = strpos($template, $closeToken, $tagOpen + $tagOpenLen);
            if ($tagClose === false) {
                // Unclosed tag, output literal and stop
                $output .= substr($template, $tagOpen);
                break;
            }

            $tagInner = trim(substr($template, $tagOpen + $tagOpenLen, $tagClose - ($tagOpen + $tagOpenLen)));
            $tagTotalLen = ($tagClose + strlen($closeToken)) - $tagOpen;

            if ($tagInner === '') {
                $offset = $tagClose + strlen($closeToken);
                continue;
            }

            $firstChar = $tagInner[0];

            if ($firstChar === '!') {
                // Comment: ignore
                $offset = $tagClose + strlen($closeToken);
                continue;
            }

            if ($firstChar === '#' || $firstChar === '^') {
                // Section or Inverted Section
                $sectionKey = trim(substr($tagInner, 1));
                $isInverted = ($firstChar === '^');

                $closeTag = '{{/' . $sectionKey . '}}';
                $sectionEnd = $this->findMatchingSectionEnd($template, $sectionKey, $tagOpen + $tagTotalLen);

                if ($sectionEnd === false) {
                    // Mismatched or unclosed section, treat as literal text
                    $output .= substr($template, $tagOpen, $tagTotalLen);
                    $offset = $tagOpen + $tagTotalLen;
                    continue;
                }

                $innerTemplate = substr($template, $tagOpen + $tagTotalLen, $sectionEnd['content_length']);
                $val = $this->resolveValue($sectionKey, $contextStack);

                if ($isInverted) {
                    if ($this->isFalsy($val)) {
                        $output .= $this->parse($innerTemplate, $contextStack);
                    }
                } else {
                    if (!$this->isFalsy($val)) {
                        if (is_array($val) && $this->isSequentialArray($val)) {
                            foreach ($val as $item) {
                                $newStack = $contextStack;
                                if (is_array($item)) {
                                    array_unshift($newStack, $item);
                                } elseif (is_object($item)) {
                                    array_unshift($newStack, (array) $item);
                                } else {
                                    array_unshift($newStack, ['.' => $item, 'this' => $item]);
                                }
                                $output .= $this->parse($innerTemplate, $newStack);
                            }
                        } elseif (is_array($val)) {
                            $newStack = $contextStack;
                            array_unshift($newStack, $val);
                            $output .= $this->parse($innerTemplate, $newStack);
                        } elseif (is_object($val)) {
                            $newStack = $contextStack;
                            array_unshift($newStack, (array) $val);
                            $output .= $this->parse($innerTemplate, $newStack);
                        } else {
                            // Truthy scalar (e.g. true or non-empty string)
                            $output .= $this->parse($innerTemplate, $contextStack);
                        }
                    }
                }

                $offset = $sectionEnd['total_end'];
                continue;
            }

            // Normal variable interpolation: {{ key }} or {{{ key }}} or {{& key }}
            if ($firstChar === '&') {
                $tagInner = trim(substr($tagInner, 1));
            }

            $resolved = $this->resolveValue($tagInner, $contextStack);

            if ($resolved !== null && !is_array($resolved) && !is_object($resolved)) {
                $output .= (string) $resolved;
            } elseif (is_array($resolved)) {
                // If it's a simple list of scalar strings, join with comma
                if ($this->isSequentialArray($resolved)) {
                    $allScalars = true;
                    foreach ($resolved as $item) {
                        if (is_array($item) || is_object($item)) {
                            $allScalars = false;
                            break;
                        }
                    }
                    if ($allScalars) {
                        $output .= implode(', ', $resolved);
                    }
                }
            }

            $offset = $tagClose + strlen($closeToken);
        }

        return $output;
    }

    /**
     * Resolve a variable by key against the context stack.
     */
    protected function resolveValue(string $key, array $contextStack): mixed
    {
        $key = trim($key);

        if ($key === '.' || $key === 'this') {
            foreach ($contextStack as $context) {
                if (is_array($context) && array_key_exists('.', $context)) {
                    return $context['.'];
                }
                if (!is_array($context) && !is_object($context)) {
                    return $context;
                }
            }
        }

        foreach ($contextStack as $context) {
            $val = $this->resolveDotNotation($key, $context);
            if ($val !== null) {
                return $val;
            }
        }

        return null;
    }

    /**
     * Resolve dot-notation path on context (e.g. 'job.title' or 'client.name').
     */
    protected function resolveDotNotation(string $key, mixed $context): mixed
    {
        if ($context === null) {
            return null;
        }

        // Direct key check first
        if (is_array($context) && array_key_exists($key, $context)) {
            return $context[$key];
        }

        if (is_object($context) && isset($context->{$key})) {
            return $context->{$key};
        }

        // Split by dot
        if (str_contains($key, '.')) {
            $parts = explode('.', $key);
            $current = $context;

            foreach ($parts as $part) {
                if (is_array($current) && array_key_exists($part, $current)) {
                    $current = $current[$part];
                } elseif (is_object($current)) {
                    if (isset($current->{$part})) {
                        $current = $current->{$part};
                    } elseif (method_exists($current, $part)) {
                        $current = $current->{$part}();
                    } elseif ($current instanceof ArrayAccess && isset($current[$part])) {
                        $current = $current[$part];
                    } else {
                        return null;
                    }
                } else {
                    return null;
                }
            }

            return $current;
        }

        return null;
    }

    /**
     * Find matching closing section tag accounting for nested sections of the same key.
     */
    protected function findMatchingSectionEnd(string $template, string $sectionKey, int $startIndex): array|false
    {
        $openTag = '{{#' . $sectionKey . '}}';
        $openInvertedTag = '{{^' . $sectionKey . '}}';
        $closeTag = '{{/' . $sectionKey . '}}';

        $depth = 1;
        $offset = $startIndex;
        $len = strlen($template);

        while ($offset < $len) {
            $nextClose = strpos($template, $closeTag, $offset);
            if ($nextClose === false) {
                return false;
            }

            // Check if there is an inner open tag of the same key before the next close
            $nextOpenNorm = strpos($template, $openTag, $offset);
            $nextOpenInv = strpos($template, $openInvertedTag, $offset);

            $nextOpen = false;
            if ($nextOpenNorm !== false && $nextOpenInv !== false) {
                $nextOpen = min($nextOpenNorm, $nextOpenInv);
            } elseif ($nextOpenNorm !== false) {
                $nextOpen = $nextOpenNorm;
            } elseif ($nextOpenInv !== false) {
                $nextOpen = $nextOpenInv;
            }

            if ($nextOpen !== false && $nextOpen < $nextClose) {
                $depth++;
                $offset = $nextOpen + strlen($openTag);
            } else {
                $depth--;
                if ($depth === 0) {
                    return [
                        'content_length' => $nextClose - $startIndex,
                        'total_end' => $nextClose + strlen($closeTag),
                    ];
                }
                $offset = $nextClose + strlen($closeTag);
            }
        }

        return false;
    }

    /**
     * Check if a value is considered falsey in Mustache.
     */
    protected function isFalsy(mixed $value): bool
    {
        if ($value === null || $value === false || $value === '' || $value === 0 || $value === '0') {
            return true;
        }

        if (is_array($value) && count($value) === 0) {
            return true;
        }

        return false;
    }

    /**
     * Check if array is sequential (list) vs associative map.
     */
    protected function isSequentialArray(array $arr): bool
    {
        if (empty($arr)) {
            return true;
        }
        return array_is_list($arr);
    }

    /**
     * Extract all tag variable names from a template.
     */
    public static function extractTags(string $template): array
    {
        preg_match_all('/\{\{\{?([#^\/&!]?[a-zA-Z0-9_\.]+)\}?\}\}/', $template, $matches);
        $tags = [];

        foreach ($matches[1] ?? [] as $match) {
            $clean = ltrim(trim($match), '#^/&!');
            if ($clean !== '' && !in_array($clean, $tags, true)) {
                $tags[] = $clean;
            }
        }

        return $tags;
    }

    /**
     * Returns dictionary of available dynamic tags categorized for UI documentation.
     */
    public static function getAvailableTags(): array
    {
        return [
            'Job Information' => [
                '{{ job.title }}' => 'Job title from Upwork posting',
                '{{ job.description }}' => 'Full job description text',
                '{{ job.budget }}' => 'Formatted budget (e.g. $500 or $30 - $60)',
                '{{ job.budget_min }}' => 'Minimum budget or hourly rate',
                '{{ job.budget_max }}' => 'Maximum budget or hourly rate',
                '{{ job.type }}' => 'Job payment type (HOURLY or FIXED RATE)',
                '{{ job.location }}' => 'Job location preference / requirement',
                '{{ job.total_applicants }}' => 'Total applicants submitted',
                '{{ job.posted_at }}' => 'When job was posted (e.g. 2 hours ago)',
                '{{ job.upwork_url }}' => 'Direct link to Upwork job posting',
                '{{ job.skills }}' => 'Required skills listed on the job',
            ],
            'Client Details' => [
                '{{ client.name }}' => 'Client company / team name',
                '{{ client.location }}' => 'Client location / country',
                '{{ client.country }}' => 'Client country',
                '{{ client.total_spent }}' => 'Total money spent by client on Upwork',
                '{{ client.total_hires }}' => 'Total number of freelancers hired',
                '{{ client.total_reviews }}' => 'Total reviews received by client',
                '{{ client.feedback }}' => 'Average feedback rating (e.g. 4.9)',
                '{{ client.total_posted_jobs }}' => 'Total jobs posted by client',
                '{{ client.payment_verified }}' => 'Payment method verified (Yes / No)',
            ],
            'Job Insights (Auto-Analyzed)' => [
                '{{ insights }}' => 'Full summary: Urgency, Risk, and Goal',
                '{{ insights_data.urgency }}' => 'Detected urgency level (High / Normal)',
                '{{ insights_data.risk }}' => 'Detected project risk / system instability',
                '{{ insights_data.goal }}' => 'Detected project goal (e.g. MVP / fix existing)',
            ],
            'Freelancer Profile Context' => [
                '{{ freelancer.name }}' => 'Freelancer full name from admin config',
                '{{ freelancer.skills }}' => 'Matched relevant skills for this specific job',
                '{{ freelancer.achievements }}' => 'Matched achievements relevant to the job',
                '{{ freelancer.experience }}' => 'Relevant work experience',
                '{{ freelancer.certifications }}' => 'Relevant certifications',
                '{{ freelancer.case_study }}' => 'Matched past case study / project proof',
            ],
            'AI Provider' => [
                '{{ provider.name }}' => 'Current provider identifier (e.g. gemini, openai)',
                '{{ provider.model }}' => 'Model name used for generation',
            ],
            'Mustache Conditional & Loop Syntax' => [
                '{{#job.budget}}...{{/job.budget}}' => 'Conditional block: renders only if budget is available',
                '{{^job.budget}}...{{/job.budget}}' => 'Inverted block: renders if budget is empty/missing',
                '{{#freelancer.skills_list}}...{{/freelancer.skills_list}}' => 'Loop through list of matched skills',
                '{{! comment }}' => 'Comment tag (not included in output)',
            ],
        ];
    }
}
