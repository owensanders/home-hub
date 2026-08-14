<?php

declare(strict_types=1);

namespace App\UseCases\Documents;

use App\Contracts\Repositories\DocumentRepositoryInterface;
use App\Models\DocumentFolder;

class DeleteDocumentFolderUseCase
{
    public function __construct(private readonly DocumentRepositoryInterface $documents) {}

    public function execute(DocumentFolder $folder): void
    {
        $this->documents->deleteFolder($folder);
    }
}
