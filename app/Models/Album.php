<?php

namespace App\Models;

use App\Models\User;
use App\Models\Track;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Album extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'cover_pic',
        'release_date'
    ];

    protected $dates = [
        'release_date' => 'datetime',
        'deleted_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tracks()
    {
        return $this->hasMany(Track::class)->orderBy('number', 'ASC');
    }
}
