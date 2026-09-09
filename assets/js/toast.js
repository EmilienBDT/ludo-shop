(function () {
    'use strict';

    var provider = document.getElementById('toast-provider');
    if (!provider) return;

    var count = 0;

    var icons = {
        success: '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.5 4.5L6 12L2.5 8.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        error: '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M12 4L4 12M4 4l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        warning: '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 5v4M8 11h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        info: '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/><path d="M8 7v4M8 5h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    };

    function createToast(opts) {
        var id = ++count;
        var duration = opts.duration != null ? opts.duration : 4000;
        var type = opts.type || 'info';

        var el = document.createElement('div');
        el.className = 'shadcn-toast shadcn-toast--' + type;
        el.setAttribute('role', 'status');
        el.setAttribute('aria-live', 'polite');
        el.dataset.toastId = id;

        var iconHtml = icons[type] || icons.info;

        el.innerHTML =
            '<span class="shadcn-toast__icon">' + iconHtml + '</span>' +
            '<div class="shadcn-toast__content">' +
                '<p class="shadcn-toast__title">' + escapeHtml(opts.title || '') + '</p>' +
                (opts.description ? '<p class="shadcn-toast__desc">' + escapeHtml(opts.description) + '</p>' : '') +
            '</div>' +
            (opts.actionLabel
                ? '<button class="shadcn-toast__action" type="button">' + escapeHtml(opts.actionLabel) + '</button>'
                : '') +
            '<button class="shadcn-toast__close" type="button" aria-label="Fermer">' +
                '<svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M11 3L3 11M3 3l8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
            '</button>';

        var closeBtn = el.querySelector('.shadcn-toast__close');
        closeBtn.addEventListener('click', function () { dismiss(id); });

        var actionBtn = el.querySelector('.shadcn-toast__action');
        if (actionBtn && typeof opts.onAction === 'function') {
            actionBtn.addEventListener('click', function () { opts.onAction(id); });
        }

        provider.appendChild(el);

        if (duration > 0) {
            setTimeout(function () { dismiss(id); }, duration);
        }

        return id;
    }

    function dismiss(id) {
        var el = provider.querySelector('[data-toast-id="' + id + '"]');
        if (!el || el.classList.contains('shadcn-toast--out')) return;
        el.classList.add('shadcn-toast--out');
        setTimeout(function () {
            if (el.parentNode) el.parentNode.removeChild(el);
        }, 200);
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    window.toast = {
        add: function (opts) {
            if (typeof opts === 'string') {
                opts = { title: opts, type: 'info' };
            }
            return createToast(opts);
        },
        close: dismiss,
        success: function (title, description) {
            return createToast({ title: title, description: description, type: 'success' });
        },
        error: function (title, description) {
            return createToast({ title: title, description: description, type: 'error' });
        },
        warning: function (title, description) {
            return createToast({ title: title, description: description, type: 'warning' });
        },
        info: function (title, description) {
            return createToast({ title: title, description: description, type: 'info' });
        },
    };

    window.showToast = function (message, type) {
        window.toast.add({ title: message, type: type || 'info' });
    };

    var flashEls = document.querySelectorAll('.js-flash');
    for (var i = 0; i < flashEls.length; i++) {
        var el = flashEls[i];
        var msg = el.textContent.trim();
        if (msg) {
            var type = 'success';
            if (el.classList.contains('alert-danger') || el.classList.contains('alert-error')) {
                type = 'error';
            } else if (el.classList.contains('alert-warning')) {
                type = 'warning';
            }
            window.toast.add({ title: msg, type: type });
        }
        el.remove();
    }
})();
