(function () {
    const roots = document.querySelectorAll('[data-colt-xp]');

    if (!roots.length) {
        return;
    }

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    roots.forEach((root) => {
        const reveals = root.querySelectorAll('.colt-reveal');

        if (!prefersReduced && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.16 });

            reveals.forEach((item) => observer.observe(item));
        } else {
            reveals.forEach((item) => item.classList.add('is-visible'));
        }

        const card = root.querySelector('[data-colt-card]');

        if (card && !prefersReduced) {
            root.addEventListener('pointermove', (event) => {
                const rect = card.getBoundingClientRect();
                const cx = rect.left + rect.width / 2;
                const cy = rect.top + rect.height / 2;
                const dx = (event.clientX - cx) / rect.width;
                const dy = (event.clientY - cy) / rect.height;

                card.style.setProperty('--ry', `${Math.max(-18, Math.min(18, dx * 18))}deg`);
                card.style.setProperty('--rx', `${Math.max(-10, Math.min(10, dy * -12))}deg`);
            }, { passive: true });

            window.addEventListener('scroll', () => {
                const offset = Math.sin(window.scrollY / 180) * 8;
                card.style.setProperty('--float', `${offset}px`);
            }, { passive: true });
        }
    });
})();
