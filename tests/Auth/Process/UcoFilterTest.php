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

namespace Tests\SimpleSAML\Modules\UcoFilter\Auth\Process;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Modules\UcoFilter\Auth\Process\UcoFilter;

class UcoFilterTest extends TestCase
{
    /**
     * @test
     */
    public function it_maps_attributes()
    {
        \SimpleSAML_Configuration::loadFromArray([], '', 'simplesaml');

        $config['mapping'] = [
            'eduPersonPrincipalName' => [
                'mail[0]',
                '"multievaluated"' => true,
            ],
            'schacPersonalUniqueCode' => '"urn:schac:personalUniqueCode:es:rediris:sir:mbid:{sha1}:"~sha1(mail[0])',
            'eduPersonEntitlement' => [
                '"urn:mace:dir:entitlement:common-lib-terms"' => '"username" in attributes["uid"]',
            ],
        ];

        $request['Attributes']['mail'][] = 'test@mail.com';
        $request['Attributes']['uid'][] = 'username';

        $filter = new UcoFilter($config, null);
        $filter->process($request);

        $this->assertArrayHasKey('eduPersonPrincipalName', $request['Attributes']);
        $this->assertContains('test@mail.com', $request['Attributes']['eduPersonPrincipalName']);
        $this->assertContains('multievaluated', $request['Attributes']['eduPersonPrincipalName']);

        $this->assertArrayHasKey('schacPersonalUniqueCode', $request['Attributes']);
        $this->assertContains('urn:schac:personalUniqueCode:es:rediris:sir:mbid:{sha1}:'.sha1('test@mail.com'), $request['Attributes']['schacPersonalUniqueCode']);

        $this->assertArrayHasKey('eduPersonEntitlement', $request['Attributes']);
        $this->assertContains('urn:mace:dir:entitlement:common-lib-terms', $request['Attributes']['eduPersonEntitlement']);
    }

    /**
     * @test
     */
    public function it_maps_attributes_when_condition_is_true()
    {
        \SimpleSAML_Configuration::loadFromArray(['debug' => true], '', 'simplesaml');

        $config['mapping'] = [
            'schacPersonalUniqueCode' => [
                '"urn:schac:personalUniqueCode:es:rediris:sir:mbid:{sha1}:"~sha1(mail[0])' => [
                    '"https://idp/sp" in request["saml:RequesterID"]',
                ],
            ],
        ];

        $request['Attributes']['mail'][] = 'test@mail.com';
        $request['saml:RequesterID'][] = 'https://idp/sp';

        $filter = new UcoFilter($config, null);
        $filter->process($request);

        $this->assertArrayHasKey('schacPersonalUniqueCode', $request['Attributes']);
        $this->assertContains('urn:schac:personalUniqueCode:es:rediris:sir:mbid:{sha1}:'.sha1('test@mail.com'), $request['Attributes']['schacPersonalUniqueCode']);
    }

    /**
     * @test
     */
    public function it_does_not_maps_attributes_when_condition_is_false()
    {
        \SimpleSAML_Configuration::loadFromArray([], '', 'simplesaml');

        $config['mapping'] = [
            'schacPersonalUniqueCode' => [
                '"urn:schac:personalUniqueCode:es:rediris:sir:mbid:{sha1}:"~sha1(mail[0])' => [
                    '"https://idp/sp" in request["saml:RequesterID"]',
                ],
            ],
        ];

        $request['Attributes']['mail'][] = 'test@mail.com';
        $request['saml:RequesterID'][] = 'https://other-idp/sp';

        $filter = new UcoFilter($config, null);
        $filter->process($request);

        $this->assertArrayNotHasKey('eduPersonPrincipalName', $request['Attributes']);
        $this->assertArrayNotHasKey('schacPersonalUniqueCode', $request['Attributes']);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage No mapping field specified in configuration
     */
    public function it_throw_exception_when_mapping_is_missing()
    {
        \SimpleSAML_Configuration::loadFromArray([], '', 'simplesaml');

        $request['Attributes']['mail'][] = 'test@mail.com';

        $filter = new UcoFilter([], null);
        $filter->process($request);
    }
}
