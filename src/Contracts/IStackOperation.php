<?php

namespace SMSkin\LaravelSaga\Contracts;

interface IStackOperation
{
    public function execute(): void;
}
