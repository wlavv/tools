<?php

namespace Modules\DocumentManager\Services;

use Modules\DocumentManager\Repositories\DocumentRepository;

class SearchService
{
    public function __construct(private DocumentRepository $documents)
    {
    }

    public function search(array $filters = [], int $perPage = 25)
    {
        return $this->documents->paginate($filters, $perPage);
    }

    public function provider(): string
    {
        return (string) config('documentmanager.providers.search', 'database');
    }
}
