<?php

namespace SMSkin\LaravelSaga\Example;

use Exception;
use RuntimeException;
use SMSkin\LaravelSaga\Example\Events\EUserBlocked;
use SMSkin\LaravelSaga\Example\Events\EUserCreated;

class UserCommandService
{
    /** @noinspection PhpUnusedParameterInspection */
    public function create(string $correlationId, string $email): void
    {
        try {
            $userId = random_int(1, 9999);
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        event(new EUserCreated($correlationId, $userId));
    }

    public function block(int $userId): void
    {
        event(new EUserBlocked($userId));
    }
}
