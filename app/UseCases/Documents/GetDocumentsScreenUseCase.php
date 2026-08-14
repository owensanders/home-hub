<?php

declare(strict_types=1);

namespace App\UseCases\Documents;

use App\Contracts\Repositories\DocumentRepositoryInterface;
use App\Data\DocumentData;
use App\Data\DocumentFolderData;
use App\Models\DocumentFolder;
use App\Models\Household;
use Illuminate\Support\Collection;

class GetDocumentsScreenUseCase
{
    public function __construct(private readonly DocumentRepositoryInterface $documents) {}

    /**
     * @return array{
     *     folders: list<DocumentFolderData>,
     *     active: ?DocumentFolderData,
     *     documents: list<DocumentData>,
     *     storageLabel: string,
     *     storagePct: int,
     * }
     */
    public function execute(Household $household, ?DocumentFolder $active): array
    {
        $folders = $this->documents->foldersFor($household);
        $active ??= $folders->first();

        /** @var Collection<int, \App\Models\Document> $activeDocuments */
        $activeDocuments = $active !== null ? $this->documents->documentsFor($active) : collect();

        $totalBytes = $this->documents->totalSizeFor($household);
        $quotaBytes = (int) config('documents.quota_bytes');

        return [
            'folders' => $folders
                ->map(fn (DocumentFolder $folder) => DocumentFolderData::fromModel($folder, $folder->documents_count))
                ->all(),
            'active' => $active !== null ? DocumentFolderData::fromModel($active, $activeDocuments->count()) : null,
            'documents' => $activeDocuments->map(DocumentData::fromModel(...))->all(),
            'storageLabel' => $this->formatGb($totalBytes).' of '.$this->formatGb($quotaBytes),
            'storagePct' => $quotaBytes > 0 ? max(2, (int) round($totalBytes / $quotaBytes * 100)) : 0,
        ];
    }

    private function formatGb(int $bytes): string
    {
        return number_format($bytes / 1024 / 1024 / 1024, 1).' GB';
    }
}
