<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use App\Constants\GeneralConst;
use App\Traits\ApiResponseTrait;

class PostSearchRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search_post_name' => ['nullable', 'string', 'max:255'],
            'search_post_description' => ['nullable', 'string', 'max:255'],
            'search_post_status' => ['nullable', 'in:' . implode(',', array_keys(GeneralConst::POST_STATUS))],
            'search_created_from' => ['nullable', 'date'],
            'search_created_to' => ['nullable', 'date', 'after_or_equal:search_created_from'],
        ];
    }

    /**
     * failedValidation
     *
     * @param  mixed $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        throw new HttpResponseException(
            $this->error($errors->toArray(), JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }
}
