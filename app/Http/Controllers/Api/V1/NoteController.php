<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\TagNote;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\NoteResource;
use App\Http\Resources\V1\NoteCollection;
use App\Http\Requests\V1\StoreNoteRequest; 
use App\Http\Requests\V1\UpdateNoteRequest;


class NoteController extends Controller
{
    /**
     *  Display a listing of the resource
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new NoteCollection(Note::all());
    }


    /**
     *  Display a one thing of resource
     * 
     * @param \App\Models\Note $note
     * @return \Illuminate\Http\Response
     */
    public function show(Note $note)
    {
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
        return new NoteResource(Note::create($request->all()));
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
        $note->update($request->all());
    }

    /**
     *  Remove the specified resource from storage
     * 
     * @param \App\Models\Note $note
     * @return \Illuminate\Http\Response
     */
    public function destroy(Note $note)
    {
        TagNote::where('note_id', $note->id)->delete();
        return Note::destroy($note->id);
    }
}
