<?php

namespace Modules\DocumentManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\DocumentManager\Support\DocumentTable;

class ShareService
{
    public function createLink(int $documentId, array $options = []): ?string
    {
        if (!DocumentTable::exists('document_core_shares')) {
            return null;
        }

        $token = Str::random(64);

        DB::table('document_core_shares')->insert([
            'uuid' => (string) Str::uuid(),
            'document_id' => $documentId,
            'token' => $token,
            'share_type' => 'link',
            'password_hash' => !empty($options['password']) ? Hash::make($options['password']) : null,
            'can_download' => (bool) ($options['can_download'] ?? false),
            'expires_at' => $options['expires_at'] ?? null,
            'created_by' => auth()->id(),
            'permissions' => json_encode($options['permissions'] ?? ['view'], JSON_UNESCAPED_SLASHES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $token;
    }
}
