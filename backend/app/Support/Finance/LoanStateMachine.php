<?php

namespace App\Support\Finance;

class LoanStateMachine
{
    private const TRANSITIONS = [

        'draft' => [
            'submitted'
        ],

        'submitted' => [
            'under_review'
        ],

        'under_review' => [
            'approved',
            'rejected',
        ],

        'approved' => [
            'disbursed'
        ],

        'disbursed' => [
            'completed'
        ],

        'completed' => [],

        'rejected' => [],
    ];

    public static function canTransition(
        string $from,
        string $to
    ): bool {

        return in_array(
            $to,
            self::TRANSITIONS[$from] ?? []
        );
    }

    public static function assert(
        string $from,
        string $to
    ): void {

        if (!self::canTransition($from, $to)) {

            throw new \DomainException(
                "Invalid workflow transition: {$from} → {$to}"
            );

        }
    }
}