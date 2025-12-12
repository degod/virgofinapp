<?php

namespace App\Http\Requests;

use App\Enums\AssetEnum;
use App\Enums\OrderSideEnum;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
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
        return [
            'symbol' => ['required', 'string', 'in:' . implode(',', AssetEnum::symbols())],
            'side' => ['required', 'string', 'in:' . implode(',', OrderSideEnum::getSides())],
            'price' => ['required', 'numeric', 'gt:0'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
