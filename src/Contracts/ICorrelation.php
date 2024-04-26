<?php

namespace SMSkin\LaravelSaga\Contracts;

interface ICorrelation
{
    public function getCorrelationId(): string;
}
