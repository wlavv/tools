<?php

namespace Modules\DocumentManager\Events;

class DocumentReady
{
    public function __construct(public int $documentId)
    {
    }
}
