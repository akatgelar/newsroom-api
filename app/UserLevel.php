<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLevel extends Model
{
    use Blameable;

    protected $fillable = [
        'name', 'description', 'is_active'
    ];


    protected $casts = [
        'is_active' => 'integer',
    ];


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
