<?php

namespace SMSkin\LaravelSaga\Command;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class SagaCacheClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saga:cache:clear';

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
        $filePath = SagaCache::getCacheFile();
        if (!file_exists($filePath)) {
            $this->warn('Sagas not cached');
            return;
        }
        $this->files->delete($filePath);
        $this->info('Sagas cache file deleted');
    }
}
