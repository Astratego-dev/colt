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

        if (!prefersReduced) {
            setupCinematic(root);
        }
    });

    function setupCinematic(root) {
        const gsap = window.gsap;
        const ScrollTrigger = window.ScrollTrigger;
        const cinema = root.querySelector('[data-colt-cinema]');

        if (!cinema || !gsap || !ScrollTrigger || window.innerWidth < 981) {
            return;
        }

        gsap.registerPlugin(ScrollTrigger);

        if (window.Lenis) {
            const lenis = new window.Lenis({
                duration: 1.18,
                smoothWheel: true,
                wheelMultiplier: 0.82,
            });

            lenis.on('scroll', ScrollTrigger.update);
            gsap.ticker.add((time) => lenis.raf(time * 1000));
            gsap.ticker.lagSmoothing(0);
        }

        const sticky = cinema.querySelector('.colt-xp__cinema-sticky');
        const panels = Array.from(cinema.querySelectorAll('[data-cinema-panel]'));
        const card = cinema.querySelector('.colt-xp__cinema-card');
        const vault = cinema.querySelector('.colt-xp__cinema-vault');
        const faceVoid = cinema.querySelector('.colt-xp__face-void');
        const paths = Array.from(cinema.querySelectorAll('.colt-xp__cinema-paths a'));
        const guardian = cinema.querySelector('.colt-xp__cinema-guardian');
        const wordmark = cinema.querySelector('.colt-xp__cinema-wordmark');
        const rings = cinema.querySelectorAll('.colt-xp__tunnel-ring');
        const glow = cinema.querySelector('.colt-xp__tunnel-glow');

        gsap.set(panels.slice(1), { autoAlpha: 0, y: 28 });
        gsap.set(paths, { autoAlpha: 0, y: 26, scale: 0.94 });
        gsap.set(vault, { autoAlpha: 0, scale: 0.58, rotate: -20 });
        gsap.set(card, { autoAlpha: 0, y: 40, scale: 0.7 });
        gsap.set(faceVoid, { autoAlpha: 0, scale: 0.2 });

        const activatePanel = (index) => {
            panels.forEach((panel, panelIndex) => {
                panel.classList.toggle('is-active', panelIndex === index);
            });
        };

        const timeline = gsap.timeline({
            defaults: { ease: 'power2.inOut' },
            scrollTrigger: {
                trigger: cinema,
                start: 'top top',
                end: 'bottom bottom',
                scrub: 1.1,
                pin: sticky,
                anticipatePin: 1,
                onUpdate: (self) => {
                    root.style.setProperty('--cinema-progress', self.progress.toFixed(4));
                    const panelIndex = Math.min(3, Math.floor(self.progress * 4.05));
                    activatePanel(panelIndex);
                },
            },
        });

        timeline
            .to(wordmark, { autoAlpha: 0.18, y: -80, scale: 1.12, duration: 0.9 }, 0)
            .to(guardian, { scale: 0.72, rotateY: 0, y: -12, autoAlpha: 0.92, duration: 0.85 }, 0)
            .to(rings, { rotate: 160, scale: 1.22, stagger: 0.05, duration: 0.85 }, 0)
            .to(faceVoid, { autoAlpha: 0.72, scale: 0.92, y: -16, duration: 0.55 }, 0.3)
            .to(panels[0], { autoAlpha: 0, y: -24, duration: 0.35 }, 0.62)
            .to(panels[1], { autoAlpha: 1, y: 0, duration: 0.45 }, 0.7)

            .to(guardian, { scale: 1.62, y: 70, autoAlpha: 0.62, duration: 0.95 }, 0.86)
            .to(faceVoid, { scale: 9.8, y: 40, autoAlpha: 1, duration: 1.05 }, 0.9)
            .to(wordmark, { autoAlpha: 0, scale: 1.36, duration: 0.75 }, 0.92)
            .to(card, { autoAlpha: 1, y: 0, scale: 1, duration: 0.65 }, 1.42)
            .to(vault, { autoAlpha: 1, scale: 0.82, rotate: 0, duration: 0.7 }, 1.44)
            .to(rings, { scale: 0.56, rotate: 390, duration: 0.9 }, 1.35)
            .to(glow, { scale: 2.2, autoAlpha: 0.66, duration: 0.8 }, 1.35)
            .to(panels[1], { autoAlpha: 0, y: -24, duration: 0.35 }, 1.78)
            .to(panels[2], { autoAlpha: 1, y: 0, duration: 0.45 }, 1.86)

            .to(faceVoid, { autoAlpha: 0.28, scale: 14, duration: 0.6 }, 1.92)
            .to(guardian, { autoAlpha: 0.14, scale: 1.95, y: 130, duration: 0.7 }, 1.92)
            .to(card, { x: '31vw', y: '-2vh', rotation: 3, scale: 0.72, autoAlpha: 0.5, duration: 0.8 }, 2.02)
            .to(vault, { x: '18vw', scale: 0.6, autoAlpha: 0.46, duration: 0.8 }, 2.02)
            .to(paths, { autoAlpha: 1, y: 0, scale: 1, stagger: 0.09, duration: 0.76 }, 2.14)
            .to(panels[2], { autoAlpha: 0, y: -24, duration: 0.35 }, 2.72)
            .to(panels[3], { autoAlpha: 1, y: 0, duration: 0.45 }, 2.82)

            .to(paths, { y: -8, scale: 1.04, stagger: 0.04, duration: 0.55 }, 2.95)
            .to(card, { x: '18vw', y: '16vh', scale: 0.46, autoAlpha: 0.18, duration: 0.55 }, 3.02)
            .to(vault, { scale: 0.42, autoAlpha: 0.24, duration: 0.5 }, 3.04)
            .to(rings, { scale: 1.34, rotate: 650, duration: 0.7 }, 3.04);
    }

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
