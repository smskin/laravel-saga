<?php

declare(strict_types=1);

namespace SMSkin\LaravelSaga\Tests;

use Mockery;
use Orchestra\Testbench\TestCase as BaseTestCase;
use ReflectionClass;
use SMSkin\LaravelSaga\Contracts\ISagaLogger;
use SMSkin\LaravelSaga\Contracts\ISagaRepository;
use SMSkin\LaravelSaga\Models\SagaContext;
use SMSkin\LaravelSaga\Tests\Mocks\LoggerInterfaceMock;
use SMSkin\LaravelSaga\Tests\Mocks\SagaDatabaseModelMock;
use SMSkin\LaravelSaga\Tests\Mocks\SagaLoggerMock;
use SMSkin\LaravelSaga\Tests\Mocks\SagaRepositoryMock;
use SMSkin\LaravelSaga\Tests\TestSupport\TestSaga;

abstract class TestCase extends BaseTestCase
{
    use SagaDatabaseModelMock;
    use LoggerInterfaceMock;
    use SagaRepositoryMock;
    use SagaLoggerMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpSagaDatabaseModelMock();
        $this->setUpLoggerInterfaceMock();
        $this->setUpDatabaseRepositoryMock();
        $this->setUpSagaLoggerMock();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    protected function createTestSaga(): TestSaga
    {
        return new TestSaga(
            $this->app,
            $this->app->make(ISagaLogger::class),
            $this->app->make(ISagaRepository::class)
        );
    }

    protected function setTestSagaContext(TestSaga $saga, SagaContext $context)
    {
        $r = new ReflectionClass($saga);
        $r->getProperty('context')->setValue($saga, $context);
    }
}
