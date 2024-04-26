<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\TestSupport;

use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Contracts\ISagaRepository;
use SMSkin\LaravelSaga\Models\SagaContext;

class TestRepository implements ISagaRepository
{
    public function create(BaseSaga $saga, SagaContext $context): void
    {
    }

    public function getById(BaseSaga $saga, string $id): SagaContext
    {
        return new SagaContext($id);
    }

    public function getByField(BaseSaga $saga, string $field, string $value): SagaContext
    {
        return new SagaContext($value);
    }

    public function update(BaseSaga $saga, SagaContext $context): void
    {
    }
}
