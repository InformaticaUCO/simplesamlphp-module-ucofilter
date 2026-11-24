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

class SafeAttributes implements \ArrayAccess
{
    /**
     * @var array
     */
    private $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return isset($this->attributes[$offset]) ? $this->attributes[$offset] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
    }
}
