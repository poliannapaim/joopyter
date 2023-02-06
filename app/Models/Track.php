<?php

namespace App\Models;

use App\Models\Album;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Track extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'title'
    ];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

}
