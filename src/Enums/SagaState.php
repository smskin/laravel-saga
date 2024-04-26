<?php

namespace SMSkin\LaravelSaga\Enums;

enum SagaState: string
{
    case INITIAL = 'INITIAL';
    case FINALIZED = 'FINALIZED';
}
