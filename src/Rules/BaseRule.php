<?php

namespace Yepteam\Typograph\Rules;

use Yepteam\Typograph\Helpers\TokenHelper;

class BaseRule {

    public static function apply(int $index, array &$tokens, array $options): void
    {

    }

    public static function logRule(array &$token, string $rule, bool $applied = true): void
    {
        if(empty($options['debug'])){
            return;
        }

        TokenHelper::logRule($token, $rule, $applied);
    }

}