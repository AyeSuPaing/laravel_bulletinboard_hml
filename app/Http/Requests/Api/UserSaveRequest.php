<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use App\Constants\GeneralConst;
use App\Traits\ApiResponseTrait;

class UserSaveRequest extends FormRequest
{
    use ApiResponseTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user_id = $this->route('id');
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user_id)
            ],
            'password' => [
                Rule::requiredIf(!$user_id),
                'nullable',
                'string',
                'min:8',
                'confirmed'
            ],
            'password_confirmation' => [
                Rule::requiredIf(!$user_id),
                'nullable',
                'string',
                'min:8'
            ],
            'profile' => ['nullable', 'mimes:' . implode(',', GeneralConst::UPLOAD_FILE_TYPES), 'max:' . 1024 * GeneralConst::MAX_UPLOAD_SIZE],
            'role' => [
                'required',
                'in:' . implode(',', array_keys(GeneralConst::ROLES))
            ],
            'phone' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'dob' => ['nullable', 'date'],
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
