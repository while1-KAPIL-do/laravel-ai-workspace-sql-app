<?php
namespace App\Console\Commands;

use App\Models\BlockedIp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UnblockIp extends Command
{
    protected $signature = 'ip:unblock {ip}';
    protected $description = 'Unblock an IP address';

    public function handle()
    {
        $ip = $this->argument('ip');

        BlockedIp::where('ip_address', $ip)->delete();
        Cache::forget("ip_blocked:{$ip}");

        $this->info("✓ Unblocked: {$ip}");
    }
}