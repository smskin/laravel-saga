<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\Mocks;

use Mockery;
use Mockery\MockInterface;
use SMSkin\LaravelSaga\Contracts\ISagaRepository;

trait SagaRepositoryMock
{
    private MockInterface $sagaRepositoryMock;

    public function setUpDatabaseRepositoryMock(): void
    {
        $this->sagaRepositoryMock = Mockery::mock(ISagaRepository::class);
        $this->app->instance(ISagaRepository::class, $this->sagaRepositoryMock);
    }

    public function getSagaRepositoryMock(): MockInterface
    {
        return $this->sagaRepositoryMock;
    }
}
