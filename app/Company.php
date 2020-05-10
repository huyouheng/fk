<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'company';

    protected $fillable = [
        'title',
        'slug',
        'operator',
        'phone',
        'lg_class',
        'sm_class',
        'start_at',
        'users',
        'money',
        'ye_shouru',
        'total_money',
        'zz_shui',
        'sd_shui',
        'zz_sd_total',
        'two_zz',
        'bank_type',
        'bank_num',
    ];

    public function files()
    {
        return $this->hasOne(CompanyAdd::class, 'enterprise_id', 'id');
    }

    public function report()
    {
        return $this->hasOne(Report::class, 'enterprise_id')->orderByDesc('report_at');
    }
}
