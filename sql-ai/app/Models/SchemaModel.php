<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchemaModel extends Model
{
    protected $table = 'schemas';

    protected $fillable = [
        'user_id',
        'session_id',
        'name',
        'file_path',
        'schema_json',
        'raw_sql',
        'tables_count',
        'columns_count',
        'is_active',
        'estimated_tokens'
    ];
    

    protected $casts = [
        'schema_json' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Relation: Schema belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}