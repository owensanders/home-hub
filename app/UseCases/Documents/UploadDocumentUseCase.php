<?php

declare(strict_types=1);

namespace App\UseCases\Documents;

use App\Contracts\Repositories\DocumentRepositoryInterface;
use App\Data\DocumentData;
use App\Models\DocumentFolder;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class UploadDocumentUseCase
{
    public function __construct(private readonly DocumentRepositoryInterface $documents) {}

    public function execute(DocumentFolder $folder, UploadedFile $file, ?User $uploader): DocumentData
    {
        return DocumentData::fromModel($this->documents->storeUpload($folder, $file, $uploader));
    }
}
