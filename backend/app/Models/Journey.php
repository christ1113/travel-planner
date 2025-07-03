<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journey extends Model
{
    protected $primaryKey = 'journey_id';
    //
    protected $fillable = [
        'plan_id',
        'date',
        'time',
        'journey_title',
        'links',
        'image',
        'notes',
    ];

    protected $casts = [
        'date' => 'date', // 轉為 Carbon 物件
        'time' => 'datetime:H:i',
        'links' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    // 一個 journey 屬於一個 plan
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'plan_id');
    }
}
