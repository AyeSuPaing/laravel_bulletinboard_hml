<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use App\Constants\GeneralConst;
use App\Traits\ApiResponseTrait;

class PostSaveRequest extends FormRequest
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
        $post_id = $this->route('id');
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('posts', 'title')
                    ->whereNull('deleted_at')
                    ->ignore($post_id)
            ],
            'description' => ['required', 'string', 'max:255'],
            'status' => [
                Rule::requiredIf(!!$post_id),
                'in:' . implode(',', array_keys(GeneralConst::POST_STATUS))
            ],
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
