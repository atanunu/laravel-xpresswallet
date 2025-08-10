<?php

namespace Atanunu\XpressWallet\Commands;

use Atanunu\XpressWallet\Facades\XpressWallet;
use Illuminate\Console\Command;

class XpressRefreshTokensCommand extends Command
{
    protected $signature = 'xpress:refresh';

    protected $description = 'Refresh Xpress Wallet tokens';

    public function handle(): int
    {
        $res = XpressWallet::refresh();
        $this->info('Tokens refreshed.');
        $this->line((string) json_encode($res, JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
