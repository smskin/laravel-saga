<?php

namespace SMSkin\LaravelSaga\Models;

use BackedEnum;
use Closure;
use RuntimeException;
use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Contracts\ICorrelation;
use SMSkin\LaravelSaga\Enums\SagaState;
use SMSkin\LaravelSaga\Events\ESagaRaised;

final class SagaBuilder
{
    /**
     * @var array
     */
    private array $initialEvents = [];

    /**
     * @var StateListener[]
     */
    private array $listeners = [];

    /**
     * @var Closure[]
     */
    private array $correlations = [];

    public function __construct(protected BaseSaga $saga)
    {
    }

    public function correlatedById(string $eventClass, Closure|null $closure = null): self
    {
        if (array_key_exists($eventClass, $this->correlations)) {
            throw new RuntimeException('Correlation for this already defined');
        }

        if (!$closure) {
            $this->correlations[$eventClass] = static function (BaseSaga $saga, ICorrelation $event) {
                return $saga->getRepository()->getById($saga, $event->getCorrelationId());
            };
            return $this;
        }

        $this->correlations[$eventClass] = static function (BaseSaga $saga, object $event) use ($closure) {
            $id = call_user_func($closure, $event);
            return $saga->getRepository()->getById($saga, $id);
        };

        return $this;
    }

    public function correlatedBy(string $eventClass, string $field, Closure $closure): self
    {
        if (array_key_exists($eventClass, $this->correlations)) {
            throw new RuntimeException('Correlation for this already defined');
        }

        $this->correlations[$eventClass] = function (BaseSaga $saga, object $event) use ($closure, $field) {
            $value = call_user_func($closure, $event);
            return $this->saga->getRepository()->getByField($saga, $field, $value);
        };
        return $this;
    }

    public function onInitEvent(string $eventClass, Closure $closure): self
    {
        if (array_key_exists($eventClass, $this->initialEvents)) {
            throw new RuntimeException('Initial event already defined');
        }

        $this->initialEvents[$eventClass] = $closure;
        return $this;
    }

    public function duringState(BackedEnum $state): StateListener
    {
        if (array_key_exists($state->value, $this->listeners)) {
            return $this->listeners[$state->value];
        }

        $this->listeners[$state->value] = new StateListener($this->saga);
        return $this->listeners[$state->value];
    }

    /**
     * @return StateListener[]
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }

    /**
     * @return array
     */
    public function getInitialEvents(): array
    {
        return $this->initialEvents;
    }

    /**
     * @return Closure[]
     */
    public function getCorrelations(): array
    {
        return $this->correlations;
    }

    public function initial(): EventListener
    {
        return $this
            ->duringState(SagaState::INITIAL)
            ->on(ESagaRaised::class);
    }
}
