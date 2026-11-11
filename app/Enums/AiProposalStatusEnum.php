<?php

namespace App\Enums;

enum AiProposalStatusEnum: string
{
    case PENDING = 'pending';
    case QUEUED = 'queued';
    case GENERATING = 'generating';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    /**
     * Statuses that mean a worker is still responsible for the row.
     */
    public static function inFlight(): array
    {
        return [self::QUEUED->value, self::GENERATING->value];
    }

    /**
     * Statuses that should not trigger a new dispatch.
     */
    public static function dispatched(): array
    {
        return [self::QUEUED->value, self::GENERATING->value, self::COMPLETED->value];
    }
}
