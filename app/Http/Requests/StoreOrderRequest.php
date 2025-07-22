<?php

namespace App\Http\Requests;

use Illuminate\Validation\Validator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOrderRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(ValidatorContract $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'comment' => 'sometimes|string|min:1|max:1024',

            'items.*.product_id' =>  'required|exists:products,id',
            'items.*.count' => 'required|integer|min:1',

        ];
    }

// Ограничения на количество товаров в заказе
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            $totalCount = array_sum(array_column($items, 'count'));

            if ($totalCount > 100) {
                $validator->errors()->add(
                    'items',
                    'Общая сумма всех товаров в заказе не может превышать 100 штук.'
                );
            }

            // Ограничение на число различных позиций
            if (count($items) > 10) {
                $validator->errors()->add(
                    'items',
                    'В заказе не может быть более 10 различных позиций.'
                );
            }
        });
    }
}
