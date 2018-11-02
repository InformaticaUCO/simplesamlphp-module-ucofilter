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

namespace Tests\SimpleSAML\Modules\UcoFilter\ExpressionLanguage;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Modules\UcoFilter\ExpressionLanguage\AttributeExpressionLanguageProvider;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class AttributeExpressionLanguageProviderTest extends TestCase
{
    /**
     * @test
     */
    public function it_check_attribute_contains_value()
    {
        $expressionLanguage = new ExpressionLanguage(null, [new AttributeExpressionLanguageProvider()]);

        $this->assertTrue($expressionLanguage->evaluate('in_attribute(["external", "staff"], ["student", "staff", "faculty"])'));
        $this->assertFalse($expressionLanguage->evaluate('in_attribute(["external", "staff"], ["one", "two", "three"])'));
    }
}
