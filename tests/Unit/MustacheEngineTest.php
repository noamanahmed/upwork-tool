<?php

use App\Services\Template\MustacheEngine;

test('it renders simple variables', function () {
    $template = 'Hello {{ name }}, welcome to {{ place }}!';
    $result = MustacheEngine::render($template, [
        'name' => 'Alice',
        'place' => 'Wonderland',
    ]);

    expect($result)->toBe('Hello Alice, welcome to Wonderland!');
});

test('it renders empty string for missing variables without errors', function () {
    $template = 'Hello {{ name }}, your role is {{ role }}.';
    $result = MustacheEngine::render($template, [
        'name' => 'Bob',
    ]);

    expect($result)->toBe('Hello Bob, your role is .');
});

test('it resolves dot notation properties', function () {
    $template = 'Job: {{ job.title }} (Budget: {{ job.budget.amount }} {{ job.budget.currency }})';
    $result = MustacheEngine::render($template, [
        'job' => [
            'title' => 'Senior Laravel Engineer',
            'budget' => [
                'amount' => 5000,
                'currency' => 'USD',
            ],
        ],
    ]);

    expect($result)->toBe('Job: Senior Laravel Engineer (Budget: 5000 USD)');
});

test('it preserves literal special characters for LLMs without HTML escaping', function () {
    $template = 'Requirements: {{ job.requirements }} & {{{ job.raw_code }}}';
    $result = MustacheEngine::render($template, [
        'job' => [
            'requirements' => 'PHP 8.3 & Vue.js > 3.0 < 4.0 "best"',
            'raw_code' => '<div>Test</div>',
        ],
    ]);

    expect($result)->toBe('Requirements: PHP 8.3 & Vue.js > 3.0 < 4.0 "best" & <div>Test</div>');
});

test('it handles truthy and falsy section blocks', function () {
    $template = '{{#has_budget}}Budget: {{ budget }}{{/has_budget}}{{#has_deadline}}Deadline: {{ deadline }}{{/has_deadline}}';
    $result = MustacheEngine::render($template, [
        'has_budget' => true,
        'budget' => '$1,000',
        'has_deadline' => false,
        'deadline' => 'Tomorrow',
    ]);

    expect($result)->toBe('Budget: $1,000');
});

test('it handles inverted section blocks', function () {
    $template = '{{^budget}}No budget specified{{/budget}}{{#budget}}Budget: {{ budget }}{{/budget}}';

    $resultWithoutBudget = MustacheEngine::render($template, ['budget' => null]);
    expect($resultWithoutBudget)->toBe('No budget specified');

    $resultWithBudget = MustacheEngine::render($template, ['budget' => '$250']);
    expect($resultWithBudget)->toBe('Budget: $250');
});

test('it loops over sequential scalar array using dot or this', function () {
    $template = "Skills:\n{{#skills}}- {{.}}\n{{/skills}}";
    $result = MustacheEngine::render($template, [
        'skills' => ['Laravel', 'Vue.js', 'TailwindCSS'],
    ]);

    expect($result)->toBe("Skills:\n- Laravel\n- Vue.js\n- TailwindCSS\n");
});

test('it loops over array of associative items', function () {
    $template = '{{#projects}}[{{ title }} -> {{ result }}] {{/projects}}';
    $result = MustacheEngine::render($template, [
        'projects' => [
            ['title' => 'Project A', 'result' => '10x scale'],
            ['title' => 'Project B', 'result' => '50% faster'],
        ],
    ]);

    expect(trim($result))->toBe('[Project A -> 10x scale] [Project B -> 50% faster]');
});

test('it ignores comments in template', function () {
    $template = 'Hello {{! secret note }}world!';
    $result = MustacheEngine::render($template, []);

    expect($result)->toBe('Hello world!');
});

test('it extracts tag names from template', function () {
    $template = 'Job: {{ job.title }}, Budget: {{#has_budget}}{{ job.budget }}{{/has_budget}}, {{^missing}}fallback{{/missing}}';
    $tags = MustacheEngine::extractTags($template);

    expect($tags)->toContain('job.title', 'has_budget', 'job.budget', 'missing');
});

test('it provides available dynamic tags documentation', function () {
    $docs = MustacheEngine::getAvailableTags();

    expect($docs)->toBeArray()
        ->toHaveKeys(['Job Information', 'Client Details', 'Job Insights (Auto-Analyzed)', 'Freelancer Profile Context', 'AI Provider']);
});
