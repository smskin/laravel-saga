<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\Models;

use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Models\ClosureOperation;
use SMSkin\LaravelSaga\Tests\TestCase;
use SMSkin\LaravelSaga\Tests\TestSupport\TestSaga;

class ClosureOperationTest extends TestCase
{
    public function testExecuteClosure()
    {
        $saga = $this->createTestSaga();
        /** @noinspection PhpUndefinedFieldInspection */
        $saga->testExecuted = false;

        (new ClosureOperation($saga, function (BaseSaga $saga) {
            $this->assertInstanceOf(TestSaga::class, $saga);
            /** @noinspection PhpUndefinedFieldInspection */
            $this->testExecuted = true;
        }))->execute();

        /** @noinspection PhpUndefinedFieldInspection */
        $this->assertTrue($this->testExecuted);
    }
}
