<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileConversionRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|max:10240', // 10MB max file size
            'format' => 'required|string|in:pdf,docx,doc,xlsx,xls,pptx,ppt,txt',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to convert',
            'file.file' => 'The uploaded file is invalid',
            'file.max' => 'File size cannot exceed 10MB',
            'format.required' => 'Please select a target format',
            'format.in' => 'The selected format is not supported',
        ];
    }
}
