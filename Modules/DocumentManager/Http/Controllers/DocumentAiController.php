<?php

namespace Modules\DocumentManager\Http\Controllers;

use App\Services\AI\DocumentAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\DocumentManager\Models\Document;
use Modules\DocumentManager\Models\DocumentAiResult;
use Modules\DocumentManager\Support\DocumentTable;
use Throwable;

class DocumentAiController extends BaseDocumentController
{
    public function extractExpense(int $document, DocumentAiService $ai)
    {
        $documentRecord = Document::query()->findOrFail($document);

        try {
            $result = $ai->extractExpenseFromDocument($documentRecord);

            return redirect()
                ->route('document-manager.documents.ai.results', $documentRecord->id)
                ->with('success', 'Sugestao AI de despesa criada.')
                ->with('ai_result_id', $result['ai_result_id'] ?? null);
        } catch (Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function showAiResults(int $document)
    {
        $documentRecord = Document::query()->findOrFail($document);

        return view('documentmanager::documents.ai-results', [
            'document' => $documentRecord,
            'results' => DocumentTable::safeGet('document_manager_ai_results', function ($query) use ($document) {
                $query->where('document_id', $document)
                    ->orderByDesc('processed_at')
                    ->orderByDesc('id');
            }),
        ]);
    }

    public function createExpenseFromSuggestion(
        Request $request,
        int $document,
        int $aiResult,
        DocumentAiService $ai
    ) {
        $documentRecord = Document::query()->findOrFail($document);
        $result = DocumentAiResult::query()
            ->where('document_id', $documentRecord->id)
            ->where('operation', 'extract_expense')
            ->findOrFail($aiResult);

        $prefill = $ai->expenseFormData($result);

        if (Route::has('expense-manager.expenses.create')) {
            return redirect()->route('expense-manager.expenses.create', $prefill);
        }

        if (Route::has('expense-tracker.expenses.create')) {
            return redirect()->route('expense-tracker.expenses.create', $prefill);
        }

        return view('documentmanager::documents.expense-suggestion', [
            'document' => $documentRecord,
            'result' => $result,
            'prefill' => $prefill,
            'expenseRouteMissing' => true,
        ]);
    }
}
