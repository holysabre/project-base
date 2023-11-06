<?php

namespace App\Http\Requests\Api;

use App\Models\Production;
use App\Models\ProductionGroup;
use Illuminate\Validation\Rule;

class ProductionGroupRequest extends FormRequest
{
    public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                $user_id = auth('api')->id();

                return [
                    'name' => ['required', 'between:2,12', 'string', function ($attribute, $value, $fail) use ($user_id) {
                        $is_exists = ProductionGroup::query()->where('user_id', $user_id)->where('name', $value)->exists();
                        if ($is_exists) {
                            return $fail('分组名字已存在');
                        }
                    }],
                    'type' => ['required', Rule::in(array_keys(Production::$mapType))],
                    'sort' => 'sometimes',
                ];
                break;
            case 'PUT':
                $user_id = auth('api')->id();

                return [
                    'name' => ['between:2,12', 'string', function ($attribute, $value, $fail) use ($user_id) {
                        $model = $this->production_group;
                        $is_exists = ProductionGroup::query()->where('user_id', $user_id)->where('name', $value)->where('id', '<>', $model->id)->exists();
                        if ($is_exists) {
                            return $fail('分组名字已存在');
                        }
                    }],
                    'type' => ['required', Rule::in(array_keys(Production::$mapType))],
                    'sort' => 'sometimes',
                ];
                break;
        }
    }
}
