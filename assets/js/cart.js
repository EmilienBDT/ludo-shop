document.querySelectorAll('.js-cart-quantity').forEach((input) => {
    let timer = null;

    input.dataset.lastValid = input.value;

    input.addEventListener('input', () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => updateQuantity(input), 400);
    });
});

async function updateQuantity(input) {
    if (input.value === '') {
        input.value = input.dataset.lastValid;
        return;
    }

    const row = input.closest('tr');
    const feedback = row.querySelector('.js-quantity-feedback');
    const max = Number(input.dataset.max);
    const quantity = Number(input.value);

    if (!Number.isInteger(quantity) || quantity < 1 || quantity > max) {
        showError(input, feedback, `Quantité invalide. Maximum : ${max}.`);
        return;
    }

    hideError(input, feedback);

    input.disabled = true;
    let rejected = false;

    try {
        const response = await fetch(input.dataset.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new URLSearchParams({ quantity: String(quantity) }).toString(),
        });

        let data = {};
        try {
            data = await response.json();
        } catch (e) {
            // Keep default data when the server does not return JSON.
        }

        if (!response.ok || data.ok === false) {
            rejected = true;
            showError(input, feedback, data.error || 'Impossible de mettre à jour la quantité.');
            return;
        }

        if (data.removed) {
            if (data.itemCount === 0) {
                window.location.reload();
                return;
            }
            row.remove();
            updateTotals(data.total, data.itemCount);
            window.toast.success('Article retiré du panier.');
            return;
        }

        row.querySelector('.js-line-total').textContent = formatPrice(data.lineTotal);
        input.dataset.lastValid = String(quantity);
        updateTotals(data.total, data.itemCount);
    } catch (e) {
        rejected = true;
        showError(input, feedback, 'Erreur réseau. Veuillez réessayer.');
    } finally {
        input.disabled = false;
        if (!rejected && input.value !== String(quantity)) {
            updateQuantity(input);
        }
    }
}

function showError(input, feedback, message) {
    input.classList.add('is-invalid');
    if (feedback) {
        feedback.textContent = message;
        feedback.hidden = false;
    }
    input.value = input.dataset.lastValid;
    window.toast.error(message);
    announce(message);
}

function hideError(input, feedback) {
    input.classList.remove('is-invalid');
    if (feedback) {
        feedback.textContent = '';
        feedback.hidden = true;
    }
}

function updateTotals(total, itemCount) {
    const totalElement = document.getElementById('cart-total');
    if (totalElement) {
        totalElement.textContent = formatPrice(total);
    }

    const badge = document.getElementById('cart-count-badge');
    if (badge) {
        badge.textContent = String(itemCount);
        if (itemCount <= 0) {
            badge.remove();
        }
    }

    announce(`Total mis à jour : ${formatPrice(total)}.`);
}

function announce(message) {
    const liveRegion = document.getElementById('cart-total-live');
    if (liveRegion) {
        liveRegion.textContent = message;
    }
}

function formatPrice(value) {
    return value.toLocaleString('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }) + ' €';
}
