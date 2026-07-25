/**
 * clientBG.js - Fully Adaptive Library Animated Background (Optimized)
 * Static bookshelf background with smooth floating books, dust, and dynamic light.
 */

class ClientBG {
  constructor(options = {}) {
    this.options = Object.assign(
      {
        textHeading: '',
        textSubheading: '',
        showOverlay: true,
        particleCount: null,
        bookCount: null,
      },
      options
    );

    this.canvas = null;
    this.ctx = null;
    this.width = 0;
    this.height = 0;
    this.particles = [];
    this.books = [];
    this.staticShelfBooks = [];
    this.animationFrameId = null;

    this.mouse = {
      x: 0,
      y: 0,
      targetX: 0,
      targetY: 0,
      radius: 250,
      down: false,
    };

    this.themes = {
      dark: {
        bgInner: '#1a100a',
        bgOuter: '#050302',
        shelf: '#0a0604',
        leather: ['#3e1f17', '#2b170e', '#4a2511', '#1f0d07', '#5c2d18', '#1c2217', '#151f28'],
        gold: 'rgba(212, 163, 89, 0.8)',
        paper: '#e8d8b8',
        dust: 'rgba(230, 200, 150, 1)',
        lightGlowInner: 'rgba(255, 215, 130, 0.35)',
        lightGlowMid: 'rgba(212, 163, 89, 0.15)',
        overlayHeading: 'rgba(240, 230, 210, 0.85)',
        overlaySub: 'rgba(212, 163, 89, 0.7)',
        shadow: 'rgba(0, 0, 0, 0.4)'
      },
      light: {
        bgInner: '#fdf8ef',
        bgOuter: '#ebdccb',
        shelf: '#8c5a3c',
        leather: ['#a64b2a', '#7a3118', '#c07842', '#8d5b36', '#4a6b52', '#3b5266', '#d19045'],
        gold: 'rgba(168, 115, 30, 0.9)',
        paper: '#fffdfa',
        dust: 'rgba(140, 90, 40, 1)',
        lightGlowInner: 'rgba(255, 230, 150, 0.55)',
        lightGlowMid: 'rgba(230, 180, 100, 0.25)',
        overlayHeading: '#3b2314',
        overlaySub: '#7a5132',
        shadow: 'rgba(80, 40, 10, 0.15)'
      }
    };

    this.isLightMode = false;
    this.themeProgress = 0; 
    this.activeColors = {}; // Cached frame colors
    this.runes = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZαβγδεζηθικλμνξοπρστυφχψω0123456789';

    this.init();
  }

  init() {
    this.createDomElements();
    this.handleResize();
    this.bindEvents();
    this.initElements();
    this.detectTheme();
    this.animate();
  }

  detectTheme() {
    const doc = document.documentElement;
    const body = document.body;

    const hasLightClass = 
      doc.classList.contains('light-mode') || 
      doc.classList.contains('light') || 
      body.classList.contains('light-mode') || 
      body.classList.contains('light') ||
      doc.getAttribute('data-theme') === 'light' ||
      body.getAttribute('data-theme') === 'light';

    const hasDarkClass = 
      doc.classList.contains('dark-mode') || 
      doc.classList.contains('dark') || 
      body.classList.contains('dark-mode') || 
      body.classList.contains('dark') ||
      doc.getAttribute('data-theme') === 'dark' ||
      body.getAttribute('data-theme') === 'dark';

    const prefersLightOS = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches;

    if (hasLightClass) {
      this.isLightMode = true;
    } else if (hasDarkClass) {
      this.isLightMode = false;
    } else {
      this.isLightMode = prefersLightOS;
    }
  }

