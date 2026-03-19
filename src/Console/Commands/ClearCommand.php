<?php

namespace QuerySpy\Console\Commands;

use Illuminate\Console\Command;
use QuerySpy\Services\LogStore;

class ClearCommand extends Command
{
    protected $signature   = 'queryspy:clear';
    protected $description = 'Clear all QuerySpy performance log entries';

    public function handle(LogStore $store): int
    {
        $store->clear();
        $this->info('QuerySpy logs cleared.');
        return self::SUCCESS;
    }
}
