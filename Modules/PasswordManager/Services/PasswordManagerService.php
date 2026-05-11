<?php

namespace Modules\PasswordManager\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Modules\PasswordManager\Models\PasswordEntry;

class PasswordManagerService
{
    public function listForUser(int $userId, ?string $search = null): Collection
    {
        return PasswordEntry::query()
            ->where('user_id', $userId)
            ->when($search, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('title', 'like', '%' . $search . '%')
                        ->orWhere('category', 'like', '%' . $search . '%')
                        ->orWhere('account_email', 'like', '%' . $search . '%') // legacy search support
                        ->orWhere('login_username', 'like', '%' . $search . '%')
                        ->orWhere('url', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('title')
            ->get();
    }

    public function createForUser(int $userId, array $data): PasswordEntry
    {
        return PasswordEntry::query()->create($this->payload($userId, $data));
    }

    public function updateForUser(PasswordEntry $entry, array $data): PasswordEntry
    {
        $entry->update($this->payload((int) $entry->user_id, $data, $entry));

        return $entry->refresh();
    }

    public function deleteForUser(PasswordEntry $entry): void
    {
        $entry->delete();
    }

    public function reveal(PasswordEntry $entry): array
    {
        return [
            'password' => $entry->encrypted_password ? Crypt::decryptString($entry->encrypted_password) : null,
            'secret' => $entry->encrypted_secret ? Crypt::decryptString($entry->encrypted_secret) : null,
            'notes' => $entry->encrypted_notes ? Crypt::decryptString($entry->encrypted_notes) : null,
        ];
    }

    public function markAsUsed(PasswordEntry $entry): void
    {
        $entry->update([
            'last_used_at' => Carbon::now(),
        ]);
    }

    protected function payload(int $userId, array $data, ?PasswordEntry $entry = null): array
    {
        return [
            'user_id' => $userId,
            'title' => $data['title'],
            'category' => $data['category'] ?? null,
            'url' => $data['url'] ?? null,
            'account_email' => $entry?->account_email,
            'login_username' => $data['login_username'] ?? null,
            'encrypted_password' => filled($data['password'] ?? null)
                ? Crypt::encryptString($data['password'])
                : ($entry?->encrypted_password),
            'encrypted_secret' => filled($data['secret'] ?? null)
                ? Crypt::encryptString($data['secret'])
                : ($entry?->encrypted_secret),
            'encrypted_notes' => filled($data['notes'] ?? null)
                ? Crypt::encryptString($data['notes'])
                : ($entry?->encrypted_notes),
            'is_favorite' => array_key_exists('is_favorite', $data)
                ? (bool) $data['is_favorite']
                : (bool) ($entry?->is_favorite ?? false),
        ];
    }
}
