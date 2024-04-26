<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\Support\DatabaseRepository;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use RuntimeException;
use SMSkin\LaravelSaga\Enums\SagaState;
use SMSkin\LaravelSaga\Exceptions\SagaContextNotFound;
use SMSkin\LaravelSaga\Exceptions\SagaWithThisIdAlreadyExists;
use SMSkin\LaravelSaga\Support\DatabaseRepository\DatabaseRepository;
use SMSkin\LaravelSaga\Support\DatabaseRepository\Saga;
use SMSkin\LaravelSaga\Tests\TestCase;
use SMSkin\LaravelSaga\Tests\TestSupport\TestSagaContext;

class DatabaseRepositoryTest extends TestCase
{
    public function testCreate()
    {
        $saga = $this->createTestSaga();
        $context = new TestSagaContext(Str::uuid()->toString());
        $context->transitionTo(SagaState::INITIAL);

        $repository = new DatabaseRepository();

        $this->getSagaDatabaseModel()
            ->byDefault()
            ->shouldReceive('setAttribute', 'save');

        /** @noinspection PhpUnhandledExceptionInspection */
        $repository->create($saga, $context);

        $this->getSagaDatabaseModel()
            ->byDefault()
            ->shouldHaveReceived('save');
    }

    public function testCreateNotUniqueConstraint()
    {
        $saga = $this->createTestSaga();
        $context = new TestSagaContext(Str::uuid()->toString());
        $context->transitionTo(SagaState::INITIAL);

        $repository = new DatabaseRepository();

        $this->getSagaDatabaseModel()
            ->byDefault()
            ->shouldReceive('setAttribute');

        $this->getSagaDatabaseModel()
            ->byDefault()
            ->shouldReceive('save')
            ->andThrows(new QueryException('a', 'a', [], new RuntimeException('Duplicate entry \'' . $context->getId() . '\' for key \'PRIMARY\'', 23000)));

        $this->expectException(SagaWithThisIdAlreadyExists::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $repository->create($saga, $context);
    }

    public function testGetById()
    {
        $saga = $this->createTestSaga();
        $id = Str::uuid()->toString();
        $context = new TestSagaContext($id);
        $context->transitionTo(SagaState::INITIAL);

        $repository = new DatabaseRepository();

        $model = new Saga();
        $model->setAttribute('uuid', $id);
        $model->setAttribute('class', get_class($saga));
        $model->setAttribute('context_class', get_class($context));
        $model->setAttribute('context_value', $context->toArray());

        $this
            ->sagaDatabaseModelModelByDefault()
            ->sagaDatabaseModelModelShouldReceiveWhere()
            ->sagaDatabaseModelModelShouldReceiveFirstOrFail($model);

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = $repository->getById($saga, $id);

        $this->assertInstanceOf(TestSagaContext::class, $result);
    }

    public function testGetByIdSagaContextNotFound()
    {
        $saga = $this->createTestSaga();
        $id = Str::uuid()->toString();
        $repository = new DatabaseRepository();

        $this
            ->sagaDatabaseModelModelByDefault()
            ->sagaDatabaseModelModelShouldReceiveWhere()
            ->getSagaDatabaseModel()
            ->shouldReceive('firstOrFail')
            ->andThrows(new ModelNotFoundException());

        $this->expectException(SagaContextNotFound::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $repository->getById($saga, $id);
    }

    public function testGetByField()
    {
        $saga = $this->createTestSaga();
        $id = Str::uuid()->toString();
        $context = new TestSagaContext($id);
        $context->transitionTo(SagaState::INITIAL);

        $repository = new DatabaseRepository();

        $model = new Saga();
        $model->setAttribute('uuid', $id);
        $model->setAttribute('class', get_class($saga));
        $model->setAttribute('context_class', get_class($context));
        $model->setAttribute('context_value', $context->toArray());

        $this
            ->sagaDatabaseModelModelByDefault()
            ->sagaDatabaseModelModelShouldReceiveWhere()
            ->sagaDatabaseModelModelShouldReceiveWhereJsonContains()
            ->sagaDatabaseModelModelShouldReceiveFirstOrFail($model);

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = $repository->getByField($saga, 'id', $id);
        $this->assertInstanceOf(TestSagaContext::class, $result);
    }

    public function testGetByFieldSagaContextNotFound()
    {
        $saga = $this->createTestSaga();
        $id = Str::uuid()->toString();
        $repository = new DatabaseRepository();

        $this
            ->sagaDatabaseModelModelByDefault()
            ->sagaDatabaseModelModelShouldReceiveWhere()
            ->sagaDatabaseModelModelShouldReceiveWhereJsonContains()
            ->getSagaDatabaseModel()
            ->shouldReceive('firstOrFail')
            ->andThrows(new ModelNotFoundException());

        $this->expectException(SagaContextNotFound::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $repository->getByField($saga, 'id', $id);
    }

    public function testUpdate()
    {
        $saga = $this->createTestSaga();
        $id = Str::uuid()->toString();
        $context = new TestSagaContext($id);
        $context->transitionTo(SagaState::INITIAL);

        $repository = new DatabaseRepository();

        $this
            ->sagaDatabaseModelModelByDefault()
            ->sagaDatabaseModelModelShouldReceiveWhere()
            ->sagaDatabaseModelModelShouldReceiveFirstOrFail(app(Saga::class))
            ->sagaDatabaseModelModelShouldReceiveSetAttribute()
            ->sagaDatabaseModelModelShouldReceiveSave();

        /** @noinspection PhpUnhandledExceptionInspection */
        $repository->update($saga, $context);

        $this->getSagaDatabaseModel()
            ->byDefault()
            ->shouldHaveReceived('save');
    }

    public function testUpdateSagaContextNotFound()
    {
        $saga = $this->createTestSaga();
        $context = new TestSagaContext(Str::uuid()->toString());
        $repository = new DatabaseRepository();

        $this
            ->sagaDatabaseModelModelByDefault()
            ->sagaDatabaseModelModelShouldReceiveWhere()
            ->getSagaDatabaseModel()
            ->shouldReceive('firstOrFail')
            ->andThrows(new ModelNotFoundException());

        $this->expectException(SagaContextNotFound::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $repository->update($saga, $context);
    }
}
