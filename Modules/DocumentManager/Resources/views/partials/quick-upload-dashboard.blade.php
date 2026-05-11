<details class="dms-card dms-collapsible-upload" @if(!empty($open)) open @endif>
    <summary>
        <span>
            <span class="dms-eyebrow">Entrada rapida</span>
            <strong>Upload documental</strong>
        </span>
        <i class="fa-solid fa-cloud-arrow-up"></i>
    </summary>

    @include('documentmanager::partials.quick-upload-form')
</details>
