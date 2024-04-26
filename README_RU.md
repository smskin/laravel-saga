# Движок стейт-машины для Laravel проектов
В процессе работы с .Net Core и [MassTransit](https://masstransit.io) стало обидно, что в Laravel нет готового движка стейт-машины.
По мотивам MassTransit написал библиотеку, работающую аналогично [сагам MassTransit](https://masstransit.io/documentation/patterns/saga/state-machine).

## Установка
1. ```composer require smskin/laravel-saga```
2. ```php artisan vendor:publish --provider=SMSkin\LaravelSaga\Providers\ServiceProvider```

## Настройка
В файле конфигурации ```config/saga.php``` находятся настройки движка, а также описаны стейт-машины.

- logger - класс, отвечающий за логирование процесса работы стейт-машины. Может быть изменен на другой, реализующий интерфейс ```ISagaLogger```
- state-machines - массив зарегистрированных стейт-машин
- repositories
  - default - репозиторий хранения состояния стейт-машины (database)
  - database
    - class - класс репозитория. Может быть изменен на другой, реализующий интерфейс ```ISagaRepository```
    - table - название таблицы, в которой будет хранится состояние стейт-машин

## Структура саги
Рассмотрим сагу из примера данной библиотеки (```SMSkin\LaravelSaga\Example\SagaExample```).

### Свойство $context
В свойстве описан тип(cast) хранимого объекта стейт-машины. Это может быть любой класс, наследуемый от ```SagaContext```.
Объект позволяет хранить некие промежуточные значения полученные при взаимодействии с другими сервисами в ходе работы.

### Метод setup()
В методе описан алгоритм работы стейт-машины.

3 ключевых блока саги:
- корреляция
- инициализационный эвент\команда
- логика перехода стейт-машины

#### Корелляция
Данный блок описывает алгоритм получения идентификатора контекста стейт-машины в репозитории
Описывается с помощью 2х методов:
- ```correlatedById``` - получение по ID объекта
- ```correlatedBy``` - получение по произвольному полю хранилища

```php
$this->builder()
     ->correlatedById(EUserCreated::class, static function (EUserCreated $event) {
            return $event->corrId;
     })
```
Блок читается как: 
При получении эвента EUserCreated возьми ID стейт-машины из свойства ```corrId```.

```php
$this->builder()
     ->correlatedBy(EUserBlocked::class, 'userId', static function (EUserBlocked $event) {
            return $event->userId;
     });
```

Блок читается как: 
При получении эвента EUserBlocked найди стейт-машину по полю userId, значение возьми из ```userId``` эвента.
То есть движок может искать контекст стейт-машины как по UUID, так и по произвольному полю контекста.

#### Инициализационный эвент\команда
Данный блок описывает эвенты\команды, при которых будет инициализирована стейт-машина.

Метод ```onInitEvent``` принимает 2 аргумента:
- класс эвента, который будет зарегистрирован в Laravel
- Closure трансформации эвента в контекст стейт-машины. С помощью данного метод можно записать некие инициализационные данные в объект контекста

```php
 $this->builder()
      ->onInitEvent(CreateUserCommand::class, static function (CreateUserCommand $command) {
            return (new SagaExampleContext($command->correlationId))
                ->setEmail($command->email);
      });
```
Блок читается как:
- при получении команды CreateUserCommand инициализируй стейт-машину
- ID стейт-машины возьми из ```correlationId``` команды
- в контекст стейт-машины сохрани ```email``` из команды

#### Логика перехода стейт-машины
Данный блок описывает алгоритм стейт-машины.
Ключевые слова:
- ```duringState``` - пока состояние стейт-машины
- ```on``` - при получении эвента
- ```then``` - сделай (closure)
- ```activity``` - выполни подпрограмму (класс, реализующий интерфейс ```IActivity```)
- ```transitionTo``` - переключи статус стейт-машины
- ```publish``` - опубликуй эвент
- ```initial``` - сахар инициализации (первый этап)
- ```finalize``` - сахар финализации
 
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

Блок читается как:
- при инициализации
- переключи состояние в ```USER_CREATING```
- выполни подпрограмму ```UserCreatingActivity```
- выполни Closure - вызови ```UserCommandService->create```, передав ID контекста и email (который мы записали в контекст при инициализации)

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

Блок читается как:
- при состоянии стейт-машины ```USER_CREATING```
- при получении эвента ```EUserCreated```
- выполни Closure, которая запишет ```userId``` (из эвента) в контекст стейт-машины
- переключи состояние на ```USER_BLOCKING```
- выполни Closure - вызови ```UserCommandService->block```, передав userId из контекста

```php
$this->builder()
     ->duringState(SagaExampleStates::USER_BLOCKING)
     ->on(EUserBlocked::class)
     ->finalize()
     ->publish(function () {
            return new ESagaExampleFinalized($this->context->getId());
     });
```

Блок читается как:
- при состоянии стейт-машины ```USER_BLOCKING```
- при получении эвента ```EUserBlocked```
- финализируй стейт-машину
- опубликуй эвент ```ESagaExampleFinalized```, передав ID саги

### Базовый принцип работы
Движок работает на базе шины [Laravel Events](https://laravel.com/docs/11.x/events). 
Эвенты, описанные в методе ```setup()``` регистрируются в EventServiceProvider. Сага выступает в качестве Listener.

При попадании эвента в шину брокер Laravel выполняет метод ```handle``` зарегистрированных на данный эвент саг. 

### Оптимизация выполнения
Так как эвенты, на которые регистрируется сага описаны в самой саге, Laravel потребуется время на вычисление данных эвентов из всех саг.
Для оптимизации написана artisan команда, которая сохраняет уже готовый к регистрации кэш сопоставления эвент=сага.

Кэширование
```php
php artisan saga:cache
```

Сброс кэша
```php
php artisan saga:cache:clear
```

### Возможности конфигурации
#### Смена хранилища данных саг
1. Создайте класс, реализующий интерфейс ```ISagaRepository```
2. Добавьте его в config ```saga.repositories```
3. Пропишите его в config переменной ```saga.repositories.default```

### Смена логгера
1. Создайте класс, реализующий интерфейс ```ISagaLogger```
2. Пропишите его в config ```saga.logger```

### Создание своих саг
1. Создайте класс, наследующийся от ```BaseSaga```
2. Опишите в методе ```setup()``` логику стейт-машины
3. Пропишите класс в config ```saga.state-machines```

