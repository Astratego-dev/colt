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

                event.preventDefault();
                overlay.classList.add('is-active');
                root.classList.add('is-traveling');
                document.documentElement.classList.add('colt-is-traveling');
                window.setTimeout(() => {
                    window.location.href = href;
                }, prefersReduced ? 100 : 760);
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
