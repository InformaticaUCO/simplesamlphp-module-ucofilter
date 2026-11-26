<?php

declare(strict_types=1);

namespace SimpleSAML\Module\ucofilter\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class AttributeExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('in_attribute', static function ($from, $to): string {
                return sprintf(
                    '(is_array(%1$s) && is_array(%2$s) && !empty(array_intersect(%1$s, %2$s)))',
                    $from,
                    $to
                );
            }, static function ($args, $from, $to): bool {
                if (!is_array($from) || !is_array($to)) {
                    return false;
                }

                foreach ($from as $value) {
                    if (in_array($value, $to, true)) {
                        return true;
                    }
                }

                return false;
            }),
        ];
    }
}
