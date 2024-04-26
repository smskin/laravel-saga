<?php

namespace SMSkin\LaravelSaga\Traits;

trait SagaRegisterTrait
{
    public function getEvents(): array
    {
        $this->setup();

        $events = array_keys($this->builder()->getInitialEvents());
        foreach ($this->builder()->getListeners() as $stateListener) {
            $events = array_merge($events, array_keys($stateListener->getEventListeners()));
        }
        return array_unique($events);
    }
}
