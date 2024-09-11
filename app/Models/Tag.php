<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    /**
     * @OA\Schema(
     *     schema="Tag",
     *     type="object",
     *     title="Tag",
     *     description="Модель тега",
     *     required={"id", "tag_name"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Идентификатор тега"
     *     ),
     *     @OA\Property(
     *         property="tag_name",
     *         type="string",
     *         description="Название тега"
     *     ),
     *     @OA\Property(
     *         property="user_id",
     *         type="integer",
     *         description="ID пользователя, которому принадлежит тег"
     *     )
     * )
     */
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
