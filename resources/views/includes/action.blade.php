@if(!empty($actions) && is_array($actions))
    @php
        $renderActionIcon = function ($icon) {
            if (empty($icon)) {
                return '';
            }

            if (str_contains($icon, '<')) {
                return $icon;
            }

            return '<i class="' . e($icon) . '" aria-hidden="true"></i>';
        };

        $resolveTone = function (array $action): string {
            $class = strtolower((string) ($action['class'] ?? ''));
            $key   = strtolower((string) ($action['key'] ?? ''));
            $name  = strtolower((string) ($action['name'] ?? ''));

            if (str_contains($class, 'danger') || $key === 'delete' || str_contains($name, 'delete') || str_contains($name, 'remove')) {
                return 'danger';
            }

            if (str_contains($class, 'warning') || $key === 'edit' || str_contains($name, 'edit')) {
                return 'warning';
            }

            if (str_contains($class, 'success') || $key === 'new' || str_contains($name, 'new') || str_contains($name, 'create') || str_contains($name, 'add')) {
                return 'success';
            }

            if ($key === 'save' || str_contains($name, 'save') || str_contains($class, 'primary')) {
                return 'gold';
            }

            return 'back';
        };
    @endphp

    <div class="lsg-page-actions" role="toolbar" aria-label="Page actions">
        @foreach($actions as $action)
            @php
                $actionType = $action['type'] ?? 'link';
                $actionLabel = $action['label'] ?? $action['name'] ?? 'Action';
                $actionTone = $resolveTone($action);
                $actionClasses = trim('lsg-action-btn lsg-action-btn--' . $actionTone . ' ' . ($action['extra_class'] ?? ''));
                $iconMarkup = $renderActionIcon($action['icon'] ?? null);
            @endphp

            @if($actionType === 'submit')
                <button
                    type="submit"
                    class="{{ $actionClasses }}"
                    @if(!empty($action['form'])) form="{{ $action['form'] }}" @endif
                >
                    <span class="lsg-action-btn__glow"></span>
                    @if($iconMarkup)
                        <span class="lsg-action-btn__icon">{!! $iconMarkup !!}</span>
                    @endif
                    <span class="lsg-action-btn__label">{{ $actionLabel }}</span>
                </button>
            @elseif($actionType === 'delete')
                <form
                    method="POST"
                    action="{{ $action['url'] ?? '#' }}"
                    class="lsg-action-form"
                    onsubmit="return {{ !empty($action['confirm']) ? "confirm('" . addslashes($action['confirm']) . "')" : 'true' }};"
                >
                    @csrf
                    @method($action['method'] ?? 'DELETE')

                    <button type="submit" class="{{ $actionClasses }}">
                        <span class="lsg-action-btn__glow"></span>
                        @if($iconMarkup)
                            <span class="lsg-action-btn__icon">{!! $iconMarkup !!}</span>
                        @endif
                        <span class="lsg-action-btn__label">{{ $actionLabel }}</span>
                    </button>
                </form>
            @else
                <a href="{{ $action['url'] ?? '#' }}" class="{{ $actionClasses }}">
                    <span class="lsg-action-btn__glow"></span>
                    @if($iconMarkup)
                        <span class="lsg-action-btn__icon">{!! $iconMarkup !!}</span>
                    @endif
                    <span class="lsg-action-btn__label">{{ $actionLabel }}</span>
                </a>
            @endif
        @endforeach
    </div>
@endif
