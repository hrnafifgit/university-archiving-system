<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasUuids;

    protected $table = 'document_types';
    protected $primaryKey = 'document_type_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['type_code', 'type_name_ar', 'type_name_en', 'applies_to', 'is_required_by_default', 'is_active'];

    public function documents()
    {
        return $this->hasMany(Document::class, 'document_type_id', 'document_type_id');
    }
}
