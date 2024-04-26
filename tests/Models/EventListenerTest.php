<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\Models;

use Illuminate\Support\Str;
use SMSkin\LaravelSaga\Enums\SagaState;
use SMSkin\LaravelSaga\Models\ActivityOperation;
use SMSkin\LaravelSaga\Models\ClosureOperation;
use SMSkin\LaravelSaga\Models\EventListener;
use SMSkin\LaravelSaga\Models\PublishOperation;
use SMSkin\LaravelSaga\Models\TransitionOperation;
use SMSkin\LaravelSaga\Tests\TestCase;
use SMSkin\LaravelSaga\Tests\TestSupport\TestSagaActivity;
use SMSkin\LaravelSaga\Tests\TestSupport\TestSagaContext;

class EventListenerTest extends TestCase
{
    public function testActivity()
    {
        $saga = $this->createTestSaga();

        $listener = new EventListener($saga, null);
        $listener->activity(TestSagaActivity::class);

        $this->assertIsArray($listener->stack);
        $this->assertCount(1, $listener->stack);
        $this->assertInstanceOf(ActivityOperation::class, $listener->stack[0]);
    }

    public function testTransitionTo()
    {
        $saga = $this->createTestSaga();

        $listener = new EventListener($saga, null);
        $listener->transitionTo(SagaState::FINALIZED);

        $this->assertIsArray($listener->stack);
        $this->assertCount(1, $listener->stack);
        $this->assertInstanceOf(TransitionOperation::class, $listener->stack[0]);
    }

    public function testThen()
    {
        $saga = $this->createTestSaga();

        $listener = new EventListener($saga, null);
        $listener->then(static function () {
        });

        $this->assertIsArray($listener->stack);
        $this->assertCount(1, $listener->stack);
        $this->assertInstanceOf(ClosureOperation::class, $listener->stack[0]);
    }

    public function testFinalize()
    {
        $saga = $this->createTestSaga();

        $listener = new EventListener($saga, null);
        $listener->finalize();

        $this->assertIsArray($listener->stack);
        $this->assertCount(2, $listener->stack);
        $this->assertInstanceOf(TransitionOperation::class, $listener->stack[0]);
        $this->assertInstanceOf(ClosureOperation::class, $listener->stack[1]);
    }

    public function testPublish()
    {
        $saga = $this->createTestSaga();

        $listener = new EventListener($saga, null);
        $listener->publish(static function () {
        });

        $this->assertIsArray($listener->stack);
        $this->assertCount(1, $listener->stack);
        $this->assertInstanceOf(PublishOperation::class, $listener->stack[0]);
    }

    public function testHandle()
    {
        $saga = $this->createTestSaga();
        $context = new TestSagaContext(Str::uuid()->toString());
        $context->transitionTo(SagaState::INITIAL);
        $this->setTestSagaContext($saga, $context);

        $listener = new EventListener($saga, null);
        $listener->transitionTo(SagaState::FINALIZED);

        $this->getSagaRepositoryMock()
            ->shouldReceive('update');

        $listener->handle();

        $this->assertEquals(SagaState::FINALIZED->value, $context->getState());
    }
}
