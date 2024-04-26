<?php

namespace SMSkin\LaravelSaga\Example;

use SMSkin\LaravelSaga\Models\SagaContext;

class SagaExampleContext extends SagaContext
{
    protected string $email;
    public int|null $userId = null;

    public function getUserId(): int|null
    {
        return $this->userId;
    }

    public function setUserId(int|null $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'email' => $this->email,
            'userId' => $this->userId,
        ]);
    }

    public function fromArray(array $data): static
    {
        $this->email = $data['email'];
        $this->userId = $data['userId'];

        return parent::fromArray($data);
    }
}
