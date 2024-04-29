<?php

declare(strict_types=1);

namespace Square\Models\Builders;

use Core\Utils\CoreHelper;
use Square\Models\ListTransactionsResponse;

/**
 * Builder for model ListTransactionsResponse
 *
 * @see ListTransactionsResponse
 */
class ListTransactionsResponseBuilder
{
    /**
     * @var ListTransactionsResponse
     */
    private $instance;

    private function __construct(ListTransactionsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new list transactions response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListTransactionsResponse());
    }

    /**
     * Sets errors field.
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Sets transactions field.
     */
    public function transactions(?array $value): self
    {
        $this->instance->setTransactions($value);
        return $this;
    }

    /**
     * Sets cursor field.
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
        return $this;
    }

    /**
     * Initializes a new list transactions response object.
     */
    public function build(): ListTransactionsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
