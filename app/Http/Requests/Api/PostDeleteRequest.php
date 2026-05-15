<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponseTrait;

class PostDeleteRequest extends FormRequest
{
    use ApiResponseTrait;
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
            "deleted_post_ids" => ['required', 'array'],
            "deleted_post_ids.*" => ['required', 'exists:posts,id'],
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
