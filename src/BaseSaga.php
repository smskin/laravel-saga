<?php

namespace SMSkin\LaravelSaga;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use SMSkin\LaravelSaga\Contracts\ISagaLogger;
use SMSkin\LaravelSaga\Contracts\ISagaRepository;
use SMSkin\LaravelSaga\Events\ESagaRaised;
use SMSkin\LaravelSaga\Models\SagaBuilder;
use SMSkin\LaravelSaga\Traits\SagaContextTrait;
use SMSkin\LaravelSaga\Traits\SagaEventHandlerTrait;
use SMSkin\LaravelSaga\Traits\SagaRegisterTrait;

abstract class BaseSaga implements ShouldQueue
{
    use SagaRegisterTrait;
    use SagaEventHandlerTrait;
    use SagaContextTrait;
    use InteractsWithQueue;

    private SagaBuilder $builder;

    public function __construct(protected Application $app, protected ISagaLogger $logger, protected ISagaRepository $repository)
    {
        $this->builder = (new SagaBuilder($this))
            ->correlatedById(ESagaRaised::class);
    }

    public function builder(): SagaBuilder
    {
        return $this->builder;
    }


    abstract protected function setup(): void;

    /**
     * @return ISagaRepository
     */
    public function getRepository(): ISagaRepository
    {
        return $this->repository;
    }
}
