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
            // если в теле запроса отсутсвует одно из указанных полей, выдается ошибка валидации
            return [
                'header' => ['required', 'max:150', 'string'],
                'text_note' => ['required','string'],
                'tags' => ['required', 'array']
            ];
        }
        else
        {
            // в теле запроса можно отправить только поля которые будут изменены
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
            'header.max:150' => 'Заголовок заметки не должен превышать 150 символов.',
            'header.required' => 'Поле header должно быть в теле запроса',
            'tags.array' => 'Список тегов должен быть массивом.',
            'text_note.string' => 'Текст заметки должен быть строкой.',
            'text_note.required' => 'Поле text_note должно быть в теле запроса',
        ];
    }
}
