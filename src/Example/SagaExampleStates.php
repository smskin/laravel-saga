<?php

namespace SMSkin\LaravelSaga\Example;

enum SagaExampleStates: string
{
    case USER_CREATING = 'USER_CREATING';
    case USER_BLOCKING = 'USER_BLOCKING';
}
