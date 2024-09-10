<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;

class BulkStoreTagRequest extends FormRequest
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
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.tagName' => ['required', 'max:80']
        ];
    }

    protected function prepareForValidation() 
    {
        $data = [];

        foreach ($this->toArray() as $obj)
        {
            $obj['tag_name'] = $obj['tagName'] ?? null;

            $data[] = $obj;
        }

        $this->merge($data);
    }
}
