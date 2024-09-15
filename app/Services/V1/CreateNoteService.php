<?php

namespace App\Services\V1;

use App\Models\Tag;
use App\Models\Note;
use Illuminate\Http\Request;
use App\Http\Resources\V1\NoteResource;
use App\Http\Requests\V1\StoreNoteRequest; 

class CreateNoteService
{
    public function createNewNote(StoreNoteRequest $request)
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
        
        return $note;
    }
}