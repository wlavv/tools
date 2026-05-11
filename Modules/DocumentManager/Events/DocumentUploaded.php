<?php

namespace Modules\DocumentManager\Events;

class DocumentUploaded
{
    public function __construct(public int $documentId, public ?int $versionId = null)
    {
    }
}
