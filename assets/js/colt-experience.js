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

        const video = sequence.querySelector('[data-origin-video]');
        const chapters = Array.from(sequence.querySelectorAll('[data-origin-chapter]'));
        const fallbackDuration = video ? parseFloat(video.dataset.videoDuration || '10') : 10;
        let videoTargetTime = 0;
        let videoFrameRequest = 0;

        const syncVideo = (progress) => {
            if (!video) return;
            const duration = Number.isFinite(video.duration) && video.duration > 0 ? video.duration : fallbackDuration;
            videoTargetTime = Math.max(0, Math.min(duration - 0.04, duration * progress));
            if (videoFrameRequest) return;
            videoFrameRequest = window.requestAnimationFrame(() => {
                videoFrameRequest = 0;
                if (Math.abs(video.currentTime - videoTargetTime) > 0.025) {
                    video.currentTime = videoTargetTime;
                }
            });
        };

        if (video) {
            video.pause();
            video.currentTime = 0;
            video.addEventListener('loadedmetadata', () => syncVideo(0), { once: true });
        }

        if (prefersReduced || !gsap || !ScrollTrigger || window.innerWidth < 981) {
            syncVideo(0);
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
        const copy = sequence.querySelector('.colt-origin__copy');
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

        gsap.set(copy, { xPercent: 50, yPercent: -50, y: 0, autoAlpha: 1 });
        gsap.set(chapters, { autoAlpha: 0, y: 32, filter: 'blur(10px)' });
        gsap.set(rail, { autoAlpha: 1 });
        gsap.set(serviceTab, { autoAlpha: 1 });
        syncVideo(0);

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
                    syncVideo(self.progress);
                },
            },
        });

        showChapter(0, 0.2);
        showChapter(1, 0.62);
        showChapter(2, 1.02);

        tl
            .to(skyLines, { x: '-8vw', y: -8, stagger: 0.03, duration: 0.5 }, 0)
            .to(atmosphere, { x: '-6vw', y: -6, stagger: 0.04, duration: 0.5 }, 0)
            .to(label, { autoAlpha: 0, y: 18, duration: 0.22 }, 0.08)
            .to(rail, { y: -18, autoAlpha: 0.9, duration: 0.26 }, 0.08)
            .to(serviceTab, { x: -80, autoAlpha: 0, duration: 0.32 }, 1.24)
            .to(rail, { x: 40, autoAlpha: 0, duration: 0.34 }, 1.28)
            .to(skyLines, { x: '-38vw', autoAlpha: 0.18, duration: 0.56 }, 1.25);
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
