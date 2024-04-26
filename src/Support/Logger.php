<?php

namespace SMSkin\LaravelSaga\Support;

use Psr\Log\LoggerInterface;
use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Contracts\ISagaLogger;
use SMSkin\LaravelSaga\Models\SagaContext;

class Logger implements ISagaLogger
{
    public function __construct(protected LoggerInterface $logger)
    {
    }

    public function handledEvent(BaseSaga $saga, object $event)
    {
        $this->logger->debug('Handled event', [
            'saga' => get_class($saga),
            'event' => [
                'class' => get_class($event),
                'payload' => json_encode($event),
            ],
        ]);
    }

    public function correlationNotDefined(BaseSaga $saga, object $event)
    {
        $this->logger->debug('Event correlation not defined', [
            'saga' => get_class($saga),
            'event' => [
                'class' => get_class($event),
                'payload' => json_encode($event),
            ],
        ]);
    }

    public function sagaRaised(BaseSaga $saga, SagaContext $context)
    {
        $this->logger->debug('Saga raised', [
            'saga' => get_class($saga),
            'context' => [
                'class' => get_class($context),
                'payload' => json_encode($context),
            ],
        ]);
    }

    public function sagaContextNotFound(BaseSaga $saga, object $event)
    {
        $this->logger->debug('Saga context not found', [
            'saga' => get_class($saga),
            'event' => [
                'class' => get_class($event),
                'payload' => json_encode($event),
            ],
        ]);
    }

    public function sagaAlreadyFinished(BaseSaga $saga, SagaContext $context, object $event)
    {
        $this->logger->debug('Saga already finished', [
            'saga' => get_class($saga),
            'event' => [
                'class' => get_class($event),
                'payload' => json_encode($event),
            ],
            'context' => [
                'class' => get_class($context),
                'payload' => json_encode($context),
            ],
        ]);
    }

    public function undefinedSagaState(BaseSaga $saga, SagaContext $context, object $event)
    {
        $this->logger->debug('Undefined saga state', [
            'saga' => get_class($saga),
            'event' => [
                'class' => get_class($event),
                'payload' => json_encode($event),
            ],
            'context' => [
                'class' => get_class($context),
                'payload' => json_encode($context),
            ],
        ]);
    }

    public function undefinedEventForSagaState(BaseSaga $saga, SagaContext $context, object $event)
    {
        $this->logger->debug('Undefined event for current saga state', [
            'saga' => get_class($saga),
            'event' => [
                'class' => get_class($event),
                'payload' => json_encode($event),
            ],
            'context' => [
                'class' => get_class($context),
                'payload' => json_encode($context),
            ],
        ]);
    }
}
