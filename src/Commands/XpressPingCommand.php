<?php

namespace Atanunu\XpressWallet\Commands;

use Illuminate\Console\Command;
use Atanunu\XpressWallet\Facades\XpressWallet;

class XpressPingCommand extends Command
{
    protected $signature = 'xpress:ping';
    protected $description = 'Sample call to test connectivity (fetch customers page 1)';

    public function handle(): int
    {
        $res = XpressWallet::customers()->all(1);
    $this->line((string) json_encode($res, JSON_UNESCAPED_SLASHES));
        return self::SUCCESS;
    }
}