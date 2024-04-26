<?php

namespace SMSkin\LaravelSaga\Models;

use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Contracts\IActivity;
use SMSkin\LaravelSaga\Contracts\IStackOperation;

final class ActivityOperation implements IStackOperation
{
    public function __construct(protected BaseSaga $saga, protected string $class)
    {
    }

    public function execute(): void
    {
        $this->getActivity()->execute();
    }

    private function getActivity(): IActivity
    {
        $class = $this->class;
        return new $class($this->saga);
    }
}
