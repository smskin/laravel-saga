<?php

namespace SMSkin\LaravelSaga\Models;

use Closure;
use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Contracts\IStackOperation;

class PublishOperation implements IStackOperation
{
    public function __construct(protected BaseSaga $saga, protected Closure $closure)
    {
    }

    public function execute(): void
    {
        event($this->getEvent());
    }

    private function getEvent(): object
    {
        return call_user_func($this->closure, $this->saga);
    }
}
