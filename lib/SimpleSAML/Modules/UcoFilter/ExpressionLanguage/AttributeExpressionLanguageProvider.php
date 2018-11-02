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

class AttributeExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new ExpressionFunction('in_attribute', function ($from, $to) {
                return sprintf('
                foreach (%1$s as $value) {
                    if (\in_array($value, %2$s, true)) {
                        return true;
                    }
                }
                
                return false;
                ', $from, $to);
            }, function ($args, $from, $to) {
                foreach ($from as $value) {
                    if (\in_array($value, $to, true)) {
                        return true;
                    }
                }

                return false;
            }),
        ];
    }
}
