<?php

namespace SMSkin\LaravelSaga\Traits;

use Closure;
use RuntimeException;
use SMSkin\LaravelSaga\Enums\SagaState;
use SMSkin\LaravelSaga\Events\ESagaRaised;
use SMSkin\LaravelSaga\Exceptions\CorrelationNotDefined;
use SMSkin\LaravelSaga\Exceptions\SagaContextNotFound;
use SMSkin\LaravelSaga\Exceptions\SagaFinalized;
use SMSkin\LaravelSaga\Exceptions\SagaWithThisIdAlreadyExists;
use SMSkin\LaravelSaga\Exceptions\UndefinedEventForState;
use SMSkin\LaravelSaga\Exceptions\UndefinedSagaState;
use SMSkin\LaravelSaga\Models\EventListener;
use SMSkin\LaravelSaga\Models\SagaContext;
use SMSkin\LaravelSaga\Models\StateListener;
use Throwable;

trait SagaEventHandlerTrait
{
    use SagaContextTrait;

    private object|null $handledEvent = null;

    /**
     * @return object|null
     */
    protected function getHandledEvent(): object|null
    {
        return $this->handledEvent;
    }

    /**
     * @throws Throwable
     */
    public function handle(object $event): void
    {
        $logger = $this->logger;
        $logger->handledEvent($this, $event);
        $this->handledEvent = $event;

        try {
            $this->processHandler($event);
        } catch (CorrelationNotDefined $exception) {
            $this->correlationNotDefined($event, $exception);
        } catch (SagaFinalized $exception) {
            $this->sagaAlreadyFinished($event, $exception);
        } catch (UndefinedEventForState $exception) {
            $this->undefinedEventForSagaState($event, $exception);
        } catch (UndefinedSagaState $exception) {
            $this->undefinedSagaState($event, $exception);
        } catch (SagaContextNotFound $exception) {
            $this->sagaContextNotFound($event, $exception);
        }
    }

    /**
     * @throws CorrelationNotDefined
     * @throws SagaFinalized
     * @throws UndefinedEventForState
     * @throws UndefinedSagaState
     * @throws SagaContextNotFound
     */
    private function processHandler(object $event): void
    {
        $this->setup();

        if ($this->isInitialEvent($event)) {
            $context = $this->createContext($event);
            $this->context = $context;
            if ($context->getState() === SagaState::INITIAL->value) {
                $this->logger->sagaRaised($this, $context);
                event(new ESagaRaised(get_class($this), $context->getId()));
                return;
            }
        } else {
            $context = $this->getContextByEvent($event);
            $this->context = $context;
        }

        if ($context->getState() === SagaState::FINALIZED->value) {
            throw new SagaFinalized();
        }

        $stateListener = $this->getStateListener();
        if (!$stateListener) {
            throw new UndefinedSagaState();
        }

        $eventListener = $this->getEventListener($stateListener, $event);
        if (!$eventListener) {
            throw new UndefinedEventForState();
        }

        $eventListener->handle();
    }

    /**
     * @throws CorrelationNotDefined
     * @throws SagaContextNotFound
     */
    private function getContextByEvent(object $event): SagaContext
    {
        $correlation = $this->builder()->getCorrelations()[get_class($event)] ?? null;
        if (!$correlation) {
            throw new CorrelationNotDefined();
        }
        return call_user_func($correlation, $this, $event);
    }

    private function getEventListener(StateListener $stateListener, object $event): EventListener|null
    {
        return $stateListener->getEventListeners()[get_class($event)] ?? null;
    }

    private function getStateListener(): StateListener|null
    {
        return $this->builder()->getListeners()[$this->context->getState()] ?? null;
    }

    private function isInitialEvent(object $event): bool
    {
        return array_key_exists(get_class($event), $this->builder()->getInitialEvents());
    }

    private function createContext(object $event): SagaContext
    {
        /**
         * @var $mapper Closure
         */
        $mapper = $this->builder()->getInitialEvents()[get_class($event)];

        /**
         * @var $context SagaContext
         */
        $context = call_user_func($mapper, $event);
        $context->transitionTo(SagaState::INITIAL);
        try {
            $this->getRepository()->create($this, $context);
            return $context;
        } catch (SagaWithThisIdAlreadyExists) {
            try {
                return $this->getRepository()->getById($this, $context->getId());
            } catch (SagaContextNotFound $exception) {
                throw new RuntimeException($exception->getMessage(), 500, $exception);
            }
        }
    }

    /** @noinspection PhpUnusedParameterInspection */
    protected function correlationNotDefined(object $event, CorrelationNotDefined $exception): void
    {
        $this->logger->correlationNotDefined($this, $event);
    }

    /** @noinspection PhpUnusedParameterInspection */
    protected function sagaAlreadyFinished(object $event, SagaFinalized $exception): void
    {
        $this->logger->sagaAlreadyFinished($this, $this->context, $event);
    }

    /** @noinspection PhpUnusedParameterInspection */
    protected function undefinedEventForSagaState(object $event, UndefinedEventForState $exception): void
    {
        $this->logger->undefinedEventForSagaState($this, $this->context, $event);
    }

    /** @noinspection PhpUnusedParameterInspection */
    protected function undefinedSagaState(object $event, UndefinedSagaState $exception): void
    {
        $this->logger->undefinedSagaState($this, $this->context, $event);
    }

    /** @noinspection PhpUnusedParameterInspection */
    protected function sagaContextNotFound(object $event, SagaContextNotFound $exception): void
    {
        $this->logger->sagaContextNotFound($this, $event);
    }
}
