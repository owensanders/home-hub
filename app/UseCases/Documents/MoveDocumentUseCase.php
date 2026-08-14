<?php

declare(strict_types=1);

namespace App\UseCases\Documents;

use App\Contracts\Repositories\DocumentRepositoryInterface;
use App\Data\DocumentData;
use App\Models\Document;
use App\Models\DocumentFolder;

class MoveDocumentUseCase
{
    public function __construct(private readonly DocumentRepositoryInterface $documents) {}

    public function execute(Document $document, DocumentFolder $folder): DocumentData
    {
        return DocumentData::fromModel($this->documents->moveDocument($document, $folder));
    }
}
