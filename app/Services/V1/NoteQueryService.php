<?php

namespace App\Services\V1;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\Tag;

class NoteQueryService
{
   public function filterByTags(Request $request)
   {
        $tagNames = $request->tags;

        $query = Note::query();

        if (!empty($tagNames)) 
        {
            $tagIds = Tag::whereIn('tag_name', $tagNames)->pluck('id');
            $query->whereHas('tags', function ($query) use ($tagIds) 
            {
                $query->whereIn('tags.id', $tagIds);
            });
        }

        return $query;
    }
}
