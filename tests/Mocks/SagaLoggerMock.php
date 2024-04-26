<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\Mocks;

use Mockery;
use Mockery\MockInterface;
use SMSkin\LaravelSaga\Contracts\ISagaLogger;

trait SagaLoggerMock
{
    private MockInterface $sagaLoggerMock;

    public function setUpSagaLoggerMock(): void
    {
        $this->sagaLoggerMock = Mockery::mock(ISagaLogger::class);
        $this->app->instance(ISagaLogger::class, $this->sagaLoggerMock);
    }

    public function getSagaLoggerMock(): MockInterface
    {
        return $this->sagaLoggerMock;
    }
}
