<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\Household;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface DocumentRepositoryInterface
{
    /** @return Collection<int, DocumentFolder> */
    public function foldersFor(Household $household): Collection;

    /** @return Collection<int, Document> documents with their uploader eager loaded */
    public function documentsFor(DocumentFolder $folder): Collection;

    public function createFolder(Household $household, string $name): DocumentFolder;

    public function findFolder(int $id): ?DocumentFolder;

    public function deleteFolder(DocumentFolder $folder): void;

    public function storeUpload(DocumentFolder $folder, UploadedFile $file, ?User $uploader): Document;

    public function moveDocument(Document $document, DocumentFolder $folder): Document;

    public function deleteDocument(Document $document): void;

    public function totalSizeFor(Household $household): int;
}
