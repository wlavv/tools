(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        const disabledTimelineLink = event.target.closest('.erp-flow-tab[aria-disabled="true"]');

        if (disabledTimelineLink) {
            event.preventDefault();
        }
    });
})();
