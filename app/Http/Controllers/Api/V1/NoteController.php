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

class NoteController extends Controller
{
    protected $noteQuery;

    public function __construct(NoteQuery $noteQuery)
    {
        $this->noteQuery = $noteQuery;
    }

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

        $notes = $this->noteQuery->filterByTags($request) // get filter request: 
            ->where('user_id', $user->id)   // localhost:8000/api/v1/notes?tags[]=tag1&tags[]=tag2
            ->get();

        return new NoteCollection($notes);
    }

    /**
     *  Display a one thing of resource
     * 
     * @param \App\Models\Note $note
     * @return \Illuminate\Http\Response
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
     *  Store a newly created resource in storage
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
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
     *  Update the specified resource in storage
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Note $note
     * @return \Illuminate\Http\Response
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
     *  Remove the specified resource from storage
     * 
     * @param \App\Models\Note $note
     * @return \Illuminate\Http\Response
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
