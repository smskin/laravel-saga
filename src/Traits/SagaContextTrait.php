<?php

namespace SMSkin\LaravelSaga\Traits;

use RuntimeException;
use SMSkin\LaravelSaga\Exceptions\SagaContextNotFound;
use SMSkin\LaravelSaga\Models\SagaContext;

trait SagaContextTrait
{
    protected SagaContext $context;

    public function saveContext(): void
    {
        try {
            $this->getRepository()->update($this, $this->context);
        } catch (SagaContextNotFound $exception) {
            throw new RuntimeException($exception->getMessage(), 500, $exception);
        }
    }

    public function getContext(): SagaContext
    {
        return $this->context;
    }
}
