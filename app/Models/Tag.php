<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = "tags";
    protected $fillable = [
        "tag_name",
        "user_id"
    ];

    /**
     * @return \App\Models\User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notes()
    {
        return $this->belongsToMany(Note::class, 'tag_notes');
    }
}
