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
        return true;
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
                'text_note' => ['string'], // изменено на text_note
                'owner' => ['required', 'exists:users,id'], // если owner - это ID пользователя
            ];
        }
        else
        {
            return [
                'header' => ['sometimes', 'max:150', 'string'],
                'text_note' => ['sometimes', 'string'], // изменено на text_note
                'owner' => ['sometimes','required', 'exists:users,id'], // если owner - это ID пользователя
            ];
        }
    }

    protected function prepareForValidation() 
    {
        if($this->owner)
        {
            $this->merge([
                'user_id' => $this->owner
            ]);
        }
    }
}
