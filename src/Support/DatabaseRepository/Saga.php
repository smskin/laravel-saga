<?php

namespace SMSkin\LaravelSaga\Support\DatabaseRepository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Saga extends Model
{
    public function getTable(): string
    {
        return Config::get('saga.repositories.database.table');
    }

    protected $primaryKey = 'uuid';
    protected $keyType = 'string';

    protected $casts = [
        'context_value' => 'json',
    ];
}
