<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';
    protected $primaryKey = 'country_id';
    public $timestamps = false;

    protected $fillable = ['country_name_ar', 'country_name_en', 'iso_code'];
}
