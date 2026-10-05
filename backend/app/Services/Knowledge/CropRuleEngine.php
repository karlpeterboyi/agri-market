<?php

namespace App\Services\Knowledge;

use App\Models\CropRule;

class CropRuleEngine
{
    public function evaluate(array $context): array
    {
        $rules = CropRule::query()
            ->where('crop_id', $context['crop_id'])
            ->where('enabled', true)
            ->orderByDesc('priority')
            ->get();

        $recommendations = [];

        foreach ($rules as $rule) {

            if ($this->matches($rule, $context)) {

                $recommendations[] = [
                    'rule_id' => $rule->id,
                    'type' => $rule->rule_type,
                    'actions' => $rule->actions,
                    'priority' => $rule->priority,
                ];
            }
        }

        return $recommendations;
    }

    protected function matches(CropRule $rule, array $context): bool
    {
        foreach ($rule->conditions as $field => $value) {

            if (!array_key_exists($field, $context)) {
                return false;
            }

            if ($context[$field] != $value) {
                return false;
            }
        }

        return true;
    }
}