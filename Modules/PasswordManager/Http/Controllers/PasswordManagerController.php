<?php

namespace Modules\PasswordManager\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\PasswordManager\Http\Requests\StorePasswordEntryRequest;
use Modules\PasswordManager\Http\Requests\UpdatePasswordEntryRequest;
use Modules\PasswordManager\Models\PasswordEntry;
use Modules\PasswordManager\Services\PasswordManagerService;

class PasswordManagerController extends Controller
{
    public function __construct( protected PasswordManagerService $service ) {
        
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
        $this->middleware('auth');
    }

    public function index(Request $request): View{

        $entries = $this->service->listForUser(
            userId: (int) $request->user()->id,
            search: $request->string('q')->toString(),
            perPage: (int) config('password-manager.pagination', 15),
        );

        return $this->view('password-manager::Index', [ 'entries' => $entries, 'search' => $request->string('q')->toString() ]);
    }

    public function create(): View{

        $data = [
            'entry' => null,
            'revealed' => ['password' => null, 'secret' => null, 'notes' => null],
            'action' => route('password_manager.store'),
            'method' => 'POST',
        ];

        return $this->view('password-manager::pages.form', $data);
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

        return $this->view('password-manager::pages.show', $data);
    }

    public function edit(PasswordEntry $passwordEntry): View{

        $this->authorizeEntry($passwordEntry);

        $data = [
            'entry' => $passwordEntry,
            'revealed' => $this->service->reveal($passwordEntry),
            'action' => route('password_manager.update', $passwordEntry),
            'method' => 'PUT',
        ];

        return $this->view('password-manager::pages.form', $data);
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
