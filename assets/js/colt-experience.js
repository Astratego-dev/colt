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

        const frameCanvas = sequence.querySelector('[data-origin-canvas]');
        const chapters = Array.from(sequence.querySelectorAll('[data-origin-chapter]'));
        const renderer = createOriginRenderer(root, sequence, frameCanvas, prefersReduced);
        const chapterRanges = [
            [0.12, 0.36],
            [0.43, 0.66],
            [0.72, 0.9],
        ];

        const updateChapters = (progress) => {
            chapters.forEach((chapter, index) => {
                const range = chapterRanges[index] || [0, 0];
                const fadeIn = smoothstep(range[0], range[0] + 0.055, progress);
                const fadeOut = 1 - smoothstep(range[1] - 0.055, range[1], progress);
                const visibility = Math.max(0, Math.min(1, fadeIn * fadeOut));
                const travel = -34 + visibility * 34 + Math.max(0, progress - range[1]) * -90;
                chapter.style.opacity = visibility.toFixed(3);
                chapter.style.transform = `translate3d(0, ${travel.toFixed(2)}px, 0) scale(${(0.985 + visibility * 0.015).toFixed(4)})`;
                chapter.style.filter = `blur(${((1 - visibility) * 10).toFixed(2)}px)`;
                chapter.style.pointerEvents = visibility > 0.82 ? 'auto' : 'none';
            });

            const stage = progress < 0.4 ? '0' : progress < 0.7 ? '1' : '2';
            sequence.dataset.originStage = stage;
            sequence.querySelectorAll('[data-origin-step]').forEach((step) => {
                step.classList.toggle('is-active', step.dataset.originStep === stage);
            });
        };

        if (prefersReduced || !gsap || !ScrollTrigger || window.innerWidth < 981) {
            renderer.setProgress(0);
            updateChapters(0.18);
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
        const skyLines = sequence.querySelectorAll('.colt-origin__sky span');
        const atmosphere = sequence.querySelectorAll('.colt-origin__atmosphere span');
        const label = sequence.querySelector('.colt-origin__label');
        const rail = sequence.querySelector('.colt-origin__rail');
        const serviceTab = sequence.querySelector('.colt-origin__service-tab');

        gsap.set(chapters, { autoAlpha: 0, y: 32, filter: 'blur(10px)' });
        gsap.set(rail, { autoAlpha: 1 });
        gsap.set(serviceTab, { autoAlpha: 1 });
        renderer.setProgress(0);
        updateChapters(0);

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
                    renderer.setProgress(self.progress);
                    updateChapters(self.progress);
                },
            },
        });

        tl
            .to(skyLines, { x: '-8vw', y: -8, stagger: 0.03, duration: 0.5 }, 0)
            .to(atmosphere, { x: '-6vw', y: -6, stagger: 0.04, duration: 0.5 }, 0)
            .to(label, { autoAlpha: 0, y: 18, duration: 0.22 }, 0.08)
            .to(rail, { y: -18, autoAlpha: 0.9, duration: 0.26 }, 0.08)
            .to(serviceTab, { x: -80, autoAlpha: 0, duration: 0.32 }, 1.24)
            .to(rail, { x: 40, autoAlpha: 0, duration: 0.34 }, 1.28)
            .to(skyLines, { x: '-38vw', autoAlpha: 0.18, duration: 0.56 }, 1.25);
    }

    function createOriginRenderer(root, sequence, canvas, prefersReduced) {
        const poster = sequence.querySelector('.colt-origin__poster');
        if (!canvas) {
            return { setProgress: () => {} };
        }

        const ctx = canvas.getContext('2d', { alpha: false });
        const frameCount = Math.max(1, parseInt(canvas.dataset.frameCount || '1', 10));
        const pad = Math.max(1, parseInt(canvas.dataset.framePad || '4', 10));
        const ext = canvas.dataset.frameExt || 'webp';
        const base = canvas.dataset.frameBase || '';
        const version = root.dataset.version || Date.now().toString();
        const images = new Array(frameCount);
        const requested = new Array(frameCount).fill(false);
        const loaded = new Array(frameCount).fill(false);

        let width = 0;
        let height = 0;
        let ratio = 1;
        let targetProgress = 0;
        let currentProgress = 0;
        let renderRequest = 0;
        let firstFrameReady = false;
        let lastDrawn = -1;

        const clampIndex = (index) => Math.max(0, Math.min(frameCount - 1, index));
        const frameUrl = (index) => `${base}${String(index).padStart(pad, '0')}.${ext}?ver=${encodeURIComponent(version)}`;

        const resize = () => {
            ratio = Math.min(window.devicePixelRatio || 1, 1.6);
            width = Math.max(1, canvas.clientWidth || window.innerWidth);
            height = Math.max(1, canvas.clientHeight || window.innerHeight);
            canvas.width = Math.floor(width * ratio);
            canvas.height = Math.floor(height * ratio);
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            drawFrame(currentProgress, true);
        };

        const drawImageCover = (image, alpha) => {
            const imageRatio = image.naturalWidth / image.naturalHeight;
            const canvasRatio = width / height;
            let drawWidth = width;
            let drawHeight = height;
            if (imageRatio > canvasRatio) {
                drawHeight = height;
                drawWidth = height * imageRatio;
            } else {
                drawWidth = width;
                drawHeight = width / imageRatio;
            }
            const x = (width - drawWidth) / 2;
            const y = (height - drawHeight) / 2;
            ctx.globalAlpha = alpha;
            ctx.drawImage(image, x, y, drawWidth, drawHeight);
            ctx.globalAlpha = 1;
        };

        const requestFrame = (index) => {
            const safeIndex = clampIndex(index);
            if (requested[safeIndex]) return;
            requested[safeIndex] = true;
            const image = new Image();
            image.decoding = 'async';
            image.onload = () => {
                loaded[safeIndex] = true;
                if (safeIndex === 0) {
                    firstFrameReady = true;
                    if (poster) poster.classList.add('is-hidden');
                }
                scheduleRender();
            };
            image.src = frameUrl(safeIndex);
            images[safeIndex] = image;
        };

        const prefetchAround = (progress) => {
            const exact = progress * (frameCount - 1);
            const center = Math.round(exact);
            for (let offset = -4; offset <= 10; offset += 1) {
                requestFrame(center + offset);
            }
        };

        const drawFrame = (progress, force) => {
            if (!width || !height) return;
            const exact = Math.max(0, Math.min(frameCount - 1, progress * (frameCount - 1)));
            const lower = clampIndex(Math.floor(exact));
            const upper = clampIndex(Math.ceil(exact));
            const blend = exact - lower;

            prefetchAround(progress);

            const lowerImage = loaded[lower] ? images[lower] : null;
            const upperImage = loaded[upper] ? images[upper] : null;
            if (!lowerImage && !upperImage) {
                if (!force && lastDrawn >= 0 && loaded[lastDrawn]) {
                    return;
                }
                const fallback = loaded[lastDrawn] ? images[lastDrawn] : null;
                if (!fallback) return;
                ctx.clearRect(0, 0, width, height);
                drawImageCover(fallback, 1);
                return;
            }

            ctx.clearRect(0, 0, width, height);
            if (lowerImage && upperImage && lower !== upper) {
                drawImageCover(lowerImage, 1);
                drawImageCover(upperImage, blend);
            } else {
                drawImageCover(lowerImage || upperImage, 1);
            }
            lastDrawn = lowerImage ? lower : upper;
            if (firstFrameReady && poster) {
                poster.classList.add('is-hidden');
            }
        };

        const tick = () => {
            renderRequest = 0;
            const stiffness = prefersReduced ? 1 : 0.22;
            currentProgress += (targetProgress - currentProgress) * stiffness;
            if (Math.abs(targetProgress - currentProgress) < 0.00045) {
                currentProgress = targetProgress;
            }
            drawFrame(currentProgress, false);
            if (currentProgress !== targetProgress) {
                scheduleRender();
            }
        };

        const scheduleRender = () => {
            if (!renderRequest) {
                renderRequest = window.requestAnimationFrame(tick);
            }
        };

        requestFrame(0);
        requestFrame(1);
        requestFrame(2);
        resize();
        window.addEventListener('resize', resize, { passive: true });

        const loadRest = () => {
            for (let index = 0; index < frameCount; index += 1) {
                requestFrame(index);
            }
        };
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(loadRest, { timeout: 1800 });
        } else {
            window.setTimeout(loadRest, 700);
        }

        return {
            setProgress(progress) {
                targetProgress = Math.max(0, Math.min(1, progress));
                prefetchAround(targetProgress);
                scheduleRender();
            },
        };
    }

    function smoothstep(start, end, value) {
        if (start === end) return value >= end ? 1 : 0;
        const x = Math.max(0, Math.min(1, (value - start) / (end - start)));
        return x * x * (3 - 2 * x);
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
