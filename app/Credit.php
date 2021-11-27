<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    protected $fillable = [
        'id','month', 'date','bonus','note'
        ];
}
