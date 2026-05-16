<span class="prm-permission-label">
    <span class="prm-permission-label__name">{{ $permission->displayName($contextModule ?? null) }}</span>
    <i
        class="fa-solid fa-circle-info prm-permission-label__info"
        title="{{ $permission->technicalName() }}"
        aria-label="{{ $permission->technicalName() }}"
    ></i>
</span>
