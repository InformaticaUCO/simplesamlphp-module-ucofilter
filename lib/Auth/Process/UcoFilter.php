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

namespace SimpleSAML\Modules\UcoFilter\Auth\Process;

use SimpleSAML\Modules\UcoFilter\ExpressionLanguage\AttributeExpressionLanguageProvider;
use SimpleSAML\Modules\UcoFilter\ExpressionLanguage\HashExpressionLanguageProvider;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Webmozart\Assert\Assert;

class UcoFilter extends \SimpleSAML_Auth_ProcessingFilter
{
    /**
     * @var ExpressionLanguage
     */
    protected $language;

    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * @var bool|array
     */
    protected $reset = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config, $reserved)
    {
        parent::__construct($config, $reserved);

        $this->language = new ExpressionLanguage(null, [
            new AttributeExpressionLanguageProvider(),
            new HashExpressionLanguageProvider(),
        ]);

        Assert::keyExists($config, 'mapping', 'No mapping field specified in configuration');
        $this->mapping = $config['mapping'];

        if (array_key_exists('reset', $config)) {
            Assert::isArray($config['reset'], 'Reset option must be an array');
            $this->reset = $config['reset'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(&$request)
    {
        Assert::isArray($request);
        Assert::keyExists($request, 'Attributes');

        $attributes = &$request['Attributes'];

        $mustBeReseted = $this->checkConditions($this->reset, [
            'request' => $request,
            'attributes' => $attributes,
        ]);

        foreach ($this->mapping as $attribute => $rules) {
            if ($mustBeReseted) {
                unset($attributes[$attribute]);
            }

            if (!is_array($rules)) {
                $rules = [$rules];
            }

            foreach ($rules as $expression => $conditions) {
                if (!is_string($expression)) {
                    $expression = $conditions;
                    $conditions = true;
                }

                try {
                    $value = $this->language->evaluate($expression, $attributes);
                    \SimpleSAML\Logger::debug(sprintf('[UcoFilter] expresion ["%s"] value: %s', $expression, $value));
                } catch (SyntaxError $e) {
                    throw \SimpleSAML_Error_Error::fromException($e);
                }

                $mustBeProcessed = $this->checkConditions($conditions, [
                    'request' => $request,
                    'attributes' => $attributes,
                    'value' => $value,
                ]);
                if (!$mustBeProcessed) {
                    continue;
                }

                $attributes[$attribute][] = $value;
            }
        }
    }

    /**
     * @param $request
     *
     * @return bool
     */
    private function checkConditions($conditions, $values)
    {
        if (!is_array($conditions)) {
            $conditions = [$conditions];
        }

        foreach ($conditions as $condition) {
            try {
                if (true === $condition || true === $this->language->evaluate($condition, $values)) {
                    \SimpleSAML\Logger::debug('UcoFilter: Valid condition: '.$condition);

                    return true;
                }
            } catch (SyntaxError $e) {
                \SimpleSAML\Logger::debug(sprintf('Filter condition ["%s"] syntax error: %s', $condition, $e->getMessage()));
            }
        }

        return false;
    }
}
