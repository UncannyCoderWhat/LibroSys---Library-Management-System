// ==========================================
// SECTION 1: Game of Thrones Animation (Optimized)
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const gotHero = document.querySelector('.bd-hero');
    if (!gotHero) return;

    // Check if the current book page is Game of Thrones
    const titleElement = gotHero.querySelector('.bd-title');
    const isGameOfThrones = titleElement && titleElement.textContent.toLowerCase().includes('thrones');

    if (!isGameOfThrones) return;

    // Inject styles specifically for the Game of Thrones hero section
    if (!document.getElementById('got-hero-styles')) {
        const styleTag = document.createElement('style');
        styleTag.id = 'got-hero-styles';
        styleTag.textContent = `
            .bd-hero {
                position: relative;
                overflow: hidden;
                transition: transform 0.15s ease-out, box-shadow 0.4s ease, border-color 0.4s ease;
            }

            .got-dim-bg {
                position: absolute;
                inset: 0;
                background: radial-gradient(circle at center, #1a0800 0%, #080300 60%, #000000 100%);
                opacity: 0;
                pointer-events: none;
                z-index: 1;
                transition: opacity 0.5s ease;
            }

            .got-light-bg {
                position: absolute;
                inset: 0;
                background: radial-gradient(circle at center, #3d1400 0%, #1a0900 60%, #050200 100%);
                opacity: 0;
                pointer-events: none;
                z-index: 2;
                transition: opacity 0.5s ease;
            }

            .bd-hero:hover .got-dim-bg,
            .bd-hero:hover .got-light-bg {
                opacity: 0.85;
            }

            .bd-hero:hover {
                box-shadow: 0 0 35px rgba(255, 100, 0, 0.35), 0 0 50px rgba(139, 0, 0, 0.25);
                border-color: rgba(212, 175, 55, 0.7) !important;
            }

            .bd-hero > *:not(canvas):not(.got-dim-bg):not(.got-light-bg):not(.bd-hero-bg) {
                position: relative;
                z-index: 3;
            }
        `;
        document.head.appendChild(styleTag);
    }

    // Build DOM elements inside the hero section
    let dimBg = gotHero.querySelector('.got-dim-bg') || document.createElement('div');
    if (!dimBg.parentNode) { dimBg.className = 'got-dim-bg'; gotHero.appendChild(dimBg); }

    let lightBg = gotHero.querySelector('.got-light-bg') || document.createElement('div');
    if (!lightBg.parentNode) { lightBg.className = 'got-light-bg'; gotHero.appendChild(lightBg); }

    let canvas = gotHero.querySelector('.got-battlefield-canvas') || document.createElement('canvas');
    if (!canvas.parentNode) {
        canvas.className = 'got-battlefield-canvas';
        canvas.style.cssText = `
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            z-index: 2;
            border-radius: 16px;
        `;
        gotHero.appendChild(canvas);
    }

    const ctx = canvas.getContext('2d', { alpha: true });
    let animationFrameId = null;
    let isHovered = false;
    let isMouseMoving = false;
    let mouseX = -500;
    let mouseY = -500;
    let heroRect = gotHero.getBoundingClientRect();

    const MAX_PARTICLES = 80;
    const particlePool = [];

    class Particle {
        constructor() {
            this.active = false;
        }

        init(x, y, type) {
            const w = heroRect.width;
            const h = heroRect.height;
            this.x = x !== undefined ? x : Math.random() * w;
            this.y = y !== undefined ? y : h + Math.random() * 20;
            this.type = type || (Math.random() > 0.4 ? 'ember' : 'ash');
            this.life = 1;
            this.active = true;

            if (this.type === 'ember') {
                this.size = Math.random() * 2.5 + 1;
                this.vx = (Math.random() - 0.5) * 1.5;
                this.vy = -(Math.random() * 2.5 + 1);
                this.decay = Math.random() * 0.015 + 0.008;
                this.color = Math.random() > 0.3 ? '#ff4500' : '#ffaa00';
            } else if (this.type === 'blood_spark') {
                this.size = Math.random() * 3 + 1.5;
                this.vx = (Math.random() - 0.5) * 4;
                this.vy = (Math.random() - 0.5) * 4;
                this.decay = Math.random() * 0.03 + 0.02;
                this.color = '#8b0000';
            } else {
                this.size = Math.random() * 3 + 1;
                this.vx = (Math.random() - 0.5) * 0.8;
                this.vy = -(Math.random() * 1 + 0.3);
                this.decay = Math.random() * 0.008 + 0.004;
                this.color = '#888888';
            }
        }

        update() {
            if (!this.active) return;
            this.x += this.vx;
            this.y += this.vy;
            this.life -= this.decay;

            if (this.life <= 0 || this.y < -10 || this.x < -10 || this.x > heroRect.width + 10) {
                this.active = false;
            }
        }

        draw() {
            if (!this.active) return;
            ctx.globalAlpha = Math.max(0, this.life);
            ctx.fillStyle = this.color;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    for (let i = 0; i < MAX_PARTICLES; i++) {
        particlePool.push(new Particle());
    }

    function spawnParticle(x, y, type) {
        for (let i = 0; i < MAX_PARTICLES; i++) {
            if (!particlePool[i].active) {
                particlePool[i].init(x, y, type);
                break;
            }
        }
    }

    function resizeCanvas() {
        heroRect = gotHero.getBoundingClientRect();
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = heroRect.width * dpr;
        canvas.height = heroRect.height * dpr;
        ctx.scale(dpr, dpr);
    }
    resizeCanvas();

    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(resizeCanvas, 100);
    }, { passive: true });

    function render() {
        ctx.clearRect(0, 0, heroRect.width, heroRect.height);

        // Draw flashlight spotlight directly on Canvas (60 FPS smooth, hardware-accelerated)
        if (isHovered && mouseX > -100 && mouseY > -100) {
            const gradient = ctx.createRadialGradient(mouseX, mouseY, 0, mouseX, mouseY, 220);
            gradient.addColorStop(0, 'rgba(61, 20, 0, 0.85)');
            gradient.addColorStop(0.6, 'rgba(26, 9, 0, 0.5)');
            gradient.addColorStop(1, 'rgba(5, 2, 0, 0)');

            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, heroRect.width, heroRect.height);
        }

        let activeCount = 0;

        if (isHovered) {
            for (let i = 0; i < MAX_PARTICLES; i++) {
                if (particlePool[i].active) activeCount++;
            }

            if (activeCount < 50) {
                spawnParticle();
            }

            if (isMouseMoving) {
                spawnParticle(mouseX, mouseY, Math.random() > 0.7 ? 'blood_spark' : 'ember');
                isMouseMoving = false;
            }
        }

        activeCount = 0;
        for (let i = 0; i < MAX_PARTICLES; i++) {
            const p = particlePool[i];
            if (p.active) {
                p.update();
                p.draw();
                if (p.active) activeCount++;
            }
        }

        if (isHovered || activeCount > 0) {
            animationFrameId = requestAnimationFrame(render);
        } else {
            animationFrameId = null;
        }
    }

    // 3D Tilt handling moved out of render loop (throttled to mouse movement via RAF)
    let tiltFrame = null;
    function updateTilt() {
        if (!isHovered) return;
        const centerX = heroRect.width / 2;
        const centerY = heroRect.height / 2;
        const rotateX = ((mouseY - centerY) / centerY) * -3;
        const rotateY = ((mouseX - centerX) / centerX) * 3;
        gotHero.style.transform = `perspective(1200px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-2px)`;
        tiltFrame = null;
    }

    gotHero.addEventListener('mouseenter', (e) => {
        isHovered = true;
        heroRect = gotHero.getBoundingClientRect();
        gotHero.style.willChange = 'transform';

        mouseX = e.clientX - heroRect.left;
        mouseY = e.clientY - heroRect.top;

        if (!animationFrameId) {
            animationFrameId = requestAnimationFrame(render);
        }
    }, { passive: true });

    gotHero.addEventListener('mousemove', (e) => {
        mouseX = e.clientX - heroRect.left;
        mouseY = e.clientY - heroRect.top;
        isMouseMoving = true;

        if (!tiltFrame) {
            tiltFrame = requestAnimationFrame(updateTilt);
        }
    }, { passive: true });

    gotHero.addEventListener('mouseleave', () => {
        isHovered = false;
        gotHero.style.willChange = 'auto';
        gotHero.style.transform = 'perspective(1200px) rotateX(0deg) rotateY(0deg) translateY(0px)';

        mouseX = -500;
        mouseY = -500;
    }, { passive: true });
});






