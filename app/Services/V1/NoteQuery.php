<?php

namespace App\Services\V1;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\Tag;
use App\Http\Resources\V1\NoteResource;
use App\Http\Resources\V1\NoteCollection;


class NoteQuery
{
   public function filterByTags(Request $request)
   {
        $tagNames = $request->input('tags', []);

        $query = Note::query();

        if (!empty($tagNames)) {
            $tagIds = Tag::whereIn('tag_name', $tagNames)->pluck('id');
            $query->whereHas('tags', function ($query) use ($tagIds) {
                $query->whereIn('id', $tagIds);
            });
        }

        return $query;
    }
}
