<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests\Models;

use Illuminate\Support\Str;
use SMSkin\LaravelSaga\Enums\SagaState;
use SMSkin\LaravelSaga\Models\SagaContext;
use SMSkin\LaravelSaga\Tests\TestCase;

class SagaContextTest extends TestCase
{
    public function testGetId()
    {
        $id = Str::uuid()->toString();
        $context = new SagaContext($id);
        $this->assertEquals($id, $context->getId());
    }

    public function testTransitionTo()
    {
        $state = SagaState::FINALIZED;
        $context = new SagaContext(Str::uuid()->toString());
        $context->transitionTo($state);

        $this->assertEquals($state->value, $context->getState());
    }

    public function testFromArray()
    {
        $state = SagaState::FINALIZED;
        $context = new SagaContext(Str::uuid()->toString());
        $context->fromArray(['state' => $state->value]);

        $this->assertEquals($state->value, $context->getState());
    }

    public function testToArray()
    {
        $state = SagaState::FINALIZED;
        $context = new SagaContext(Str::uuid()->toString());
        $context->transitionTo($state);

        $this->assertIsArray($context->toArray());
        $this->assertArrayHasKey('state', $context->toArray());
        $this->assertEquals($context->toArray()['state'], $state->value);
    }
}
