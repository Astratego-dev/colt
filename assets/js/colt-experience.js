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
        if (!sequence) return;

        const guardian = sequence.querySelector('.colt-origin__guardian');
        const frames = Array.from(sequence.querySelectorAll('[data-guardian-frame]'));
        const frameCount = frames.length;

        const setFrame = (frame) => {
            if (frameCount < 2) return;
            const bounded = Math.max(0, Math.min(frameCount - 1, frame));
            frames.forEach((node, index) => {
                node.style.opacity = index === bounded ? '1' : '0';
            });
        };

        setFrame(0);

        const chapters = Array.from(sequence.querySelectorAll('[data-origin-chapter]'));

        if (prefersReduced || !gsap || !ScrollTrigger || window.innerWidth < 981) {
            setFrame(4);
            chapters.forEach((chapter, index) => {
                chapter.style.opacity = index === 0 ? '1' : '0';
                chapter.style.transform = 'none';
            });
            return;
        }

        gsap.registerPlugin(ScrollTrigger);

        if (window.Lenis && !window.__coltLenis) {
            window.__coltLenis = new window.Lenis({ duration: 1.18, smoothWheel: true, wheelMultiplier: 0.82 });
            window.__coltLenis.on('scroll', ScrollTrigger.update);
            gsap.ticker.add((time) => window.__coltLenis.raf(time * 1000));
            gsap.ticker.lagSmoothing(0);
        }

        const pin = sequence.querySelector('.colt-origin__pin');
        const world = sequence.querySelector('.colt-origin__world');
        const copy = sequence.querySelector('.colt-origin__copy');
        const voidNode = sequence.querySelector('.colt-origin__void');
        const portal = sequence.querySelector('.colt-origin__portal');
        const portalRings = sequence.querySelectorAll('.colt-origin__portal span');
        const skyLines = sequence.querySelectorAll('.colt-origin__sky span');
        const atmosphere = sequence.querySelectorAll('.colt-origin__atmosphere span');
        const label = sequence.querySelector('.colt-origin__label');
        const rail = sequence.querySelector('.colt-origin__rail');
        const serviceTab = sequence.querySelector('.colt-origin__service-tab');

        const showChapter = (index, at) => {
            const chapter = chapters[index];
            if (!chapter) return;
            tl.to(chapter, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.22 }, at);
            tl.to(chapter, { autoAlpha: 0, y: -32, filter: 'blur(10px)', duration: 0.2 }, at + 0.28);
        };

        const crossfadeFrame = (from, to, at) => {
            if (!frames[from] || !frames[to]) return;
            tl.to(frames[from], { autoAlpha: 0, duration: 0.12 }, at);
            tl.to(frames[to], { autoAlpha: 1, duration: 0.12 }, at);
        };

        gsap.set(copy, { xPercent: 50, yPercent: -50, y: 0, autoAlpha: 1 });
        gsap.set(chapters, { autoAlpha: 0, y: 32, filter: 'blur(10px)' });
        gsap.set(frames, { autoAlpha: 0 });
        if (frames[0]) gsap.set(frames[0], { autoAlpha: 1 });
        gsap.set(guardian, { xPercent: 50, y: 0, scale: 0.16, autoAlpha: 1, transformOrigin: '50% 74%' });
        gsap.set(voidNode, { xPercent: -50, yPercent: -50, scale: 0.16, autoAlpha: 0 });
        gsap.set(portal, { xPercent: -50, yPercent: -50, scale: 0.18, rotate: 0, autoAlpha: 0 });
        gsap.set(rail, { autoAlpha: 1 });
        gsap.set(serviceTab, { autoAlpha: 1 });

        const tl = gsap.timeline({
            defaults: { ease: 'power2.inOut' },
            scrollTrigger: {
                trigger: sequence,
                start: 'top top',
                end: 'bottom bottom',
                scrub: 1.08,
                pin,
                anticipatePin: 1,
                onUpdate: (self) => {
                    sequence.style.setProperty('--origin-progress', self.progress.toFixed(3));
                },
            },
        });

        showChapter(0, 0.2);
        showChapter(1, 0.62);
        showChapter(2, 1.02);
        crossfadeFrame(0, 1, 0.46);
        crossfadeFrame(1, 2, 0.68);
        crossfadeFrame(2, 3, 0.98);
        crossfadeFrame(3, 4, 1.26);
        crossfadeFrame(4, 5, 1.68);

        tl
            .to(world, { scale: 1.06, xPercent: -0.8, yPercent: 0.6, duration: 0.42 }, 0)
            .to(skyLines, { x: '-8vw', y: -8, stagger: 0.03, duration: 0.5 }, 0)
            .to(atmosphere, { x: '-6vw', y: -6, stagger: 0.04, duration: 0.5 }, 0)
            .to(label, { autoAlpha: 0, y: 18, duration: 0.22 }, 0.08)
            .to(rail, { y: -18, autoAlpha: 0.9, duration: 0.26 }, 0.08)
            .to(guardian, { scale: 0.28, y: -8, duration: 0.42, ease: 'power1.inOut' }, 0.1)

            .to(world, { scale: 1.15, xPercent: -2.2, yPercent: 1.8, duration: 0.46 }, 0.42)
            .to(guardian, { scale: 0.58, y: -18, duration: 0.48 }, 0.46)
            .to(voidNode, { autoAlpha: 0.72, scale: 0.62, duration: 0.28 }, 0.56)
            .to(portal, { autoAlpha: 0.56, scale: 0.42, rotate: 40, duration: 0.32 }, 0.62)

            .to(world, { scale: 1.25, xPercent: -4.2, yPercent: 3, duration: 0.5 }, 0.84)
            .to(guardian, { scale: 1.02, y: 16, duration: 0.5 }, 0.84)
            .to(voidNode, { scale: 1.55, autoAlpha: 0.92, duration: 0.42 }, 0.86)
            .to(world, { filter: 'saturate(1.22) contrast(1.13) brightness(.76)', duration: 0.54 }, 0.94)
            .to(portalRings, { rotate: 220, scale: 1.12, stagger: 0.05, duration: 0.52 }, 0.94)
            .to(portal, { scale: 1.02, autoAlpha: 0.8, duration: 0.54 }, 1)

            .to(guardian, { scale: 1.72, y: 82, autoAlpha: 0.88, duration: 0.56 }, 1.28)
            .to(voidNode, { scale: 6.2, autoAlpha: 1, duration: 0.58 }, 1.22)
            .to(serviceTab, { x: -80, autoAlpha: 0, duration: 0.32 }, 1.24)
            .to(rail, { x: 40, autoAlpha: 0, duration: 0.34 }, 1.28)
            .to(skyLines, { x: '-38vw', autoAlpha: 0.18, duration: 0.56 }, 1.25)
            .to(portal, { scale: 1.85, autoAlpha: 0.92, duration: 0.58 }, 1.35)

            .to(guardian, { scale: 2.45, y: 170, autoAlpha: 0.3, duration: 0.48 }, 1.78)
            .to(voidNode, { scale: 18, duration: 0.62 }, 1.74)
            .to(portal, { scale: 3.1, autoAlpha: 0, duration: 0.5 }, 1.84)
            .to(world, { scale: 1.48, autoAlpha: 0.28, duration: 0.52 }, 1.86)
            .to(voidNode, { scale: 30, autoAlpha: 0, duration: 0.42 }, 2.2);
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
