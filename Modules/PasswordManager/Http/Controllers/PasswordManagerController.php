<?php

namespace Modules\PasswordManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\PasswordManager\Http\Requests\StorePasswordEntryRequest;
use Modules\PasswordManager\Http\Requests\UpdatePasswordEntryRequest;
use Modules\PasswordManager\Models\PasswordEntry;
use Modules\PasswordManager\Services\PasswordManagerService;

class PasswordManagerController extends Controller
{
    public function __construct(protected PasswordManagerService $service)
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();

        $entries = $this->service->listForUser(
            userId: (int) $request->user()->id,
            search: $search,
        );

        $entries->transform(function (PasswordEntry $entry) {
            $revealed = $this->service->reveal($entry);
            $entry->setAttribute('copy_password', (string) ($revealed['password'] ?? ''));

            return $entry;
        });

        return $this->view('password-manager::Index', [
            'entries' => $entries,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return $this->view('password-manager::pages.form', [
            'entry' => null,
            'revealed' => [
                'password' => null,
                'secret' => null,
                'notes' => null,
            ],
            'action' => route('password_manager.store'),
            'method' => 'POST',
        ]);
    }

    public function store(StorePasswordEntryRequest $request): RedirectResponse
    {
        $this->service->createForUser(
            userId: (int) $request->user()->id,
            data: $request->validated()
        );

        return redirect()
            ->route('password_manager.index')
            ->with('success', 'Registo guardado com sucesso.');
    }

    public function show(PasswordEntry $passwordEntry): View
    {
        $this->authorizeEntry($passwordEntry);

        $revealed = $this->service->reveal($passwordEntry);
        $this->service->markAsUsed($passwordEntry);

        return $this->view('password-manager::pages.show', [
            'entry' => $passwordEntry,
            'revealed' => $revealed,
        ]);
    }

    public function edit(PasswordEntry $passwordEntry): View
    {
        $this->authorizeEntry($passwordEntry);

        return $this->view('password-manager::pages.form', [
            'entry' => $passwordEntry,
            'revealed' => $this->service->reveal($passwordEntry),
            'action' => route('password_manager.update', $passwordEntry),
            'method' => 'PUT',
        ]);
    }

    public function update(UpdatePasswordEntryRequest $request, PasswordEntry $passwordEntry): RedirectResponse
    {
        $this->authorizeEntry($passwordEntry);

        $this->service->updateForUser(
            entry: $passwordEntry,
            data: $request->validated()
        );

        return redirect()
            ->route('password_manager.index')
            ->with('success', 'Registo atualizado com sucesso.');
    }

    public function copy(Request $request, PasswordEntry $passwordEntry): JsonResponse
    {
        $this->authorizeEntry($passwordEntry);

        $field = (string) $request->input('field');

        if (! in_array($field, ['username', 'password'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid copy field.',
            ], 422);
        }

        $value = $field === 'username'
            ? (string) ($passwordEntry->login_username ?? '')
            : (string) ($this->service->reveal($passwordEntry)['password'] ?? '');

        if ($field === 'password') {
            $this->service->markAsUsed($passwordEntry);
        }

        return response()->json([
            'success' => true,
            'value' => $value,
        ]);
    }

    public function destroy(PasswordEntry $passwordEntry): RedirectResponse
    {
        $this->authorizeEntry($passwordEntry);

        $this->service->deleteForUser($passwordEntry);

        return redirect()
            ->route('password_manager.index')
            ->with('success', 'Registo removido com sucesso.');
    }

    protected function authorizeEntry(PasswordEntry $entry): void
    {
        abort_unless((int) $entry->user_id === (int) auth()->id(), 403);
    }
}
