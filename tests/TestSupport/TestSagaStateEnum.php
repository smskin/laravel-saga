<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\TestSupport;

enum TestSagaStateEnum: string
{
    case INITIALIZED = 'INITIALIZED';
    case SAME_STATE = 'SAME_STATE';
}