  createDomElements() {
    const style = document.createElement('style');
    style.id = 'client-bg-styles';
    style.textContent = `
      .client-bg-canvas {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: -1;
        display: block;
        pointer-events: auto;
      }
      .client-bg-overlay {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 0;
        text-align: center;
        pointer-events: none;
        font-family: 'Georgia', serif;
        letter-spacing: 2px;
      }
      .client-bg-overlay h1 {
        font-size: 3rem;
        font-weight: 300;
        margin-bottom: 0.5rem;
      }
      .client-bg-overlay p {
        font-size: 1.1rem;
        font-style: italic;
      }
    `;
    document.head.appendChild(style);

    this.canvas = document.createElement('canvas');
    this.canvas.className = 'client-bg-canvas';
    this.ctx = this.canvas.getContext('2d');
    document.body.prepend(this.canvas);

    if (this.options.showOverlay) {
      this.overlay = document.createElement('div');
      this.overlay.className = 'client-bg-overlay';
      this.overlay.innerHTML = `
        <h1 id="client-bg-title">${this.options.textHeading}</h1>
        <p id="client-bg-sub">${this.options.textSubheading}</p>
      `;
      document.body.appendChild(this.overlay);
    }
  }

  bindEvents() {
    window.addEventListener('resize', () => {
      this.handleResize();
      this.initElements();
    });

    window.addEventListener('mousemove', (e) => {
      this.mouse.targetX = e.clientX;
      this.mouse.targetY = e.clientY;
    });

    window.addEventListener('mousedown', () => (this.mouse.down = true));
    window.addEventListener('mouseup', () => (this.mouse.down = false));

    // Pause animation loop when tab is unfocused/hidden
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        if (this.animationFrameId) cancelAnimationFrame(this.animationFrameId);
      } else {
        this.animate();
      }
    });

    if (window.matchMedia) {
      window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', () => {
        this.detectTheme();
      });
    }

    const observer = new MutationObserver(() => this.detectTheme());
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });
    observer.observe(document.body, { attributes: true, attributeFilter: ['class', 'data-theme'] });
  }

  handleResize() {
    this.width = this.canvas.width = window.innerWidth;
    this.height = this.canvas.height = window.innerHeight;
    if (this.mouse.x === 0 && this.mouse.y === 0) {
      this.mouse.x = this.mouse.targetX = this.width / 2;
      this.mouse.y = this.mouse.targetY = this.height / 2;
    }
  }

  initElements() {
    const pCount = this.options.particleCount || Math.floor((this.width * this.height) / 10000);
    const bCount = this.options.bookCount || Math.floor(this.width / 110);

    this.particles = Array.from({ length: pCount }, () => new DustParticle(this));
    this.books = Array.from({ length: bCount }, () => new FloatingBook(this));

    this.generateStaticShelves();
  }

  generateStaticShelves() {
    this.staticShelfBooks = [];
    const shelfHeights = [this.height * 0.25, this.height * 0.55, this.height * 0.85];

    shelfHeights.forEach((sy) => {
      let currentX = 20;
      while (currentX < this.width) {
        const bWidth = Math.random() * 12 + 8;
        const bHeight = Math.random() * 50 + 40;
        const isLeaning = Math.random() > 0.85;

        this.staticShelfBooks.push({
          x: currentX,
          sy: sy,
          width: bWidth,
          height: bHeight,
          isLeaning: isLeaning
        });

        currentX += bWidth + Math.random() * 4 + 2;
      }
    });
  }

  lerpColor(color1, color2, factor) {
    const parse = (c) => {
      if (c.startsWith('#')) {
        let hex = c.slice(1);
        if (hex.length === 3) hex = hex.split('').map(x => x + x).join('');
        const num = parseInt(hex, 16);
        return [num >> 16, (num >> 8) & 255, num & 255, 1];
      } else if (c.startsWith('rgba') || c.startsWith('rgb')) {
        return c.match(/\d+(\.\d+)?/g).map(Number);
      }
      return [0, 0, 0, 1];
    };

    const c1 = parse(color1);
    const c2 = parse(color2);

    const r = Math.round(c1[0] + (c2[0] - c1[0]) * factor);
    const g = Math.round(c1[1] + (c2[1] - c1[1]) * factor);
    const b = Math.round(c1[2] + (c2[2] - c1[2]) * factor);
    const a = (c1[3] !== undefined && c2[3] !== undefined) ? (c1[3] + (c2[3] - c1[3]) * factor) : 1;

    return `rgba(${r}, ${g}, ${b}, ${a})`;
  }

  // Caches colors once per frame instead of recalculating during renders
  updateFrameColors() {
    const p = this.themeProgress;
    const dark = this.themes.dark;
    const light = this.themes.light;

    this.activeColors = {
      bgInner: this.lerpColor(dark.bgInner, light.bgInner, p),
      bgOuter: this.lerpColor(dark.bgOuter, light.bgOuter, p),
      shelf: this.lerpColor(dark.shelf, light.shelf, p),
      gold: this.lerpColor(dark.gold, light.gold, p),
      paper: this.lerpColor(dark.paper, light.paper, p),
      dust: this.lerpColor(dark.dust, light.dust, p),
      lightGlowInner: this.lerpColor(dark.lightGlowInner, light.lightGlowInner, p),
      lightGlowMid: this.lerpColor(dark.lightGlowMid, light.lightGlowMid, p),
      overlayHeading: this.lerpColor(dark.overlayHeading, light.overlayHeading, p),
      overlaySub: this.lerpColor(dark.overlaySub, light.overlaySub, p),
      shadow: this.lerpColor(dark.shadow, light.shadow, p),
      leather: dark.leather.map((c, i) => this.lerpColor(c, light.leather[i], p))
    };
  }

  drawBackgroundShelves() {
    const ctx = this.ctx;
    ctx.save();
    ctx.fillStyle = this.activeColors.shelf;

    const shelfHeights = [this.height * 0.25, this.height * 0.55, this.height * 0.85];

    shelfHeights.forEach((sy) => {
      ctx.fillRect(0, sy, this.width, 14);
    });

    this.staticShelfBooks.forEach((b) => {
      if (b.isLeaning) {
        ctx.save();
        ctx.translate(b.x, b.sy);
        ctx.rotate(0.15);
        ctx.fillRect(0, -b.height, b.width, b.height);
        ctx.restore();
      } else {
        ctx.fillRect(b.x, b.sy - b.height, b.width, b.height);
      }
    });

    ctx.restore();
  }

  animate() {
    const targetProgress = this.isLightMode ? 1 : 0;
    this.themeProgress += (targetProgress - this.themeProgress) * 0.08;

    this.mouse.x += (this.mouse.targetX - this.mouse.x) * 0.08;
    this.mouse.y += (this.mouse.targetY - this.mouse.y) * 0.08;

    // Cache colors for this frame
    this.updateFrameColors();

    const ctx = this.ctx;

    if (this.overlay) {
      const h1 = this.overlay.querySelector('h1');
      const p = this.overlay.querySelector('p');
      if (h1) h1.style.color = this.activeColors.overlayHeading;
      if (p) p.style.color = this.activeColors.overlaySub;
    }

    // Outer Background
    ctx.fillStyle = this.activeColors.bgOuter;
    ctx.fillRect(0, 0, this.width, this.height);

    // Vignette
    const bgGlow = ctx.createRadialGradient(
      this.width / 2,
      this.height / 2,
      100,
      this.width / 2,
      this.height / 2,
      Math.max(this.width, this.height) * 0.8
    );
    bgGlow.addColorStop(0, this.activeColors.bgInner);
    bgGlow.addColorStop(1, this.activeColors.bgOuter);
    ctx.fillStyle = bgGlow;
    ctx.fillRect(0, 0, this.width, this.height);

    // Static Shelves
    this.drawBackgroundShelves();

    // Floating Books
    this.books.forEach((book) => {
      book.update();
      book.draw();
    });

    // Particles & Runes
    this.particles.forEach((particle) => {
      particle.update();
      particle.draw();
    });

    // Mouse Lantern Light Source
    ctx.save();
    ctx.globalCompositeOperation = this.themeProgress > 0.5 ? 'multiply' : 'screen';

    const lightRadius = this.mouse.down ? this.mouse.radius * 1.4 : this.mouse.radius;
    const lanternGlow = ctx.createRadialGradient(
      this.mouse.x,
      this.mouse.y,
      10,
      this.mouse.x,
      this.mouse.y,
      lightRadius
    );

    lanternGlow.addColorStop(0, this.activeColors.lightGlowInner);
    lanternGlow.addColorStop(0.4, this.activeColors.lightGlowMid);
    lanternGlow.addColorStop(1, 'rgba(0, 0, 0, 0)');

    ctx.fillStyle = lanternGlow;
    ctx.beginPath();
    ctx.arc(this.mouse.x, this.mouse.y, lightRadius, 0, Math.PI * 2);
    ctx.fill();
    ctx.restore();

    this.animationFrameId = requestAnimationFrame(() => this.animate());
  }

  setTheme(isLight) {
    this.isLightMode = isLight;
  }

  destroy() {
    if (this.animationFrameId) {
      cancelAnimationFrame(this.animationFrameId);
    }
    if (this.canvas && this.canvas.parentNode) {
      this.canvas.parentNode.removeChild(this.canvas);
    }
  }
}

