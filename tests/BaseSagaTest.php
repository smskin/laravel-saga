<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests;

use Illuminate\Support\Facades\Event;
use SMSkin\LaravelSaga\Enums\SagaState;
use SMSkin\LaravelSaga\Events\ESagaRaised;
use SMSkin\LaravelSaga\Tests\TestSupport\ExecutionEvent;
use SMSkin\LaravelSaga\Tests\TestSupport\InitialEvent;
use SMSkin\LaravelSaga\Tests\TestSupport\SameEvent;
use SMSkin\LaravelSaga\Tests\TestSupport\TestSagaContext;
use SMSkin\LaravelSaga\Tests\TestSupport\TestSagaStateEnum;

class BaseSagaTest extends TestCase
{
    public function testGetEvents()
    {
        $saga = $this->createTestSaga();
        $events = $saga->getEvents();
        $this->assertIsArray($events);
        $this->assertArrayHasKey(0, $events);
        $this->assertArrayHasKey(1, $events);
        $this->assertEquals(InitialEvent::class, $events[0]);
        $this->assertEquals(ESagaRaised::class, $events[1]);
    }

    public function testHandleInitialEvent()
    {
        Event::fake();

        /**
         * Initial event
         */
        $event = new InitialEvent();
        $saga = $this->createTestSaga();

        $this->getSagaLoggerMock()
            ->byDefault()
            ->shouldReceive('handledEvent', 'sagaRaised');

        $this->getSagaRepositoryMock()
            ->byDefault()
            ->shouldReceive('create');

        /** @noinspection PhpUnhandledExceptionInspection */
        $saga->handle($event);

        Event::assertDispatched(ESagaRaised::class);
    }

    public function testHandleESagaRaised()
    {
        $saga = $this->createTestSaga();
        $context = new TestSagaContext('1');
        $context->transitionTo(SagaState::INITIAL);

        $this->getSagaLoggerMock()
            ->byDefault()
            ->shouldReceive('handledEvent');

        $this->getSagaRepositoryMock()
            ->byDefault()
            ->shouldReceive('getById')
            ->andReturnUsing(static function () use ($context) {
                return $context;
            });

        $this->getSagaRepositoryMock()
            ->byDefault()
            ->shouldReceive('update');

        /** @noinspection PhpUnhandledExceptionInspection */
        $saga->handle(new ESagaRaised(get_class($saga), '1'));
        $this->assertEquals(TestSagaStateEnum::INITIALIZED->value, $saga->getContext()->getState());
    }

    public function testHandleCorrelationNotDefined()
    {
        $saga = $this->createTestSaga();

        $this->getSagaLoggerMock()
            ->byDefault()
            ->shouldReceive('handledEvent', 'correlationNotDefined');

        $event = new SameEvent();
        /** @noinspection PhpUnhandledExceptionInspection */
        $saga->handle($event);

        $this->getSagaLoggerMock()
            ->byDefault()
            ->shouldHaveReceived('correlationNotDefined');
    }

    public function testHandleSagaAlreadyFinished()
    {
        $saga = $this->createTestSaga();
        $context = new TestSagaContext('1');
        $context->transitionTo(SagaState::FINALIZED);

        $this->getSagaLoggerMock()
            ->byDefault()
            ->shouldReceive('handledEvent', 'sagaAlreadyFinished');

        $this->getSagaRepositoryMock()
            ->byDefault()
            ->shouldReceive('getById')
            ->andReturnUsing(static function () use ($context) {
                return $context;
            });

        /** @noinspection PhpUnhandledExceptionInspection */
        $saga->handle(new ESagaRaised(get_class($saga), '1'));

        $this->getSagaLoggerMock()
            ->byDefault()
            ->shouldHaveReceived('sagaAlreadyFinished');
    }

    public function testHandleUndefinedEventForSagaState()
    {
        $saga = $this->createTestSaga();
        $context = new TestSagaContext('1');
        $context->transitionTo(TestSagaStateEnum::INITIALIZED);

        $this->getSagaLoggerMock()
            ->byDefault()
            ->shouldReceive('handledEvent', 'undefinedEventForSagaState');

        $this->getSagaRepositoryMock()
            ->byDefault()
            ->shouldReceive('getById')
            ->andReturnUsing(static function () use ($context) {
                return $context;
            });

        /** @noinspection PhpUnhandledExceptionInspection */
        $saga->handle(new ESagaRaised(get_class($saga), '1'));

        $this->getSagaLoggerMock()->shouldHaveReceived('undefinedEventForSagaState');
    }

    public function testHandleUndefinedSagaState()
    {
        $saga = $this->createTestSaga();
        $context = new TestSagaContext('1');
        $context->transitionTo(TestSagaStateEnum::SAME_STATE);

        $this->getSagaLoggerMock()
            ->byDefault()
            ->shouldReceive('handledEvent', 'undefinedSagaState');

        $this->getSagaRepositoryMock()
            ->byDefault()
            ->shouldReceive('getById')
            ->andReturnUsing(static function () use ($context) {
                return $context;
            });

        $this->getSagaRepositoryMock()
            ->byDefault()
            ->shouldReceive('update');

        /** @noinspection PhpUnhandledExceptionInspection */
        $saga->handle(new ExecutionEvent());

        $this->getSagaLoggerMock()->shouldHaveReceived('undefinedSagaState');
    }

    public function testHandle()
    {
        $saga = $this->createTestSaga();
        $context = new TestSagaContext('1');
        $context->transitionTo(TestSagaStateEnum::INITIALIZED);

        $this->getSagaLoggerMock()
            ->byDefault()
            ->shouldReceive('handledEvent');

        $this->getSagaRepositoryMock()
            ->byDefault()
            ->shouldReceive('getById')
            ->andReturnUsing(static function () use ($context) {
                return $context;
            });

        $this->getSagaRepositoryMock()
            ->byDefault()
            ->shouldReceive('update');

        /** @noinspection PhpUnhandledExceptionInspection */
        $saga->handle(new ExecutionEvent());

        $this->assertEquals(SagaState::FINALIZED->value, $saga->getContext()->getState());
    }
}
