<?php

namespace SMSkin\LaravelSaga\Models;

use Closure;
use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Contracts\IStackOperation;

final class ClosureOperation implements IStackOperation
{
    public function __construct(protected BaseSaga $saga, protected Closure $closure)
    {
    }

    public function execute(): void
    {
        call_user_func($this->closure, $this->saga);
    }
}
