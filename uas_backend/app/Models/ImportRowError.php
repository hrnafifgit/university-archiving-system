<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportRowError extends Model
{
    protected $table = 'import_row_errors';
    protected $primaryKey = 'import_row_error_id';
    public $timestamps = false;

    protected $fillable = [
        'import_batch_id',
        'source_row_number',
        'row_data',
        'error_details',
    ];

    protected $casts = [
        'row_data' => 'array',
    ];

    public function batch()
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id', 'import_batch_id');
    }
}
