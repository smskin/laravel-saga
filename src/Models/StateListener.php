<?php

namespace SMSkin\LaravelSaga\Models;

use Closure;
use SMSkin\LaravelSaga\BaseSaga;

final class StateListener
{
    /**
     * @var EventListener[]
     */
    protected array $eventListeners = [];

    public function __construct(protected BaseSaga $saga)
    {
    }

    public function on(string $eventClass, Closure|null $filter = null): EventListener
    {
        if (array_key_exists($eventClass, $this->eventListeners)) {
            return $this->eventListeners[$eventClass];
        }
        $this->eventListeners[$eventClass] = new EventListener($this->saga, $filter);
        return $this->eventListeners[$eventClass];
    }

    /**
     * @return EventListener[]
     */
    public function getEventListeners(): array
    {
        return $this->eventListeners;
    }
}
