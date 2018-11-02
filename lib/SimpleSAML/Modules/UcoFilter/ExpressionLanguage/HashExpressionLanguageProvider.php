<?php

/*
 * This file is part of the simplesamlphp-module-ucofilter.
 *
 * Copyright (C) 2018 by Sergio Gómez <sergio@uco.es>
 *
 * This code was developed by Universidad de Córdoba (UCO https://www.uco.es)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleSAML\Modules\UcoFilter\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class HashExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new ExpressionFunction('sha1', function ($str) {
                return sprintf('(sha1(%1$s))', $str);
            }, function ($args, $str) {
                return sha1($str);
            }),
            new ExpressionFunction('md5', function ($str) {
                return sprintf('(is_string(%1$s) ? md5(%1$s) : %1$s)', $str);
            }, function ($args, $str) {
                if (!is_string($str)) {
                    return $str;
                }

                return md5($str);
            }),
        ];
    }
}
