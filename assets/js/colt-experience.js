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

        if (window.Lenis && !window.__coltLenis) {
            window.__coltLenis = new window.Lenis({ duration: 1.18, smoothWheel: true, wheelMultiplier: 0.82 });
            window.__coltLenis.on('scroll', ScrollTrigger.update);
            gsap.ticker.add((time) => window.__coltLenis.raf(time * 1000));
            gsap.ticker.lagSmoothing(0);
        }

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
                let overlayDelay = 0;

                if (!prefersReduced && orbitWorld && typeof orbitWorld.__coltFocusPlanet === 'function') {
                    const planets = Array.from(orbitWorld.querySelectorAll('[data-orbit-planet]'));
                    const planetIndex = planets.indexOf(orbitPlanet);
                    if (planetIndex >= 0) {
                        orbitWorld.classList.add('is-planet-traveling');
                        planets.forEach((planet) => planet.classList.remove('is-travel-target'));
                        orbitPlanet.classList.add('is-travel-target');
                        travelDelay = Math.max(1120, orbitWorld.__coltFocusPlanet(planetIndex) + 260);
                        overlayDelay = Math.max(520, travelDelay - 540);
                    }
                }

                event.preventDefault();
                root.classList.add('is-traveling');
                document.documentElement.classList.add('colt-is-traveling');
                if (overlayDelay > 0) {
                    window.setTimeout(() => {
                        overlay.classList.add('is-active');
                    }, overlayDelay);
                } else {
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
})();
