<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\TestSupport;

use Illuminate\Support\Str;
use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Models\SagaContext;

class TestSaga extends BaseSaga
{
    protected TestSagaContext|SagaContext $context;

    protected function setup(): void
    {
        $this->builder()
            ->correlatedById(InitialEvent::class, static function () {
                return 1;
            })
            ->correlatedById(ExecutionEvent::class, static function () {
                return 1;
            });

        $this->builder()
            ->onInitEvent(InitialEvent::class, static function () {
                return new TestSagaContext(Str::uuid()->toString());
            });

        $this->builder()
            ->initial()
            ->transitionTo(TestSagaStateEnum::INITIALIZED)
            ->activity(TestSagaActivity::class)
            ->then(static function () {
            });

        $this->builder()
            ->duringState(TestSagaStateEnum::INITIALIZED)
            ->on(ExecutionEvent::class)
            ->finalize();
    }
}
