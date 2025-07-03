<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $primaryKey = 'plan_id';
    //
    protected $fillable = [
        'user_id',
        'plan_title',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    //一個plan屬於一個user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    //一個plan可以有很多個journey
    public function journeys()
    {
        return $this->hasMany(Journey::class, 'journey_id', 'plan_id');
    }
}
