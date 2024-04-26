<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\Models;

use RuntimeException;
use SMSkin\LaravelSaga\Enums\SagaState;
use SMSkin\LaravelSaga\Models\SagaBuilder;
use SMSkin\LaravelSaga\Models\StateListener;
use SMSkin\LaravelSaga\Tests\TestCase;
use SMSkin\LaravelSaga\Tests\TestSupport\InitialEvent;

class SagaBuilderTest extends TestCase
{
    public function testInitial()
    {
        $saga = $this->createTestSaga();

        $builder = new SagaBuilder($saga);
        $builder->initial();

        $state = SagaState::INITIAL;

        $this->assertIsArray($builder->getListeners());
        $this->assertCount(1, $builder->getListeners());
        $this->assertArrayHasKey($state->value, $builder->getListeners());
        $this->assertInstanceOf(StateListener::class, $builder->getListeners()[$state->value]);
    }

    public function testDuringState()
    {
        $state = SagaState::FINALIZED;
        $saga = $this->createTestSaga();

        $builder = new SagaBuilder($saga);
        $builder->duringState($state);

        $this->assertIsArray($builder->getListeners());
        $this->assertCount(1, $builder->getListeners());
        $this->assertArrayHasKey($state->value, $builder->getListeners());
        $this->assertInstanceOf(StateListener::class, $builder->getListeners()[$state->value]);
    }

    public function testDuringStateRetry()
    {
        $state = SagaState::FINALIZED;
        $saga = $this->createTestSaga();

        $builder = new SagaBuilder($saga);
        $builder->duringState($state);
        $builder->duringState($state);
        $this->assertCount(1, $builder->getListeners());
    }

    public function testOnInitEvent()
    {
        $event = InitialEvent::class;
        $saga = $this->createTestSaga();
        $closure = static function () {
        };

        $builder = new SagaBuilder($saga);
        $builder->onInitEvent($event, $closure);

        $this->assertIsArray($builder->getInitialEvents());
        $this->assertCount(1, $builder->getInitialEvents());
        $this->assertArrayHasKey($event, $builder->getInitialEvents());
        $this->assertEquals($closure, $builder->getInitialEvents()[$event]);
    }

    public function testOnInitEventRetry()
    {
        $event = InitialEvent::class;
        $saga = $this->createTestSaga();
        $closure = static function () {
        };

        $builder = new SagaBuilder($saga);
        $builder->onInitEvent($event, $closure);

        $this->expectException(RuntimeException::class);
        $builder->onInitEvent($event, $closure);
    }

    public function testCorrelatedBy()
    {
        $event = InitialEvent::class;
        $closure = static function () {
        };

        $saga = $this->createTestSaga();
        $builder = new SagaBuilder($saga);

        $builder->correlatedBy($event, 'field', $closure);

        $this->assertIsArray($builder->getCorrelations());
        $this->assertCount(1, $builder->getCorrelations());
        $this->assertEquals($closure, $builder->getCorrelations()[$event]);
    }

    public function testCorrelatedByRetry()
    {
        $event = InitialEvent::class;
        $closure = static function () {
        };

        $saga = $this->createTestSaga();
        $builder = new SagaBuilder($saga);

        $builder->correlatedBy($event, 'field', $closure);

        $this->expectException(RuntimeException::class);
        $builder->correlatedBy($event, 'field', $closure);
    }

    public function testCorrelatedById()
    {
        $event = InitialEvent::class;
        $closure = static function () {
        };

        $saga = $this->createTestSaga();
        $builder = new SagaBuilder($saga);

        $builder->correlatedById($event, $closure);

        $this->assertIsArray($builder->getCorrelations());
        $this->assertCount(1, $builder->getCorrelations());
        $this->assertEquals($closure, $builder->getCorrelations()[$event]);
    }

    public function testCorrelatedByIdRetry()
    {
        $event = InitialEvent::class;
        $closure = static function () {
        };

        $saga = $this->createTestSaga();
        $builder = new SagaBuilder($saga);

        $builder->correlatedById($event, $closure);

        $this->expectException(RuntimeException::class);
        $builder->correlatedById($event, $closure);
    }
}
