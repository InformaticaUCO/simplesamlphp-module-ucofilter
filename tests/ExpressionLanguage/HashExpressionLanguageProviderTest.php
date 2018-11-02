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
use SimpleSAML\Modules\UcoFilter\ExpressionLanguage\HashExpressionLanguageProvider;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class HashExpressionLanguageProviderTest extends TestCase
{
    /**
     * @test
     */
    public function it_check_hashes_functions()
    {
        $expressionLanguage = new ExpressionLanguage(null, [new HashExpressionLanguageProvider()]);

        $this->assertEquals(md5('foo'), $expressionLanguage->evaluate('md5("foo")'));
        $this->assertEquals(sha1('foo'), $expressionLanguage->evaluate('sha1("foo")'));
    }
}
