<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\TestSupport;

use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Contracts\ISagaLogger;
use SMSkin\LaravelSaga\Models\SagaContext;

class TestLogger implements ISagaLogger
{
    public function handledEvent(BaseSaga $saga, object $event)
    {
        // TODO: Implement handledEvent() method.
    }

    public function correlationNotDefined(BaseSaga $saga, object $event)
    {
        // TODO: Implement correlationNotDefined() method.
    }

    public function sagaRaised(BaseSaga $saga, SagaContext $context)
    {
        // TODO: Implement sagaRaised() method.
    }

    public function sagaContextNotFound(BaseSaga $saga, object $event)
    {
        // TODO: Implement sagaContextNotFound() method.
    }

    public function sagaAlreadyFinished(BaseSaga $saga, SagaContext $context, object $event)
    {
        // TODO: Implement sagaAlreadyFinished() method.
    }

    public function undefinedSagaState(BaseSaga $saga, SagaContext $context, object $event)
    {
        // TODO: Implement undefinedSagaState() method.
    }

    public function undefinedEventForSagaState(BaseSaga $saga, SagaContext $context, object $event)
    {
        // TODO: Implement undefinedEventForSagaState() method.
    }
}
