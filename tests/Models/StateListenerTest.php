<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\Models;

use SMSkin\LaravelSaga\Models\EventListener;
use SMSkin\LaravelSaga\Models\StateListener;
use SMSkin\LaravelSaga\Tests\TestCase;
use SMSkin\LaravelSaga\Tests\TestSupport\InitialEvent;

class StateListenerTest extends TestCase
{
    public function testOn()
    {
        $saga = $this->createTestSaga();
        $eventClass = InitialEvent::class;

        $listener = new StateListener($saga);
        $listener->on($eventClass, static function () {
        });

        $this->assertIsArray($listener->getEventListeners());
        $this->assertArrayHasKey($eventClass, $listener->getEventListeners());
        $this->assertInstanceOf(EventListener::class, $listener->getEventListeners()[$eventClass]);
    }

    public function testOnRetryExistEvent()
    {
        $saga = $this->createTestSaga();
        $eventClass = InitialEvent::class;

        $listener = new StateListener($saga);
        $listener->on($eventClass, static function () {
        });
        $listener->on($eventClass, static function () {
        });

        $this->assertCount(1, $listener->getEventListeners());
    }
}
