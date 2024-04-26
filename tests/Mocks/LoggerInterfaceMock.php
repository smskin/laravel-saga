<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\Mocks;

use Mockery;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;

trait LoggerInterfaceMock
{
    private MockInterface $loggerInterfaceMock;

    public function setUpLoggerInterfaceMock(): void
    {
        $this->loggerInterfaceMock = Mockery::mock(LoggerInterface::class);
        $this->app->instance(LoggerInterface::class, $this->loggerInterfaceMock);
    }

    public function loggerInterfaceMockReceiveDebug(): static
    {
        $this->loggerInterfaceMock
            ->shouldReceive('debug')
            ->andReturnSelf();
        return $this;
    }

    public function loggerInterfaceMockShouldHaveReceivedDebug(): void
    {
        $this->loggerInterfaceMock
            ->shouldHaveReceived('debug');
    }
}
