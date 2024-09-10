<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class NoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'header' => $this->header,
            'text' => $this->text_note,
            'owner' => $this->user_id,
            'created_at' => $this->created_at->format('d-M-Y h:i:s'),
            'tags' => TagResource::collection($this->tags),
        ];
    }
}
