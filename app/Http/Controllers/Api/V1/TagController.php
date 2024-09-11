<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tag;
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
     *  Display a listing of the resource
     * 
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) 
        {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return new TagCollection(Tag::all()->where('user_id', $user->id)); 
    }

     /**
     *  Display a one thing of resource
     * 
     * @param \App\Models\Tag $tag
     * @return \Illuminate\Http\Response
     */
    public function show(Tag $tag)
    {
        return  new TagResource($tag);
    }

    /**
     *  Store a newly created resource in storage
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTagRequest $request)
    { 
        $tag = Tag::create([
            'tag_name' => $request->tag_name,
            'user_id' => $request->user()->id
        ]);

        return $tag;
    }

    /*
    *  Remove the specified resource from storage
    * 
    * @param \App\Models\Tag $tag
    * @return \Illuminate\Http\Response
    */
   public function destroy(Tag $tag)
   {
        if ($note->user_id !== auth()->id()) 
        {
            return response()->json(['message' => 'Not authorized.'], 403);
        }

        TagNote::where('tag_id', $tag->id)->delete();
        Tag::destroy($tag->id);
   }
}
