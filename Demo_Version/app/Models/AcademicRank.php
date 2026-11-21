<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AcademicRank extends Model
{
    use HasUuids;

    protected $table = 'academic_ranks';
    protected $primaryKey = 'academic_rank_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['rank_code', 'rank_name_ar', 'rank_name_en', 'rank_order'];

    public function employmentRecords()
    {
        return $this->hasMany(EmploymentRecord::class, 'academic_rank_id', 'academic_rank_id');
    }
}
