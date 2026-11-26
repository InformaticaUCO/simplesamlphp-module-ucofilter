<?php

declare(strict_types=1);

namespace SimpleSAML\Module\ucofilter\Auth\Process;

use SimpleSAML\Auth\ProcessingFilter;
use SimpleSAML\Logger;
use SimpleSAML\Module\ucofilter\ExpressionLanguage\AttributeExpressionLanguageProvider;
use SimpleSAML\Module\ucofilter\ExpressionLanguage\HashExpressionLanguageProvider;
use SimpleSAML\Module\ucofilter\ExpressionLanguage\SafeAttributes;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class UcoFilter extends ProcessingFilter
{
    protected ExpressionLanguage $language;

    /** @var array<int, string|bool> */
    protected array $rules = ['true'];

    /** @var string[] */
    protected array $reset = [];

    /** @var array<string, mixed> */
    protected array $mapping = [];

    /**
     * @param array<string, mixed> &$config
     * @param mixed $reserved
     */
    public function __construct(array &$config, $reserved)
    {
        parent::__construct($config, $reserved);

        $this->language = new ExpressionLanguage(null, [
            new AttributeExpressionLanguageProvider(),
            new HashExpressionLanguageProvider(),
        ]);

        if (array_key_exists('rules', $config)) {
            if (!is_array($config['rules'])) {
                throw new \InvalidArgumentException('Rules option must be an array');
            }
            $this->rules = $config['rules'];
        }

        if (array_key_exists('reset', $config)) {
            if (!is_array($config['reset'])) {
                throw new \InvalidArgumentException('Reset option must be an array');
            }
            $this->reset = $config['reset'];
        }

        if (!array_key_exists('mapping', $config) || !is_array($config['mapping'])) {
            throw new \InvalidArgumentException('No mapping field specified in configuration');
        }
        $this->mapping = $config['mapping'];
    }

    /**
     * @param array<string, mixed> &$state
     */
    public function process(array &$state): void
    {
        if (!array_key_exists('Attributes', $state) || !is_array($state['Attributes'])) {
            return;
        }

        $attributes = &$state['Attributes'];

        $mustBeExecuted = $this->checkConditions($this->rules, [
            'request' => new SafeAttributes($state),
            'attributes' => new SafeAttributes($attributes),
        ]);
        if (!$mustBeExecuted) {
            return;
        }

        foreach ($this->reset as $attribute) {
            unset($attributes[$attribute]);
        }

        foreach ($this->mapping as $attribute => $rules) {
            if (!is_array($rules)) {
                $rules = [$rules];
            }

            foreach ($rules as $expression => $conditions) {
                if (!is_string($expression)) {
                    $expression = $conditions;
                    $conditions = true;
                }

                try {
                    $value = $this->language->evaluate((string) $expression, $attributes);
                    Logger::debug(sprintf('[UcoFilter] expression ["%s"] value: %s', (string) $expression, (string) $value));
                } catch (\Throwable $e) {
                    Logger::debug(sprintf('[UcoFilter] expression ["%s"] skipped: %s', (string) $expression, $e->getMessage()));
                    continue;
                }

                $mustBeProcessed = $this->checkConditions($conditions, [
                    'request' => new SafeAttributes($state),
                    'attributes' => new SafeAttributes($attributes),
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
     * @param mixed $conditions
     * @param array<string, mixed> $values
     */
    private function checkConditions(mixed $conditions, array $values): bool
    {
        if (!is_array($conditions)) {
            $conditions = [$conditions];
        }

        foreach ($conditions as $condition) {
            try {
                if ($condition === true || $this->language->evaluate((string) $condition, $values) === true) {
                    return true;
                }
            } catch (\Throwable $e) {
                Logger::debug(sprintf('[UcoFilter] condition ["%s"] skipped: %s', (string) $condition, $e->getMessage()));
            }
        }

        return false;
    }
}
