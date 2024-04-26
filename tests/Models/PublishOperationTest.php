<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\Models;

use Illuminate\Support\Facades\Event;
use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Models\PublishOperation;
use SMSkin\LaravelSaga\Tests\TestCase;
use SMSkin\LaravelSaga\Tests\TestSupport\InitialEvent;
use SMSkin\LaravelSaga\Tests\TestSupport\TestSaga;

class PublishOperationTest extends TestCase
{
    public function testPublishEvent()
    {
        $saga = $this->createTestSaga();

        Event::fake();

        (new PublishOperation(
            $saga,
            function (BaseSaga $saga) {
                $this->assertInstanceOf(TestSaga::class, $saga);
                return new InitialEvent();
            }
        ))->execute();

        Event::assertDispatched(InitialEvent::class);
    }
}
