<?php

namespace SMSkin\LaravelSaga\Models;

use BackedEnum;
use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Contracts\IStackOperation;

final class TransitionOperation implements IStackOperation
{
    public function __construct(protected BaseSaga $saga, protected BackedEnum $state)
    {
    }

    public function execute(): void
    {
        $this->saga->getContext()->transitionTo($this->state);
    }
}
