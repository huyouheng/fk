<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $table = 'industry';

    public $timestamps = true;

    protected $primaryKey = 'id';

    protected $fillable = [
        'pid', 'name',
    ];

    public static function parent()
    {
        return self::where('pid', 0)
            ->select('id', 'pid', 'name')
            ->get();
    }

    public static function fetchData()
    {
        return self::where('pid', 0)->with('children:id,pid,name')
            ->select('id', 'pid', 'name')
            ->get();
    }

    public function children()
    {
        return $this->hasMany(self::class, 'pid','id');
    }
}
