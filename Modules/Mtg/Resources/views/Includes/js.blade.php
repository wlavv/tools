<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-mtg-card-filter]').forEach(function (button) {
            button.addEventListener('click', function () {
                const color = button.getAttribute('data-mtg-card-filter');

                document.querySelectorAll('[data-mtg-card]').forEach(function (card) {
                    card.style.display = color === 'all' || card.getAttribute('data-mtg-card') === color ? '' : 'none';
                });

                document.querySelectorAll('[data-mtg-card-filter]').forEach(function (item) {
                    item.classList.toggle('is-active', item === button);
                });
            });
        });

        const modal = document.getElementById('mtgCardModal');

        if (!modal || typeof jQuery === 'undefined') {
            return;
        }

        const setText = function (selector, value) {
            const element = modal.querySelector(selector);

            if (element) {
                element.textContent = value || '';
            }
        };

        const toggleWrap = function (selector, value) {
            const element = modal.querySelector(selector);

            if (element) {
                element.style.display = value ? '' : 'none';
            }
        };

        document.querySelectorAll('[data-mtg-card-open]').forEach(function (card) {
            card.addEventListener('click', function () {
                const image = modal.querySelector('[data-mtg-modal-image]');
                const name = card.getAttribute('data-name') || '';
                const type = card.getAttribute('data-type') || '';
                const text = card.getAttribute('data-text') || '';
                const mana = card.getAttribute('data-mana') || '';
                const rarity = card.getAttribute('data-rarity') || '';
                const number = card.getAttribute('data-number') || '';
                const rarityChip = modal.querySelector('[data-mtg-modal-rarity]');

                if (image) {
                    image.src = card.getAttribute('data-image') || '';
                    image.alt = name;
                }

                if (rarityChip) {
                    rarityChip.className = 'mtg-card-modal__rarity mtg-card-modal__rarity--' + (rarity || 'unknown').toLowerCase();
                }

                setText('[data-mtg-modal-title]', name);
                setText('[data-mtg-modal-meta]', number ? 'Collector #' + number : '');
                setText('[data-mtg-modal-rarity]', rarity || 'Sem raridade');
                setText('[data-mtg-modal-mana]', mana || 'Sem custo de mana');
                setText('[data-mtg-modal-type]', type);
                setText('[data-mtg-modal-text]', text);
                toggleWrap('[data-mtg-modal-type-wrap]', type);
                toggleWrap('[data-mtg-modal-text-wrap]', text);

                jQuery(modal).modal('show');
            });
        });
    });
</script>
