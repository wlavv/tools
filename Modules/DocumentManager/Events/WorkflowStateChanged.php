<?php

namespace Modules\DocumentManager\Events;

class WorkflowStateChanged
{
    public function __construct(public int $documentId, public string $fromState, public string $toState)
    {
    }
}
