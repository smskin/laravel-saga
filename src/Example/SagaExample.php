<?php

namespace SMSkin\LaravelSaga\Example;

use SMSkin\LaravelSaga\BaseSaga;
use SMSkin\LaravelSaga\Example\Commands\CreateUserCommand;
use SMSkin\LaravelSaga\Example\Events\ESagaExampleFinalized;
use SMSkin\LaravelSaga\Example\Events\EUserBlocked;
use SMSkin\LaravelSaga\Example\Events\EUserCreated;
use SMSkin\LaravelSaga\Models\SagaContext;

class SagaExample extends BaseSaga
{
    protected SagaExampleContext|SagaContext $context;

    protected function setup(): void
    {
        /**
         * Event mapper.
         * Mapper explains to the engine how to find the saga when receiving an event
         */
        $this->builder()
            /**
             * EUserCreated event, in which the correlation id is stored in the corrId field.
             * With the second argument we indicate how to get the correlation id from the event
             */
            ->correlatedById(EUserCreated::class, static function (EUserCreated $event) {
                return $event->corrId;
            })
            /**
             * The EUserBlocked event does not have a correlation id inside it.
             * As the second argument (userId), we pass a field that is in the state machine storage (SagaExampleContext).
             * As the third argument we pass the method with which we will receive the field from the event
             */
            ->correlatedBy(EUserBlocked::class, 'userId', static function (EUserBlocked $event) {
                return $event->userId;
            });

        /**
         * Initialization event/command
         * This block specifies the initialization event/command (when this event/command is received, the saga will be launched).
         */
        $this->builder()
            /**
             * The CreateUserCommand command contains a correlation id that will be assigned to the saga.
             * As the second argument we pass the method for getting the saga context from the command. The method must return a SagaContext instance.
             */
            ->onInitEvent(CreateUserCommand::class, static function (CreateUserCommand $command) {
                return (new SagaExampleContext($command->correlationId))
                    ->setEmail($command->email);
            });

        /**
         * State machine logic
         * This block describes the state machine operation algorithm.
         * Basic words:
         * - initial - The block describes the initialization of the state machine. Reads like: When initializing, do the following...
         * - duringState - The following describes the logic that will be executed for a state machine that is in the specified status (ex: Execute if status is INITIAL)
         * - on - Condition indicating an event (ex: Complete it if you get the event)
         * - transitionTo - State machine status change command (ex: change status to USER_CREATING)
         * - activity - Pointer to execute subprogram (ex: run UserCreatingActivity)
         * - then - Pointer executor of anonymous function (Closure) (ex: run function)
         *
         * In total, this block reads like this:
         * When initializing the state machine,
         * change the status to USER_CREATING,
         * execute the UserCreatingActivity class,
         * execute an anonymous function (Create a user via UserService)
         */
        $this->builder()
            ->initial()
            ->transitionTo(SagaExampleStates::USER_CREATING)
            ->activity(UserCreatingActivity::class)
            ->then(function () {
                (new UserCommandService())->create(
                    $this->context->getId(),
                    $this->context->getEmail()
                );
            });

        /**
         * In total, this block reads like this:
         * When receiving the EUserCreated event, provided that the current state of the state machine is USER_CREATING,
         * execute an anonymous function (save the userId from the received event in the state machine storage),
         * change the status to USER_BLOCKING,
         * execute an anonymous function (Block a user via UserService)
         */
        $this->builder()
            ->duringState(SagaExampleStates::USER_CREATING)
            ->on(EUserCreated::class)
            ->then(function () {
                /**
                 * @var $event EUserCreated
                 */
                $event = $this->getHandledEvent();
                $this->context->setUserId($event->userId);
            })
            ->transitionTo(SagaExampleStates::USER_BLOCKING)
            ->then(function () {
                (new UserCommandService())->block(
                    $this->context->getUserId()
                );
            });

        /**
         * In total, this block reads like this:
         * When receiving the EUserBlocked event, provided that the current state of the state machine is USER_BLOCKING,
         * finalize the state machine
         * publish event ESagaExampleFinalized to the service bus
         */
        $this->builder()
            ->duringState(SagaExampleStates::USER_BLOCKING)
            ->on(EUserBlocked::class)
            ->finalize()
            ->publish(function () {
                return new ESagaExampleFinalized($this->context->getId());
            });
    }
}
