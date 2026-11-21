<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ImportBatch extends Model
{
    use HasUuids;

    protected $table = 'import_batches';
    protected $primaryKey = 'import_batch_id';
    public $timestamps = false;

    protected $fillable = [
        'source_file_name',
        'data_source_id',
        'imported_by_user_id',
        'imported_at',
        'total_rows',
        'successful_rows',
        'rejected_rows',
        'notes',
    ];

    protected $casts = [
        'imported_at' => 'datetime',
        'total_rows' => 'integer',
        'successful_rows' => 'integer',
        'rejected_rows' => 'integer',
    ];

    public function errors()
    {
        return $this->hasMany(ImportRowError::class, 'import_batch_id', 'import_batch_id');
    }
}
