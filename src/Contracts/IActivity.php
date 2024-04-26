<?php

namespace SMSkin\LaravelSaga\Contracts;

use SMSkin\LaravelSaga\BaseSaga;

interface IActivity
{
    public function __construct(BaseSaga $saga);

    public function execute(): void;
}
