<?php

namespace SMSkin\LaravelSaga\Support\DatabaseRepository;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Contracts\ISagaRepository;
use SMSkin\LaravelSaga\Exceptions\SagaContextNotFound;
use SMSkin\LaravelSaga\Exceptions\SagaWithThisIdAlreadyExists;
use SMSkin\LaravelSaga\Models\SagaContext;

class DatabaseRepository implements ISagaRepository
{
    /**
     * @throws SagaWithThisIdAlreadyExists
     */
    public function create(BaseSaga $saga, SagaContext $context): void
    {
        $model = app(Saga::class);
        $model->setAttribute('uuid', $context->getId());
        $model->setAttribute('class', get_class($saga));
        $model->setAttribute('context_class', get_class($context));
        $model->setAttribute('context_value', $context->toArray());

        try {
            $model->save();
        } catch (QueryException $exception) {
            if ($exception->getCode() == 23000) {
                throw new SagaWithThisIdAlreadyExists();
            }
            throw $exception;
        }
    }

    /**
     * @throws SagaContextNotFound
     */
    public function getById(BaseSaga $saga, string $id): SagaContext
    {
        try {
            $context = $this->getSagaById(get_class($saga), $id);
        } catch (ModelNotFoundException) {
            throw new SagaContextNotFound();
        }
        return $this->createSagaContext($context);
    }

    /**
     * @throws SagaContextNotFound
     */
    public function getByField(BaseSaga $saga, string $field, string|int|float $value): SagaContext
    {
        try {
            $context = $this->getSagaByField(get_class($saga), $field, $value);
        } catch (ModelNotFoundException) {
            throw new SagaContextNotFound();
        }

        return $this->createSagaContext($context);
    }

    private function createSagaContext(Saga $saga): SagaContext
    {
        $className = $saga->getAttribute('context_class');
        return (new $className($saga->getAttribute('uuid')))->fromArray($saga->getAttribute('context_value'));
    }

    /**
     * @throws SagaContextNotFound
     */
    public function update(BaseSaga $saga, SagaContext $context): void
    {
        try {
            $model = $this->getSagaById(get_class($saga), $context->getId());
        } catch (ModelNotFoundException) {
            throw new SagaContextNotFound();
        }

        $model->setAttribute('context_value', $context->toArray());
        $model->save();
    }

    /**
     * @throws ModelNotFoundException
     */
    private function getSagaById(string $class, string $id): Saga
    {
        return app(Saga::class)->where('class', $class)->where('uuid', $id)->firstOrFail();
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getSagaByField(string $class, string $field, string|int|float $value): Saga
    {
        return app(Saga::class)->where('class', $class)->whereJsonContains('context_value->' . $field, $value)->firstOrFail();
    }
}
