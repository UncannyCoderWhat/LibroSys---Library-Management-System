// Lightbox caller using separate full cover source
const bookCover = document.getElementById('mainBookCover');
const imageModal = document.getElementById('imageModal');
const imgFull = document.getElementById('imgFull');
const closeBtn = document.querySelector('.lightbox-close');

// 3D Rotation State Variables
let isDragging = false;
let previousMousePosition = { x: 0, y: 0 };
let rotation = { x: 8, y: -15 };
let scale = 1;
let rafId = null;

// Helper to update 3D transform matrix smoothly
function updateTransform() {
    imgFull.style.transform = `rotateX(${rotation.x}deg) rotateY(${rotation.y}deg) scale(${scale})`;
}

if (bookCover && imageModal && imgFull) {
    bookCover.addEventListener('click', () => {
        imageModal.style.display = 'flex';
        imgFull.src = bookCover.dataset.fullCover || bookCover.src;
        
        // Reset rotation and zoom on open
        rotation = { x: 8, y: -15 };
        scale = 1;
        updateTransform();
    });
}

// Drag to Rotate Logic
if (imageModal && imgFull) {
    imgFull.style.cursor = 'grab';

    const startDrag = (e) => {
        isDragging = true;
        imgFull.style.cursor = 'grabbing';
        imgFull.style.transition = 'none'; // Instant response while dragging
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        previousMousePosition = { x: clientX, y: clientY };
    };

    const doDrag = (e) => {
        if (!isDragging) return;
        
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;

        const deltaX = clientX - previousMousePosition.x;
        const deltaY = clientY - previousMousePosition.y;

        rotation.y += deltaX * 0.5;
        rotation.x -= deltaY * 0.5;

        previousMousePosition = { x: clientX, y: clientY };

        // Sync with browser render cycle for zero latency
        if (!rafId) {
            rafId = requestAnimationFrame(() => {
                updateTransform();
                rafId = null;
            });
        }
    };

    const stopDrag = () => {
        if (isDragging) {
            isDragging = false;
            imgFull.style.cursor = 'grab';
        }
    };

    // Mouse Events
    imageModal.addEventListener('mousedown', (e) => {
        if (e.target === imgFull) {
            e.preventDefault(); // Prevents browser image dragging
            startDrag(e);
        }
    });
    window.addEventListener('mousemove', doDrag);
    window.addEventListener('mouseup', stopDrag);

    // Touch Events for Mobile
    imageModal.addEventListener('touchstart', (e) => {
        if (e.target === imgFull) startDrag(e);
    }, { passive: true });
    window.addEventListener('touchmove', doDrag, { passive: true });
    window.addEventListener('touchend', stopDrag);

    // Scroll to Zoom Logic
    imageModal.addEventListener('wheel', (e) => {
        if (imageModal.style.display !== 'flex') return;
        e.preventDefault();

        scale += e.deltaY * -0.0015;
        scale = Math.min(Math.max(0.5, scale), 2.5);

        updateTransform();
    }, { passive: false });
}

// Close Modal Logic (Only closes via close button)
if (closeBtn) {
    closeBtn.addEventListener('click', () => {
        imageModal.style.display = 'none';
    });
}