<?php

declare(strict_types=1);

namespace App\UseCases\Documents;

use App\Contracts\Repositories\DocumentRepositoryInterface;
use App\Models\Document;

class DeleteDocumentUseCase
{
    public function __construct(private readonly DocumentRepositoryInterface $documents) {}

    public function execute(Document $document): void
    {
        $this->documents->deleteDocument($document);
    }
}
