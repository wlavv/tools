<style>
    .mtg-lsg {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .mtg-lsg-hero,
    .mtg-lsg-panel {
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 5px;
        background: var(--lsg-card-bg, rgba(17, 24, 39, .88));
        box-shadow: 0 14px 32px rgba(0, 0, 0, .18);
    }

    .mtg-lsg-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 20px 22px;
        overflow: hidden;
    }

    .mtg-lsg-hero__main {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
    }

    .mtg-lsg-hero__icon,
    .mtg-lsg-set-card__symbol,
    .mtg-lsg-filter__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background: rgba(255, 255, 255, .06);
        border: 1px solid rgba(255, 255, 255, .08);
    }

    .mtg-lsg-hero__icon {
        width: 70px;
        height: 70px;
        flex: 0 0 70px;
    }

    .mtg-lsg-hero__icon img {
        width: 52px;
        height: 52px;
        object-fit: contain;
    }

    .mtg-lsg-eyebrow {
        margin: 0 0 5px;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .mtg-lsg-title {
        margin: 0;
        color: #f8fafc;
        font-size: 28px;
        line-height: 1.12;
        font-weight: 800;
    }

    .mtg-lsg-subtitle {
        margin: 7px 0 0;
        color: #cbd5e1;
        font-size: 14px;
        line-height: 1.55;
    }

    .mtg-lsg-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #facc15;
        font-weight: 800;
        white-space: nowrap;
    }

    .mtg-lsg-hero__side {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex: 0 0 auto;
    }

    .mtg-lsg-hero-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 38px;
        padding: 0 12px;
        border: 1px solid rgba(250, 204, 21, .38);
        border-radius: 5px;
        color: #111827;
        background: #facc15;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
        cursor: pointer;
    }

    .mtg-lsg-hero-action:hover {
        background: #fde047;
        border-color: rgba(250, 204, 21, .72);
    }

    .mtg-webcatalogue-modal .modal-content {
        color: #111827;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 5px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
    }

    .mtg-webcatalogue-modal .modal-header,
    .mtg-webcatalogue-modal .modal-footer {
        background: #f8fafc;
        border-color: #e5e7eb;
        padding: 14px 18px;
    }

    .mtg-webcatalogue-modal .modal-body {
        padding: 18px;
        background: #ffffff;
    }

    .mtg-webcatalogue-modal .modal-title {
        color: #111827;
        font-size: 18px;
        font-weight: 800;
    }

    .mtg-webcatalogue-modal .form-label,
    .mtg-webcatalogue-modal .form-check-label {
        color: #374151;
        font-size: 13px;
        font-weight: 700;
    }

    .mtg-webcatalogue-modal .form-control,
    .mtg-webcatalogue-modal .form-select {
        color: #111827;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 5px;
    }

    .mtg-webcatalogue-modal .form-control:focus,
    .mtg-webcatalogue-modal .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .14);
    }

    .mtg-webcatalogue-modal .btn-primary {
        color: #ffffff;
        background: #2563eb;
        border-color: #2563eb;
    }

    .mtg-webcatalogue-modal .btn-secondary {
        color: #374151;
        background: #ffffff;
        border-color: #d1d5db;
    }

    .mtg-lsg-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
        gap: 14px;
        padding: 16px;
    }

    .mtg-lsg-set-card,
    .mtg-lsg-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
        width: 100%;
        border: 1px solid rgba(148, 163, 184, .16);
        border-radius: 5px;
        background: rgba(15, 23, 42, .72);
        color: #e5edf7;
        text-decoration: none;
        overflow: hidden;
        padding: 0;
        text-align: inherit;
        cursor: pointer;
        transition: transform .18s ease, border-color .18s ease, background .18s ease;
    }

    .mtg-lsg-card:focus-visible {
        outline: 3px solid rgba(250, 204, 21, .45);
        outline-offset: 3px;
    }

    .mtg-lsg-set-card:hover,
    .mtg-lsg-card:hover {
        color: #fff;
        text-decoration: none;
        transform: translateY(-2px);
        border-color: rgba(250, 204, 21, .45);
        background: rgba(30, 41, 59, .88);
    }

    .mtg-lsg-set-card {
        min-height: 210px;
        padding: 14px;
        justify-content: space-between;
    }

    .mtg-lsg-set-card__symbol {
        height: 124px;
    }

    .mtg-lsg-set-card__symbol img {
        width: 88px;
        height: 88px;
        object-fit: contain;
    }

    .mtg-lsg-set-card__code {
        color: #60a5fa;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .mtg-lsg-set-card__name {
        margin-top: 4px;
        color: #f8fafc;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.35;
    }

    .mtg-lsg-filters {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(118px, 1fr));
        gap: 10px;
        padding: 16px;
    }

    .mtg-lsg-filter {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        min-height: 62px;
        border: 1px solid rgba(148, 163, 184, .16);
        border-radius: 5px;
        background: rgba(15, 23, 42, .66);
        color: #e5edf7;
        cursor: pointer;
    }

    .mtg-lsg-filter.is-active {
        border-color: rgba(250, 204, 21, .5);
        background: rgba(250, 204, 21, .10);
    }

    .mtg-lsg-filter__icon {
        width: 48px;
        height: 48px;
        margin-left: 8px;
        flex: 0 0 48px;
    }

    .mtg-lsg-filter__icon img {
        width: 34px;
        height: 34px;
        object-fit: contain;
    }

    .mtg-lsg-filter__count {
        padding-right: 12px;
        font-size: 24px;
        line-height: 1;
        font-weight: 800;
    }

    .mtg-lsg-cards {
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    }

    .mtg-lsg-card__image {
        aspect-ratio: 63 / 88;
        padding: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(2, 6, 23, .35);
    }

    .mtg-lsg-card__image img {
        width: 100%;
        max-width: 250px;
        height: 100%;
        object-fit: contain;
        border-radius: 5px;
    }

    .mtg-lsg-card__body {
        padding: 0 12px 12px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .mtg-lsg-card__name {
        min-height: 42px;
        color: #f8fafc;
        font-size: 14px;
        line-height: 1.4;
        font-weight: 700;
        text-align: center;
    }

    .mtg-lsg-card__footer {
        display: grid;
        grid-template-columns: 1fr 34px 1fr;
        align-items: center;
        gap: 8px;
        min-height: 42px;
        padding: 7px 8px;
        border-radius: 5px;
        background: rgba(255, 255, 255, .94);
        color: #111827;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .mtg-lsg-card__footer img {
        width: 30px;
        height: 30px;
        object-fit: contain;
        justify-self: center;
    }

    .mtg-lsg-card__number {
        text-align: right;
    }

    .mtg-rarity-common { color: #6b7280; }
    .mtg-rarity-uncommon { color: #2563eb; }
    .mtg-rarity-rare { color: #b45309; }
    .mtg-rarity-mythic { color: #dc2626; }

    .mtg-card-modal .modal-content {
        position: relative;
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 5px;
        background: rgba(15, 23, 42, .98);
        color: #f8fafc;
        box-shadow: 0 30px 80px rgba(0, 0, 0, .45);
        overflow: hidden;
    }

    .mtg-card-modal__close {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 3;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 5px;
        background: rgba(15, 23, 42, .80);
        color: #f8fafc;
        cursor: pointer;
    }

    .mtg-card-modal__body {
        display: grid;
        grid-template-columns: minmax(260px, 420px) minmax(0, 1fr);
        gap: 24px;
        padding: 24px;
    }

    .mtg-card-modal__image-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 520px;
        border-radius: 5px;
        background: transparent;
    }

    .mtg-card-modal__image {
        width: 100%;
        max-width: 390px;
        max-height: 78vh;
        object-fit: contain;
        border-radius: 12px;
        filter: drop-shadow(0 26px 28px rgba(0, 0, 0, .42)) drop-shadow(0 8px 12px rgba(250, 204, 21, .16));
    }

    .mtg-card-modal__content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
        padding-right: 34px;
    }

    .mtg-card-modal__title {
        margin: 0 0 14px;
        color: #f8fafc;
        font-size: 34px;
        line-height: 1.1;
        font-weight: 800;
    }

    .mtg-card-modal__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }

    .mtg-card-modal__chips span {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 7px 10px;
        border-radius: 5px;
        background: rgba(51, 65, 85, .14);
        border: 1px solid rgba(51, 65, 85, .28);
        color: #cbd5e1;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .mtg-card-modal__rarity--common {
        background: rgba(107, 114, 128, .16) !important;
        border-color: rgba(107, 114, 128, .34) !important;
        color: #9ca3af !important;
    }

    .mtg-card-modal__rarity--uncommon {
        background: linear-gradient(135deg, rgba(148, 163, 184, .24), rgba(71, 85, 105, .16)) !important;
        border-color: rgba(148, 163, 184, .42) !important;
        color: #d1d5db !important;
    }

    .mtg-card-modal__rarity--rare {
        background: rgba(180, 83, 9, .16) !important;
        border-color: rgba(217, 119, 6, .38) !important;
        color: #f59e0b !important;
    }

    .mtg-card-modal__rarity--mythic {
        background: rgba(76, 29, 149, .20) !important;
        border-color: rgba(109, 40, 217, .40) !important;
        color: #c4b5fd !important;
    }

    .mtg-card-modal__section {
        padding-top: 16px;
        border-top: 1px solid rgba(148, 163, 184, .16);
    }

    .mtg-card-modal__section + .mtg-card-modal__section {
        margin-top: 16px;
    }

    .mtg-card-modal__section strong {
        display: block;
        margin-bottom: 7px;
        color: #94a3b8;
        font-size: 12px;
        text-transform: uppercase;
    }

    .mtg-card-modal__section p {
        margin: 0;
        color: #e2e8f0;
        font-size: 15px;
        line-height: 1.65;
        white-space: pre-line;
    }

    body.theme-light .mtg-lsg-hero,
    body.theme-light .mtg-lsg-panel,
    body[data-theme="light"] .mtg-lsg-hero,
    body[data-theme="light"] .mtg-lsg-panel {
        background: #fff;
        border-color: rgba(15, 23, 42, .10);
        box-shadow: 0 14px 32px rgba(15, 23, 42, .08);
    }

    body.theme-light .mtg-lsg-title,
    body.theme-light .mtg-lsg-set-card__name,
    body.theme-light .mtg-lsg-card__name,
    body[data-theme="light"] .mtg-lsg-title,
    body[data-theme="light"] .mtg-lsg-set-card__name,
    body[data-theme="light"] .mtg-lsg-card__name {
        color: #111827;
    }

    body.theme-light .mtg-lsg-subtitle,
    body[data-theme="light"] .mtg-lsg-subtitle {
        color: #475569;
    }

    body.theme-light .mtg-lsg-set-card,
    body.theme-light .mtg-lsg-card,
    body.theme-light .mtg-lsg-filter,
    body[data-theme="light"] .mtg-lsg-set-card,
    body[data-theme="light"] .mtg-lsg-card,
    body[data-theme="light"] .mtg-lsg-filter {
        background: #f8fafc;
        border-color: rgba(15, 23, 42, .10);
        color: #111827;
    }

    body.theme-light .mtg-card-modal .modal-content,
    body[data-theme="light"] .mtg-card-modal .modal-content {
        background: #fff;
        color: #111827;
    }

    body.theme-light .mtg-card-modal__title,
    body[data-theme="light"] .mtg-card-modal__title {
        color: #111827;
    }

    body.theme-light .mtg-card-modal__section p,
    body[data-theme="light"] .mtg-card-modal__section p {
        color: #334155;
    }

    @media (max-width: 768px) {
        .mtg-lsg-hero {
            align-items: flex-start;
            flex-direction: column;
        }

        .mtg-lsg-meta {
            white-space: normal;
        }

        .mtg-lsg-hero__side {
            align-items: flex-start;
        }

        .mtg-lsg-hero-action {
            width: 100%;
        }

        .mtg-lsg-title {
            font-size: 24px;
        }

        .mtg-card-modal__body {
            grid-template-columns: 1fr;
            gap: 16px;
            padding: 16px;
        }

        .mtg-card-modal__image-wrap {
            min-height: 0;
            padding: 12px;
        }

        .mtg-card-modal__image {
            max-height: 62vh;
        }

        .mtg-card-modal__content {
            padding-right: 0;
        }

        .mtg-card-modal__title {
            font-size: 26px;
        }
    }
</style>
