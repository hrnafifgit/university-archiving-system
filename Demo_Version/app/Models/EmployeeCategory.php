<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCategory extends Model
{
    protected $table = 'employee_categories';
    protected $primaryKey = 'employee_category_id';
    public $timestamps = false;

    protected $fillable = ['category_code', 'category_name_ar', 'category_name_en', 'description'];
}
