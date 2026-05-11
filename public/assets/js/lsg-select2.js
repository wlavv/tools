(function (window, document, $) {
    'use strict';

    if (!$ || !$.fn || !$.fn.select2) {
        return;
    }

    function resolveDropdownParent($select) {
        var $modal = $select.closest('.modal');
        if ($modal.length) {
            return $modal;
        }

        var parentSelector = $select.attr('data-dropdown-parent');
        if (parentSelector && $(parentSelector).length) {
            return $(parentSelector).first();
        }

        return $(document.body);
    }

    function normalizeBoolean(value, fallback) {
        if (typeof value === 'undefined' || value === null || value === '') {
            return fallback;
        }

        return String(value).toLowerCase() === 'true' || String(value) === '1';
    }

    function initSelect2(context) {
        var $context = context ? $(context) : $(document);

        $context.find('select.lsg-select2').each(function () {
            var $select = $(this);

            if ($select.hasClass('select2-hidden-accessible')) {
                return;
            }

            var placeholder = $select.attr('data-placeholder') || $select.attr('placeholder') || '';
            var allowClear = normalizeBoolean($select.attr('data-allow-clear'), false);
            var searchThreshold = parseInt($select.attr('data-search-threshold') || '8', 10);

            var options = {
                width: '100%',
                minimumResultsForSearch: isNaN(searchThreshold) ? 8 : searchThreshold,
                dropdownParent: resolveDropdownParent($select),
                allowClear: allowClear,
                language: {
                    noResults: function () {
                        return 'Sem resultados';
                    },
                    searching: function () {
                        return 'A pesquisar...';
                    },
                    inputTooShort: function () {
                        return 'Introduza mais caracteres';
                    }
                }
            };

            if (placeholder !== '') {
                options.placeholder = placeholder;
            }

            $select.select2(options);
        });
    }

    $(function () {
        initSelect2(document);
    });

    document.addEventListener('shown.bs.modal', function (event) {
        initSelect2(event.target);
    });

    window.LSGSelect2 = {
        init: initSelect2,
        refresh: function (context) {
            initSelect2(context || document);
        },
        destroy: function (context) {
            var $context = context ? $(context) : $(document);
            $context.find('select.lsg-select2.select2-hidden-accessible').each(function () {
                $(this).select2('destroy');
            });
        }
    };

})(window, document, window.jQuery);
