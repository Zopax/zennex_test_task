<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use App\Models\Tag;
use App\Models\Note;
use App\Models\TagNote;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\V1\NoteQueryService;
use App\Services\V1\CreateNoteService;
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
    protected $createNote;

    public function __construct(NoteQueryService $noteQuery, CreateNoteService $createNote)
    {
        $this->noteQuery = $noteQuery;
        $this->createNote = $createNote;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/notes",
     *     summary="Получить все заметки пользователя",
     *     tags={"Notes"},
     *     @OA\Parameter(
     *         name="tags[]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(type="string", example="Имя тега")
     *         ),
     *         description="Введите тег(и) для поиска заметок \n Пример"
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(type="string", example="id,asc")
     *         ),
     *         description="Сортировка по полю. Пример заполнения: ColumnName.filterOrSortMethod"
     *     ),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список заметок",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Note")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="У вас нет заметок"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка в запросе"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try 
        { 
            $user = $request->user();

            if (!empty($request->tags))
            {
                $notes = $this->noteQuery->filterByTags($request)
                    ->where('user_id', $user->id)
                    ->filter()->get();
        
                return new NoteCollection($notes);
            }

            $notes = Note::where('user_id', $user->id)->filter()->get();

            return new NoteCollection($notes);
        }
        catch(\Throwable $th)
        {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
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
     *         response=400,
     *         description="Ошибка в запросе"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */
    public function show(Note $note)
    {    
        return new NoteResource($note);
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
     *         ),
     *       @OA\Response(
     *         response=400,
     *         description="Ошибка в запросе"
     *      ),
     *      @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ"
     *      ),
     *      @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *      )
     *     )
     * )
     */
    public function store(StoreNoteRequest $request)
    {
        $note = $this->createNote->createNewNote($request);
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
     *         description="Заметка обновлена",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Note")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заметка не найдена"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка в запросе"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
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
     *         description="Заметка обновлена",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Note")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заметка не найдена"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка в запросе"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */
    public function update(UpdateNoteRequest $request, Note $note)
    {   
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
     *         description="Заметка удалена успешно",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Заметка не найдена"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ"
     *     )
     * )
     */
    public function destroy(Note $note)
    {
        try 
        {   
            DB::transaction(function() use ($note){
                TagNote::where('note_id', $note->id)->delete();
                Note::destroy($note->id);
            });

            return response()->json([
                'message' => 'Заметка удалена'
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
