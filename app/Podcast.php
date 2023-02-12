<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    use Blameable;

    protected $fillable = [
        'title', 'slug', 'creator', 'category', 'image', 'source',
        'link', 'link_type', 'tags', 'desc_short', 'desc_long', 'notes',
        'count_like', 'count_view', 'count_share_fb', 'count_share_tw',
        'count_share_wa', 'count_share_tl', 'count_share_link',
        'is_active'

    ];


    protected $hidden = [
    ];


    protected $casts = [
        'is_active' => 'integer',
    ];


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
