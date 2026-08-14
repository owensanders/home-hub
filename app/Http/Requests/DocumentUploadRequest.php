<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class DocumentUploadRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:'.config('documents.max_upload_kb'),
                'mimes:pdf,jpg,jpeg,png,heic,doc,docx,xls,xlsx,csv,zip',
            ],
        ];
    }

    public function uploadedFile(): UploadedFile
    {
        /** @var UploadedFile */
        return $this->file('file');
    }
}
