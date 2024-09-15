<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tag;
use App\Models\TagNote;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TagResource;
use App\Http\Resources\V1\TagCollection;
use App\Http\Requests\V1\StoreTagRequest;
use App\Http\Requests\V1\UpdateTagRequest;

class TagController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/tags",
     *     summary="Получение списка тегов",
     *     description="Возвращает список всех тегов",
     *     tags={"Tags"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Список тегов",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Tag")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Тег не найден"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ"
     *     )
     * )
     */
    public function index(Request $request)
    {
        return new TagCollection(Tag::all()->where('user_id', $request->user()->id)); 
    }

     /**
     * @OA\Get(
     *     path="/api/v1/tags/{id}",
     *     summary="Получение тега по ID",
     *     tags={"Tags"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Тег найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="tag_name", type="string"),
     *             @OA\Property(property="user_id", type="integer"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Тег не найден"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ"
     *     )
     * )
     */
    public function show(Tag $tag)
    {
        return  new TagResource($tag);
    }

     /**
     * @OA\Post(
     *     path="/api/v1/tags",
     *     tags={"Tags"},
     *     summary="Создание нового тега",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="tag_name", type="string", example="Имя тега")
     *         )
     *     ),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=201,
     *         description="Тег создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="tag_name", type="string"),
     *             @OA\Property(property="user_id", type="integer"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка валидации"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Тег не найден"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ"
     *     )
     * )
     */
    public function store(StoreTagRequest $request)
    { 
        $tag = Tag::create([
            'tag_name' => $request->tag_name,
            'user_id' => $request->user()->id
        ]);

        return $tag;
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/tags/{id}",
     *     tags={"Tags"},
     *     summary="Удаление тегов по ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the tag",
     *         @OA\Schema(type="integer")
     *     ),
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=204,
     *         description="Тег удален успешно"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Тег не найден"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ"
     *     )
     * )
     */
   public function destroy(Tag $tag)
   {
        try 
        {
            if ($tag->user_id !== auth()->id()) 
            {
                return response()->json(['message' => 'Not authorized.'], 403);
            }
            
            TagNote::where('tag_id', $tag->id)->delete();
            Tag::destroy($tag->id);
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