// Particle Helper Class
class DustParticle {
  constructor(app) {
    this.app = app;
    this.reset();
  }

  reset() {
    this.x = Math.random() * this.app.width;
    this.y = Math.random() * this.app.height;
    this.z = Math.random() * 0.8 + 0.2;
    this.size = (Math.random() * 2 + 1) * this.z;
    this.vx = (Math.random() - 0.5) * 0.3 * this.z;
    this.vy = (Math.random() - 0.2) * -0.4 * this.z;
    this.alpha = Math.random() * 0.6 + 0.1;
    this.baseAlpha = this.alpha;
    this.char =
      Math.random() < 0.2
        ? this.app.runes[Math.floor(Math.random() * this.app.runes.length)]
        : null;
    this.angle = Math.random() * Math.PI * 2;
    this.spin = (Math.random() - 0.5) * 0.02;
  }

  update() {
    this.x += this.vx;
    this.y += this.vy;
    this.angle += this.spin;

    const dx = this.app.mouse.x - this.x;
    const dy = this.app.mouse.y - this.y;
    const dist = Math.hypot(dx, dy);

    if (dist < this.app.mouse.radius) {
      const factor = 1 - dist / this.app.mouse.radius;
      this.x -= (dx / dist) * factor * 1.5;
      this.y -= (dy / dist) * factor * 1.5;
      this.alpha = Math.min(1, this.baseAlpha + factor * 0.6);
    } else {
      this.alpha += (this.baseAlpha - this.alpha) * 0.05;
    }

    if (this.x < -20) this.x = this.app.width + 20;
    if (this.x > this.app.width + 20) this.x = -20;
    if (this.y < -20) this.y = this.app.height + 20;
    if (this.y > this.app.height + 20) this.y = -20;
  }

