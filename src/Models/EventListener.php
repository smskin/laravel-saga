<?php

namespace SMSkin\LaravelSaga\Models;

use BackedEnum;
use Closure;
use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Contracts\IStackOperation;
use SMSkin\LaravelSaga\Enums\SagaState;
use SMSkin\LaravelSaga\Events\ESagaFinalized;

final class EventListener
{
    /**
     * @var IStackOperation[]
     */
    public array $stack = [];

    public function __construct(protected BaseSaga $saga, protected Closure|null $filter)
    {
    }

    public function handle(): void
    {
        foreach ($this->stack as $item) {
            $item->execute();
            $this->saga->saveContext();
        }
    }

    public function activity(string $class): self
    {
        $this->stack[] = new ActivityOperation($this->saga, $class);
        return $this;
    }

    public function transitionTo(BackedEnum $state): self
    {
        $this->stack[] = new TransitionOperation($this->saga, $state);
        return $this;
    }

    public function then(Closure $closure): self
    {
        $this->stack[] = new ClosureOperation($this->saga, $closure);
        return $this;
    }

    public function finalize(): self
    {
        $this->stack[] = new TransitionOperation($this->saga, SagaState::FINALIZED);
        $this->stack[] = new ClosureOperation($this->saga, static function (BaseSaga $saga) {
            event(new ESagaFinalized(get_class($saga), $saga->getContext()->getId()));
        });
        return $this;
    }

    public function publish(Closure $closure): self
    {
        $this->stack[] = new PublishOperation($this->saga, $closure);
        return $this;
    }
}
