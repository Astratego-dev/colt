(function () {
    const roots = document.querySelectorAll('[data-colt-xp]');

    if (!roots.length) {
        return;
    }

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    roots.forEach((root) => {
        const reveals = root.querySelectorAll('.colt-reveal');
        const scenes = root.querySelectorAll('[data-colt-scene]');
        const card = root.querySelector('[data-colt-card]');
        const portal = root.querySelector('[data-colt-portal]');
        const canvas = root.querySelector('[data-colt-canvas]');
        const rail = root.querySelector('[data-colt-rail]');

        if (!prefersReduced && 'IntersectionObserver' in window) {
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15 });

            reveals.forEach((item) => revealObserver.observe(item));
        } else {
            reveals.forEach((item) => item.classList.add('is-visible'));
        }

        if (card && !prefersReduced) {
            root.addEventListener('pointermove', (event) => {
                const rect = card.getBoundingClientRect();
                const cx = rect.left + rect.width / 2;
                const cy = rect.top + rect.height / 2;
                const dx = (event.clientX - cx) / rect.width;
                const dy = (event.clientY - cy) / rect.height;

                card.style.setProperty('--ry', `${Math.max(-20, Math.min(20, dx * 20))}deg`);
                card.style.setProperty('--rx', `${Math.max(-12, Math.min(12, dy * -14))}deg`);
            }, { passive: true });
        }

        const updateScroll = () => {
            const rect = root.getBoundingClientRect();
            const docProgress = Math.min(1, Math.max(0, (window.innerHeight - rect.top) / (rect.height + window.innerHeight)));
            root.style.setProperty('--progress', docProgress.toFixed(4));

            if (rail) {
                rail.style.height = `${docProgress * 100}%`;
            }

            if (portal) {
                portal.style.setProperty('--spin', `${docProgress * 520}deg`);
            }

            if (card) {
                const offset = Math.sin(window.scrollY / 160) * 10;
                card.style.setProperty('--float', `${offset}px`);
            }

            let active = 'hero';
            scenes.forEach((scene) => {
                const sceneRect = scene.getBoundingClientRect();
                if (sceneRect.top < window.innerHeight * 0.55 && sceneRect.bottom > window.innerHeight * 0.25) {
                    active = scene.getAttribute('data-colt-scene') || active;
                }
            });
            root.setAttribute('data-active-scene', active);
        };

        window.addEventListener('scroll', updateScroll, { passive: true });
        window.addEventListener('resize', updateScroll, { passive: true });
        updateScroll();

        if (canvas && !prefersReduced) {
            runCanvas(canvas, root);
        }
    });

    function runCanvas(canvas, root) {
        const ctx = canvas.getContext('2d');
        const particles = [];
        let width = 0;
        let height = 0;
        let raf = 0;

        const resize = () => {
            const ratio = Math.min(window.devicePixelRatio || 1, 1.5);
            width = Math.max(1, Math.floor(window.innerWidth));
            height = Math.max(1, Math.floor(window.innerHeight));
            canvas.width = Math.floor(width * ratio);
            canvas.height = Math.floor(height * ratio);
            canvas.style.width = `${width}px`;
            canvas.style.height = `${height}px`;
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);

            particles.length = 0;
            const count = Math.min(120, Math.max(48, Math.floor(width / 14)));
            for (let i = 0; i < count; i += 1) {
                particles.push({
                    x: Math.random() * width,
                    y: Math.random() * height,
                    r: 0.7 + Math.random() * 2.2,
                    vx: -0.18 + Math.random() * 0.36,
                    vy: -0.08 + Math.random() * 0.24,
                    hue: Math.random() > 0.68 ? 'hot' : 'gold',
                    phase: Math.random() * Math.PI * 2,
                });
            }
        };

        const draw = (time) => {
            ctx.clearRect(0, 0, width, height);
            const progress = parseFloat(getComputedStyle(root).getPropertyValue('--progress')) || 0;
            const drift = progress * 180;

            particles.forEach((p, index) => {
                p.x += p.vx + Math.sin(time / 1300 + p.phase) * 0.12;
                p.y += p.vy + Math.cos(time / 1700 + p.phase) * 0.08;

                if (p.x < -20) p.x = width + 20;
                if (p.x > width + 20) p.x = -20;
                if (p.y < -20) p.y = height + 20;
                if (p.y > height + 20) p.y = -20;

                const alpha = 0.12 + Math.sin(time / 900 + index) * 0.06;
                ctx.beginPath();
                ctx.fillStyle = p.hue === 'gold'
                    ? `rgba(217, 183, 111, ${alpha})`
                    : `rgba(197, 54, 67, ${alpha * 0.8})`;
                ctx.arc(p.x + Math.sin(progress * 5 + p.phase) * drift * 0.08, p.y, p.r, 0, Math.PI * 2);
                ctx.fill();
            });

            ctx.save();
            ctx.globalAlpha = 0.16;
            ctx.strokeStyle = 'rgba(217, 183, 111, 0.22)';
            ctx.lineWidth = 1;
            for (let i = 0; i < 7; i += 1) {
                const y = (height * 0.18) + i * 120 + Math.sin(time / 1200 + i) * 18;
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.bezierCurveTo(width * 0.28, y - 80, width * 0.62, y + 120, width, y - 28);
                ctx.stroke();
            }
            ctx.restore();

            raf = window.requestAnimationFrame(draw);
        };

        resize();
        window.addEventListener('resize', resize, { passive: true });
        raf = window.requestAnimationFrame(draw);

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                window.cancelAnimationFrame(raf);
            } else {
                raf = window.requestAnimationFrame(draw);
            }
        });
    }
})();
