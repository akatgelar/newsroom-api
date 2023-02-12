<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Blameable;
    use Notifiable;

    protected $fillable = [
        'user_level_id',
        'email', 'phone', 'username', 'password', 'nama',
        'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'foto',
        'api_token','firebase_token', 'is_active',

    ];


    protected $hidden = [
        'password', 'pin', 'remember_token', 'password_plain'
    ];


    protected $casts = [
        'is_active' => 'integer',
    ];


    public function generateToken()
    {
        $this->api_token = str_random(60);
        $this->save();

        return $this->api_token;
    }


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
