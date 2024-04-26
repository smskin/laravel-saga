<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\Models;

use SMSkin\LaravelSaga\Models\ActivityOperation;
use SMSkin\LaravelSaga\Tests\TestCase;
use SMSkin\LaravelSaga\Tests\TestSupport\TestSagaActivity;

class ActivityOperationTest extends TestCase
{
    public function testExecuteActivity()
    {
        $saga = $this->createTestSaga();
        /** @noinspection PhpUndefinedFieldInspection */
        $saga->testExecuted = false;

        (new ActivityOperation($saga, TestSagaActivity::class))->execute();

        /** @noinspection PhpUnitAssertTrueWithIncompatibleTypeArgumentInspection */
        $this->assertTrue($saga->testExecuted);
    }
}
