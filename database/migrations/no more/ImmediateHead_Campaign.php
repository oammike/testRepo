<?php

namespace OAMPI_Eval;

use Illuminate\Database\Eloquent\Model;

class ImmediateHead_Campaign extends Model
{
    protected $table= 'immediateHead_Campaigns';

    protected $fillable = [
        'immediateHead_id', 'campaign_id'
    ]; 

}
