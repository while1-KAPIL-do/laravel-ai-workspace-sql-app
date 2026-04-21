<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    protected $fillable = ['ip_address', 'reason', 'blocked_by', 'hit_count', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public static function isBlocked(string $ip): bool
    {
        return static::where('ip_address', $ip)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public static function block(
        string $ip,
        string $reason = 'manual',
        string $by = 'system',
        ?int $minutesUntilExpiry = null
    ): void {
        static::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason'     => $reason,
                'blocked_by' => $by,
                'expires_at' => $minutesUntilExpiry
                    ? now()->addMinutes($minutesUntilExpiry)
                    : null,
            ]
        );
    }
}