<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    // use Blameable;

    protected $fillable = [
        'action', 'object', 'object_id', 'user_id'
    ];


    protected $hidden = [
        // 'created_by', 'updated_by', 'updated_at'
    ];


    protected $casts = [
        // 'is_active' => 'integer',
    ];


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