// ==========================================
// SECTION 2: Jujutsu Kaisen Animation (Optimized)
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const jjkHero = document.querySelector('.bd-hero');
    if (!jjkHero) return;

    // Check if the current page title contains "jujutsu"
    const titleElement = jjkHero.querySelector('.bd-title');
    const isJujutsuKaisen = titleElement && titleElement.textContent.toLowerCase().includes('jujutsu');

    if (!isJujutsuKaisen) return;

    // Inject styles specifically for the Jujutsu Kaisen hero section
    if (!document.getElementById('jjk-hero-styles')) {
        const styleTag = document.createElement('style');
        styleTag.id = 'jjk-hero-styles';
        styleTag.textContent = `
            .bd-hero {
                position: relative;
                overflow: hidden;
                transition: transform 0.15s ease-out, box-shadow 0.4s ease, border-color 0.4s ease;
            }

            .jjk-dim-bg {
                position: absolute;
                inset: 0;
                background: radial-gradient(circle at center, #1a0208 0%, #08000a 60%, #000000 100%);
                opacity: 0;
                pointer-events: none;
                z-index: 1;
                transition: opacity 0.5s ease;
            }

            .bd-hero:hover .jjk-dim-bg {
                opacity: 0.85;
            }

            .bd-hero:hover {
                box-shadow: 0 0 35px rgba(255, 0, 85, 0.45), 0 0 50px rgba(128, 0, 255, 0.35);
                border-color: rgba(255, 0, 85, 0.7) !important;
            }

            .bd-hero > *:not(canvas):not(.jjk-dim-bg):not(.bd-hero-bg) {
                position: relative;
                z-index: 3;
            }
        `;
        document.head.appendChild(styleTag);
    }

    // Build DOM elements inside the hero container
    let dimBg = jjkHero.querySelector('.jjk-dim-bg') || document.createElement('div');
    if (!dimBg.parentNode) { dimBg.className = 'jjk-dim-bg'; jjkHero.appendChild(dimBg); }

    let canvas = jjkHero.querySelector('.jjk-master-canvas') || document.createElement('canvas');
    if (!canvas.parentNode) {
        canvas.className = 'jjk-master-canvas';
        canvas.style.cssText = `
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            z-index: 5;
            border-radius: 16px;
        `;
        jjkHero.appendChild(canvas);
    }

    const ctx = canvas.getContext('2d', { alpha: true });
    let heroRect = jjkHero.getBoundingClientRect();
    let animId = null;
    let isHovered = false;
    let mouseX = -500;
    let mouseY = -500;
    let targetX = -500;
    let targetY = -500;

    // Flash effect variables handled directly on canvas
    let flashAlpha = 0;
    let flashX = 0;
    let flashY = 0;

    const POOL_SIZE = 90;
    const pool = new Array(POOL_SIZE);

    class CursedParticle {
        constructor() {
            this.active = false;
        }

        spawn(x, y, type) {
            this.active = true;
            this.x = x ?? Math.random() * heroRect.width;
            this.y = y ?? Math.random() * heroRect.height;
            this.type = type;
            this.life = 1;

            if (type === 'spark') {
                this.length = Math.random() * 40 + 20;
                this.angle = Math.random() * Math.PI * 2;
                this.decay = Math.random() * 0.06 + 0.03;
                this.lineWidth = Math.random() * 2.5 + 1;
                const speed = Math.random() * 7 + 2;
                this.vx = Math.cos(this.angle) * speed;
                this.vy = Math.sin(this.angle) * speed;
                this.color = Math.random() > 0.3 ? '#ff0033' : '#110022';
            } else if (type === 'aura') {
                this.radius = Math.random() * 3.5 + 1;
                this.vx = (Math.random() - 0.5) * 1.5;
                this.vy = -(Math.random() * 2.5 + 0.8);
                this.decay = Math.random() * 0.02 + 0.01;
                this.color = Math.random() > 0.4 ? '#8000ff' : '#ff0055';
            } else {
                this.radius = Math.random() * 2 + 0.5;
                this.vx = (Math.random() - 0.5) * 0.5;
                this.vy = (Math.random() - 0.5) * 0.5;
                this.decay = Math.random() * 0.015 + 0.005;
                this.color = '#110022';
            }
        }

        update() {
            if (!this.active) return;
            this.x += this.vx;
            this.y += this.vy;
            
            if (this.type === 'spark') {
                this.vx *= 0.91;
                this.vy *= 0.91;
            }

            this.life -= this.decay;
            if (this.life <= 0 || this.x < -20 || this.x > heroRect.width + 20 || this.y < -20 || this.y > heroRect.height + 20) {
                this.active = false;
            }
        }

        draw() {
            if (!this.active) return;
            ctx.globalAlpha = Math.max(0, this.life);

            if (this.type === 'spark') {
                ctx.strokeStyle = this.color;
                ctx.lineWidth = this.lineWidth;
                ctx.beginPath();
                ctx.moveTo(this.x, this.y);
                const midX = this.x + Math.cos(this.angle) * (this.length * 0.5);
                const midY = this.y + Math.sin(this.angle) * (this.length * 0.5);
                const endX = this.x + Math.cos(this.angle) * this.length;
                const endY = this.y + Math.sin(this.angle) * this.length;
                ctx.lineTo(midX, midY);
                ctx.lineTo(endX, endY);
                ctx.stroke();
            } else {
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                ctx.fill();
            }
        }
    }

    for (let i = 0; i < POOL_SIZE; i++) {
        pool[i] = new CursedParticle();
    }

    function emit(x, y, type) {
        for (let i = 0; i < POOL_SIZE; i++) {
            if (!pool[i].active) {
                pool[i].spawn(x, y, type);
                break;
            }
        }
    }

    function updateSize() {
        heroRect = jjkHero.getBoundingClientRect();
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = heroRect.width * dpr;
        canvas.height = heroRect.height * dpr;
        ctx.scale(dpr, dpr);
    }
    updateSize();

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(updateSize, 100);
    }, { passive: true });

    function loop() {
        ctx.clearRect(0, 0, heroRect.width, heroRect.height);

        // Draw radial light spotlight on Canvas instead of using CSS mask
        if (isHovered && targetX > -100 && targetY > -100) {
            ctx.globalAlpha = 1;
            const gradient = ctx.createRadialGradient(targetX, targetY, 0, targetX, targetY, 220);
            gradient.addColorStop(0, 'rgba(61, 0, 22, 0.85)');
            gradient.addColorStop(0.6, 'rgba(28, 0, 43, 0.5)');
            gradient.addColorStop(1, 'rgba(5, 0, 13, 0)');

            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, heroRect.width, heroRect.height);
        }

        // Draw Black Flash effect on Canvas directly if active
        if (flashAlpha > 0) {
            ctx.globalAlpha = flashAlpha;
            const flashGrad = ctx.createRadialGradient(flashX, flashY, 0, flashX, flashY, heroRect.width);
            flashGrad.addColorStop(0, 'rgba(255, 0, 51, 0.95)');
            flashGrad.addColorStop(0.55, 'rgba(0, 0, 0, 0.9)');
            flashGrad.addColorStop(0.8, 'rgba(0, 0, 0, 0)');

            ctx.fillStyle = flashGrad;
            ctx.fillRect(0, 0, heroRect.width, heroRect.height);
            flashAlpha -= 0.08; 
        }

        if (isHovered) {
            if (Math.random() < 0.3) emit(Math.random() * heroRect.width, heroRect.height + 5, 'aura');
            if (Math.random() < 0.15) emit(Math.random() * heroRect.width, Math.random() * heroRect.height, 'domain');

            mouseX += (targetX - mouseX) * 0.2;
            mouseY += (targetY - mouseY) * 0.2;
            emit(mouseX, mouseY, Math.random() > 0.6 ? 'spark' : 'aura');
        }

        let activeCount = 0;
        for (let i = 0; i < POOL_SIZE; i++) {
            if (pool[i].active) {
                pool[i].update();
                pool[i].draw();
                activeCount++;
            }
        }

        if (isHovered || activeCount > 0 || flashAlpha > 0) {
            animId = requestAnimationFrame(loop);
        } else {
            animId = null;
        }
    }

    let tiltFrame = null;
    function updateTilt() {
        if (!isHovered) return;
        const cx = heroRect.width / 2;
        const cy = heroRect.height / 2;
        const rx = ((targetY - cy) / cy) * -3;
        const ry = ((targetX - cx) / cx) * 3;
        jjkHero.style.transform = `perspective(1200px) rotateX(${rx}deg) rotateY(${ry}deg) translateY(-2px)`;
        tiltFrame = null;
    }

    function triggerBlackFlash(e) {
        jjkHero.animate([
            { transform: 'scale(1) translate(0, 0)' },
            { transform: 'scale(1.01) translate(-4px, 2px)' },
            { transform: 'scale(0.995) translate(4px, -2px)' },
            { transform: 'scale(1.005) translate(-2px, -1px)' },
            { transform: 'scale(1) translate(0, 0)' }
        ], { duration: 160, iterations: 1 });

        flashX = e.clientX - heroRect.left;
        flashY = e.clientY - heroRect.top;
        flashAlpha = 1.0;

        for (let i = 0; i < 20; i++) {
            emit(flashX, flashY, 'spark');
        }
    }

    jjkHero.addEventListener('mouseenter', (e) => {
        isHovered = true;
        heroRect = jjkHero.getBoundingClientRect();
        jjkHero.style.willChange = 'transform';
        targetX = e.clientX - heroRect.left;
        targetY = e.clientY - heroRect.top;
        mouseX = targetX;
        mouseY = targetY;

        triggerBlackFlash(e);

        if (!animId) animId = requestAnimationFrame(loop);
    }, { passive: true });

    jjkHero.addEventListener('mousemove', (e) => {
        targetX = e.clientX - heroRect.left;
        targetY = e.clientY - heroRect.top;

        if (!tiltFrame) {
            tiltFrame = requestAnimationFrame(updateTilt);
        }
    }, { passive: true });

    jjkHero.addEventListener('mouseleave', () => {
        isHovered = false;
        jjkHero.style.willChange = 'auto';
        jjkHero.style.transform = 'perspective(1200px) rotateX(0deg) rotateY(0deg) translateY(0px)';
        
        targetX = -500;
        targetY = -500;
    }, { passive: true });
});