  draw() {
    const ctx = this.app.ctx;
    ctx.save();
    ctx.translate(this.x, this.y);
    ctx.rotate(this.angle);

    if (this.char) {
      ctx.font = `${Math.floor(this.size * 5)}px serif`;
      ctx.fillStyle = this.app.activeColors.dust;
      ctx.globalAlpha = this.alpha * 0.8;
      ctx.fillText(this.char, 0, 0);
    } else {
      ctx.beginPath();
      ctx.arc(0, 0, this.size, 0, Math.PI * 2);
      ctx.fillStyle = this.app.activeColors.dust;
      ctx.globalAlpha = this.alpha;
      ctx.fill();
    }

    ctx.restore();
  }
}

// Floating Book Helper Class
class FloatingBook {
  constructor(app) {
    this.app = app;
    this.colorIndex = Math.floor(Math.random() * 7);
    this.reset(true);
  }

  reset(initial = false) {
    this.x = Math.random() * this.app.width;
    this.y = initial ? Math.random() * this.app.height : this.app.height + 100;
    this.z = Math.random() * 0.7 + 0.3;
    this.w = (Math.random() * 30 + 40) * this.z;
    this.h = (Math.random() * 40 + 60) * this.z;

    this.vx = (Math.random() - 0.5) * 0.4 * this.z;
    this.vy = -(Math.random() * 0.4 + 0.2) * this.z;

    this.rotX = Math.random() * 0.6 - 0.3;
    this.rotY = Math.random() * Math.PI;
    this.rotZ = Math.random() * 0.4 - 0.2;

    this.vRotX = (Math.random() - 0.5) * 0.005;
    this.vRotY = (Math.random() - 0.5) * 0.01;
    this.vRotZ = (Math.random() - 0.5) * 0.005;

    this.pageFlap = 0;
    this.pageSpeed = Math.random() * 0.03 + 0.01;

    this.hasGoldDetails = Math.random() > 0.4;
  }

