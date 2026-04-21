<?php
namespace App\Console\Commands;

use App\Models\BlockedIp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class BlockIp extends Command
{
    protected $signature = 'ip:block {ip} {--reason=manual} {--hours=}';
    protected $description = 'Block an IP address';

    public function handle()
    {
        $ip      = $this->argument('ip');
        $minutes = $this->option('hours') ? (int)$this->option('hours') * 60 : null;

        BlockedIp::block($ip, $this->option('reason'), 'manual', $minutes);
        Cache::forget("ip_blocked:{$ip}");

        $this->info("✓ Blocked: {$ip}");
    }
}