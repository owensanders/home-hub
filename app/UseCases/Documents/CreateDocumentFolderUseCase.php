<?php

declare(strict_types=1);

namespace App\UseCases\Documents;

use App\Contracts\Repositories\DocumentRepositoryInterface;
use App\Data\DocumentFolderData;
use App\Models\Household;

class CreateDocumentFolderUseCase
{
    public function __construct(private readonly DocumentRepositoryInterface $documents) {}

    public function execute(Household $household, string $name): DocumentFolderData
    {
        return DocumentFolderData::fromModel($this->documents->createFolder($household, $name), 0);
    }
}
