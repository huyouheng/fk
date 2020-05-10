<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyAdd extends Model
{
    protected $table= 'company_adds';

    protected $fillable = [
        'enterprise_id',
        'ye_copy',
        'sd_report',
        'ns_prove',
        'zzs_prove',
        'cp_card',
    ];
    protected $casts = [
//        'ye_copy' => 'array',
//        'sd_report' => 'array',
//        'ns_prove' => 'array',
//        'zzs_prove' => 'array',
//        'cp_card' => 'array',
    ];
}
