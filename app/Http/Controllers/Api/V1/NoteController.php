<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tag;
use App\Models\Note;
use App\Models\TagNote;
use Illuminate\Http\Request;
use App\Services\V1\NoteQuery;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\NoteResource;
use App\Http\Resources\V1\NoteCollection;
use App\Http\Requests\V1\StoreNoteRequest; 
use App\Http\Requests\V1\UpdateNoteRequest;

/**
 * @OA\Info(
 *     title="Laravel API Documentation",
 *     version="1.0.0",
 * ),
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer"
 * ),
 */
class NoteController extends Controller
{
    protected $noteQuery;

    public function __construct(NoteQuery $noteQuery)
    {
        $this->noteQuery = $noteQuery;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notes",
     *     summary="Получить все заметки пользователя",
     *     tags={"Notes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список заметок"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="У вас нет заметок"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) 
        {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notes = $this->noteQuery->filterByTags($request)
            ->where('user_id', $user->id) 
            ->get();

        return new NoteCollection($notes);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notes/{id}",
     *     summary="Получить заметку по ID",
     *     tags={"Notes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Заметка найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="header", type="string"),
     *             @OA\Property(property="text_onote", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заметка не найдена"
     *     )
     * )
     */
    public function show(Note $note)
    {
        if ($note->user_id !== auth()->id()) 
        {
            return response()->json([
                'message' => 'Not authorized.'
            ], 403);
        }
    
        return new NoteResource($note);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notes/search",
     *     summary="Поиск заметок по тегам",
     *     tags={"Notes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *              @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string",
     *                     example="Тест"
     *                 )
     *             ) 
     *         )
     *     ),
     *      security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Заметки найдены",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="header", type="string"),
     *             @OA\Property(property="text_note", type="string")
     *         )
     *     ),
     * @OA\Response(
     *         response=204,
     *         description="Результата нет",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="header", type="string"),
     *             @OA\Property(property="text_note", type="string")
     *         )
     *     )
     * )
     */
    public function searchByTags(Request $request)
    {
        $user = $request->user();
        $tags = $request->input('tags');
        $userId = auth()->id();

        if (!$user) 
        {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notes = Note::where('user_id', $userId) 
        ->whereHas('tags', function ($query) use ($tags) {
            $query->whereIn('tag_name', $tags);
        })->get();

        // Возвращаем заметки в формате JSON
        return new NoteCollection($notes);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notes",
     *     summary="Создать новую заметку",
     *     tags={"Notes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="header", type="string", example="Новая заметка"),
     *             @OA\Property(property="text_note", type="string", example="Содержание заметки"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="tag_name", type="string", example="Тест")
     *                 )
     *             ) 
     *         )
     *     ),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Заметка создана",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="header", type="string"),
     *             @OA\Property(property="text_note", type="string"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="tag_name", type="string")
     *                 )
     *            )
     *         )
     *     )
     * )
     */
    public function store(StoreNoteRequest $request)
    {
        $note = Note::create([
            'header' => $request->header,
            'text_note' => $request->text_note,
            'user_id' => $request->user()->id,
        ]);

        if ($request->has('tags')) 
        {
            foreach ($request->tags as $tag)
            {   
                if (Tag::where('tag_name', "=", $tag['tag_name'])
                    ->where('user_id', "=", $request->user()->id)
                    ->get()
                    ->isEmpty())
                {
                    Tag::create([
                        'tag_name' => $tag['tag_name'],
                        'user_id' => $request->user()->id,
                    ]);
                }
            }
        }

        $tags = Tag::whereIn('tag_name', $request->tags)->get();
        $note->tags()->sync($tags);
    
        return new NoteResource($note);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/notes/{id}",
     *     summary="Обновить заметку",
     *     tags={"Notes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="header", type="string", example="Обновленный заголовок"),
     *             @OA\Property(property="text_note", type="string", example="Обновленное содержание"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="tag_name", type="string", example="Тег")
     *                 )
     *            )
     *         )
     *     ),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Заметка обновлена"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заметка не найдена"
     *     )
     * )
     * @OA\Put(
     *     path="/api/v1/notes/{id}",
     *     summary="Обновить заметку",
     *     tags={"Notes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="header", type="string", example="Обновленный заголовок"),
     *             @OA\Property(property="text_note", type="string", example="Обновленное содержание"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="tag_name", type="string", example="Тег")
     *                 )
     *            )
     *         )
     *     ),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Заметка обновлена"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заметка не найдена"
     *     )
     * )
     */
    public function update(UpdateNoteRequest $request, Note $note)
    {   
        if ($note->user_id !== auth()->id()) 
        {
            return response()->json([
                'message' => 'Not authorized.'
            ], 403);
        }
    
        $note->update($request->all());
        return new NoteResource($note);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/notes/{id}",
     *     tags={"Notes"},
     *     summary="Удаление заметок по ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the note",
     *         @OA\Schema(type="integer")
     *     ),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=204,
     *         description="Заметка удалена успешно"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заметка не найдена"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy(Note $note)
    {
        try 
        {
            if ($note->user_id !== auth()->id()) 
            {
                return response()->json(['message' => 'Not authorized.'], 403);
            }
            
            TagNote::where('note_id', $note->id)->delete();
            Note::destroy($note->id);
            return response()->json([
                'message' => 'Note is deleted'
            ]);
        }
        catch(\Throwable $th)
        {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
}
