<?php

namespace SMSkin\LaravelSaga\Models;

use BackedEnum;

class SagaContext
{
    protected string $state;

    final public function __construct(protected string $id)
    {
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function transitionTo(BackedEnum $state): static
    {
        $this->state = $state->value;
        return $this;
    }

    public function fromArray(array $data): static
    {
        $this->state = $data['state'];
        return $this;
    }

    public function toArray(): array
    {
        return [
            'state' => $this->state,
        ];
    }
}
