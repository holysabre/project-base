<?php

namespace App\Http\Requests\Api;

use App\Models\Production;
use Illuminate\Validation\Rule;

class ProductionRequest extends FormRequest
{
    public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'type' => ['required', Rule::in(array_keys(Production::$mapType))],
                    'production_group_id' => ['sometimes'],
                    'media_id' => ['required', 'exists:media,id'],
                    'title' => 'required',
                    'lng' => 'required',
                    'lat' => 'required',
                    'description' => 'required',
                ];
                break;
            case 'PUT':
                return [
                    'hotspots' => 'sometimes|array',
                    'hotspots.*.name' => 'required',
                    'hotspots.*.ath' => 'required',
                    'hotspots.*.atv' => 'required',
                ];
                break;
        }
    }
}
