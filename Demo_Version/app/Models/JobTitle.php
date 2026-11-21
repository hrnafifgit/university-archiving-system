<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JobTitle extends Model
{
    use HasUuids;

    protected $table = 'job_titles';
    protected $primaryKey = 'job_title_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['title_name_ar', 'title_name_en', 'employee_category_id', 'is_active'];
}
