<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\Models;

use Illuminate\Support\Str;
use SMSkin\LaravelSaga\Enums\SagaState;
use SMSkin\LaravelSaga\Models\TransitionOperation;
use SMSkin\LaravelSaga\Tests\TestCase;
use SMSkin\LaravelSaga\Tests\TestSupport\TestSagaContext;

class TransitionOperationTest extends TestCase
{
    public function testExecute()
    {
        $saga = $this->createTestSaga();
        $context = new TestSagaContext(Str::uuid()->toString());
        $context->transitionTo(SagaState::INITIAL);
        $this->setTestSagaContext($saga, $context);

        (new TransitionOperation($saga, SagaState::FINALIZED))->execute();

        $this->assertEquals(SagaState::FINALIZED->value, $saga->getContext()->getState());
    }
}
