<?php

namespace App\Console\Commands;

use App\Models\RefreshToken;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PruneRefreshTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:prune {--dry-run : Show counts only} {--limit=1000 : Max rows to delete per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune revoked/expired refresh tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $retainDays = (int) env('REFRESH_TOKENS_RETAIN_DAYS', 30);
        $cutoff = Carbon::now()->subDays($retainDays);
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $query = RefreshToken::query()
            ->whereNotNull('revoked_at')
            ->orWhere('expires_at', '<', $cutoff);

        $total = (clone $query)->count();
        $this->info("Target rows: {$total} (retain {$retainDays}d, cutoff {$cutoff->toDateTimeString()})");

        if ($dryRun || $total === 0) {
            return self::SUCCESS;
        }

        $deleted = 0;
        do {
            $batch = (clone $query)->orderBy('id')->limit($limit)->pluck('id');
            if ($batch->isEmpty()) break;
            $deleted += RefreshToken::query()->whereIn('id', $batch)->delete();
        } while (true);

        $this->info("Deleted rows: {$deleted}");
        return self::SUCCESS;
    }
}
