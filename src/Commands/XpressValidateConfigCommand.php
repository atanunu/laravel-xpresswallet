<?php

namespace Atanunu\XpressWallet\Commands;

use Illuminate\Console\Command;

class XpressValidateConfigCommand extends Command
{
    protected $signature = 'xpress:validate-config';

    protected $description = 'Validate XpressWallet configuration (feature 7)';

    public function handle(): int
    {
        $cfg = config('xpresswallet');
        $errors = [];
        foreach (['base_url', 'email', 'password'] as $k) {
            if (empty($cfg[$k])) {
                $errors[] = "Missing config key: $k";
            }
        }
        if (! filter_var($cfg['base_url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'base_url is not a valid URL';
        }
        if (($cfg['retries']['max_attempts'] ?? 1) < 1) {
            $errors[] = 'retries.max_attempts must be >=1';
        }
        if ($errors) {
            foreach ($errors as $e) {
                $this->error($e);
            }
            $this->line((string) json_encode(['ok' => false, 'errors' => $errors]));

            return 1;
        }
        $this->info('Configuration valid');
        $this->line((string) json_encode(['ok' => true]));

        return 0;
    }
}
