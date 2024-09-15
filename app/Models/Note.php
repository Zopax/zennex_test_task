<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mehradsadeghi\FilterQueryString\FilterQueryString;

class Note extends Model
{
    /**
     * @OA\Schema(
     *     schema="Note",
     *     type="object",
     *     required={"header", "text_note", "user_id"},
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         format="int64",
     *         description="ID заметки"
     *     ),
     *     @OA\Property(
     *         property="header",
     *         type="string",
     *         description="Название заметки"
     *     ),
     *     @OA\Property(
     *         property="text_note",
     *         type="string",
     *         description="Контент заметки"
     *     ),
     *     @OA\Property(
     *         property="user_id",
     *         type="integer",
     *         format="int64",
     *         description="ID пользователя создавшего заметку"
     *     ),
     *     @OA\Property(
     *         property="tags",
     *         type="array",
     *         @OA\Items(
     *         type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="tag_name", type="string"),
     *             @OA\Property(property="user_id", type="integer"),
     *         )
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         description="Метка даты"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         description="Последнее обновление"
     *     )
     * )
     */
    use HasFactory;
    use FilterQueryString;
    protected $table = "notes";
    protected $fillable = [
        "header", 
        "text_note", 
        "user_id"
    ];
    protected $filters = [
        'sort' ,
        'like',
        'greater',
        'in'
    ];

    /**
     * @return \App\Models\User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_notes');
    }
}
