<?php

namespace SMSkin\LaravelSaga\Contracts;

use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Models\SagaContext;

interface ISagaLogger
{
    public function handledEvent(BaseSaga $saga, object $event);

    public function correlationNotDefined(BaseSaga $saga, object $event);

    public function sagaRaised(BaseSaga $saga, SagaContext $context);

    public function sagaContextNotFound(BaseSaga $saga, object $event);

    public function sagaAlreadyFinished(BaseSaga $saga, SagaContext $context, object $event);

    public function undefinedSagaState(BaseSaga $saga, SagaContext $context, object $event);

    public function undefinedEventForSagaState(BaseSaga $saga, SagaContext $context, object $event);
}
