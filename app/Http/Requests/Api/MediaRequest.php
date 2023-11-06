<?php

namespace App\Http\Requests\Api;

use App\Models\Media;
use Illuminate\Validation\Rule;

class MediaRequest extends FormRequest
{
    public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'type' => ['required', Rule::in(array_keys(Media::$mapType))],
                    'media_group_id' => ['sometimes'],
                    'path' => 'required'
                ];
                break;
            case 'PUT':
                break;
        }
    }
}
