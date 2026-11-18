<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicRank extends Model
{
    use HasFactory;

    protected $table = 'academic_ranks';

    protected $fillable = ['name', 'seniority_order'];
}
