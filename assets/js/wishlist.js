(function () {
    'use strict';

    var filledSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
    var emptySvg = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-wishlist');
        if (!btn) return;

        e.preventDefault();

        var url = btn.dataset.url;
        var productName = btn.closest('.card-body, .card')?.querySelector('.card-title, h2')?.textContent?.trim() || 'Produit';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (res) {
                if (!res.ok) throw new Error('Network error');
                return res.json();
            })
            .then(function (data) {
                if (data.error) {
                    toast.add({
                        title: data.error,
                        type: 'error',
                    });
                    return;
                }

                btn.innerHTML = data.added ? filledSvg : emptySvg;
                btn.setAttribute('aria-label', data.added ? 'Retirer de la wishlist' : 'Ajouter à la wishlist');

                if (data.added) {
                    toast.add({
                        title: productName,
                        description: 'Ajouté à la wishlist',
                        type: 'success',
                        duration: 3000,
                    });
                } else {
                    var id = toast.add({
                        title: productName,
                        description: 'Retiré de la wishlist',
                        type: 'info',
                        duration: 5000,
                        actionLabel: 'Annuler',
                        onAction: function (toastId) {
                            toast.close(toastId);
                            btn.click();
                        },
                    });
                }
            })
            .catch(function () {
                toast.add({
                    title: 'Erreur réseau',
                    description: 'Veuillez réessayer.',
                    type: 'error',
                });
            });
    });
})();