  update() {
    this.x += this.vx;
    this.y += this.vy;

    this.rotX += this.vRotX;
    this.rotY += this.vRotY;
    this.rotZ += this.vRotZ;
    this.pageFlap += this.pageSpeed;

    const dx = this.app.mouse.x - this.x;
    const dy = this.app.mouse.y - this.y;
    const dist = Math.hypot(dx, dy);

    if (dist < this.app.mouse.radius * 1.2) {
      const force = (1 - dist / (this.app.mouse.radius * 1.2)) * 2;
      this.x -= (dx / dist) * force;
      this.y -= (dy / dist) * force;
      this.rotY += force * 0.02;
    }

    if (this.y < -150 || this.x < -100 || this.x > this.app.width + 100) {
      this.reset(false);
    }
  }

  draw() {
    const ctx = this.app.ctx;
    ctx.save();
    ctx.translate(this.x, this.y);

    const cosY = Math.cos(this.rotY);
    const tilt = Math.sin(this.rotX);

    ctx.rotate(this.rotZ);

    const effW = this.w * Math.abs(cosY);
    const coverColor = this.app.activeColors.leather[this.colorIndex];

    // Shadow via radial gradient (Optimized substitute for ctx.filter = 'blur()')
    ctx.save();
    ctx.translate(0, 40 * this.z);
    ctx.scale(1, 0.3);
    const shadowGrad = ctx.createRadialGradient(0, 0, 0, 0, 0, this.w * 0.8);
    shadowGrad.addColorStop(0, this.app.activeColors.shadow);
    shadowGrad.addColorStop(1, 'rgba(0,0,0,0)');
    ctx.fillStyle = shadowGrad;
    ctx.beginPath();
    ctx.arc(0, 0, this.w * 0.8, 0, Math.PI * 2);
    ctx.fill();
    ctx.restore();

    // Pages Block
    ctx.fillStyle = this.app.activeColors.paper;
    ctx.beginPath();
    ctx.rect(-effW / 2 + 2, -this.h / 2 + 2, effW - 4, this.h);
    ctx.fill();

    // Spine
    ctx.fillStyle = coverColor;
    ctx.beginPath();
    ctx.rect(-effW / 2 - 2, -this.h / 2, 4, this.h);
    ctx.fill();

    // Front Cover
    ctx.save();
    ctx.transform(cosY, tilt, 0, 1, 0, 0);
    ctx.fillStyle = coverColor;
    ctx.beginPath();
    if (ctx.roundRect) {
      ctx.roundRect(-this.w / 2, -this.h / 2, this.w, this.h, [2]);
    } else {
      ctx.rect(-this.w / 2, -this.h / 2, this.w, this.h);
    }
    ctx.fill();

    if (this.hasGoldDetails && Math.abs(cosY) > 0.3) {
      ctx.strokeStyle = this.app.activeColors.gold;
      ctx.lineWidth = 1.5 * this.z;
      ctx.strokeRect(-this.w / 2 + 4, -this.h / 2 + 4, this.w - 8, this.h - 8);
      ctx.beginPath();
      ctx.arc(0, 0, this.w * 0.15, 0, Math.PI * 2);
      ctx.stroke();
    }
    ctx.restore();

    // Animated Pages
    const flapOffset = Math.sin(this.pageFlap) * 15 * this.z;
    ctx.strokeStyle = this.app.activeColors.paper;
    ctx.lineWidth = 1 * this.z;
    ctx.beginPath();
    ctx.moveTo(0, -this.h / 2);
    ctx.quadraticCurveTo(-effW * 0.3, -this.h / 2 - flapOffset, -effW * 0.8, -this.h / 2 + 5);
    ctx.moveTo(0, this.h / 2);
    ctx.quadraticCurveTo(-effW * 0.3, this.h / 2 - flapOffset, -effW * 0.8, this.h / 2 + 5);
    ctx.stroke();

    ctx.restore();
  }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
  window.clientBG = new ClientBG();
});