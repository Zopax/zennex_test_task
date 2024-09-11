<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user != null && $user->tokenCan('update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $method = $this->method();

        if($method == "PUT") 
        {
            return [
                'header' => ['max:150', 'string'],
                'text_note' => ['string'],
                'tags' => ['array']
            ];
        }
        else
        {
            return [
                'header' => ['sometimes', 'max:150', 'string'],
                'text_note' => ['sometimes', 'string'],
                'owner' => ['sometimes','required', 'exists:users,id'], 
                'tags' => ['sometimes','array']
            ];
        }
    }

    public function messages()
    {
        return [
            'header.max:150' => 'The header must be less than 150 characters.',
            'tags.array' => 'Tags of the note must be in array.',
            'text_note.string' => 'Text of the note must be string.'
        ];
    }
}
