<?php

namespace SMSkin\LaravelSaga\Example\Commands;

class CreateUserCommand
{
    public function __construct(public string $correlationId, public string $email)
    {
    }
}
