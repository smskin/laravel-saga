<?php

namespace SMSkin\LaravelSaga\Command;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use LogicException;
use SMSkin\LaravelSaga\BaseSaga;
use Throwable;

class SagaCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saga:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $sagas = Config::get('saga.state-machines');
        $mapped = [];
        foreach ($sagas as $className) {
            $saga = $this->getSaga($className);
            $events = $saga->getEvents();

            foreach ($events as $event) {
                $mapped[$event][] = $className;
            }
        }

        $filePath = $this->getCacheFile();
        /** @noinspection DebugFunctionUsageInspection */
        $this->files->put(
            $filePath,
            '<?php return ' . var_export($mapped, true) . ';' . PHP_EOL
        );

        try {
            require $filePath;
        } catch (Throwable $e) {
            $this->files->delete($filePath);

            throw new LogicException('Your configuration files are not serializable.', 0, $e);
        }

        $this->components->info('Sagas cached successfully.');
    }

    private function getSaga(string $className): BaseSaga
    {
        return app($className);
    }

    public static function getCacheFile(): string
    {
        return app()->bootstrapPath('cache/sagas.php');
    }
}
