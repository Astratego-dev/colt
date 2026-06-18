(function () {
    const roots = document.querySelectorAll('[data-colt-xp]');
    if (!roots.length) return;

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    roots.forEach((root) => {
        setupReveal(root, prefersReduced);
        setupCanvas(root, prefersReduced);
        setupOrigin(root, prefersReduced);
        setupCoreWorld(root, prefersReduced);
        setupOrbitWorld(root, prefersReduced);
        setupProductWorld(root, prefersReduced);
        setupFinale(root, prefersReduced);
        setupHyperspace(root, prefersReduced);
        setupVaultExperience(root, prefersReduced);
        setupMysteryBoxExperience(root, prefersReduced);
        setupServiceExperience(root, prefersReduced);
        setupAstrategoTower(root, prefersReduced);
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

    function ensureSmoothScroll(gsap, ScrollTrigger) {
        if (isCompactViewport() || !window.Lenis || window.__coltLenis) return;

        window.__coltLenis = new window.Lenis({ duration: 1.18, smoothWheel: true, wheelMultiplier: 0.82 });
        window.__coltLenis.on('scroll', ScrollTrigger.update);
        gsap.ticker.add((time) => window.__coltLenis.raf(time * 1000));
        gsap.ticker.lagSmoothing(0);
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

        if (prefersReduced || !gsap || !ScrollTrigger) {
            renderer.setProgress(0);
            updateChapters(0.18);
            return;
        }

        gsap.registerPlugin(ScrollTrigger);

        const pin = sequence.querySelector('.colt-origin__pin');
        const skyLines = sequence.querySelectorAll('.colt-origin__sky span');
        const atmosphere = sequence.querySelectorAll('.colt-origin__atmosphere span');
        const label = sequence.querySelector('.colt-origin__label');
        const rail = sequence.querySelector('.colt-origin__rail');
        const serviceTab = sequence.querySelector('.colt-origin__service-tab');

        if (isCompactViewport()) {
            root.classList.add('is-mobile-motion');
            gsap.set(chapters, { autoAlpha: 0, y: 28, filter: 'blur(10px)' });
            renderer.setProgress(0);
            updateChapters(0);

            gsap.timeline({
                defaults: { ease: 'power2.inOut' },
                scrollTrigger: {
                    trigger: sequence,
                    start: 'top top',
                    end: 'bottom bottom',
                    scrub: 0.78,
                    pin,
                    anticipatePin: 1,
                    onUpdate: (self) => {
                        sequence.style.setProperty('--origin-progress', self.progress.toFixed(3));
                        renderer.setProgress(self.progress);
                        updateChapters(self.progress);
                    },
                },
            })
                .to(skyLines, { x: '-18vw', autoAlpha: 0.36, stagger: 0.04, duration: 0.72 }, 0)
                .to(atmosphere, { x: '-10vw', y: -8, stagger: 0.04, duration: 0.72 }, 0)
                .to(label, { autoAlpha: 0, y: 18, duration: 0.18 }, 0.08);
            return;
        }

        ensureSmoothScroll(gsap, ScrollTrigger);

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

    function isCompactViewport() {
        return window.matchMedia('(max-width: 980px)').matches;
    }

    function setupCoreWorld(root, prefersReduced) {
        const gsap = window.gsap;
        const ScrollTrigger = window.ScrollTrigger;
        const scene = root.querySelector('[data-core-world]');
        if (!scene) return;

        const pin = scene.querySelector('.colt-core-world__pin');
        const copy = scene.querySelector('[data-core-copy]');
        const cards = Array.from(scene.querySelectorAll('[data-core-card]'));
        const dots = Array.from(scene.querySelectorAll('[data-core-dot]'));
        const rings = scene.querySelectorAll('.colt-core-world__rings span');
        const petals = scene.querySelectorAll('.colt-core-world__bloom span');
        const garden = scene.querySelector('.colt-core-world__garden');

        const updateStage = (progress) => {
            const portal = smoothstep(0.02, 0.18, progress) * 96;
            const glow = smoothstep(0.12, 0.42, progress);
            const active = Math.max(0, Math.min(cards.length - 1, Math.floor(smoothstep(0.33, 0.94, progress) * cards.length)));
            scene.style.setProperty('--core-portal', `${portal.toFixed(2)}vmax`);
            scene.style.setProperty('--core-glow', glow.toFixed(3));
            scene.style.setProperty('--core-progress', progress.toFixed(3));
            scene.style.setProperty('--core-shift-x', `${(-4 * progress).toFixed(2)}vw`);
            scene.style.setProperty('--core-shift-y', `${(2 * progress).toFixed(2)}vh`);
            scene.dataset.coreStage = `${active}`;
            scene.classList.toggle('is-card-stage', progress > 0.3);
            cards.forEach((card, index) => card.classList.toggle('is-active', index === active));
            dots.forEach((dot, index) => dot.classList.toggle('is-active', index === active));
        };

        if (prefersReduced || !gsap || !ScrollTrigger) {
            updateStage(1);
            if (copy) {
                copy.style.opacity = '1';
                copy.style.transform = 'none';
            }
            cards.forEach((card) => {
                card.style.opacity = '1';
                card.style.transform = 'none';
                card.style.pointerEvents = 'auto';
            });
            return;
        }

        gsap.registerPlugin(ScrollTrigger);

        if (isCompactViewport()) {
            root.classList.add('is-mobile-motion');
            updateStage(0);
            if (copy) {
                gsap.set(copy, { autoAlpha: 0, y: 24, filter: 'blur(10px)' });
            }
            gsap.set(rings, { autoAlpha: 0.28, scale: 0.92, rotate: -12 });
            gsap.set(petals, { autoAlpha: 0.42, y: 18, rotate: -8 });

            gsap.timeline({
                defaults: { ease: 'power2.inOut' },
                scrollTrigger: {
                    trigger: scene,
                    start: 'top top',
                    end: 'bottom bottom',
                    scrub: 0.74,
                    pin,
                    anticipatePin: 1,
                    onUpdate: (self) => updateStage(self.progress),
                },
            })
                .to(garden, { scale: 1.09, xPercent: -4, yPercent: -2.4, duration: 0.9 }, 0)
                .to(rings, { autoAlpha: 0.82, scale: 1.1, rotate: 22, stagger: 0.03, duration: 0.55 }, 0.06)
                .to(petals, { autoAlpha: 0.82, y: 0, rotate: 0, stagger: 0.01, duration: 0.42 }, 0.1)
                .to(copy, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.18 }, 0.04)
                .to(copy, { autoAlpha: 0, y: -24, filter: 'blur(8px)', duration: 0.18 }, 0.25)
                .to(rings, { scale: 1.34, rotate: 42, stagger: 0.025, duration: 0.42 }, 0.62);
            return;
        }

        gsap.set(copy, { autoAlpha: 0, y: 34, filter: 'blur(12px)' });
        gsap.set(cards, { autoAlpha: 0, y: 80, scale: 0.88, rotateX: 8, filter: 'blur(14px)', pointerEvents: 'none' });
        gsap.set(rings, { scale: 0.48, autoAlpha: 0, rotate: -24 });
        gsap.set(petals, { autoAlpha: 0, y: 40, rotate: -12 });
        updateStage(0);

        const tl = gsap.timeline({
            defaults: { ease: 'power2.inOut' },
            scrollTrigger: {
                trigger: scene,
                start: 'top top',
                end: 'bottom bottom',
                scrub: 0.88,
                pin,
                anticipatePin: 1,
                onUpdate: (self) => updateStage(self.progress),
            },
        });

        tl
            .to(garden, { scale: 1.06, xPercent: -2.2, yPercent: -1.4, duration: 0.9 }, 0)
            .to(rings, { autoAlpha: 1, scale: 1, rotate: 0, stagger: 0.035, duration: 0.34 }, 0.08)
            .to(rings, { scale: 1.56, rotate: 28, stagger: 0.02, duration: 0.64 }, 0.34)
            .to(copy, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.22 }, 0.2)
            .to(copy, { autoAlpha: 0, y: -36, filter: 'blur(9px)', duration: 0.18 }, 0.46)
            .to(petals, { autoAlpha: 1, y: 0, rotate: 0, stagger: 0.012, duration: 0.36 }, 0.18);

        cards.forEach((card, index) => {
            const at = 0.38 + index * 0.13;
            tl.to(card, {
                autoAlpha: 1,
                y: 0,
                scale: 1,
                rotateX: 0,
                filter: 'blur(0px)',
                pointerEvents: 'auto',
                duration: 0.22,
            }, at);
            tl.to(card, {
                y: -12,
                scale: index === cards.length - 1 ? 1.02 : 0.98,
                duration: 0.16,
            }, at + 0.18);
        });

        tl
            .to(cards.slice(0, -1), { y: -24, scale: 0.96, autoAlpha: 0.9, stagger: 0.03, duration: 0.22 }, 0.9)
            .to(rings, { scale: 2.24, autoAlpha: 0.38, stagger: 0.02, duration: 0.28 }, 0.86);
    }

    function setupOrbitWorld(root, prefersReduced) {
        const gsap = window.gsap;
        const ScrollTrigger = window.ScrollTrigger;
        const scene = root.querySelector('[data-orbit-world]');
        if (!scene) return;

        const pin = scene.querySelector('.colt-orbit__pin');
        const copy = scene.querySelector('[data-orbit-copy]');
        const planets = Array.from(scene.querySelectorAll('[data-orbit-planet]'));
        const rings = scene.querySelectorAll('.colt-orbit__rings span');
        const space = scene.querySelectorAll('.colt-orbit__space span');
        const guardianWrap = scene.querySelector('.colt-orbit__guardian-wrap');
        const dockItems = scene.querySelectorAll('.colt-orbit__dock span');
        const threeMount = scene.querySelector('[data-orbit-three]');

        setupOrbitThree(scene, threeMount, prefersReduced);

        const updateStage = (progress) => {
            const focus = smoothstep(0.2, 0.96, progress);
            const active = planets.length
                ? Math.max(0, Math.min(planets.length - 1, Math.floor(focus * planets.length)))
                : 0;

            scene.style.setProperty('--orbit-progress', progress.toFixed(3));
            scene.style.setProperty('--orbit-focus', focus.toFixed(3));
            scene.dataset.orbitStage = `${active}`;
            scene.classList.toggle('is-planet-stage', progress > 0.28);
            planets.forEach((planet, index) => planet.classList.toggle('is-active', index === active));
            dockItems.forEach((item, index) => item.classList.toggle('is-active', index === active || (index === dockItems.length - 1 && active >= dockItems.length)));
        };

        if (prefersReduced || !gsap || !ScrollTrigger) {
            updateStage(0.72);
            if (copy) {
                copy.style.opacity = '1';
                copy.style.transform = 'none';
            }
            planets.forEach((planet) => {
                planet.style.opacity = '1';
                planet.style.transform = 'none';
                planet.style.filter = 'none';
                planet.style.pointerEvents = 'auto';
            });
            if (guardianWrap) {
                guardianWrap.style.opacity = '1';
                guardianWrap.style.transform = 'none';
                guardianWrap.style.filter = 'none';
            }
            return;
        }

        gsap.registerPlugin(ScrollTrigger);

        if (isCompactViewport()) {
            root.classList.add('is-mobile-motion');
            updateStage(0);
            if (copy) {
                gsap.set(copy, { autoAlpha: 0, y: 24, filter: 'blur(10px)' });
            }
            gsap.set(guardianWrap, { autoAlpha: 0, y: 62, scale: 0.9, filter: 'blur(10px)' });
            gsap.set(rings, { autoAlpha: 0.22, scale: 0.86, rotate: -12 });

            gsap.timeline({
                defaults: { ease: 'power2.inOut' },
                scrollTrigger: {
                    trigger: scene,
                    start: 'top top',
                    end: 'bottom bottom',
                    scrub: 0.74,
                    pin,
                    anticipatePin: 1,
                    onUpdate: (self) => updateStage(self.progress),
                },
            })
                .to(space, { scale: 1.15, xPercent: -5, yPercent: -3, stagger: 0.035, duration: 0.92 }, 0)
                .to(copy, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.2 }, 0.03)
                .to(copy, { autoAlpha: 0, y: -24, filter: 'blur(8px)', duration: 0.18 }, 0.24)
                .to(guardianWrap, { autoAlpha: 1, y: 0, scale: 1, filter: 'blur(0px)', duration: 0.32 }, 0.12)
                .to(rings, { autoAlpha: 0.9, scale: 1.08, rotate: 22, stagger: 0.035, duration: 0.48 }, 0.18)
                .to(guardianWrap, { y: -14, scale: 1.04, duration: 0.38 }, 0.64)
                .to(rings, { scale: 1.22, rotate: 40, stagger: 0.025, duration: 0.38 }, 0.68);
            return;
        }

        gsap.set(copy, { autoAlpha: 0, y: 34, filter: 'blur(12px)' });
        gsap.set(guardianWrap, { autoAlpha: 0, y: 90, scale: 0.86, filter: 'blur(12px)' });
        gsap.set(rings, { autoAlpha: 0, scale: 0.42, rotate: -18 });
        gsap.set(planets, { autoAlpha: 0, y: 82, scale: 0.72, filter: 'blur(18px)', pointerEvents: 'none' });
        gsap.set(dockItems, { autoAlpha: 0, y: 20 });
        updateStage(0);

        const tl = gsap.timeline({
            defaults: { ease: 'power2.inOut' },
            scrollTrigger: {
                trigger: scene,
                start: 'top top',
                end: 'bottom bottom',
                scrub: 0.92,
                pin,
                anticipatePin: 1,
                onUpdate: (self) => updateStage(self.progress),
            },
        });

        tl
            .to(space, { scale: 1.12, xPercent: -3.4, yPercent: -2.2, stagger: 0.035, duration: 0.94 }, 0)
            .to(copy, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.22 }, 0.04)
            .to(copy, { autoAlpha: 0, y: -36, filter: 'blur(10px)', duration: 0.18 }, 0.36)
            .to(guardianWrap, { autoAlpha: 1, y: 0, scale: 1, filter: 'blur(0px)', duration: 0.32 }, 0.1)
            .to(rings, { autoAlpha: 1, scale: 1, rotate: 0, stagger: 0.04, duration: 0.34 }, 0.2)
            .to(planets, {
                autoAlpha: 1,
                y: 0,
                scale: 1,
                filter: 'blur(0px)',
                pointerEvents: 'auto',
                stagger: 0.055,
                duration: 0.34,
            }, 0.31)
            .to(dockItems, { autoAlpha: 1, y: 0, stagger: 0.035, duration: 0.24 }, 0.45)
            .to(rings, { scale: 1.16, rotate: 30, stagger: 0.025, duration: 0.56 }, 0.54)
            .to(planets, { y: -22, stagger: 0.035, duration: 0.28 }, 0.66)
            .to(guardianWrap, { y: -18, scale: 1.07, duration: 0.34 }, 0.7)
            .to(space, { scale: 1.2, xPercent: -7, yPercent: -4, duration: 0.36 }, 0.82);
    }

    async function setupOrbitThree(orbitScene, mount, prefersReduced) {
        if (!mount || prefersReduced || !window.WebGLRenderingContext) return;

        const modelUrls = [
            mount.dataset.modelPrimary,
            mount.dataset.modelSecondary,
            mount.dataset.modelTertiary,
        ].filter(Boolean);
        if (!modelUrls.length) return;

        try {
            const [THREE, loaderModule] = await Promise.all([
                import('https://esm.sh/three@0.160.0'),
                import('https://esm.sh/three@0.160.0/examples/jsm/loaders/GLTFLoader.js'),
            ]);
            if (!document.body.contains(mount)) return;

            const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true, powerPreference: 'high-performance' });
            renderer.setClearColor(0x000000, 0);
            renderer.outputColorSpace = THREE.SRGBColorSpace;
            mount.appendChild(renderer.domElement);

            const world = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(38, 1, 0.1, 80);
            camera.position.set(0, 0.1, 6.4);

            world.add(new THREE.AmbientLight(0xffffff, 1.18));
            const key = new THREE.DirectionalLight(0xfff0c6, 2.1);
            key.position.set(4, 5, 5);
            world.add(key);
            const fill = new THREE.PointLight(0x91f0df, 2.6, 10);
            fill.position.set(-3.4, 1.4, 2.8);
            world.add(fill);
            const rose = new THREE.PointLight(0xff91c8, 1.9, 9);
            rose.position.set(3.2, -1.5, 2.4);
            world.add(rose);

            const loader = new loaderModule.GLTFLoader();
            const specs = [
                { position: [2.18, 0.54, -0.52], scale: 1.1, spin: -0.006, drift: 0.9, push: 0.44 },
                { position: [-2.3, 0.28, -0.34], scale: 1.18, spin: 0.006, drift: 1.45, push: -0.46 },
                { position: [1.52, -1.18, 0.08], scale: 1.02, spin: 0.008, drift: 2.1, push: 0.28 },
                { position: [-1.78, -1.18, -0.04], scale: 0.92, spin: -0.007, drift: 2.75, push: -0.3 },
                { position: [0.0, 0.9, -0.62], scale: 0.82, spin: 0.006, drift: 3.4, push: 0.12 },
            ];
            const sourceSpec = { position: [0, 0, 0], scale: 1, spin: 0, drift: 0, push: 0 };

            const sources = await Promise.all(modelUrls.map((url) => loadOrbitModel(loader, THREE, url, sourceSpec)));
            const groups = specs.map((spec, index) => cloneOrbitModel(sources[index % sources.length], spec));
            groups.forEach((group) => world.add(group));
            mount.classList.add('is-loaded');
            orbitScene.classList.add('has-3d-planets');
            const travelState = { activeIndex: -1, startedAt: 0, duration: 1040 };

            orbitScene.__coltFocusPlanet = (index) => {
                const activeIndex = Math.max(0, Math.min(groups.length - 1, index));
                travelState.activeIndex = activeIndex;
                travelState.startedAt = performance.now();
                travelState.duration = isCompactViewport() ? 900 : 1040;
                groups.forEach((group) => {
                    group.userData.travelStartPosition = group.position.clone();
                    group.userData.travelStartScale = group.scale.x;
                });
                return travelState.duration;
            };

            const resize = () => {
                const rect = mount.getBoundingClientRect();
                const width = Math.max(1, rect.width);
                const height = Math.max(1, rect.height);
                renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
                renderer.setSize(width, height, false);
                camera.aspect = width / height;
                camera.updateProjectionMatrix();
            };

            const render = (time) => {
                if (!document.body.contains(mount)) return;
                const progress = parseFloat(orbitScene.style.getPropertyValue('--orbit-progress')) || 0;
                const focus = parseFloat(orbitScene.style.getPropertyValue('--orbit-focus')) || 0;
                const travelProgress = travelState.activeIndex >= 0
                    ? smoothstep(0, 1, (time - travelState.startedAt) / travelState.duration)
                    : 0;
                groups.forEach((group, index) => {
                    const spec = group.userData.spec;
                    group.rotation.y += spec.spin;
                    group.rotation.x = Math.sin(time * 0.00045 + index) * 0.12;
                    group.rotation.z = Math.cos(time * 0.00035 + index) * 0.08;
                    const naturalX = spec.position[0] + Math.sin(time * 0.00035 + spec.drift) * 0.1 + (progress - 0.5) * spec.push;
                    const naturalY = spec.position[1] + Math.cos(time * 0.00042 + spec.drift) * 0.08 + focus * (index === 4 ? -0.02 : 0.04);
                    const naturalZ = spec.position[2];
                    const pulse = 1 + Math.sin(time * 0.001 + index) * 0.035 + focus * (index === 4 ? 0.04 : 0.08);
                    const naturalScale = group.userData.baseScale * pulse;

                    if (travelProgress > 0 && travelState.activeIndex === index) {
                        const start = group.userData.travelStartPosition || group.position;
                        const startScale = group.userData.travelStartScale || naturalScale;
                        group.position.x = start.x + (0 - start.x) * travelProgress;
                        group.position.y = start.y + ((isCompactViewport() ? -0.04 : -0.02) - start.y) * travelProgress;
                        group.position.z = start.z + (1.72 - start.z) * travelProgress;
                        group.rotation.y += 0.032 + travelProgress * 0.032;
                        group.rotation.x *= 1 - travelProgress * 0.38;
                        group.rotation.z *= 1 - travelProgress * 0.3;
                        group.scale.setScalar(startScale + (group.userData.baseScale * (isCompactViewport() ? 2.35 : 2.75) - startScale) * travelProgress);
                        return;
                    }

                    if (travelProgress > 0) {
                        const side = index % 2 === 0 ? 1 : -1;
                        group.position.x = naturalX + side * 0.46 * travelProgress;
                        group.position.y = naturalY + (index < travelState.activeIndex ? 0.22 : -0.22) * travelProgress;
                        group.position.z = naturalZ - 1.1 * travelProgress;
                        group.scale.setScalar(naturalScale * (1 - 0.42 * travelProgress));
                        return;
                    }

                    group.position.x = naturalX;
                    group.position.y = naturalY;
                    group.position.z = naturalZ;
                    group.scale.setScalar(naturalScale);
                });
                camera.position.z = 6.4 - focus * 0.76;
                camera.position.x = (progress - 0.5) * 0.32;
                camera.lookAt(0, -0.08, 0);
                renderer.render(world, camera);
                window.requestAnimationFrame(render);
            };

            resize();
            window.addEventListener('resize', resize, { passive: true });
            window.requestAnimationFrame(render);
        } catch (error) {
            mount.classList.add('is-fallback');
        }
    }

    function loadOrbitModel(loader, THREE, url, spec) {
        return new Promise((resolve, reject) => {
            loader.load(url, (gltf) => {
                const object = gltf.scene;
                const box = new THREE.Box3().setFromObject(object);
                const center = box.getCenter(new THREE.Vector3());
                const size = box.getSize(new THREE.Vector3());
                const maxSize = Math.max(size.x, size.y, size.z) || 1;
                object.position.sub(center);
                object.traverse((child) => {
                    if (!child.isMesh) return;
                    child.frustumCulled = false;
                    if (child.material) {
                        child.material = Array.isArray(child.material)
                            ? child.material.map((material) => material.clone())
                            : child.material.clone();
                        const materials = Array.isArray(child.material) ? child.material : [child.material];
                        materials.forEach((material) => {
                            material.roughness = Math.min(1, (material.roughness || 0.55) + 0.12);
                            material.metalness = Math.max(0, (material.metalness || 0) * 0.4);
                        });
                    }
                });

                const group = new THREE.Group();
                group.add(object);
                group.position.set(spec.position[0], spec.position[1], spec.position[2]);
                group.userData.spec = spec;
                group.userData.modelScale = 1 / maxSize;
                group.userData.baseScale = spec.scale * group.userData.modelScale;
                group.scale.setScalar(group.userData.baseScale);
                resolve(group);
            }, undefined, reject);
        });
    }

    function cloneOrbitModel(source, spec) {
        const group = source.clone(true);
        group.traverse((child) => {
            if (!child.isMesh || !child.material) return;
            child.material = Array.isArray(child.material)
                ? child.material.map((material) => material.clone())
                : child.material.clone();
        });
        group.position.set(spec.position[0], spec.position[1], spec.position[2]);
        group.userData.spec = spec;
        group.userData.modelScale = source.userData.modelScale || 1;
        group.userData.baseScale = spec.scale * group.userData.modelScale;
        group.scale.setScalar(group.userData.baseScale);
        return group;
    }

    function setupFinale(root, prefersReduced) {
        const gsap = window.gsap;
        const ScrollTrigger = window.ScrollTrigger;
        const scene = root.querySelector('[data-finale]');
        if (!scene) return;

        const pin = scene.querySelector('.colt-finale__pin');
        const garden = scene.querySelector('.colt-finale__garden');
        const brand = scene.querySelector('[data-finale-brand]');
        const contact = scene.querySelector('[data-finale-contact]');
        const socials = scene.querySelector('[data-finale-socials]');
        const socialLinks = scene.querySelectorAll('.colt-finale__social');
        const petals = scene.querySelectorAll('.colt-finale__petals span');
        const halos = scene.querySelectorAll('.colt-finale__halo span');

        if (prefersReduced || !gsap || !ScrollTrigger) {
            [brand, contact, socials].forEach((item) => {
                if (!item) return;
                item.style.opacity = '1';
                item.style.transform = 'none';
                item.style.filter = 'none';
            });
            return;
        }

        gsap.registerPlugin(ScrollTrigger);

        gsap.set(brand, { autoAlpha: 0, y: 38, scale: 0.96, filter: 'blur(12px)' });
        gsap.set(contact, { autoAlpha: 0, x: isCompactViewport() ? 0 : 70, y: isCompactViewport() ? 34 : 0, filter: 'blur(12px)' });
        gsap.set(socials, { autoAlpha: 0, x: isCompactViewport() ? 0 : -70, y: isCompactViewport() ? 34 : 0, filter: 'blur(12px)' });
        gsap.set(socialLinks, { autoAlpha: 0, y: 22, scale: 0.94 });
        gsap.set(petals, { autoAlpha: 0, y: 30, rotate: -18 });
        gsap.set(halos, { autoAlpha: 0, scale: 0.58, rotate: -18 });

        gsap.timeline({
            defaults: { ease: 'power2.inOut' },
            scrollTrigger: {
                trigger: scene,
                start: 'top top',
                end: 'bottom bottom',
                scrub: 0.78,
                pin,
                anticipatePin: 1,
                onUpdate: (self) => {
                    scene.style.setProperty('--finale-progress', self.progress.toFixed(3));
                },
            },
        })
            .to(garden, { scale: 1.08, xPercent: -2.5, yPercent: -1.8, duration: 0.95 }, 0)
            .to(halos, { autoAlpha: 0.74, scale: 1, rotate: 18, stagger: 0.04, duration: 0.34 }, 0.04)
            .to(petals, { autoAlpha: 0.88, y: 0, rotate: 0, stagger: 0.012, duration: 0.42 }, 0.08)
            .to(brand, { autoAlpha: 1, y: 0, scale: 1, filter: 'blur(0px)', duration: 0.24 }, 0.08)
            .to(brand, { autoAlpha: isCompactViewport() ? 0.42 : 0.62, y: isCompactViewport() ? -72 : -26, scale: 0.96, filter: 'blur(6px)', duration: 0.22 }, 0.38)
            .to(contact, { autoAlpha: 1, x: 0, y: 0, filter: 'blur(0px)', duration: 0.28 }, 0.34)
            .to(socials, { autoAlpha: 1, x: 0, y: 0, filter: 'blur(0px)', duration: 0.28 }, 0.44)
            .to(socialLinks, { autoAlpha: 1, y: 0, scale: 1, stagger: 0.04, duration: 0.28 }, 0.5)
            .to(halos, { scale: 1.24, rotate: 44, stagger: 0.04, duration: 0.38 }, 0.68)
            .to(petals, { y: -26, x: -18, stagger: 0.008, duration: 0.32 }, 0.74);
    }

    function setupProductWorld(root, prefersReduced) {
        const gsap = window.gsap;
        const ScrollTrigger = window.ScrollTrigger;
        const scene = root.querySelector('[data-product-world]');
        if (!scene) return;

        const pin = scene.querySelector('.colt-products__pin');
        const intro = scene.querySelector('[data-product-intro]');
        const categories = scene.querySelectorAll('.colt-products__category');
        const track = scene.querySelector('[data-product-track]');
        const cards = scene.querySelectorAll('[data-product-card]');
        const sky = scene.querySelectorAll('.colt-products__sky span');
        const tickerItems = scene.querySelectorAll('.colt-products__ticker span');

        if (!track || !cards.length) return;

        const updateStage = (progress) => {
            const active = Math.max(0, Math.min(cards.length - 1, Math.floor(smoothstep(0.24, 0.92, progress) * cards.length)));
            scene.style.setProperty('--product-progress', progress.toFixed(3));
            cards.forEach((card, index) => card.classList.toggle('is-active', index === active));
        };

        const trackShift = () => {
            const viewport = scene.querySelector('[data-product-viewport]');
            if (!viewport) return 0;
            return Math.min(0, viewport.clientWidth - track.scrollWidth);
        };

        if (prefersReduced || !gsap || !ScrollTrigger) {
            updateStage(0.7);
            [intro, track].forEach((item) => {
                if (!item) return;
                item.style.opacity = '1';
                item.style.transform = 'none';
                item.style.filter = 'none';
            });
            cards.forEach((card) => {
                card.style.opacity = '1';
                card.style.transform = 'none';
                card.style.filter = 'none';
            });
            return;
        }

        gsap.registerPlugin(ScrollTrigger);
        const compact = isCompactViewport();

        gsap.set(intro, { autoAlpha: 0, y: 34, filter: 'blur(12px)' });
        gsap.set(categories, { autoAlpha: 0, y: 34, scale: 0.92, filter: 'blur(10px)' });
        gsap.set(cards, { autoAlpha: 0.9, y: compact ? 34 : 46, rotate: 2.5, scale: 0.94, filter: 'blur(6px)' });
        gsap.set(tickerItems, { autoAlpha: 0, y: 18 });
        updateStage(0);

        const tl = gsap.timeline({
            defaults: { ease: 'power2.inOut' },
            scrollTrigger: {
                trigger: scene,
                start: 'top top',
                end: 'bottom bottom',
                scrub: compact ? 0.72 : 0.88,
                pin,
                anticipatePin: 1,
                invalidateOnRefresh: true,
                onUpdate: (self) => updateStage(self.progress),
            },
        });

        tl
            .to(sky, { scale: compact ? 1.12 : 1.2, xPercent: -5, yPercent: -2, stagger: 0.03, duration: 0.9 }, 0)
            .to(intro, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.2 }, 0.04)
            .to(intro, { autoAlpha: compact ? 0.5 : 0.62, y: compact ? -48 : -28, filter: 'blur(5px)', duration: 0.18 }, 0.32)
            .to(categories, { autoAlpha: 1, y: 0, scale: 1, filter: 'blur(0px)', stagger: 0.035, duration: 0.3 }, 0.18)
            .to(cards, { autoAlpha: 1, y: 0, rotate: 0, scale: 1, filter: 'blur(0px)', stagger: 0.035, duration: 0.36 }, 0.12)
            .to(tickerItems, { autoAlpha: 1, y: 0, stagger: 0.035, duration: 0.24 }, 0.42)
            .to(track, { x: () => trackShift(), duration: 0.68, ease: 'none' }, 0.3)
            .to(categories, { x: compact ? -26 : -90, stagger: 0.018, duration: 0.54 }, 0.44)
            .to(cards, { y: (index) => (index % 2 === 0 ? -18 : 18), rotate: (index) => (index % 2 === 0 ? -2 : 2), stagger: 0.012, duration: 0.48 }, 0.5)
            .to(tickerItems, { x: compact ? -60 : -180, stagger: 0.02, duration: 0.46 }, 0.54);
    }

    function setupHyperspace(root, prefersReduced) {
        const overlay = root.querySelector('[data-hyperspace]');
        if (!overlay) return;

        root.querySelectorAll('[data-hyperspace-link]').forEach((link) => {
            link.addEventListener('click', (event) => {
                if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }
                if (link.target && link.target !== '_self') {
                    return;
                }

                let href = link.href;
                if (!href) return;

                try {
                    const target = new URL(href, window.location.href);
                    const current = new URL(window.location.href);
                    if (target.origin === current.origin && target.pathname === current.pathname && target.hash) {
                        return;
                    }
                    href = target.href;
                } catch (error) {
                    return;
                }

                const orbitPlanet = link.matches('[data-orbit-planet]') ? link : null;
                const orbitWorld = orbitPlanet ? orbitPlanet.closest('[data-orbit-world]') : null;
                let travelDelay = prefersReduced ? 100 : 760;
                let shouldShowOverlay = !orbitPlanet;

                if (!prefersReduced && orbitWorld && typeof orbitWorld.__coltFocusPlanet === 'function') {
                    const planets = Array.from(orbitWorld.querySelectorAll('[data-orbit-planet]'));
                    const planetIndex = planets.indexOf(orbitPlanet);
                    if (planetIndex >= 0) {
                        orbitWorld.classList.add('is-planet-traveling');
                        planets.forEach((planet) => planet.classList.remove('is-travel-target'));
                        orbitPlanet.classList.add('is-travel-target');
                        travelDelay = Math.max(1120, orbitWorld.__coltFocusPlanet(planetIndex) + 260);
                        shouldShowOverlay = false;
                    }
                }

                event.preventDefault();
                root.classList.add('is-traveling');
                document.documentElement.classList.add('colt-is-traveling');
                if (shouldShowOverlay) {
                    overlay.classList.add('is-active');
                }
                window.setTimeout(() => {
                    window.location.href = href;
                }, travelDelay);
            });
        });
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

    function setupVaultExperience(root, prefersReduced) {
        const gsap = window.gsap;
        const ScrollTrigger = window.ScrollTrigger;
        const vault = root.matches('[data-vault-xp]') ? root : root.querySelector('[data-vault-xp]');
        if (!vault) return;

        const hero = vault.querySelector('[data-vault-hero]');
        const inside = vault.querySelector('[data-vault-inside]');
        const protocol = vault.querySelector('[data-vault-protocol]');
        const contact = vault.querySelector('[data-vault-contact]');

        if (prefersReduced || !gsap || !ScrollTrigger) {
            vault.querySelectorAll('[data-vault-hero-copy], [data-vault-ledger], [data-vault-inside-copy], [data-vault-feature], [data-vault-step], [data-vault-contact-brand], [data-vault-contact-panel]').forEach((item) => {
                item.style.opacity = '1';
                item.style.transform = 'none';
                item.style.filter = 'none';
            });
            return;
        }

        gsap.registerPlugin(ScrollTrigger);

        if (hero) {
            const pin = hero.querySelector('.colt-vault-hero__pin');
            const bg = hero.querySelector('[data-vault-hero-bg]');
            const door = hero.querySelector('[data-vault-door]');
            const copy = hero.querySelector('[data-vault-hero-copy]');
            const ledger = hero.querySelector('[data-vault-ledger]');
            const scans = hero.querySelectorAll('.colt-vault-hero__scan span');

            gsap.set(copy, { autoAlpha: 0, y: 34, filter: 'blur(12px)' });
            gsap.set(ledger, { autoAlpha: 0, y: 24, filter: 'blur(10px)' });
            gsap.set(scans, { autoAlpha: 0, scaleX: 0.2 });

            gsap.timeline({
                defaults: { ease: 'power2.inOut' },
                scrollTrigger: {
                    trigger: hero,
                    start: 'top top',
                    end: 'bottom bottom',
                    scrub: 0.82,
                    pin,
                    anticipatePin: 1,
                },
            })
                .to(bg, { scale: 1.18, xPercent: isCompactViewport() ? -4 : -2, duration: 0.92 }, 0)
                .to(door, { scale: 1.18, rotate: -18, xPercent: isCompactViewport() ? 0 : 7, duration: 0.72 }, 0.12)
                .to(copy, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.24 }, 0.05)
                .to(copy, { autoAlpha: isCompactViewport() ? 0.42 : 0.66, y: -38, filter: 'blur(5px)', duration: 0.2 }, 0.42)
                .to(scans, { autoAlpha: 0.82, scaleX: 1, stagger: 0.06, duration: 0.28 }, 0.2)
                .to(ledger, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.3 }, 0.5)
                .to(door, { scale: 1.38, rotate: -34, xPercent: isCompactViewport() ? 0 : 13, filter: 'blur(2px)', duration: 0.3 }, 0.68);
        }

        if (inside) {
            const pin = inside.querySelector('.colt-vault-inside__pin');
            const bg = inside.querySelector('[data-vault-inside-bg]');
            const copy = inside.querySelector('[data-vault-inside-copy]');
            const features = inside.querySelectorAll('[data-vault-feature]');
            const slabs = inside.querySelectorAll('.colt-vault-inside__slabs span');
            const glass = inside.querySelectorAll('.colt-vault-inside__glass span');

            gsap.set(copy, { autoAlpha: 0, y: 34, filter: 'blur(12px)' });
            gsap.set(features, { autoAlpha: 0, y: 42, scale: 0.94, filter: 'blur(12px)' });
            gsap.set(slabs, { autoAlpha: 0, y: 80, rotate: 10, scale: 0.72, filter: 'blur(10px)' });
            gsap.set(glass, { autoAlpha: 0, scaleY: 0.4 });

            gsap.timeline({
                defaults: { ease: 'power2.inOut' },
                scrollTrigger: {
                    trigger: inside,
                    start: 'top top',
                    end: 'bottom bottom',
                    scrub: 0.86,
                    pin,
                    anticipatePin: 1,
                },
            })
                .to(bg, { scale: 1.14, xPercent: isCompactViewport() ? 2 : 4, duration: 0.95 }, 0)
                .to(glass, { autoAlpha: 0.56, scaleY: 1, stagger: 0.05, duration: 0.34 }, 0.02)
                .to(copy, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.24 }, 0.08)
                .to(features, { autoAlpha: 1, y: 0, scale: 1, filter: 'blur(0px)', stagger: 0.055, duration: 0.38 }, 0.28)
                .to(slabs, { autoAlpha: 1, y: 0, rotate: 0, scale: 1, filter: 'blur(0px)', stagger: 0.045, duration: 0.36 }, 0.18)
                .to(slabs, { y: (index) => (index % 2 === 0 ? -28 : 24), rotate: (index) => (index % 2 === 0 ? -6 : 6), stagger: 0.02, duration: 0.5 }, 0.55)
                .to(copy, { autoAlpha: isCompactViewport() ? 0.42 : 0.58, y: -24, filter: 'blur(4px)', duration: 0.22 }, 0.68);
        }

        if (protocol) {
            const copy = protocol.querySelector('[data-vault-protocol-copy]');
            const steps = protocol.querySelectorAll('[data-vault-step]');
            const rings = protocol.querySelectorAll('.colt-vault-protocol__rings span');

            gsap.set(copy, { autoAlpha: 0, y: 30, filter: 'blur(12px)' });
            gsap.set(steps, { autoAlpha: 0, y: 52, scale: 0.92, filter: 'blur(12px)' });
            gsap.set(rings, { autoAlpha: 0.22, scale: 0.76, rotate: -16 });

            gsap.timeline({
                defaults: { ease: 'power2.inOut' },
                scrollTrigger: {
                    trigger: protocol,
                    start: 'top 72%',
                    end: 'bottom 78%',
                    scrub: 0.65,
                },
            })
                .to(rings, { autoAlpha: 0.86, scale: 1.12, rotate: 26, stagger: 0.04, duration: 0.6 }, 0)
                .to(copy, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.26 }, 0.05)
                .to(steps, { autoAlpha: 1, y: 0, scale: 1, filter: 'blur(0px)', stagger: 0.08, duration: 0.44 }, 0.22);
        }

        if (contact) {
            const brand = contact.querySelector('[data-vault-contact-brand]');
            const panel = contact.querySelector('[data-vault-contact-panel]');
            gsap.set([brand, panel], { autoAlpha: 0, y: 36, filter: 'blur(12px)' });

            gsap.timeline({
                defaults: { ease: 'power2.out' },
                scrollTrigger: {
                    trigger: contact,
                    start: 'top 74%',
                    end: 'center 52%',
                    scrub: 0.58,
                },
            })
                .to(brand, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.42 }, 0)
                .to(panel, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.42 }, 0.12);
        }
    }

    function setupMysteryBoxExperience(root, prefersReduced) {
        const gsap = window.gsap;
        const ScrollTrigger = window.ScrollTrigger;
        const mystery = root.matches('[data-mystery-xp]') ? root : root.querySelector('[data-mystery-xp]');
        if (!mystery) return;

        setupMysteryPicker(mystery);

        const hero = mystery.querySelector('[data-mystery-hero]');
        const reveal = mystery.querySelector('[data-mystery-reveal]');
        const options = mystery.querySelector('[data-mystery-options]');
        const buy = mystery.querySelector('[data-mystery-buy]');

        if (prefersReduced || !gsap || !ScrollTrigger) {
            mystery.querySelectorAll('[data-mystery-hero-copy], [data-mystery-rail], [data-mystery-stream] span, [data-mystery-foil] span, [data-mystery-reveal-copy], [data-mystery-rip] span, [data-mystery-showcase] span, [data-mystery-item], [data-mystery-options-copy], [data-mystery-picker], [data-mystery-summary], [data-mystery-buy-brand], [data-mystery-buy-panel]').forEach((item) => {
                item.style.opacity = '1';
                item.style.transform = 'none';
                item.style.filter = 'none';
            });
            return;
        }

        gsap.registerPlugin(ScrollTrigger);
        ensureSmoothScroll(gsap, ScrollTrigger);

        if (hero) {
            const pin = hero.querySelector('.colt-mystery-hero__pin');
            const bg = hero.querySelector('[data-mystery-hero-bg]');
            const box = hero.querySelector('[data-mystery-box]');
            const core = hero.querySelector('.colt-mystery-box__core');
            const tray = hero.querySelector('.colt-mystery-box__tray');
            const lid = hero.querySelector('.colt-mystery-box__lid');
            const seal = hero.querySelector('.colt-mystery-box__seal');
            const glow = hero.querySelector('.colt-mystery-box__glow');
            const copy = hero.querySelector('[data-mystery-hero-copy]');
            const rail = hero.querySelector('[data-mystery-rail]');
            const orbit = hero.querySelectorAll('.colt-mystery-orbit span');
            const stream = hero.querySelectorAll('[data-mystery-stream] span');
            const foil = hero.querySelectorAll('[data-mystery-foil] span');
            const compact = isCompactViewport();

            gsap.set(copy, { autoAlpha: 0, y: 34, scale: 0.985, filter: 'blur(8px)', force3D: true });
            gsap.set(rail, { autoAlpha: 0, y: 24, filter: 'blur(6px)', force3D: true });
            gsap.set(orbit, { autoAlpha: 0.22, scale: 0.72, rotate: -20, force3D: true });
            gsap.set(bg, { '--mystery-open-alpha': 0 });
            gsap.set([core, tray, lid, seal, glow], { force3D: true });
            gsap.set(tray, { autoAlpha: 0.74, y: 0 });
            gsap.set(glow, { autoAlpha: 0.58, scale: 0.82 });
            gsap.set(stream, { autoAlpha: 0, x: 0, y: 0, z: 0, scale: 0.52, rotate: 0, rotateY: 0, filter: 'blur(6px)', force3D: true });
            gsap.set(foil, { autoAlpha: 0, x: 0, y: 0, scaleX: 0.2, rotate: 0, filter: 'blur(3px)', force3D: true });

            gsap.timeline({
                defaults: { ease: 'power2.inOut' },
                scrollTrigger: {
                    trigger: hero,
                    start: 'top top',
                    end: 'bottom bottom',
                    scrub: 1.28,
                    pin,
                    anticipatePin: 1,
                },
            })
                .to(bg, { scale: compact ? 1.1 : 1.14, xPercent: compact ? -4 : -2, duration: 1.18 }, 0)
                .to(bg, { '--mystery-open-alpha': 0.9, duration: 0.82 }, 0.18)
                .to(orbit, { autoAlpha: 0.82, scale: 1.24, rotate: 38, stagger: 0.05, duration: 0.96 }, 0.04)
                .to(box, { y: compact ? 14 : -16, scale: compact ? 1.04 : 1.16, rotate: -4, duration: 0.72 }, 0.06)
                .to(seal, { autoAlpha: 0, y: compact ? -24 : -34, scaleX: 0.08, filter: 'blur(4px)', duration: 0.24 }, 0.22)
                .to(lid, { y: compact ? -58 : -86, x: compact ? -10 : -18, rotate: -32, rotateX: -26, duration: 0.68 }, 0.28)
                .to(core, { y: compact ? 12 : 18, scale: 1.04, rotate: -2, duration: 0.56 }, 0.3)
                .to(tray, { autoAlpha: 1, y: compact ? -14 : -22, scale: 1.08, duration: 0.56 }, 0.32)
                .to(glow, { autoAlpha: 0.94, scale: 1.52, filter: 'blur(24px)', duration: 0.56 }, 0.3)
                .to(foil, {
                    autoAlpha: 0.9,
                    x: (index) => (compact ? [-120, -64, -22, 28, 70, 118, -92, -38, 36, 88, -128, 128][index] : [-260, -180, -96, -30, 42, 118, 190, 268, -224, -132, 156, 232][index]),
                    y: (index) => (compact ? [-86, -46, -18, -72, -24, -58, 34, 56, 22, 70, -4, 12][index] : [-132, -82, -34, -118, -46, -98, 58, 96, 34, 122, -8, 18][index]),
                    rotate: (index) => (index % 2 === 0 ? -28 : 32) + index * 5,
                    scaleX: 1,
                    filter: 'blur(0px)',
                    stagger: 0.018,
                    duration: 0.48,
                }, 0.3)
                .to(stream, {
                    autoAlpha: 1,
                    x: (index) => (compact ? [0, -86, 86][index] : [0, -196, 196][index]),
                    y: (index) => (compact ? [-70, 4, 20][index] : [-118, 2, 26][index]),
                    scale: (index) => (index === 0 ? 1.08 : 0.96),
                    rotate: (index) => [-2, 8, -10][index],
                    rotateY: (index) => [0, -12, 12][index],
                    filter: 'blur(0px)',
                    stagger: 0.06,
                    duration: 0.62,
                }, 0.34)
                .to(copy, { autoAlpha: 1, y: 0, scale: 1, filter: 'blur(0px)', duration: 0.3 }, 0.04)
                .to(copy, { autoAlpha: compact ? 0.54 : 0.68, y: -30, filter: 'blur(3px)', duration: 0.3 }, 0.56)
                .to(rail, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.42 }, 0.52)
                .to(stream, {
                    y: (index) => (compact ? [-98, -8, -12][index] : [-154, -18, -22][index]),
                    rotate: (index) => [-7, 14, -16][index],
                    scale: (index) => (index === 0 ? 1.2 : 1.06),
                    stagger: 0.025,
                    duration: 0.48,
                }, 0.68)
                .to(glow, { autoAlpha: 0.56, scale: 1.18, duration: 0.34 }, 0.72)
                .to(box, { y: compact ? 72 : 34, scale: compact ? 1.16 : 1.28, rotate: 5, filter: 'blur(1px)', duration: 0.42 }, 0.76)
                .to(foil, { autoAlpha: 0.16, y: '+=34', filter: 'blur(4px)', duration: 0.28 }, 0.78);
        }

        if (reveal) {
            const pin = reveal.querySelector('.colt-mystery-reveal__pin');
            const bg = reveal.querySelector('[data-mystery-reveal-bg]');
            const copy = reveal.querySelector('[data-mystery-reveal-copy]');
            const items = reveal.querySelectorAll('[data-mystery-item]');
            const burst = reveal.querySelectorAll('.colt-mystery-reveal__burst span');
            const rip = reveal.querySelectorAll('[data-mystery-rip] span');
            const showcase = reveal.querySelectorAll('[data-mystery-showcase] span');
            const compact = isCompactViewport();

            gsap.set(copy, { autoAlpha: 0, y: 34, scale: 0.985, filter: 'blur(8px)', force3D: true });
            gsap.set(items, { autoAlpha: 0, y: 48, scale: 0.94, rotate: 3, filter: 'blur(8px)', force3D: true });
            gsap.set(burst, { autoAlpha: 0, scale: 0.48, rotate: -22, force3D: true });
            gsap.set(rip, { autoAlpha: 0, x: 0, y: 0, scaleX: 0.2, rotate: 0, filter: 'blur(4px)', force3D: true });
            gsap.set(showcase, { autoAlpha: 0, x: 0, y: compact ? 80 : 120, z: -160, scale: 0.64, rotate: 0, rotateY: 0, filter: 'blur(6px)', force3D: true });

            gsap.timeline({
                defaults: { ease: 'power2.inOut' },
                scrollTrigger: {
                    trigger: reveal,
                    start: 'top top',
                    end: 'bottom bottom',
                    scrub: 1.22,
                    pin,
                    anticipatePin: 1,
                },
            })
                .to(bg, { scale: 1.14, xPercent: compact ? 1 : 3, duration: 1.05 }, 0)
                .to(burst, { autoAlpha: 0.86, scale: 1.28, rotate: 44, stagger: 0.07, duration: 0.66 }, 0.04)
                .to(rip, {
                    autoAlpha: 0.82,
                    x: (index) => (compact ? [-128, -54, 0, 58, 126][index] : [-270, -116, 0, 118, 270][index]),
                    y: (index) => (compact ? [-38, -16, 8, -10, 36][index] : [-68, -28, 14, -18, 66][index]),
                    rotate: (index) => [-15, 10, -4, 8, -12][index],
                    scaleX: 1,
                    filter: 'blur(0px)',
                    stagger: 0.035,
                    duration: 0.42,
                }, 0.12)
                .to(showcase, {
                    autoAlpha: 1,
                    x: (index) => (compact ? [0, -86, 86][index] : [0, -210, 210][index]),
                    y: (index) => (compact ? [-30, 28, 26][index] : [-66, 24, 22][index]),
                    z: 0,
                    scale: (index) => (index === 0 ? 1.08 : 0.98),
                    rotate: (index) => [-4, 10, -10][index],
                    rotateY: (index) => [0, -16, 16][index],
                    filter: 'blur(0px)',
                    stagger: 0.075,
                    duration: 0.56,
                }, 0.18)
                .to(copy, { autoAlpha: 1, y: 0, scale: 1, filter: 'blur(0px)', duration: 0.3 }, 0.12)
                .to(items, { autoAlpha: 1, y: 0, scale: 1, rotate: 0, filter: 'blur(0px)', stagger: 0.09, duration: 0.52 }, 0.32)
                .to(showcase, {
                    y: (index) => (compact ? [-52, 8, 10][index] : [-94, -10, -8][index]),
                    rotate: (index) => [-8, 16, -16][index],
                    scale: (index) => (index === 0 ? 1.16 : 1.05),
                    stagger: 0.025,
                    duration: 0.56,
                }, 0.62)
                .to(items, { y: (index) => (index === 1 ? -24 : 18), rotate: (index) => (index - 1) * -4, stagger: 0.025, duration: 0.56 }, 0.62)
                .to(rip, { autoAlpha: 0.18, y: '+=28', filter: 'blur(3px)', duration: 0.28 }, 0.72)
                .to(copy, { autoAlpha: compact ? 0.5 : 0.66, y: -24, filter: 'blur(3px)', duration: 0.28 }, 0.74);
        }

        if (options) {
            const copy = options.querySelector('[data-mystery-options-copy]');
            const picker = options.querySelector('[data-mystery-picker]');
            const summary = options.querySelector('[data-mystery-summary]');
            gsap.set([copy, picker, summary], { autoAlpha: 0, y: 34, filter: 'blur(12px)' });

            gsap.timeline({
                defaults: { ease: 'power2.out' },
                scrollTrigger: {
                    trigger: options,
                    start: 'top 72%',
                    end: 'center 48%',
                    scrub: 0.58,
                },
            })
                .to(copy, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.36 }, 0)
                .to(picker, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.36 }, 0.12)
                .to(summary, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.36 }, 0.22);
        }

        if (buy) {
            const brand = buy.querySelector('[data-mystery-buy-brand]');
            const panel = buy.querySelector('[data-mystery-buy-panel]');
            gsap.set([brand, panel], { autoAlpha: 0, y: 36, filter: 'blur(12px)' });

            gsap.timeline({
                defaults: { ease: 'power2.out' },
                scrollTrigger: {
                    trigger: buy,
                    start: 'top 76%',
                    end: 'center 52%',
                    scrub: 0.58,
                },
            })
                .to(brand, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.42 }, 0)
                .to(panel, { autoAlpha: 1, y: 0, filter: 'blur(0px)', duration: 0.42 }, 0.12);
        }
    }

    function setupServiceExperience(root, prefersReduced) {
        const gsap = window.gsap;
        const ScrollTrigger = window.ScrollTrigger;
        const page = root.matches('[data-service-page]') ? root : root.querySelector('[data-service-page]');
        if (!page) return;

        const revealItems = page.querySelectorAll('[data-service-reveal]');
        const hero = page.querySelector('[data-service-hero]');
        const scene = page.querySelector('[data-service-scene]');
        const heroCopy = page.querySelector('[data-service-hero-copy]');
        const guardian = page.querySelector('[data-service-guardian]');
        const cards = page.querySelectorAll('[data-service-cards] span');
        const apparatus = page.querySelectorAll('[data-service-apparatus] span');
        const metrics = page.querySelector('[data-service-metrics]');
        const motion = page.dataset.serviceMotion || 'cascade';

        if (prefersReduced || !gsap || !ScrollTrigger) {
            [scene, heroCopy, guardian, metrics, ...cards, ...apparatus, ...revealItems].forEach((item) => {
                if (!item) return;
                item.style.opacity = '1';
                item.style.transform = 'none';
                item.style.filter = 'none';
            });
            return;
        }

        gsap.registerPlugin(ScrollTrigger);
        ensureSmoothScroll(gsap, ScrollTrigger);

        const compact = isCompactViewport();
        const cardMotion = {
            cascade: { y: [-24, 22, -10, 34, -30, 18], rotateY: [-14, 10, 14, -8, 8, -14], drift: 34 },
            orbit: { y: [-42, 12, -28, 20, -34, 8], rotateY: [24, -22, 18, -18, 14, -14], drift: 42 },
            target: { y: [-8, -8, -8, 20, 20, 20], rotateY: [0, 0, 0, -10, 10, -10], drift: 22 },
            map: { y: [-16, 28, 4, 36, -20, 18], rotateY: [-8, 14, -12, 10, -14, 8], drift: 46 },
            scan: { y: [-6, 6, -6, 6, -6, 6], rotateY: [4, -4, 4, -4, 4, -4], drift: 18 },
            spotlight: { y: [-54, 10, -38, 26, -20, 12], rotateY: [-18, 16, -12, 20, -14, 10], drift: 58 },
            strategy: { y: [-12, 18, -22, 16, -8, 20], rotateY: [10, -16, 14, -12, 8, -10], drift: 28 },
        }[motion] || { y: [-18, 24, -8, 34, -28, 16], rotateY: [-12, 10, 14, -8, 8, -14], drift: 34 };

        gsap.set(scene, { autoAlpha: 0.95, scale: compact ? 1.08 : 1.04, xPercent: compact ? 0 : -1, filter: 'saturate(1.02) contrast(1.02)' });
        gsap.set(heroCopy, { autoAlpha: 0, y: 38, scale: 0.985, filter: 'blur(10px)' });
        gsap.set(guardian, { autoAlpha: 0, y: compact ? 38 : 70, scale: 0.92, filter: 'blur(12px)' });
        gsap.set(cards, { autoAlpha: 0, y: 80, z: -120, rotateY: 18, filter: 'blur(12px)' });
        gsap.set(apparatus, { autoAlpha: 0, x: compact ? 0 : -34, y: compact ? 24 : 0, filter: 'blur(8px)' });
        gsap.set(metrics, { autoAlpha: 0, x: compact ? 0 : -44, y: compact ? 24 : 0, filter: 'blur(8px)' });

        if (hero) {
            gsap.timeline({
                defaults: { ease: 'power2.out' },
                scrollTrigger: {
                    trigger: hero,
                    start: 'top 72%',
                    end: 'bottom 42%',
                    scrub: 0.75,
                },
            })
                .to(scene, { autoAlpha: 1, scale: compact ? 1.02 : 1, xPercent: 0, filter: 'saturate(1.08) contrast(1.04)', duration: 0.6 }, 0)
                .to(heroCopy, { autoAlpha: 1, y: 0, scale: 1, filter: 'blur(0px)', duration: 0.46 }, 0)
                .to(guardian, { autoAlpha: 1, y: 0, scale: 1, filter: 'blur(0px)', duration: 0.58 }, 0.08)
                .to(cards, {
                    autoAlpha: (index) => (index % 2 ? 0.72 : 0.95),
                    y: (index) => cardMotion.y[index] || 0,
                    z: 0,
                    rotateY: (index) => cardMotion.rotateY[index] || 0,
                    filter: 'blur(0px)',
                    stagger: 0.045,
                    duration: 0.58,
                }, 0.12)
                .to(apparatus, { autoAlpha: 1, x: 0, y: 0, filter: 'blur(0px)', stagger: 0.045, duration: 0.42 }, 0.16)
                .to(metrics, { autoAlpha: 1, x: 0, y: 0, filter: 'blur(0px)', duration: 0.42 }, 0.18);

            gsap.to(scene, {
                scale: compact ? 1.14 : 1.1,
                xPercent: motion === 'target' || motion === 'map' ? 2 : -2,
                yPercent: motion === 'scan' ? -2 : 0,
                ease: 'none',
                scrollTrigger: {
                    trigger: hero,
                    start: 'top top',
                    end: 'bottom top',
                    scrub: 1.15,
                },
            });

            gsap.to(cards, {
                y: (index) => (index % 2 ? `-=${cardMotion.drift}` : `+=${Math.round(cardMotion.drift * 0.82)}`),
                rotate: (index) => (index % 2 ? '+=10' : '-=8'),
                ease: 'sine.inOut',
                scrollTrigger: {
                    trigger: hero,
                    start: 'top top',
                    end: 'bottom top',
                    scrub: 1.05,
                },
            });

            gsap.to(apparatus, {
                y: (index) => (index % 2 ? '-=22' : '+=18'),
                x: motion === 'map' ? (index) => [24, -18, 12][index] || 0 : 0,
                rotate: motion === 'orbit' || motion === 'spotlight' ? (index) => (index % 2 ? 4 : -4) : 0,
                ease: 'sine.inOut',
                scrollTrigger: {
                    trigger: hero,
                    start: 'top top',
                    end: 'bottom top',
                    scrub: 0.9,
                },
            });
        }

        revealItems.forEach((item, index) => {
            gsap.to(item, {
                autoAlpha: 1,
                y: 0,
                scale: 1,
                filter: 'blur(0px)',
                duration: 0.58,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: item,
                    start: 'top 82%',
                    end: 'top 58%',
                    scrub: 0.42,
                },
                delay: (index % 4) * 0.02,
            });
        });
    }

    function setupAstrategoTower(root, prefersReduced) {
        const tower = root.matches('[data-astratego-tower]') ? root : root.querySelector('[data-astratego-tower]');
        if (!tower) return;

        const gsap = window.gsap;
        const ScrollTrigger = window.ScrollTrigger;
        const floors = Array.from(tower.querySelectorAll('[data-astratego-floor]'));
        const compact = isCompactViewport();

        tower.querySelectorAll('a[href^="#astratego-floor-"]').forEach((link) => {
            link.addEventListener('click', (event) => {
                const target = tower.querySelector(link.getAttribute('href'));
                if (!target) return;

                event.preventDefault();
                target.scrollIntoView({ behavior: prefersReduced ? 'auto' : 'smooth', block: 'start' });
            });
        });

        floors.forEach((floor) => {
            const stations = Array.from(floor.querySelectorAll('[data-astratego-station]'));
            const workspace = floor.querySelector('[data-floor-workspace]');
            const screen = floor.querySelector('[data-floor-screen]');
            const focus = floor.querySelector('[data-floor-focus]');
            const heroBot = floor.querySelector('.stratego-bot--hero');
            const label = floor.querySelector('[data-floor-station-label]');
            const title = floor.querySelector('[data-floor-station-title]');
            const text = floor.querySelector('[data-floor-station-text]');
            const taskTitle = floor.querySelector('[data-floor-task-title]');
            const taskText = floor.querySelector('[data-floor-task-text]');

            const activateStation = (button, animate = true) => {
                if (!button) return;

                const index = Math.max(0, stations.indexOf(button));
                stations.forEach((station) => {
                    station.classList.toggle('is-active', animate && station === button);
                });

                floor.style.setProperty('--active-station', index);
                floor.dataset.activeStation = String(index);

                if (animate) {
                    floor.classList.add('is-station-focused');
                } else {
                    floor.classList.remove('is-station-focused');
                }

                if (workspace) {
                    workspace.dataset.focusStation = String(index);
                }

                if (label) label.textContent = button.dataset.stationLabel || '';
                if (title) title.textContent = button.dataset.stationTitle || '';
                if (text) text.textContent = button.dataset.stationText || '';
                if (taskTitle) taskTitle.textContent = button.dataset.stationStat || '';
                if (taskText) taskText.textContent = button.dataset.stationTask || '';

                if (!animate || prefersReduced || !gsap) return;

                const bot = button.querySelector('.astratego-bot');
                gsap.killTweensOf([screen, focus, bot, heroBot]);
                gsap.timeline({ defaults: { ease: 'power2.out' } })
                    .fromTo([screen, focus], {
                        autoAlpha: 0.62,
                        y: 18,
                        scale: 0.985,
                        filter: 'blur(10px)',
                    }, {
                        autoAlpha: 1,
                        y: 0,
                        scale: 1,
                        filter: 'blur(0px)',
                        duration: 0.42,
                        stagger: 0.045,
                    }, 0)
                    .fromTo(bot, {
                        y: 16,
                        scale: 0.92,
                        rotateY: -20,
                    }, {
                        y: 0,
                        scale: 1.12,
                        rotateY: 0,
                        duration: 0.5,
                    }, 0.04)
                    .fromTo(heroBot, {
                        y: 20,
                        scale: 0.88,
                        rotateY: 24,
                    }, {
                        y: 0,
                        scale: 1,
                        rotateY: 0,
                        duration: 0.54,
                    }, 0.08);
            };

            stations.forEach((button) => {
                button.addEventListener('click', () => activateStation(button));
            });
            activateStation(stations[0], false);

            if (!prefersReduced && gsap) {
                stations.forEach((button, index) => {
                    const stationStyle = window.getComputedStyle(button);
                    const walkX = parseFloat(stationStyle.getPropertyValue('--walk-x')) || 24;
                    const walkY = parseFloat(stationStyle.getPropertyValue('--walk-y')) || 12;
                    const travelDuration = gsap.utils.random(5.2, 9.4);
                    gsap.to(button, {
                        '--worker-x': () => `${gsap.utils.random(-walkX, walkX, 1)}px`,
                        '--worker-y': () => `${gsap.utils.random(-walkY, walkY, 1)}px`,
                        duration: travelDuration,
                        delay: index * 0.18,
                        repeat: -1,
                        yoyo: true,
                        repeatRefresh: true,
                        ease: 'sine.inOut',
                    });
                });
            }
        });

        if (prefersReduced || !gsap || !ScrollTrigger) {
            floors.forEach((floor) => floor.classList.add('is-active-floor'));
            return;
        }

        gsap.registerPlugin(ScrollTrigger);
        ensureSmoothScroll(gsap, ScrollTrigger);

        const intro = tower.querySelector('.astratego-tower__intro');
        if (intro) {
            gsap.fromTo(intro.children, {
                autoAlpha: 0,
                y: 26,
                filter: 'blur(10px)',
            }, {
                autoAlpha: 1,
                y: 0,
                filter: 'blur(0px)',
                stagger: 0.08,
                duration: 0.7,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: intro,
                    start: 'top 78%',
                    end: 'center 48%',
                    scrub: 0.5,
                },
            });
        }

        const floorLinks = Array.from(tower.querySelectorAll('a[href^="#astratego-floor-"]'));
        const setCurrentFloor = (activeIndex) => {
            floors.forEach((floor, floorIndex) => {
                floor.classList.toggle('is-current-floor', floorIndex === activeIndex);
            });

            floorLinks.forEach((link) => {
                const href = link.getAttribute('href') || '';
                link.classList.toggle('is-current', floors[activeIndex] && href === `#${floors[activeIndex].id}`);
            });
        };

        setCurrentFloor(0);

        floors.forEach((floor, index) => {
            const shell = floor.querySelector('.astratego-floor__shell');
            const workspace = floor.querySelector('.astratego-floor__workspace');
            const depth = floor.querySelector('[data-floor-stage]');
            const screen = floor.querySelector('[data-floor-screen]');
            const stations = floor.querySelectorAll('[data-astratego-station]');
            const focus = floor.querySelector('[data-floor-focus]');
            const metric = floor.querySelector('.astratego-floor__metric');
            const decor = floor.querySelectorAll('.astratego-floor__decor span');
            const ceiling = floor.querySelectorAll('.astratego-floor__ceiling span');

            if (!shell || !workspace) return;

            gsap.set(workspace, {
                autoAlpha: index === 0 ? 1 : 0.18,
                y: compact ? 30 : 70,
                scale: compact ? 1.02 : 1.06,
                filter: 'blur(14px) saturate(.76) brightness(.7)',
            });
            gsap.set(depth, {
                rotationX: compact ? 0 : 3,
                y: compact ? 0 : 18,
                transformOrigin: '50% 70%',
            });
            gsap.set([screen, focus, metric], { autoAlpha: 0, y: 28, filter: 'blur(12px)' });
            gsap.set(stations, { autoAlpha: 0, filter: 'blur(10px)' });
            gsap.set(decor, { autoAlpha: 0, y: 26, filter: 'blur(8px)' });
            gsap.set(ceiling, { scaleX: 0.25, transformOrigin: '50% 50%' });

            gsap.timeline({
                defaults: { ease: 'none' },
                scrollTrigger: {
                    trigger: floor,
                    start: 'top top',
                    end: () => `+=${Math.max(window.innerHeight * (compact ? 1.08 : 1.26), 760)}`,
                    pin: shell,
                    pinSpacing: true,
                    scrub: compact ? 0.66 : 0.84,
                    anticipatePin: 1,
                    invalidateOnRefresh: true,
                    snap: compact ? false : {
                        snapTo: [0, 0.5, 1],
                        duration: { min: 0.16, max: 0.38 },
                        delay: 0.03,
                        ease: 'power1.inOut',
                    },
                    onEnter: () => setCurrentFloor(index),
                    onEnterBack: () => setCurrentFloor(index),
                },
            })
                .to(workspace, {
                    autoAlpha: 1,
                    y: 0,
                    scale: 1,
                    filter: 'blur(0px) saturate(1.04) brightness(1)',
                    duration: 0.24,
                    ease: 'power2.out',
                }, 0)
                .to(depth, { rotationX: 0, y: 0, duration: 0.24, ease: 'power2.out' }, 0)
                .to(ceiling, { scaleX: 1, stagger: 0.035, duration: 0.2, ease: 'power2.out' }, 0.04)
                .to(stations, {
                    autoAlpha: 1,
                    filter: 'blur(0px)',
                    stagger: { each: 0.035, from: 'random' },
                    duration: 0.24,
                    ease: 'power2.out',
                }, 0.1)
                .to(workspace, {
                    y: compact ? -10 : -24,
                    scale: compact ? 1.012 : 1.026,
                    filter: 'blur(0px) saturate(1.08) brightness(1.04)',
                    duration: 0.42,
                }, 0.34)
                .to(stations, {
                    '--worker-scroll-y': (stationIndex) => (stationIndex % 2 === 0 ? '-16px' : '-30px'),
                    duration: 0.42,
                }, 0.34)
                .to([screen, focus], { autoAlpha: 0, filter: 'blur(14px)', duration: 0.16 }, 0.78)
                .to(stations, { autoAlpha: 0.2, filter: 'blur(8px)', duration: 0.16 }, 0.84)
                .to(workspace, {
                    autoAlpha: 0.16,
                    y: compact ? -36 : -74,
                    scale: compact ? 0.985 : 0.94,
                    filter: 'blur(16px) saturate(.72) brightness(.62)',
                    duration: 0.18,
                }, 0.82);
        });
    }

    function setupMysteryPicker(root) {
        const picker = root.querySelector('[data-mystery-picker]');
        if (!picker) return;

        const state = {
            world: 'pokemon',
            language: 'english',
        };
        const labels = {
            pokemon: 'Pokemon',
            'one-piece': 'One Piece',
            english: 'English',
            japanese: 'Japanese',
        };
        const checkout = root.querySelector('[data-mystery-checkout]');
        const worldOutput = root.querySelector('[data-mystery-summary-world]');
        const languageOutput = root.querySelector('[data-mystery-summary-language]');
        const baseUrl = picker.dataset.productUrl || (checkout ? checkout.href : window.location.href);

        const update = () => {
            root.dataset.mysteryWorld = state.world;
            root.dataset.mysteryLanguage = state.language;
            if (worldOutput) worldOutput.textContent = labels[state.world] || state.world;
            if (languageOutput) languageOutput.textContent = labels[state.language] || state.language;
            if (!checkout) return;

            try {
                const url = new URL(baseUrl, window.location.href);
                url.searchParams.set('mystery_world', state.world);
                url.searchParams.set('mystery_language', state.language);
                checkout.href = url.href;
            } catch (error) {
                checkout.href = baseUrl;
            }
        };

        picker.querySelectorAll('[data-mystery-choice]').forEach((button) => {
            button.addEventListener('click', () => {
                const group = button.dataset.mysteryChoice;
                const value = button.dataset.value;
                if (!group || !value) return;

                state[group] = value;
                picker.querySelectorAll(`[data-mystery-choice="${group}"]`).forEach((item) => {
                    item.classList.toggle('is-active', item === button);
                });
                update();
            });
        });

        update();
    }
})();
