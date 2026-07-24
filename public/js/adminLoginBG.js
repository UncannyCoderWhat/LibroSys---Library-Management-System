(function () {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
    canvas.id = 'bg-canvas';
    Object.assign(canvas.style, {
        position: 'fixed',
        top: '0',
        left: '0',
        width: '100vw',
        height: '100vh',
        zIndex: '0',
        pointerEvents: 'none'
    });

    const oldBg = document.querySelector('.bg-slider-container, .bg-image');
    if (oldBg) oldBg.style.display = 'none';
    document.body.prepend(canvas);

    let w, h;
    const particles = [];
    const particleCount = 120;
    const maxDistance = 160;
    const mouse = { x: null, y: null, radius: 180 };

    function resize() {
        w = canvas.width = window.innerWidth;
        h = canvas.height = window.innerHeight;
    }

    window.addEventListener('resize', resize);
    window.addEventListener('mousemove', (e) => {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    });
    window.addEventListener('mouseleave', () => {
        mouse.x = null;
        mouse.y = null;
    });

    class Particle {
        constructor() {
            this.init();
        }

        init() {
            this.x = Math.random() * w;
            this.y = Math.random() * h;
            this.vx = (Math.random() - 0.5) * 1.2;
            this.vy = (Math.random() - 0.5) * 1.2;
            this.radius = Math.random() * 2 + 1;
            this.baseHue = Math.random() * 60 + 40; // Gold/Yellow spectrum
            this.hue = this.baseHue;
            this.alpha = Math.random() * 0.5 + 0.5;
        }

        update() {
            this.x += this.vx;
            this.y += this.vy;

            if (this.x < 0 || this.x > w) this.vx *= -1;
            if (this.y < 0 || this.y > h) this.vy *= -1;

            if (mouse.x !== null && mouse.y !== null) {
                const dx = mouse.x - this.x;
                const dy = mouse.y - this.y;
                const dist = Math.hypot(dx, dy);

                if (dist < mouse.radius) {
                    const angle = Math.atan2(dy, dx);
                    const force = (mouse.radius - dist) / mouse.radius;
                    this.x -= Math.cos(angle) * force * 4;
                    this.y -= Math.sin(angle) * force * 4;
                    this.hue = 45 + force * 30;
                }
            }
        }

        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fillStyle = `hsla(${this.hue}, 100%, 60%, ${this.alpha})`;
            ctx.shadowColor = `hsla(${this.hue}, 100%, 50%, 0.8)`;
            ctx.shadowBlur = 10;
            ctx.fill();
        }
    }

    resize();
    for (let i = 0; i < particleCount; i++) {
        particles.push(new Particle());
    }

    function animate() {
        // Deep, solid dark background fill
        ctx.fillStyle = '#060709';
        ctx.fillRect(0, 0, w, h);

        for (let i = 0; i < particles.length; i++) {
            particles[i].update();
            particles[i].draw();

            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const dist = Math.hypot(dx, dy);

                if (dist < maxDistance) {
                    const alpha = (1 - dist / maxDistance) * 0.45;
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = `hsla(${(particles[i].hue + particles[j].hue) / 2}, 100%, 55%, ${alpha})`;
                    ctx.lineWidth = 0.9;
                    ctx.shadowBlur = 0;
                    ctx.stroke();
                }
            }
        }

        requestAnimationFrame(animate);
    }

    animate();
})();