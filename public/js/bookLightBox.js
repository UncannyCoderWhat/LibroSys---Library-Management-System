const bookCover = document.getElementById('mainBookCover');
const imageModal = document.getElementById('imageModal');
const imgFull = document.getElementById('imgFull');
const closeBtn = document.querySelector('.lightbox-close');

let scene, camera, renderer, bookMesh;
let isDragging = false;
let previousMousePosition = { x: 0, y: 0 };

// Generates procedural page lines and edge shadows with a grayish paper tone
function createPagesTexture(isVerticalLines) {
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 512;
    const ctx = canvas.getContext('2d');

    // Soft gray base paper color
    ctx.fillStyle = '#d8dcdb';
    ctx.fillRect(0, 0, 512, 512);

    // Render individual page lines in a darker gray
    ctx.fillStyle = '#9da3a1';
    if (isVerticalLines) {
        for (let x = 0; x < 512; x += 3) {
            ctx.fillRect(x, 0, 1, 512);
        }
    } else {
        for (let y = 0; y < 512; y += 3) {
            ctx.fillRect(0, y, 512, 1);
        }
    }

    // Edge shadow/aging gradient for depth
    const grad = ctx.createLinearGradient(0, 0, 512, 512);
    grad.addColorStop(0, 'rgba(0,0,0,0.15)');
    grad.addColorStop(0.15, 'rgba(0,0,0,0)');
    grad.addColorStop(0.85, 'rgba(0,0,0,0)');
    grad.addColorStop(1, 'rgba(0,0,0,0.15)');
    ctx.fillStyle = grad;
    ctx.fillRect(0, 0, 512, 512);

    return new THREE.CanvasTexture(canvas);
}

function init3D() {
    if (renderer) return;

    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(45, imgFull.clientWidth / imgFull.clientHeight, 0.1, 1000);
    camera.position.z = 7;

    renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(imgFull.clientWidth, imgFull.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    imgFull.appendChild(renderer.domElement);

    const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
    scene.add(ambientLight);

    const dirLight = new THREE.DirectionalLight(0xffffff, 0.6);
    dirLight.position.set(5, 5, 5);
    scene.add(dirLight);

    const domEl = renderer.domElement;

    // Drag to rotate
    domEl.addEventListener('mousedown', (e) => {
        isDragging = true;
        previousMousePosition = { x: e.clientX, y: e.clientY };
    });

    window.addEventListener('mousemove', (e) => {
        if (!isDragging || !bookMesh) return;
        const deltaX = e.clientX - previousMousePosition.x;
        const deltaY = e.clientY - previousMousePosition.y;

        bookMesh.rotation.y += deltaX * 0.01;
        bookMesh.rotation.x += deltaY * 0.01;

        previousMousePosition = { x: e.clientX, y: e.clientY };
    });

    window.addEventListener('mouseup', () => { isDragging = false; });

    // Scroll wheel to zoom
    domEl.addEventListener('wheel', (e) => {
        e.preventDefault();
        camera.position.z = Math.min(Math.max(3, camera.position.z + e.deltaY * 0.005), 15);
    }, { passive: false });

    // Window resize handler
    window.addEventListener('resize', () => {
        if (renderer && camera && imgFull) {
            camera.aspect = imgFull.clientWidth / imgFull.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(imgFull.clientWidth, imgFull.clientHeight);
        }
    });

    function animate() {
        requestAnimationFrame(animate);
        renderer.render(scene, camera);
    }
    animate();
}

function createBook(textures) {
    if (bookMesh) scene.remove(bookMesh);

    // Dimensions: [Cover Width, Height, Spine Thickness]
    const geometry = new THREE.BoxGeometry(2.8, 4, 0.5);
    const textureLoader = new THREE.TextureLoader();

    const rightPageMaterial = new THREE.MeshStandardMaterial({ map: createPagesTexture(false), roughness: 0.9 });
    const topBottomPageMaterial = new THREE.MeshStandardMaterial({ map: createPagesTexture(true), roughness: 0.9 });
    const fallbackMaterial = new THREE.MeshStandardMaterial({ color: 0x222222 });

    const materials = [
        rightPageMaterial, // Right (+X) -> Pages
        textures.spine ? new THREE.MeshStandardMaterial({ map: textureLoader.load(textures.spine) }) : fallbackMaterial, // Left (-X) -> Spine
        topBottomPageMaterial, // Top (+Y) -> Pages
        topBottomPageMaterial, // Bottom (-Y) -> Pages
        textures.front ? new THREE.MeshStandardMaterial({ map: textureLoader.load(textures.front) }) : fallbackMaterial, // Front (+Z) -> Cover
        textures.back ? new THREE.MeshStandardMaterial({ map: textureLoader.load(textures.back) }) : fallbackMaterial // Back (-Z) -> Cover
    ];

    bookMesh = new THREE.Mesh(geometry, materials);
    bookMesh.rotation.y = -0.5;
    bookMesh.rotation.x = 0.2;
    scene.add(bookMesh);
}

async function loadAndDisplayBook(src) {
    imageModal.style.display = 'flex';
    init3D();

    let blob;
    let filename = '';

    if (typeof src === 'string') {
        filename = src.split('?')[0];
        try {
            const response = await fetch(src);
            blob = await response.blob();
        } catch (err) {
            console.error('Failed to load file from URL:', err);
            return;
        }
    } else if (src instanceof File || src instanceof Blob) {
        blob = src;
        filename = src.name || '';
    }

    if (filename.toLowerCase().endsWith('.zip')) {
        const zip = await JSZip.loadAsync(blob);
        const textures = {};

        for (const name of Object.keys(zip.files)) {
            const lower = name.toLowerCase();
            if (lower.includes('front')) {
                const fileBlob = await zip.files[name].async('blob');
                textures.front = URL.createObjectURL(fileBlob);
            } else if (lower.includes('back')) {
                const fileBlob = await zip.files[name].async('blob');
                textures.back = URL.createObjectURL(fileBlob);
            } else if (lower.includes('spine')) {
                const fileBlob = await zip.files[name].async('blob');
                textures.spine = URL.createObjectURL(fileBlob);
            }
        }
        createBook(textures);
    } else {
        const imageUrl = URL.createObjectURL(blob);
        createBook({ front: imageUrl });
    }
}

if (bookCover && imageModal && imgFull) {
    bookCover.addEventListener('click', () => {
        const source = bookCover.dataset.fullCover || bookCover.src;
        loadAndDisplayBook(source);
    });
}

if (closeBtn) {
    closeBtn.addEventListener('click', () => {
        imageModal.style.display = 'none';
    });
}