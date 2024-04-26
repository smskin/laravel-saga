<?php

use SMSkin\LaravelSaga\Support\DatabaseRepository\DatabaseRepository;
use SMSkin\LaravelSaga\Support\Logger;

return [
    'logger' => Logger::class,
    'state-machines' => [
        //SMSkin\LaravelSaga\Example\SagaExample::class
    ],
    'repositories' => [
        'default' => 'database',
        'database' => [
            'class' => DatabaseRepository::class,
            'table' => 'sagas',
        ],
    ],
];
