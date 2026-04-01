<?php

namespace Modules\PasswordManager\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\PasswordManager\Http\Requests\StorePasswordEntryRequest;
use Modules\PasswordManager\Http\Requests\UpdatePasswordEntryRequest;
use Modules\PasswordManager\Models\PasswordEntry;
use Modules\PasswordManager\Services\PasswordManagerService;

use App\Http\Controllers\CustomTools\customToolsController;

class PasswordManagerController extends customToolsController
{
    public function __construct( protected PasswordManagerService $service ) {

        $this->breadcrumbs[] = [ 'name' =>  'Budget', 'url' => route('budget.index')];
    }

    public function index(Request $request): View
    {
        $entries = $this->service->listForUser(
            userId: (int) $request->user()->id,
            search: $request->string('q')->toString(),
            perPage: (int) config('password-manager.pagination', 15),
        );

        $data = [
            'entries' => $entries,
            'search' => $request->string('q')->toString(),
        ];

        $this->setViewData($data);

        return view('password-manager::Index', $this->viewData);
    }

    public function create(): View{

        $data = [
            'entry' => null,
            'revealed' => ['password' => null, 'secret' => null, 'notes' => null],
            'action' => route('password_manager.store'),
            'method' => 'POST',
        ];

        $this->setViewData($data);

        return view('password-manager::pages.form', $this->viewData);
    }

    public function store(StorePasswordEntryRequest $request): RedirectResponse{

        $this->service->createForUser( userId: (int) $request->user()->id, data: $request->validated() );
        return redirect()->route('password_manager.index')->with('success', 'Registo guardado com sucesso.');
    }

    public function show(PasswordEntry $passwordEntry): View{

        $this->authorizeEntry($passwordEntry);

        $revealed = $this->service->reveal($passwordEntry);
        $this->service->markAsUsed($passwordEntry);

        $data = [ 'entry' => $passwordEntry, 'revealed' => $revealed ];

        $this->setViewData($data);

        return view('password-manager::pages.show', $this->viewData);
    }

    public function edit(PasswordEntry $passwordEntry): View{

        $this->authorizeEntry($passwordEntry);

        $data = [
            'entry' => $passwordEntry,
            'revealed' => $this->service->reveal($passwordEntry),
            'action' => route('password_manager.update', $passwordEntry),
            'method' => 'PUT',
        ];

        $this->setViewData($data);
        return view('password-manager::pages.form', $this->viewData);
    }

    public function update(UpdatePasswordEntryRequest $request, PasswordEntry $passwordEntry): RedirectResponse{

        $this->authorizeEntry($passwordEntry);
        $this->service->updateForUser($passwordEntry, $request->validated());
        return redirect()->route('password_manager.show', $passwordEntry)->with('success', 'Registo atualizado com sucesso.');
    }

    public function destroy(PasswordEntry $passwordEntry): RedirectResponse{

        $this->authorizeEntry($passwordEntry);

        $this->service->deleteForUser($passwordEntry);

        return redirect()->route('password_manager.index')->with('success', 'Registo removido com sucesso.');
    }

    protected function authorizeEntry(PasswordEntry $entry): void{

        abort_unless((int) $entry->user_id === (int) auth()->id(), 403);
    }
}
