<?php

namespace App\Data\Finance;

class LoanRecommendation
{
    public function __construct(

        public mixed $loan,

        public int $approvalProbability,

        public array $reasons,

        public array $improvements

    ) {}
}