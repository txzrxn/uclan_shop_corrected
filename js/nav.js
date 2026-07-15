(function () {
    const hamburger = document.getElementById('hamburgerBtn') || document.getElementById('hamburger-btn');
    const navigation = document.getElementById('primaryNav') || document.getElementById('primary-nav');

    if (hamburger && navigation) {
        hamburger.addEventListener('click', function () {
            const isOpen = navigation.classList.toggle('open');
            hamburger.setAttribute('aria-expanded', isOpen.toString());
        });
    }

    function readCart() {
        try {
            const stored = JSON.parse(localStorage.getItem('uclanCart'));
            return Array.isArray(stored) ? stored : [];
        } catch (error) {
            return [];
        }
    }

    const total = readCart().reduce(function (sum, item) {
        return sum + (Number.parseInt(item.qty, 10) || 0);
    }, 0);

    ['cartBadge', 'cart-badge'].forEach(function (id) {
        const badge = document.getElementById(id);
        if (badge) {
            badge.textContent = total;
            badge.style.display = total > 0 ? 'flex' : 'none';
        }
    });
}());
