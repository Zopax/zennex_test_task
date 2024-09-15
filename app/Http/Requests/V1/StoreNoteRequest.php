<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user != null && $user->tokenCan('create');

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'header' => ['max:150', 'string'],
            'text_note' => ['string'], 
            'tags' => ['array']
        ];
    }

    public function messages()
    {
        return [
            'header.max:150' => 'Заголовок заметки должен быть не больше 150 символов.',
            'tags.array' => 'Список тегов должнен быть массивом.',
            'text_note.string' => 'Текст заметки должнен быть строкой.'
        ];
    }
}