// ==========================================
// SECTION 3: Sherlock Holmes Animation
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const sherlockHero = document.querySelector('.bd-hero[data-title*="sherlock" i]') ||
    Array.from(document.querySelectorAll('.bd-hero')).find(el => {
        const title = el.getAttribute('data-title')?.toLowerCase() || el.querySelector('.bd-title')?.textContent.toLowerCase() || '';
        return title.includes('sherlock');
    });

    if (!sherlockHero) return;

    if (!document.getElementById('sherlock-hero-spotlight-styles')) {
        const styleTag = document.createElement('style');
        styleTag.id = 'sherlock-hero-spotlight-styles';
        styleTag.textContent = `
            .bd-hero {
                position: relative;
                overflow: hidden;
                transition: transform 0.4s ease, box-shadow 0.4s ease, border-color 0.4s ease;
            }

            /* Base Dim Dark Foggy London Background */
            .sherlock-dim-bg {
                position: absolute;
                inset: 0;
                background: radial-gradient(circle at center, #0b0d0f 0%, #030405 100%);
                opacity: 0;
                pointer-events: none;
                z-index: 1;
                transition: opacity 0.5s ease;
            }

            /* Revealed Map & Clues Background Layer (Muted Dark Antique Parchment) */
            .sherlock-light-bg {
                position: absolute;
                inset: 0;
                background-color: #17130a;
                background-image: 
                    radial-gradient(circle at center, rgba(168, 140, 76, 0.75) 0%, rgba(94, 78, 41, 0.85) 60%, rgba(23, 19, 10, 0.95) 100%),
                    repeating-linear-gradient(45deg, rgba(0, 0, 0, 0.25) 0, rgba(0, 0, 0, 0.25) 1px, transparent 0, transparent 20px);
                opacity: 0;
                pointer-events: none;
                z-index: 2;
                transition: opacity 0.5s ease;
                /* Magnifying Lens Spotlight Mask */
                -webkit-mask-image: radial-gradient(circle 140px at var(--mouse-x, -500px) var(--mouse-y, -500px), rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0.8) 50%, rgba(0, 0, 0, 0) 100%);
                mask-image: radial-gradient(circle 140px at var(--mouse-x, -500px) var(--mouse-y, -500px), rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0.8) 50%, rgba(0, 0, 0, 0) 100%);
            }

            /* Hover states */
            .bd-hero:hover .sherlock-dim-bg,
            .bd-hero:hover .sherlock-light-bg {
                opacity: 1;
            }

            .bd-hero:hover {
                box-shadow: 0 0 30px rgba(180, 140, 45, 0.3);
                border-color: rgba(180, 140, 45, 0.6) !important;
            }

            /* Keep text & content readable over dark layers */
            .bd-hero > *:not(canvas):not(.sherlock-dim-bg):not(.sherlock-light-bg):not(.sherlock-lens) {
                position: relative;
                z-index: 3;
            }
        `;
        document.head.appendChild(styleTag);
    }

    // Add background layers
    const dimBg = document.createElement('div');
    dimBg.className = 'sherlock-dim-bg';
    sherlockHero.appendChild(dimBg);

    const lightBg = document.createElement('div');
    lightBg.className = 'sherlock-light-bg';
    sherlockHero.appendChild(lightBg);

    // Dynamic Canvas overlay for footprint particles
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    canvas.className = 'sherlock-canvas';
    canvas.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 10;
        border-radius: inherit;
    `;
    sherlockHero.appendChild(canvas);

    let width = (canvas.width = sherlockHero.offsetWidth);
    let height = (canvas.height = sherlockHero.offsetHeight);

    const resizeObserver = new ResizeObserver(() => {
        width = canvas.width = sherlockHero.offsetWidth;
        height = canvas.height = sherlockHero.offsetHeight;
    });
    resizeObserver.observe(sherlockHero);

    // Boot print SVG paths
    const leftFootPath = new Path2D('M 4,22 C 1,20 0,16 0,12 C 0,7 2,2 5,0 C 7,-1 11,1 11,5 C 11,9 10,13 8,17 C 7,19 6,22 4,22 Z M 3,28 C 1,27 1,25 2,23 C 4,23 7,24 8,25 C 8,27 6,28 3,28 Z');
    const rightFootPath = new Path2D('M 7,22 C 10,20 11,16 11,12 C 11,7 9,2 6,0 C 4,-1 0,1 0,5 C 0,9 1,13 3,17 C 4,19 5,22 7,22 Z M 8,28 C 10,27 10,25 9,23 C 7,23 4,24 3,25 C 3,27 5,28 8,28 Z');

    const particles = [];
    let isLeftFoot = true;
    let lastX = 0;
    let lastY = 0;
    let animId = null;

    // Brass Magnifying Glass Lens Cursor
    const lens = document.createElement('div');
    lens.className = 'sherlock-lens';
    lens.style.cssText = `
        position: absolute;
        width: 85px;
        height: 85px;
        border: 3px solid #a88c4c;
        border-radius: 50%;
        box-shadow: 0 0 20px rgba(168, 140, 76, 0.4), inset 0 0 15px rgba(255, 255, 255, 0.15);
        background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, rgba(168, 140, 76, 0.05) 70%, transparent 100%);
        pointer-events: none;
        z-index: 12;
        opacity: 0;
        transform: translate(-50%, -50%);
        transition: opacity 0.2s ease;
    `;

    // Handle rod for the magnifying glass
    const handle = document.createElement('div');
    handle.style.cssText = `
        position: absolute;
        bottom: -22px;
        right: -10px;
        width: 8px;
        height: 28px;
        background: linear-gradient(90deg, #180e08, #2a1b12, #0d0603);
        border-radius: 3px;
        border: 1px solid #a88c4c;
        transform: rotate(-45deg);
        transform-origin: top center;
    `;
    lens.appendChild(handle);
    sherlockHero.appendChild(lens);

    function addFootprint(x, y, angle) {
        particles.push({
            x,
            y,
            angle,
            isLeft: isLeftFoot,
            life: 1.0,
            decay: 0.012
        });
        isLeftFoot = !isLeftFoot;
    }

    function render() {
        ctx.clearRect(0, 0, width, height);

        for (let i = particles.length - 1; i >= 0; i--) {
            const p = particles[i];
            p.life -= p.decay;

            if (p.life <= 0) {
                particles.splice(i, 1);
                continue;
            }

            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.rotate(p.angle + Math.PI / 2);
            ctx.scale(0.85, 0.85);

            ctx.fillStyle = `rgba(5, 3, 2, ${p.life * 0.95})`;
            ctx.shadowColor = 'rgba(168, 140, 76, 0.25)';
            ctx.shadowBlur = 6;

            ctx.fill(p.isLeft ? leftFootPath : rightFootPath);
            ctx.restore();
        }

        if (particles.length > 0) {
            animId = requestAnimationFrame(render);
        } else {
            animId = null;
        }
    }

    sherlockHero.addEventListener('mousemove', (e) => {
        const rect = sherlockHero.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        // Move spotlight & lens position
        lightBg.style.setProperty('--mouse-x', `${x}px`);
        lightBg.style.setProperty('--mouse-y', `${y}px`);

        lens.style.left = `${x}px`;
        lens.style.top = `${y}px`;
        lens.style.opacity = '1';

        if (lastX === 0 && lastY === 0) {
            lastX = x;
            lastY = y;
            return;
        }

        const dist = Math.hypot(x - lastX, y - lastY);

        if (dist > 28) {
            const angle = Math.atan2(y - lastY, x - lastX);
            addFootprint(x, y, angle);

            lastX = x;
            lastY = y;

            if (!animId) {
                animId = requestAnimationFrame(render);
            }
        }
    });

    sherlockHero.addEventListener('mouseleave', () => {
        lens.style.opacity = '0';
        lightBg.style.setProperty('--mouse-x', `-500px`);
        lightBg.style.setProperty('--mouse-y', `-500px`);
        lastX = 0;
        lastY = 0;
    });
});





// ==========================================
// Harry Potter BD-Hero Animation (Dark Theme)
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const hpHero = document.querySelector('.bd-hero[data-title*="harry potter" i], .bd-hero[data-title*="cursed child" i]') ||
    Array.from(document.querySelectorAll('.bd-hero')).find(el => {
        const title = el.getAttribute('data-title')?.toLowerCase() || el.querySelector('.bd-title')?.textContent.toLowerCase() || '';
        return title.includes('harry potter') || title.includes('cursed child');
    });

    if (!hpHero) return;

    if (!document.getElementById('hp-hero-spotlight-styles')) {
        const styleTag = document.createElement('style');
        styleTag.id = 'hp-hero-spotlight-styles';
        styleTag.textContent = `
            .bd-hero {
                position: relative;
                overflow: hidden;
                transition: transform 0.4s ease, box-shadow 0.4s ease, border-color 0.4s ease;
            }

            /* Base Background Layer (Deep Obsidian Navy) */
            .hp-dim-bg {
                position: absolute;
                inset: 0;
                background: linear-gradient(135deg, #04070f 0%, #080f1d 50%, #0d1627 100%);
                opacity: 0;
                pointer-events: none;
                z-index: 1;
                transition: opacity 0.5s ease;
            }

            /* Revealed Light Layer (Muted Dark Parchment & Glow) */
            .hp-light-bg {
                position: absolute;
                inset: 0;
                background-color: #171105;
                background-image: 
                    radial-gradient(circle at center, rgba(184, 145, 62, 0.75) 0%, rgba(102, 79, 31, 0.85) 60%, rgba(23, 17, 5, 0.95) 100%),
                    repeating-linear-gradient(45deg, rgba(0, 0, 0, 0.3) 0, rgba(0, 0, 0, 0.3) 1px, transparent 0, transparent 20px);
                opacity: 0;
                pointer-events: none;
                z-index: 2;
                transition: opacity 0.5s ease;
                -webkit-mask-image: radial-gradient(circle 120px at var(--mouse-x, -500px) var(--mouse-y, -500px), rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0.8) 40%, rgba(0, 0, 0, 0) 100%);
                mask-image: radial-gradient(circle 120px at var(--mouse-x, -500px) var(--mouse-y, -500px), rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0.8) 40%, rgba(0, 0, 0, 0) 100%);
            }

            /* Hover states */
            .bd-hero:hover .hp-dim-bg,
            .bd-hero:hover .hp-light-bg {
                opacity: 1;
            }

            .bd-hero:hover {
                box-shadow: 0 0 25px rgba(212, 160, 23, 0.35), inset 0 0 15px rgba(212, 160, 23, 0.15);
                border-color: rgba(212, 160, 23, 0.6) !important;
            }

            /* Keep text & content readable over dark layer */
            .bd-hero > *:not(canvas):not(.hp-dim-bg):not(.hp-light-bg):not(.snitch-cursor) {
                position: relative;
                z-index: 3;
            }

            @keyframes snitchFlap {
                0% { transform: rotate(-25deg) scaleY(1); }
                100% { transform: rotate(45deg) scaleY(0.5); }
            }
        `;
        document.head.appendChild(styleTag);
    }

    // Add background layers
    const dimBg = document.createElement('div');
    dimBg.className = 'hp-dim-bg';
    hpHero.appendChild(dimBg);

    const lightBg = document.createElement('div');
    lightBg.className = 'hp-light-bg';
    hpHero.appendChild(lightBg);

    // Canvas overlay for sparkles
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    canvas.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 10;
        border-radius: inherit;
    `;
    hpHero.appendChild(canvas);

    let width = (canvas.width = hpHero.offsetWidth);
    let height = (canvas.height = hpHero.offsetHeight);

    const resizeObserver = new ResizeObserver(() => {
        width = canvas.width = hpHero.offsetWidth;
        height = canvas.height = hpHero.offsetHeight;
    });
    resizeObserver.observe(hpHero);

    // Glowing Golden Snitch Cursor
    const snitch = document.createElement('div');
    snitch.className = 'snitch-cursor';
    snitch.style.cssText = `
        position: absolute;
        width: 14px;
        height: 14px;
        background: radial-gradient(circle at 35% 35%, #fff1b8, #d4a017 60%, #7a5a07);
        border-radius: 50%;
        box-shadow: 0 0 15px #d4a017, 0 0 30px rgba(212, 160, 23, 0.7);
        pointer-events: none;
        z-index: 12;
        opacity: 0;
        transform: translate(-50%, -50%);
        transition: opacity 0.25s ease;
    `;

    const leftWing = document.createElement('div');
    const rightWing = document.createElement('div');
    const wingCss = `
        position: absolute;
        top: -4px;
        width: 18px;
        height: 8px;
        border-radius: 80% 0 80% 0;
        background: rgba(255, 255, 255, 0.85);
        box-shadow: 0 0 8px rgba(212, 160, 23, 0.9);
        animation: snitchFlap 0.12s infinite alternate ease-in-out;
    `;
    leftWing.style.cssText = wingCss + 'left: -14px; transform-origin: bottom right;';
    rightWing.style.cssText = wingCss + 'right: -14px; transform-origin: bottom left; border-radius: 0 80% 0 80%;';

    snitch.appendChild(leftWing);
    snitch.appendChild(rightWing);
    hpHero.appendChild(snitch);

    // Sparkle Particle Animation
    const particles = [];
    const colors = ['#d4a017', '#b8860b', '#ffe8a3', '#ffffff'];
    let animId = null;
    let lastX = 0;
    let lastY = 0;

    function addGoldenSparkles(x, y) {
        for (let i = 0; i < 3; i++) {
            const angle = Math.random() * Math.PI * 2;
            const speed = Math.random() * 1.8 + 0.4;
            particles.push({
                x,
                y,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed + 0.2,
                size: Math.random() * 3 + 1.5,
                color: colors[Math.floor(Math.random() * colors.length)],
                life: 1.0,
                decay: Math.random() * 0.025 + 0.015
            });
        }
    }

    function render() {
        ctx.clearRect(0, 0, width, height);

        for (let i = particles.length - 1; i >= 0; i--) {
            const p = particles[i];
            p.x += p.vx;
            p.y += p.vy;
            p.life -= p.decay;

            if (p.life <= 0) {
                particles.splice(i, 1);
                continue;
            }

            ctx.save();
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.size * p.life, 0, Math.PI * 2);
            ctx.fillStyle = p.color;
            ctx.globalAlpha = p.life;
            ctx.shadowColor = '#d4a017';
            ctx.shadowBlur = 8;
            ctx.fill();
            ctx.restore();
        }

        if (particles.length > 0) {
            animId = requestAnimationFrame(render);
        } else {
            animId = null;
        }
    }

    hpHero.addEventListener('mousemove', (e) => {
        const rect = hpHero.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        lightBg.style.setProperty('--mouse-x', `${x}px`);
        lightBg.style.setProperty('--mouse-y', `${y}px`);

        snitch.style.left = `${x}px`;
        snitch.style.top = `${y}px`;
        snitch.style.opacity = '1';

        if (lastX === 0 && lastY === 0) {
            lastX = x;
            lastY = y;
            return;
        }

        const dist = Math.hypot(x - lastX, y - lastY);

        if (dist > 8) {
            addGoldenSparkles(x, y);
            lastX = x;
            lastY = y;

            if (!animId) {
                animId = requestAnimationFrame(render);
            }
        }
    });

    hpHero.addEventListener('mouseleave', () => {
        snitch.style.opacity = '0';
        lightBg.style.setProperty('--mouse-x', `-500px`);
        lightBg.style.setProperty('--mouse-y', `-500px`);
        lastX = 0;
        lastY = 0;
    });
});