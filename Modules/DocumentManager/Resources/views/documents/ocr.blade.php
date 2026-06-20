@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <div class="dms-card">
        <div class="dms-card__head">
            <div>
                <span class="dms-eyebrow">OCR</span>
                <h3>{{ $document->title }}</h3>
            </div>
            <form method="POST" action="{{ route('document-manager.documents.ocr.process', $document->id) }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Processar OCR
                </button>
            </form>
        </div>
    </div>

    @forelse($ocrResults as $ocr)
        <div class="dms-card">
            <div class="dms-card__head">
                <div>
                    <span class="dms-eyebrow">{{ $ocr->provider }} / {{ $ocr->status }}</span>
                    <h3>{{ $ocr->finished_at ?: $ocr->created_at }}</h3>
                </div>
                <span class="dms-badge">{{ $ocr->confidence ?: '-' }}</span>
            </div>
            @if($ocr->error_message)
                <div class="dms-empty">{{ $ocr->error_message }}</div>
            @endif
            <div class="dms-note">
                <p style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $ocr->extracted_text ?: 'Sem texto extraido.' }}</p>
            </div>
        </div>
    @empty
        <div class="dms-card">
            <div class="dms-empty">Ainda sem OCR para este documento.</div>
        </div>
    @endforelse
@endsection
