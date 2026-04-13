<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenUsage extends Model
{
    protected $fillable = [
        'ip',
        'date',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'cost',
        'model',
        'provider'
    ];
}