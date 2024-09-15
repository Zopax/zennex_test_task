<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;

class StoreTagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user != null && $user->tokenCan('create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'tag_name' => ['required', 'max:80', 'string',
                Rule::unique('tags', 'tag_name')->where('user_id',$this->user()->id)
            ],
            
        ];
    }

    public function messages()
    {
        return [
            'tag_name.unique' => 'Тэг с таким именем уже есть.',
            'tag_name.required' => 'Название тега должно быть уникальным.',
            'tag_name.max:80' => 'Название тега не должно превышать 80 символов.',
        ];
    }
}
