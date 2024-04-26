<?php

namespace SMSkin\LaravelSaga\Contracts;

use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Exceptions\SagaContextNotFound;
use SMSkin\LaravelSaga\Exceptions\SagaWithThisIdAlreadyExists;
use SMSkin\LaravelSaga\Models\SagaContext;

interface ISagaRepository
{
    /**
     * @throws SagaWithThisIdAlreadyExists
     */
    public function create(BaseSaga $saga, SagaContext $context): void;

    /**
     * @throws SagaContextNotFound
     */
    public function getById(BaseSaga $saga, string $id): SagaContext;

    /**
     * @throws SagaContextNotFound
     */
    public function getByField(BaseSaga $saga, string $field, string $value): SagaContext;

    /**
     * @throws SagaContextNotFound
     */
    public function update(BaseSaga $saga, SagaContext $context): void;
}
