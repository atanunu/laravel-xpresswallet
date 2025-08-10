<?php

namespace Atanunu\XpressWallet\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Atanunu\XpressWallet\Models\ApiCallLog;
use Atanunu\XpressWallet\Models\WebhookEvent;
use Atanunu\XpressWallet\Models\XpressToken;

class XpressPruneCommand extends Command
{
    protected $signature = 'xpress:prune {--days=} {--dry-run}';
    protected $description = 'Prune old Xpress Wallet logs, webhook events, and excess tokens';

    public function handle(): int
    {
        $days = (int)($this->option('days') ?? config('xpresswallet.retention_days', 90));
        $cutoff = Carbon::now()->subDays($days);
        $dry = $this->option('dry-run');

        $this->info("Pruning records older than {$cutoff->toDateTimeString()} (days={$days})".($dry ? ' [DRY RUN]' : ''));

        $totals = [];
        foreach ([ApiCallLog::class => 'api_call_logs', WebhookEvent::class => 'webhook_events'] as $model => $label) {
            $count = $model::query()->where('created_at','<',$cutoff)->count();
            $totals[$label] = $count;
            if (! $dry && $count) {
                $model::query()->where('created_at','<',$cutoff)->chunkById(500, function($chunk) use ($model) {
                    $ids = $chunk->pluck('id');
                    $model::query()->whereIn('id', $ids)->delete();
                });
            }
        }

        // Token trimming: keep most recent N
        $maxTokens = (int) config('xpresswallet.max_tokens', 50);
        $tokenExcess = max(0, XpressToken::query()->count() - $maxTokens);
        if ($tokenExcess > 0) {
            $this->line("Excess tokens: {$tokenExcess} (keeping newest {$maxTokens})");
            if (! $dry) {
                $ids = XpressToken::query()->orderBy('id')->limit($tokenExcess)->pluck('id');
                XpressToken::query()->whereIn('id', $ids)->delete();
            }
        }

        foreach ($totals as $table => $count) {
            $this->line(($dry ? '[DRY] ' : '')."{$table}: {$count} old rows".($dry ? ' (not deleted)' : ' deleted'));
        }

        $this->info('Prune complete.');
        return self::SUCCESS;
    }
}
