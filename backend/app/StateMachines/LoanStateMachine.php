<?php

namespace App\StateMachines;

use InvalidArgumentException;

/**
 * Loan application lifecycle transitions.
 *
 * draft → submitted → under_review → approved → disbursed → active → completed
 *                              ↘ rejected
 *                     approved ↘ rejected (rare)
 */
class LoanStateMachine
{
    public const TRANSITIONS = [
        'draft' => ['submitted', 'cancelled'],
        'submitted' => ['under_review', 'approved', 'rejected', 'cancelled'],
        'under_review' => ['approved', 'rejected'],
        'approved' => ['disbursed', 'rejected', 'cancelled'],
        'disbursed' => ['active', 'completed'],
        'active' => ['completed', 'defaulted'],
        'rejected' => [],
        'cancelled' => [],
        'completed' => [],
        'defaulted' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        $from = strtolower(trim($from));
        $to = strtolower(trim($to));

        if ($from === $to) {
            return true;
        }

        $allowed = self::TRANSITIONS[$from] ?? null;
        if ($allowed === null) {
            // Unknown current status: allow moving to any known target once
            return array_key_exists($to, self::TRANSITIONS);
        }

        return in_array($to, $allowed, true);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function assert(string $from, string $to): void
    {
        if (! self::canTransition($from, $to)) {
            throw new InvalidArgumentException(
                "Invalid loan status transition from [{$from}] to [{$to}]."
            );
        }
    }

    public static function allowedFrom(string $from): array
    {
        return self::TRANSITIONS[strtolower(trim($from))] ?? [];
    }
}
