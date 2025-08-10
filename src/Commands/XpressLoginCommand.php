<?php

namespace Atanunu\XpressWallet\Commands;

use Illuminate\Console\Command;
use Atanunu\XpressWallet\Facades\XpressWallet;

class XpressLoginCommand extends Command
{
    protected $signature = 'xpress:login {--email=} {--password=}';
    protected $description = 'Login to Xpress Wallet and store tokens';

    public function handle(): int
    {
        $email = $this->option('email') ?? config('xpresswallet.email');
        $password = $this->option('password') ?? config('xpresswallet.password');

        $res = XpressWallet::login($email, $password);
        $this->info('Login successful; tokens stored.');
    $this->line((string) json_encode($res, JSON_UNESCAPED_SLASHES));
        return self::SUCCESS;
    }
}