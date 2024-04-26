<?php

namespace SMSkin\LaravelSaga\Example;

use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Contracts\IActivity;

class UserCreatingActivity implements IActivity
{
    public function __construct(BaseSaga $saga)
    {
    }

    public function execute(): void
    {
        // TODO: Implement execute() method.
    }
}
