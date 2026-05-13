(function () {
    const roots = document.querySelectorAll('[data-colt-xp]');
    if (!roots.length) return;

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    roots.forEach((root) => {
        setupReveal(root, prefersReduced);
        setupCanvas(root, prefersReduced);
        setupOrigin(root, prefersReduced);
    });

    function setupReveal(root, prefersReduced) {
        const items = root.querySelectorAll('.colt-reveal');
        if (prefersReduced || !('IntersectionObserver' in window)) {
            items.forEach((item) => item.classList.add('is-visible'));
            return;
        }
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.15 });
        items.forEach((item) => observer.observe(item));
    }

    function setupOrigin(root, prefersReduced) {
        const gsap = window.gsap;
        const ScrollTrigger = window.ScrollTrigger;
        const sequence = root.querySelector('[data-origin-sequence]');
        if (prefersReduced || !sequence || !gsap || !ScrollTrigger || window.innerWidth < 981) return;

        gsap.registerPlugin(ScrollTrigger);

        if (window.Lenis && !window.__coltLenis) {
            window.__coltLenis = new window.Lenis({ duration: 1.12, smoothWheel: true, wheelMultiplier: 0.82 });
            window.__coltLenis.on('scroll', ScrollTrigger.update);
            gsap.ticker.add((time) => window.__coltLenis.raf(time * 1000));
            gsap.ticker.lagSmoothing(0);
        }

        const pin = sequence.querySelector('.colt-origin__pin');
        const copy = sequence.querySelector('.colt-origin__copy');
        const guardian = sequence.querySelector('.colt-origin__guardian');
        const frames = Array.from(sequence.querySelectorAll('[data-guardian-frame]'));
        const voidNode = sequence.querySelector('.colt-origin__void');
        const portal = sequence.querySelector('.colt-origin__portal');
        const portalRings = sequence.querySelectorAll('.colt-origin__portal span');
        const skyLines = sequence.querySelectorAll('.colt-origin__sky span');
        const label = sequence.querySelector('.colt-origin__label');

        const setFrame = (index) => {
            frames.forEach((frame, frameIndex) => {
                frame.style.opacity = frameIndex === index ? '1' : '0';
            });
        };

        setFrame(0);
        gsap.set(portal, { autoAlpha: 0, scale: 0.36, rotate: 0 });
        gsap.set(voidNode, { autoAlpha: 0, scale: 0.2 });

        const tl = gsap.timeline({
            defaults: { ease: 'power2.inOut' },
            scrollTrigger: {
                trigger: sequence,
                start: 'top top',
                end: 'bottom bottom',
                scrub: 1.05,
                pin,
                anticipatePin: 1,
                onUpdate: (self) => {
                    const frame = Math.min(frames.length - 1, Math.floor(self.progress * frames.length * 1.12));
                    setFrame(frame);
                },
            },
        });

        tl
            .to(copy, { y: -24, autoAlpha: 0.82, duration: 0.55 }, 0)
            .to(guardian, { scale: 0.58, y: -40, rotateY: 0, duration: 0.72 }, 0)
            .to(skyLines, { x: '-18vw', y: -16, stagger: 0.04, duration: 0.62 }, 0)
            .to(label, { autoAlpha: 0, duration: 0.25 }, 0.18)
            .to(voidNode, { autoAlpha: 0.82, scale: 0.88, duration: 0.36 }, 0.38)

            .to(copy, { autoAlpha: 0, y: -84, duration: 0.42 }, 0.72)
            .to(guardian, { scale: 1.34, y: 54, duration: 0.78 }, 0.66)
            .to(voidNode, { scale: 2.1, y: -6, duration: 0.62 }, 0.72)
            .to(portal, { autoAlpha: 1, scale: 0.72, duration: 0.54 }, 0.82)
            .to(portalRings, { rotate: 220, scale: 1.12, stagger: 0.04, duration: 0.7 }, 0.86)

            .to(guardian, { scale: 2.75, y: 140, autoAlpha: 0.44, duration: 0.75 }, 1.18)
            .to(voidNode, { scale: 10.5, autoAlpha: 1, duration: 0.82 }, 1.16)
            .to(portal, { scale: 1.55, rotate: 180, autoAlpha: 0.92, duration: 0.82 }, 1.16)
            .to(skyLines, { x: '-42vw', autoAlpha: 0.38, duration: 0.7 }, 1.18)

            .to(guardian, { autoAlpha: 0, duration: 0.34 }, 1.82)
            .to(voidNode, { scale: 18, autoAlpha: 0, duration: 0.5 }, 1.82)
            .to(portal, { scale: 2.4, autoAlpha: 0, duration: 0.52 }, 1.84);
    }

    function setupCanvas(root, prefersReduced) {
        const canvas = root.querySelector('[data-colt-canvas]');
        if (!canvas || prefersReduced) return;

        const ctx = canvas.getContext('2d');
        const dots = [];
        let width = 0;
        let height = 0;

        const resize = () => {
            const ratio = Math.min(window.devicePixelRatio || 1, 1.5);
            width = window.innerWidth;
            height = window.innerHeight;
            canvas.width = Math.floor(width * ratio);
            canvas.height = Math.floor(height * ratio);
            canvas.style.width = `${width}px`;
            canvas.style.height = `${height}px`;
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            dots.length = 0;
            for (let i = 0; i < Math.min(120, Math.max(56, width / 14)); i += 1) {
                dots.push({
                    x: Math.random() * width,
                    y: Math.random() * height,
                    r: 0.8 + Math.random() * 2,
                    vx: -0.12 + Math.random() * 0.24,
                    vy: -0.05 + Math.random() * 0.16,
                    h: Math.random() > 0.72 ? 'hot' : 'gold',
                    p: Math.random() * Math.PI * 2,
                });
            }
        };

        const draw = (time) => {
            ctx.clearRect(0, 0, width, height);
            dots.forEach((dot, index) => {
                dot.x += dot.vx + Math.sin(time / 1500 + dot.p) * 0.08;
                dot.y += dot.vy;
                if (dot.x < -20) dot.x = width + 20;
                if (dot.x > width + 20) dot.x = -20;
                if (dot.y < -20) dot.y = height + 20;
                if (dot.y > height + 20) dot.y = -20;
                const alpha = 0.1 + Math.sin(time / 900 + index) * 0.045;
                ctx.fillStyle = dot.h === 'hot' ? `rgba(197,54,67,${alpha})` : `rgba(217,183,111,${alpha})`;
                ctx.beginPath();
                ctx.arc(dot.x, dot.y, dot.r, 0, Math.PI * 2);
                ctx.fill();
            });
            requestAnimationFrame(draw);
        };

        resize();
        window.addEventListener('resize', resize, { passive: true });
        requestAnimationFrame(draw);
    }
})();
