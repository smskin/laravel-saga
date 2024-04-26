<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\Support;

use Illuminate\Support\Str;
use SMSkin\LaravelSaga\Models\SagaContext;
use SMSkin\LaravelSaga\Support\Logger;
use SMSkin\LaravelSaga\Tests\TestCase;
use SMSkin\LaravelSaga\Tests\TestSupport\InitialEvent;

class LoggerTest extends TestCase
{
    public function testHandledEvent()
    {
        $logger = app(Logger::class);
        $saga = $this->createTestSaga();
        $event = new InitialEvent();

        $this->loggerInterfaceMockReceiveDebug();

        $logger->handledEvent($saga, $event);

        $this->loggerInterfaceMockShouldHaveReceivedDebug();
    }

    public function testCorrelationNotDefined()
    {
        $logger = app(Logger::class);
        $saga = $this->createTestSaga();
        $event = new InitialEvent();

        $this->loggerInterfaceMockReceiveDebug();

        $logger->correlationNotDefined($saga, $event);

        $this->loggerInterfaceMockShouldHaveReceivedDebug();
    }

    public function testSagaRaised()
    {
        $logger = app(Logger::class);
        $saga = $this->createTestSaga();
        $context = new SagaContext(Str::uuid()->toString());

        $this->loggerInterfaceMockReceiveDebug();

        $logger->sagaRaised($saga, $context);

        $this->loggerInterfaceMockShouldHaveReceivedDebug();
    }

    public function testSagaContextNotFound()
    {
        $logger = app(Logger::class);
        $saga = $this->createTestSaga();
        $event = new InitialEvent();

        $this->loggerInterfaceMockReceiveDebug();

        $logger->sagaContextNotFound($saga, $event);

        $this->loggerInterfaceMockShouldHaveReceivedDebug();
    }

    public function testSagaAlreadyFinished()
    {
        $logger = app(Logger::class);
        $saga = $this->createTestSaga();
        $context = new SagaContext(Str::uuid()->toString());
        $event = new InitialEvent();

        $this->loggerInterfaceMockReceiveDebug();

        $logger->sagaAlreadyFinished($saga, $context, $event);

        $this->loggerInterfaceMockShouldHaveReceivedDebug();
    }

    public function testUndefinedSagaState()
    {
        $logger = app(Logger::class);
        $saga = $this->createTestSaga();
        $context = new SagaContext(Str::uuid()->toString());
        $event = new InitialEvent();

        $this->loggerInterfaceMockReceiveDebug();

        $logger->undefinedSagaState($saga, $context, $event);

        $this->loggerInterfaceMockShouldHaveReceivedDebug();
    }

    public function testUndefinedEventForSagaState()
    {
        $logger = app(Logger::class);
        $saga = $this->createTestSaga();
        $context = new SagaContext(Str::uuid()->toString());
        $event = new InitialEvent();

        $this->loggerInterfaceMockReceiveDebug();

        $logger->undefinedEventForSagaState($saga, $context, $event);

        $this->loggerInterfaceMockShouldHaveReceivedDebug();
    }
}
