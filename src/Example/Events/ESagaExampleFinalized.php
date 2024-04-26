<?php

namespace SMSkin\LaravelSaga\Example\Events;

class ESagaExampleFinalized
{
    public function __construct(public string $id)
    {
    }
}
