<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\Mocks;

use Mockery;
use Mockery\MockInterface;
use SMSkin\LaravelSaga\Support\DatabaseRepository\Saga;

trait SagaDatabaseModelMock
{
    private MockInterface $sagaDatabaseModel;

    public function setUpSagaDatabaseModelMock(): void
    {
        $this->sagaDatabaseModel = Mockery::mock(Saga::class);
        $this->app->instance(Saga::class, $this->sagaDatabaseModel);
    }

    public function sagaDatabaseModelModelByDefault(): static
    {
        $this->sagaDatabaseModel->byDefault();
        return $this;
    }

    public function sagaDatabaseModelModelShouldReceiveWhere(): static
    {
        $this->getSagaDatabaseModel()
            ->shouldReceive('where')
            ->andReturnSelf();
        return $this;
    }

    public function sagaDatabaseModelModelShouldReceiveWhereJsonContains(): static
    {
        $this->getSagaDatabaseModel()
            ->shouldReceive('whereJsonContains')
            ->andReturnSelf();
        return $this;
    }

    public function sagaDatabaseModelModelShouldReceiveSetAttribute(): static
    {
        $this->getSagaDatabaseModel()
            ->shouldReceive('setAttribute')
            ->andReturnSelf();
        return $this;
    }

    public function sagaDatabaseModelModelShouldReceiveSave(): static
    {
        $this->getSagaDatabaseModel()
            ->shouldReceive('save')
            ->andReturnSelf();
        return $this;
    }

    public function sagaDatabaseModelModelShouldReceiveFirstOrFail(Saga $saga): static
    {
        $this->getSagaDatabaseModel()
            ->shouldReceive('firstOrFail')
            ->andReturnUsing(static function () use ($saga) {
                return $saga;
            });
        return $this;
    }

    public function getSagaDatabaseModel(): MockInterface
    {
        return $this->sagaDatabaseModel;
    }
}
