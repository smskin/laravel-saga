<?php

namespace SMSkin\LaravelSaga\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use SMSkin\LaravelSaga\Contracts\ICorrelation;

class ESagaFinalized implements ICorrelation
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public string $sagaClass, public string $id)
    {
    }

    public function getCorrelationId(): string
    {
        return $this->id;
    }
}
