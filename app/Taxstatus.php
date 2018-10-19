<?php

namespace OAMPI_Eval;

use Illuminate\Database\Eloquent\Model;

class Taxstatus extends Model
{
    protected $table='taxstatuses';

    protected $fillable = [
        'name','description',
    ];


}


