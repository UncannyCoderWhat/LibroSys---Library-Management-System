document.addEventListener('DOMContentLoaded', () => {
   // 1. Game of Thrones: Hyper-Realistic Interactive Battlefield Engine (Optimized)
    const gotCard = document.querySelector('.special-book-card[data-title*="thrones"]');
    if (gotCard) {
        if (!document.getElementById('got-card-styles')) {
            const styleTag = document.createElement('style');
            styleTag.id = 'got-card-styles';
            styleTag.textContent = `
                .special-book-card[data-title*="thrones"] {
                    position: relative;
                    overflow: hidden;
                    transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1), box-shadow 0.4s ease, border-color 0.4s ease;
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
                    -webkit-mask-image: radial-gradient(circle 130px at var(--mouse-x, -500px) var(--mouse-y, -500px), rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 100%);
                    mask-image: radial-gradient(circle 130px at var(--mouse-x, -500px) var(--mouse-y, -500px), rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 100%);
                }

                .special-book-card[data-title*="thrones"]:hover .got-dim-bg,
                .special-book-card[data-title*="thrones"]:hover .got-light-bg {
                    opacity: 1;
                }

                .special-book-card[data-title*="thrones"]:hover {
                    box-shadow: 0 0 25px rgba(255, 100, 0, 0.4), 0 0 35px rgba(139, 0, 0, 0.3);
                    border-color: rgba(212, 175, 55, 0.7) !important;
                }

                .special-book-card[data-title*="thrones"] > *:not(canvas):not(.got-dim-bg):not(.got-light-bg) {
                    position: relative;
                    z-index: 3;
                }
            `;
            document.head.appendChild(styleTag);
        }

        let dimBg = gotCard.querySelector('.got-dim-bg') || document.createElement('div');
        if (!dimBg.parentNode) { dimBg.className = 'got-dim-bg'; gotCard.appendChild(dimBg); }

        let lightBg = gotCard.querySelector('.got-light-bg') || document.createElement('div');
        if (!lightBg.parentNode) { lightBg.className = 'got-light-bg'; gotCard.appendChild(lightBg); }

        let canvas = gotCard.querySelector('.got-battlefield-canvas') || document.createElement('canvas');
        if (!canvas.parentNode) {
            canvas.className = 'got-battlefield-canvas';
            canvas.style.cssText = `
                position: absolute;
                top: 0; left: 0;
                width: 100%; height: 100%;
                pointer-events: none;
                z-index: 5;
                border-radius: 16px;
            `;
            gotCard.appendChild(canvas);
        }

        const ctx = canvas.getContext('2d', { alpha: true });
        let animationFrameId = null;
        let isHovered = false;
        let isMouseMoving = false;
        let mouseX = -500;
        let mouseY = -500;
        let cardRect = gotCard.getBoundingClientRect();

        const MAX_PARTICLES = 60;
        const particlePool = [];

        class Particle {
            constructor() {
                this.active = false;
            }

            init(x, y, type) {
                const w = cardRect.width;
                const h = cardRect.height;
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

                if (this.life <= 0 || this.y < -10 || this.x < -10 || this.x > cardRect.width + 10) {
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
            cardRect = gotCard.getBoundingClientRect();
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = cardRect.width * dpr;
            canvas.height = cardRect.height * dpr;
            ctx.scale(dpr, dpr);
        }
        resizeCanvas();

        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(resizeCanvas, 100);
        }, { passive: true });

        function render() {
            ctx.clearRect(0, 0, cardRect.width, cardRect.height);

            // Update mask position inside rAF loop to avoid lag
            lightBg.style.setProperty('--mouse-x', `${mouseX}px`);
            lightBg.style.setProperty('--mouse-y', `${mouseY}px`);

            // Apply tilt smooth transformation directly inside frame loop
            if (isHovered) {
                const centerX = cardRect.width / 2;
                const centerY = cardRect.height / 2;
                const rotateX = ((mouseY - centerY) / centerY) * -8;
                const rotateY = ((mouseX - centerX) / centerX) * 8;
                gotCard.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-6px)`;
            }

            let activeCount = 0;

            if (isHovered) {
                for (let i = 0; i < MAX_PARTICLES; i++) {
                    if (particlePool[i].active) activeCount++;
                }

                if (activeCount < 40) {
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

        gotCard.addEventListener('mouseenter', (e) => {
            isHovered = true;
            cardRect = gotCard.getBoundingClientRect();
            gotCard.style.willChange = 'transform';

            mouseX = e.clientX - cardRect.left;
            mouseY = e.clientY - cardRect.top;

            if (!animationFrameId) {
                animationFrameId = requestAnimationFrame(render);
            }
        }, { passive: true });

        gotCard.addEventListener('mousemove', (e) => {
            mouseX = e.clientX - cardRect.left;
            mouseY = e.clientY - cardRect.top;
            isMouseMoving = true;
        }, { passive: true });

        gotCard.addEventListener('mouseleave', () => {
            isHovered = false;
            gotCard.style.willChange = 'auto';
            gotCard.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0px)';

            mouseX = -500;
            mouseY = -500;
        }, { passive: true });
    }






    // 2. Jujutsu Kaisen: Black Flash / Cursed Sparks Effect (Optimized)
    const card = document.querySelector('.special-book-card[data-title*="jujutsu"]');

    if (card) {
        if (!document.getElementById('jjk-card-styles')) {
            const styleTag = document.createElement('style');
            styleTag.id = 'jjk-card-styles';
            styleTag.textContent = `
                .special-book-card[data-title*="jujutsu"] {
                    position: relative;
                    overflow: hidden;
                    transition: transform 0.3s cubic-bezier(0.25, 1, 0.5, 1), box-shadow 0.4s ease, border-color 0.4s ease;
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

                .jjk-light-bg {
                    position: absolute;
                    inset: 0;
                    background: radial-gradient(circle at center, #3d0016 0%, #1c002b 60%, #05000d 100%);
                    opacity: 0;
                    pointer-events: none;
                    z-index: 2;
                    transition: opacity 0.5s ease;
                    -webkit-mask-image: radial-gradient(circle 120px at var(--mouse-x, -500px) var(--mouse-y, -500px), rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 100%);
                    mask-image: radial-gradient(circle 120px at var(--mouse-x, -500px) var(--mouse-y, -500px), rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0) 100%);
                }

                .special-book-card[data-title*="jujutsu"]:hover .jjk-dim-bg,
                .special-book-card[data-title*="jujutsu"]:hover .jjk-light-bg {
                    opacity: 1;
                }

                .special-book-card[data-title*="jujutsu"]:hover {
                    box-shadow: 0 0 25px rgba(255, 0, 85, 0.4), 0 0 35px rgba(128, 0, 255, 0.3);
                    border-color: rgba(255, 0, 85, 0.7) !important;
                }

                .special-book-card[data-title*="jujutsu"] > *:not(canvas):not(.jjk-dim-bg):not(.jjk-light-bg) {
                    position: relative;
                    z-index: 3;
                }
            `;
            document.head.appendChild(styleTag);
        }

        let dimBg = card.querySelector('.jjk-dim-bg') || document.createElement('div');
        if (!dimBg.parentNode) { dimBg.className = 'jjk-dim-bg'; card.appendChild(dimBg); }

        let lightBg = card.querySelector('.jjk-light-bg') || document.createElement('div');
        if (!lightBg.parentNode) { lightBg.className = 'jjk-light-bg'; card.appendChild(lightBg); }

        let canvas = card.querySelector('.jjk-master-canvas') || document.createElement('canvas');
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
            card.appendChild(canvas);
        }

        const ctx = canvas.getContext('2d', { alpha: true });
        let cardRect = card.getBoundingClientRect();
        let animId = null;
        let isHovered = false;
        let mouseX = -500;
        let mouseY = -500;
        let targetX = -500;
        let targetY = -500;

        const POOL_SIZE = 80;
        const pool = new Array(POOL_SIZE);

        class CursedParticle {
            constructor() {
                this.active = false;
            }

            spawn(x, y, type) {
                this.active = true;
                this.x = x ?? Math.random() * cardRect.width;
                this.y = y ?? Math.random() * cardRect.height;
                this.type = type;
                this.life = 1;

                if (type === 'spark') {
                    this.length = Math.random() * 35 + 15;
                    this.angle = Math.random() * Math.PI * 2;
                    this.decay = Math.random() * 0.06 + 0.03;
                    this.lineWidth = Math.random() * 2.5 + 1;
                    const speed = Math.random() * 6 + 2;
                    this.vx = Math.cos(this.angle) * speed;
                    this.vy = Math.sin(this.angle) * speed;
                    this.color = Math.random() > 0.3 ? '#ff0033' : '#110022';
                } else if (type === 'aura') {
                    this.radius = Math.random() * 3 + 1;
                    this.vx = (Math.random() - 0.5) * 1.2;
                    this.vy = -(Math.random() * 2 + 0.8);
                    this.decay = Math.random() * 0.02 + 0.01;
                    this.color = Math.random() > 0.4 ? '#8000ff' : '#ff0055';
                } else {
                    this.radius = Math.random() * 1.5 + 0.5;
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
                if (this.life <= 0 || this.x < -20 || this.x > cardRect.width + 20 || this.y < -20 || this.y > cardRect.height + 20) {
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
            cardRect = card.getBoundingClientRect();
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = cardRect.width * dpr;
            canvas.height = cardRect.height * dpr;
            ctx.scale(dpr, dpr);
        }
        updateSize();

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(updateSize, 100);
        }, { passive: true });

        function loop() {
            ctx.clearRect(0, 0, cardRect.width, cardRect.height);

            // Batch mask and transform updates into the animation loop
            lightBg.style.setProperty('--mouse-x', `${targetX}px`);
            lightBg.style.setProperty('--mouse-y', `${targetY}px`);

            if (isHovered) {
                const cx = cardRect.width / 2;
                const cy = cardRect.height / 2;
                const rx = ((targetY - cy) / cy) * -8;
                const ry = ((targetX - cx) / cx) * 8;
                card.style.transform = `perspective(1000px) rotateX(${rx}deg) rotateY(${ry}deg) translateY(-5px)`;

                if (Math.random() < 0.3) emit(Math.random() * cardRect.width, cardRect.height + 5, 'aura');
                if (Math.random() < 0.15) emit(Math.random() * cardRect.width, Math.random() * cardRect.height, 'domain');

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

            if (isHovered || activeCount > 0) {
                animId = requestAnimationFrame(loop);
            } else {
                animId = null;
            }
        }

        function triggerBlackFlash(e) {
            card.animate([
                { transform: 'scale(1) translate(0, 0)' },
                { transform: 'scale(1.02) translate(-4px, 2px)' },
                { transform: 'scale(0.99) translate(4px, -2px)' },
                { transform: 'scale(1.01) translate(-2px, -1px)' },
                { transform: 'scale(1) translate(0, 0)' }
            ], { duration: 160, iterations: 1 });

            const x = e.offsetX;
            const y = e.offsetY;

            const flash = document.createElement('div');
            flash.style.cssText = `
                position: absolute;
                inset: 0;
                background: radial-gradient(circle at ${x}px ${y}px, rgba(255, 0, 51, 0.95) 0%, rgba(0, 0, 0, 0.9) 55%, transparent 80%);
                pointer-events: none;
                z-index: 6;
                transition: opacity 0.2s ease-out;
                border-radius: 16px;
            `;
            card.appendChild(flash);
            requestAnimationFrame(() => {
                flash.style.opacity = '0';
                setTimeout(() => flash.remove(), 200);
            });

            for (let i = 0; i < 15; i++) {
                emit(x, y, 'spark');
            }
        }

        card.addEventListener('mouseenter', (e) => {
            isHovered = true;
            cardRect = card.getBoundingClientRect();
            card.style.willChange = 'transform';
            targetX = e.clientX - cardRect.left;
            targetY = e.clientY - cardRect.top;
            mouseX = targetX;
            mouseY = targetY;

            triggerBlackFlash(e);

            if (!animId) animId = requestAnimationFrame(loop);
        }, { passive: true });

        card.addEventListener('mousemove', (e) => {
            targetX = e.clientX - cardRect.left;
            targetY = e.clientY - cardRect.top;
        }, { passive: true });

        card.addEventListener('mouseleave', () => {
            isHovered = false;
            card.style.willChange = 'auto';
            card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0px)';
            
            targetX = -500;
            targetY = -500;
        }, { passive: true });
    }






    // 3. Sherlock Holmes: Foggy London Night & Magnifying Reveal Effect
    const sherlockCard = document.querySelector('.special-book-card[data-title*="sherlock"]');

    if (sherlockCard) {
        if (!document.getElementById('sherlock-spotlight-styles')) {
            const styleTag = document.createElement('style');
            styleTag.id = 'sherlock-spotlight-styles';
            styleTag.textContent = `
                .special-book-card[data-title*="sherlock"] {
                    position: relative;
                    overflow: hidden;
                    transition: transform 0.4s ease, box-shadow 0.4s ease, border-color 0.4s ease;
                }

                /* Base Dim Foggy London Background */
                .sherlock-dim-bg {
                    position: absolute;
                    inset: 0;
                    background: radial-gradient(circle at center, #1a1e24 0%, #0d1013 100%);
                    opacity: 0;
                    pointer-events: none;
                    z-index: 1;
                    transition: opacity 0.5s ease;
                }

                /* Revealed Map & Clues Background Layer */
                .sherlock-light-bg {
                    position: absolute;
                    inset: 0;
                    background: radial-gradient(circle at center, #e8dcad 0%, #c2b180 60%, #8c7b50 100%);
                    background-image: 
                        radial-gradient(circle at center, rgba(232, 220, 173, 0.95) 0%, rgba(194, 177, 128, 0.95) 60%, rgba(140, 123, 80, 0.95) 100%),
                        repeating-linear-gradient(45deg, rgba(43, 29, 14, 0.05) 0, rgba(43, 29, 14, 0.05) 1px, transparent 0, transparent 20px);
                    opacity: 0;
                    pointer-events: none;
                    z-index: 2;
                    /* Magnifying Lens Spotlight Mask */
                    -webkit-mask-image: radial-gradient(circle 100px at var(--mouse-x, -500px) var(--mouse-y, -500px), rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0.8) 50%, rgba(0, 0, 0, 0) 100%);
                    mask-image: radial-gradient(circle 100px at var(--mouse-x, -500px) var(--mouse-y, -500px), rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0.8) 50%, rgba(0, 0, 0, 0) 100%);
                }

                /* Hover states */
                .special-book-card[data-title*="sherlock"]:hover .sherlock-dim-bg,
                .special-book-card[data-title*="sherlock"]:hover .sherlock-light-bg {
                    opacity: 1;
                }

                .special-book-card[data-title*="sherlock"]:hover {
                    box-shadow: 0 0 25px rgba(212, 175, 55, 0.4);
                    border-color: rgba(212, 175, 55, 0.7) !important;
                }

                /* Ensure text stays readable */
                .special-book-card[data-title*="sherlock"] > *:not(canvas):not(.sherlock-dim-bg):not(.sherlock-light-bg):not(.sherlock-lens) {
                    position: relative;
                    z-index: 3;
                }
            `;
            document.head.appendChild(styleTag);
        }

        // Add background layers
        const dimBg = document.createElement('div');
        dimBg.className = 'sherlock-dim-bg';
        sherlockCard.appendChild(dimBg);

        const lightBg = document.createElement('div');
        lightBg.className = 'sherlock-light-bg';
        sherlockCard.appendChild(lightBg);

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
        sherlockCard.appendChild(canvas);

        let width = (canvas.width = sherlockCard.offsetWidth);
        let height = (canvas.height = sherlockCard.offsetHeight);

        const resizeObserver = new ResizeObserver(() => {
            width = canvas.width = sherlockCard.offsetWidth;
            height = canvas.height = sherlockCard.offsetHeight;
        });
        resizeObserver.observe(sherlockCard);

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
            width: 80px;
            height: 80px;
            border: 3px solid #d4af37;
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.6), inset 0 0 15px rgba(255, 255, 255, 0.4);
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, rgba(212, 175, 55, 0.08) 70%, transparent 100%);
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
            background: linear-gradient(90deg, #3c2415, #5a3d28, #231209);
            border-radius: 3px;
            border: 1px solid #d4af37;
            transform: rotate(-45deg);
            transform-origin: top center;
        `;
        lens.appendChild(handle);
        sherlockCard.appendChild(lens);

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

                ctx.fillStyle = `rgba(35, 20, 10, ${p.life * 0.9})`;
                ctx.shadowColor = 'rgba(212, 175, 55, 0.4)';
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

        sherlockCard.addEventListener('mousemove', (e) => {
            const rect = sherlockCard.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // Move spotlight & lens position
            lightBg.style.setProperty('--mouse-x', `${x}px`);
            lightBg.style.setProperty('--mouse-y', `${y}px`);

            lens.style.left = `${x}px`;
            lens.style.top = `${y}px`;
            lens.style.opacity = '1';

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

        sherlockCard.addEventListener('mouseleave', () => {
            lens.style.opacity = '0';
            lightBg.style.setProperty('--mouse-x', `-500px`);
            lightBg.style.setProperty('--mouse-y', `-500px`);
            lastX = 0;
            lastY = 0;
        });
    }






    // 4. Harry Potter: Dim Map Background with Dynamic Lumos/Snitch Spotlight
    const hpCards = document.querySelectorAll('.special-book-card[data-title*="harry potter"], .special-book-card[data-title*="cursed child"]');

    if (hpCards.length > 0) {
        if (!document.getElementById('hp-spotlight-styles')) {
            const styleTag = document.createElement('style');
            styleTag.id = 'hp-spotlight-styles';
            styleTag.textContent = `
                .special-book-card[data-title*="harry potter"],
                .special-book-card[data-title*="cursed child"] {
                    position: relative;
                    overflow: hidden;
                    transition: transform 0.4s ease, box-shadow 0.4s ease, border-color 0.4s ease;
                }

                /* Base Background Layer (Deep Navy Gradient on Hover) */
                .hp-dim-bg {
                    position: absolute;
                    inset: 0;
                    background: linear-gradient(135deg, #0b1326 0%, #162447 50%, #1f4068 100%);
                    opacity: 0;
                    pointer-events: none;
                    z-index: 1;
                    transition: opacity 0.5s ease;
                }

                /* Revealed Light Layer (Parchment & Magic) */
                .hp-light-bg {
                    position: absolute;
                    inset: 0;
                    background: radial-gradient(circle at center, #f4e4bc 0%, #d3b88c 60%, #a88958 100%);
                    opacity: 0;
                    pointer-events: none;
                    z-index: 2;
                    /* Mask out the layer except where the glowing spotlight hits */
                    -webkit-mask-image: radial-gradient(circle 120px at var(--mouse-x, -500px) var(--mouse-y, -500px), rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0.8) 40%, rgba(0, 0, 0, 0) 100%);
                    mask-image: radial-gradient(circle 120px at var(--mouse-x, -500px) var(--mouse-y, -500px), rgba(0, 0, 0, 1) 0%, rgba(0, 0, 0, 0.8) 40%, rgba(0, 0, 0, 0) 100%);
                }

                /* Hover states */
                .special-book-card[data-title*="harry potter"]:hover .hp-dim-bg,
                .special-book-card[data-title*="cursed child"]:hover .hp-dim-bg {
                    opacity: 1;
                }

                .special-book-card[data-title*="harry potter"]:hover .hp-light-bg,
                .special-book-card[data-title*="cursed child"]:hover .hp-light-bg {
                    opacity: 1;
                }

                .special-book-card[data-title*="harry potter"]:hover,
                .special-book-card[data-title*="cursed child"]:hover {
                    box-shadow: 0 0 25px rgba(226, 177, 60, 0.5), inset 0 0 15px rgba(226, 177, 60, 0.2);
                    border-color: #e2b13c !important;
                }

                /* Keep text readable over dark layer */
                .special-book-card[data-title*="harry potter"] > *:not(canvas):not(.hp-dim-bg):not(.hp-light-bg):not(.snitch-cursor),
                .special-book-card[data-title*="cursed child"] > *:not(canvas):not(.hp-dim-bg):not(.hp-light-bg):not(.snitch-cursor) {
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

        hpCards.forEach((hpCard) => {
            // Add background layers
            const dimBg = document.createElement('div');
            dimBg.className = 'hp-dim-bg';
            hpCard.appendChild(dimBg);

            const lightBg = document.createElement('div');
            lightBg.className = 'hp-light-bg';
            hpCard.appendChild(lightBg);

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
            hpCard.appendChild(canvas);

            let width = (canvas.width = hpCard.offsetWidth);
            let height = (canvas.height = hpCard.offsetHeight);

            const resizeObserver = new ResizeObserver(() => {
                width = canvas.width = hpCard.offsetWidth;
                height = canvas.height = hpCard.offsetHeight;
            });
            resizeObserver.observe(hpCard);

            // Glowing Golden Snitch Cursor
            const snitch = document.createElement('div');
            snitch.className = 'snitch-cursor';
            snitch.style.cssText = `
                position: absolute;
                width: 14px;
                height: 14px;
                background: radial-gradient(circle at 35% 35%, #fff7d6, #ffd700 60%, #b8860b);
                border-radius: 50%;
                box-shadow: 0 0 15px #ffd700, 0 0 35px rgba(255, 215, 0, 0.9);
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
                background: rgba(255, 255, 255, 0.9);
                box-shadow: 0 0 8px rgba(255, 215, 0, 1);
                animation: snitchFlap 0.12s infinite alternate ease-in-out;
            `;
            leftWing.style.cssText = wingCss + 'left: -14px; transform-origin: bottom right;';
            rightWing.style.cssText = wingCss + 'right: -14px; transform-origin: bottom left; border-radius: 0 80% 0 80%;';

            snitch.appendChild(leftWing);
            snitch.appendChild(rightWing);
            hpCard.appendChild(snitch);

            // Sparkle Particle Animation
            const particles = [];
            const colors = ['#ffd700', '#ffae00', '#fff8dc', '#ffffff'];
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
                    ctx.shadowColor = '#ffd700';
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

            hpCard.addEventListener('mousemove', (e) => {
                const rect = hpCard.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                // Move light spotlight position dynamically
                lightBg.style.setProperty('--mouse-x', `${x}px`);
                lightBg.style.setProperty('--mouse-y', `${y}px`);

                snitch.style.left = `${x}px`;
                snitch.style.top = `${y}px`;
                snitch.style.opacity = '1';

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

            hpCard.addEventListener('mouseleave', () => {
                snitch.style.opacity = '0';
                lightBg.style.setProperty('--mouse-x', `-500px`);
                lightBg.style.setProperty('--mouse-y', `-500px`);
                lastX = 0;
                lastY = 0;
            });
        });
    }
});