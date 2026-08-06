(() => {
    'use strict';

    const meta = document.querySelector('meta[name="oncallforms-context"]');
    if (!meta) {
        return;
    }

    let context;
    try {
        context = JSON.parse(meta.content);
    } catch (_error) {
        return;
    }

    const pathMatchesForm = (href, formId) => {
        if (!href || !Number.isInteger(formId)) {
            return false;
        }
        try {
            const path = new URL(href, window.location.href).pathname.replace(/\/+$/, '');
            return path.endsWith(`/Form/Render/${formId}`);
        } catch (_error) {
            return false;
        }
    };

    const decorateCatalog = () => {
        const catalog = context.catalog;
        if (!catalog?.enabled || !Number.isInteger(catalog.formId)) {
            return;
        }

        document.querySelectorAll('[data-glpi-service-catalog-items] a.card[href]').forEach((link) => {
            if (!pathMatchesForm(link.getAttribute('href'), catalog.formId)) {
                return;
            }

            const container = link.closest('.col-12') || link;
            container.hidden = Boolean(catalog.hidden);
            link.classList.add('plugin-oncallforms-card');
            link.style.setProperty('--oncallforms-background', context.appearance.background);
            link.style.setProperty('--oncallforms-border', context.appearance.border);
            link.style.setProperty('--oncallforms-text', context.appearance.text);

            if (!link.querySelector('[data-oncallforms-badge]')) {
                const badge = document.createElement('span');
                badge.className = 'badge plugin-oncallforms-badge';
                badge.dataset.oncallformsBadge = '';
                badge.textContent = context.appearance.badge;
                link.querySelector('.card-body')?.prepend(badge);
            }
        });
    };

    const showWarning = () => {
        const warning = context.warning;
        if (!warning?.enabled || document.getElementById('plugin-oncallforms-warning')) {
            return;
        }

        const modal = document.createElement('div');
        modal.id = 'plugin-oncallforms-warning';
        modal.className = 'modal fade';
        modal.tabIndex = -1;
        modal.setAttribute('aria-labelledby', 'plugin-oncallforms-warning-title');
        modal.setAttribute('aria-describedby', 'plugin-oncallforms-warning-description');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('role', 'dialog');

        const dialog = document.createElement('div');
        dialog.className = 'modal-dialog modal-dialog-centered';
        const content = document.createElement('div');
        content.className = 'modal-content';
        const header = document.createElement('div');
        header.className = 'modal-header';
        const title = document.createElement('h2');
        title.id = 'plugin-oncallforms-warning-title';
        title.className = 'modal-title';
        title.textContent = warning.title;
        header.append(title);

        const body = document.createElement('div');
        body.className = 'modal-body';
        const description = document.createElement('p');
        description.id = 'plugin-oncallforms-warning-description';
        description.textContent = warning.body;
        const checkLabel = document.createElement('label');
        checkLabel.className = 'form-check mt-3';
        const checkbox = document.createElement('input');
        checkbox.className = 'form-check-input';
        checkbox.type = 'checkbox';
        const checkText = document.createElement('span');
        checkText.className = 'form-check-label';
        checkText.textContent = warning.checkbox;
        checkLabel.append(checkbox, checkText);
        body.append(description, checkLabel);

        const footer = document.createElement('div');
        footer.className = 'modal-footer';
        const oncallLink = document.createElement('a');
        oncallLink.className = 'btn btn-warning';
        oncallLink.href = warning.oncallUrl;
        oncallLink.textContent = warning.oncallButton;
        const continueButton = document.createElement('button');
        continueButton.className = 'btn btn-primary';
        continueButton.type = 'button';
        continueButton.disabled = true;
        continueButton.textContent = warning.continueButton;
        footer.append(oncallLink, continueButton);
        content.append(header, body, footer);
        dialog.append(content);
        modal.append(dialog);
        document.body.append(modal);

        const instance = new bootstrap.Modal(modal, {backdrop: 'static', keyboard: false, focus: true});
        checkbox.addEventListener('change', () => {
            continueButton.disabled = !checkbox.checked;
        });
        continueButton.addEventListener('click', () => {
            if (checkbox.checked) {
                instance.hide();
            }
        });
        modal.addEventListener('shown.bs.modal', () => checkbox.focus(), {once: true});
        modal.addEventListener('hidden.bs.modal', () => modal.remove(), {once: true});
        instance.show();
    };

    const initialize = () => {
        decorateCatalog();
        showWarning();
        const catalogRoot = document.querySelector('[data-glpi-service-catalog-items]');
        if (catalogRoot) {
            new MutationObserver(decorateCatalog).observe(catalogRoot, {childList: true, subtree: true});
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, {once: true});
    } else {
        initialize();
    }
})();
