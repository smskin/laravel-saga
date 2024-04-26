# State Machine Engine for Laravel Projects
[![Composer](https://github.com/smskin/laravel-saga/actions/workflows/composer.yml/badge.svg)](https://github.com/smskin/laravel-saga/actions/workflows/composer.yml)
[![Static Analysis](https://github.com/smskin/laravel-saga/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/smskin/laravel-saga/actions/workflows/static-analysis.yml)
[![Tests](https://github.com/smskin/laravel-saga/actions/workflows/tests.yml/badge.svg)](https://github.com/smskin/laravel-saga/actions/workflows/tests.yml)

While working with .Net Core and [MassTransit](https://masstransit.io), it became frustrating that Laravel lacked a ready-made state machine engine. Inspired by MassTransit, I wrote a library that functions similarly to [MassTransit sagas](https://masstransit.io/documentation/patterns/saga/state-machine).

## Installation
1. ```composer require smskin/laravel-saga```
2. ```php artisan vendor:publish --provider=SMSkin\LaravelSaga\Providers\ServiceProvider```

## Configuration
In the configuration file ```config/saga.php```, you will find the engine settings and descriptions of state machines.

- logger - a class responsible for logging the state machine's operation process. Can be changed to another class implementing the ```ISagaLogger``` interface.
- state-machines - an array of registered state machines.
- repositories
  - default - the repository for storing the state machine's state (database).
  - database 
    - class - the repository class. Can be changed to another class implementing the ```ISagaRepository``` interface. 
    - table - the name of the table where the state machine's state will be stored.

## Saga Structure
Let's examine a saga from an example in this library (```SMSkin\LaravelSaga\Example\SagaExample```).

### Property $context
This property describes the type (cast) of the stored object of the state machine. It can be any class inheriting from SagaContext. This object allows storing intermediate values obtained during interaction with other services during operation.

### Method setup()
This method describes the state machine's operation algorithm.

Three key blocks of a saga:
- correlation
- initialization event\command
- state machine transition logic

#### Correlation
This block describes the algorithm for obtaining the identifier of the state machine's context in the repository. It's described using two methods:
- correlatedById - getting the object by ID.
- correlatedBy - getting it by any storage field.
```php
$this->builder()
     ->correlatedById(EUserCreated::class, static function (EUserCreated $event) {
            return $event->corrId;
     })
```
This block can be read as:
Upon receiving the EUserCreated event, take the state machine's ID from the ```corrId``` property.

```php
$this->builder()
     ->correlatedBy(EUserBlocked::class, 'userId', static function (EUserBlocked $event) {
            return $event->userId;
     });
```
This block can be read as:
Upon receiving the EUserBlocked event, find the state machine by the ```userId``` field, taking the value from the ```userId``` event. Thus, the engine can search for the state machine's context both by UUID and by any context field.

#### Initialization Event\Command
This block describes the events\commands that will initialize the state machine.

The ```onInitEvent``` method takes two arguments:
- The event class to be registered in Laravel.
- A Closure for transforming the event into the state machine's context. With this method, you can save some initialization data in the context object.

```php
 $this->builder()
      ->onInitEvent(CreateUserCommand::class, static function (CreateUserCommand $command) {
            return (new SagaExampleContext($command->correlationId))
                ->setEmail($command->email);
      });
```

This block can be read as:
- Upon receiving the CreateUserCommand command, initialize the state machine.
- Take the state machine's ID from the ```correlationId``` command.
- Save the ```email``` from the command in the state machine's context.

#### State Machine Transition Logic
This block describes the state machine's algorithm.
Key phrases:
- ```duringState``` - while in the state machine's state.
- ```on``` - upon receiving an event.
- ```then``` - do (closure).
- ```activity``` - perform a subroutine (class implementing the IActivity interface).
- ```transitionTo``` - switch the state machine's status.
- ```publish``` - publish an event.
- ```initial``` - sugar for initialization (first stage).
- ```finalize``` - sugar for finalization.

```php
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
```

This block can be read as:
- Upon initialization.
- Switch the state to ```USER_CREATING```.
- Perform the ```UserCreatingActivity``` subprogram.
- Execute the Closure - call ```UserCommandService->create```, passing the ```context ID``` and ```email``` (which we stored in the context during initialization).

```php
 $this->builder()
      ->duringState(SagaExampleStates::USER_CREATING)
      ->on(EUserCreated::class)
      ->then(function () {
            $event = $this->getHandledEvent();
            $this->context->setUserId($event->userId);
      })
      ->transitionTo(SagaExampleStates::USER_BLOCKING)
      ->then(function () {
            (new UserCommandService())->block(
                $this->context->getUserId()
            );
      });
```

This block can be read as:
- While in the state ```USER_CREATING```.
- Upon receiving the ```EUserCreated``` event.
- Execute the Closure, which will write the ```userId``` (from the event) into the state machine's context.
- Switch the state to ```USER_BLOCKING```.
- Execute the Closure - call ```UserCommandService->block```, passing the ```userId``` from the context.

```php
$this->builder()
     ->duringState(SagaExampleStates::USER_BLOCKING)
     ->on(EUserBlocked::class)
     ->finalize()
     ->publish(function () {
            return new ESagaExampleFinalized($this->context->getId());
     });
```

This block can be read as:
- While in the state ```USER_BLOCKING```.
- Upon receiving the ```EUserBlocked``` event.
- Finalize the state machine.
- Publish the event ```ESagaExampleFinalized```, passing the ```saga ID```.

### Basic Operation Principle
The engine operates based on the [Laravel Events](https://laravel.com/docs/11.x/events). The events described in the ```setup()``` method are registered in the EventServiceProvider. The saga acts as a Listener.

When an event enters the bus, the Laravel broker executes the ```handle``` method of the sagas registered for that event.

### Execution Optimization
Since the events to which the saga registers are described within the saga itself, Laravel will require time to compute these events from all sagas. To optimize this, an artisan command is written that saves a pre-computed cache of event=saga mapping ready for registration.

Caching
```php
php artisan saga:cache
```

Cache Clearing
```php
php artisan saga:cache:clear
```

### Configuration Options
#### Changing the Saga Data Storage Repository
1. Create a class implementing the ```ISagaRepository``` interface.
2. Add it to the ```saga.repositories``` configuration.
3. Specify it in the ```saga.repositories.default``` configuration variable.

### Changing the Logger
1. Create a class implementing the ```ISagaLogger``` interface.
2. Specify it in the ```saga.logger``` configuration.

### Creating Custom Sagas
1. Create a class inheriting from ```BaseSaga```.
2. Describe the logic of the state machine in the ```setup()``` method.
3. Specify the class in the ```saga.state-machines``` configuration.
