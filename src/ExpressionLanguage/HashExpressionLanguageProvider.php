<?php

declare(strict_types=1);

namespace SimpleSAML\Module\ucofilter\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class HashExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('sha1', static function ($str): string {
                return sprintf('(sha1(%1$s))', $str);
            }, static function ($args, $str): string {
                return sha1((string) $str);
            }),
            new ExpressionFunction('md5', static function ($str): string {
                return sprintf('(is_string(%1$s) ? md5(%1$s) : %1$s)', $str);
            }, static function ($args, $str) {
                return is_string($str) ? md5($str) : $str;
            }),
        ];
    }
}
