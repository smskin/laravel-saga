<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\TestSupport;

use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Contracts\IActivity;

class TestSagaActivity implements IActivity
{
    public function __construct(protected BaseSaga $saga)
    {
    }

    public function execute(): void
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $this->saga->testExecuted = true;
    }
}
