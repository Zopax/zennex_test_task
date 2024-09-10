<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagNote extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'tag_notes';
}
