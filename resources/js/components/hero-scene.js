import * as THREE from 'three';

/**
 * Ambient 3D scene for the public site's hero section - a handful of soft,
 * organic "seed pod" forms in the clinic's sage/amber palette, gently
 * rotating and bobbing. Purely decorative (pointer-events-none), sits
 * behind the hero text/image as a full-bleed canvas.
 *
 * Lifecycle is manual (init/destroy) rather than tied to a framework,
 * because this canvas lives inside a Livewire page (Home) that gets torn
 * down and rebuilt by wire:navigate SPA transitions - see the
 * livewire:navigating/livewire:navigated wiring in app.js. Leaving a
 * WebGL context and rAF loop running after the canvas element is gone
 * leaks GPU memory and piles up "too many WebGL contexts" warnings.
 */

const SAGE = 0x4f7a44;
const SAGE_LIGHT = 0x93b986;
const AMBER = 0xc97a3b;
const CREAM = 0xfaf5ea;

function makeOrganicGeometry(radius, detail, jitter) {
    const geometry = new THREE.IcosahedronGeometry(radius, detail);
    const position = geometry.attributes.position;
    const vertex = new THREE.Vector3();

    for (let i = 0; i < position.count; i++) {
        vertex.fromBufferAttribute(position, i);
        const offset = 1 + (Math.sin(vertex.x * 3) + Math.cos(vertex.y * 4) + Math.sin(vertex.z * 5)) * jitter;
        vertex.multiplyScalar(offset);
        position.setXYZ(i, vertex.x, vertex.y, vertex.z);
    }

    geometry.computeVertexNormals();

    return geometry;
}

export function initHeroScene(container) {
    if (!container || typeof WebGLRenderingContext === 'undefined') {
        return null;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let width = container.clientWidth;
    let height = container.clientHeight;

    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(width, height);
    renderer.domElement.setAttribute('aria-hidden', 'true');
    container.appendChild(renderer.domElement);

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 100);
    camera.position.set(0, 0, 11);

    scene.add(new THREE.AmbientLight(CREAM, 1.1));
    const key = new THREE.DirectionalLight(0xffffff, 1.1);
    key.position.set(4, 6, 8);
    scene.add(key);
    const rim = new THREE.PointLight(AMBER, 0.6, 20);
    rim.position.set(-5, -2, 4);
    scene.add(rim);

    const group = new THREE.Group();
    scene.add(group);

    // The hero image is an opaque card sitting on top of this canvas, so
    // anything placed directly behind it is invisible regardless of WebGL
    // depth - only the cream margins above/below/beside it actually show.
    // Kept right-of-center (positive x) to stay clear of the headline text
    // column too, so shapes read as ambient company for the image rather
    // than clutter behind either the copy or the image itself.
    const blobConfigs = [
        { radius: 0.85, color: SAGE, x: 3.4, y: 4.3, z: -1, speed: 0.6, phase: 0 },
        { radius: 0.55, color: AMBER, x: 6.6, y: 3.4, z: -1, speed: 0.8, phase: 1.3 },
        { radius: 0.6, color: SAGE_LIGHT, x: 1.6, y: -4.2, z: -1, speed: 1.1, phase: 2.6 },
        { radius: 0.4, color: SAGE, x: 6.8, y: -3.6, z: -1.5, speed: 0.9, phase: 4.1 },
        { radius: 0.35, color: AMBER, x: 4.6, y: -4.6, z: -1, speed: 1.3, phase: 5.2 },
        { radius: 0.3, color: SAGE_LIGHT, x: 7.4, y: 0.4, z: -1.5, speed: 1.0, phase: 3.4 },
    ];

    const material = (color) => new THREE.MeshStandardMaterial({
        color,
        roughness: 0.6,
        metalness: 0.05,
        transparent: true,
        opacity: 0.4,
    });

    const meshes = blobConfigs.map((config) => {
        const mesh = new THREE.Mesh(
            makeOrganicGeometry(config.radius, 2, 0.09),
            material(config.color),
        );
        mesh.position.set(config.x, config.y, config.z);
        mesh.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, 0);
        group.add(mesh);

        return { mesh, ...config };
    });

    let pointerX = 0;
    let pointerY = 0;

    function handlePointerMove(event) {
        const rect = container.getBoundingClientRect();
        pointerX = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        pointerY = ((event.clientY - rect.top) / rect.height) * 2 - 1;
    }

    if (!prefersReducedMotion) {
        window.addEventListener('pointermove', handlePointerMove, { passive: true });
    }

    function handleResize() {
        width = container.clientWidth;
        height = container.clientHeight;
        if (!width || !height) return;
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height);
    }

    const resizeObserver = new ResizeObserver(handleResize);
    resizeObserver.observe(container);

    let frameId = null;
    const clock = new THREE.Clock();

    function render() {
        const elapsed = clock.getElapsedTime();

        meshes.forEach(({ mesh, speed, phase }) => {
            mesh.rotation.x = elapsed * 0.15 * speed + phase;
            mesh.rotation.y = elapsed * 0.2 * speed + phase;
            mesh.position.y += Math.sin(elapsed * speed + phase) * 0.0015;
        });

        group.rotation.y += (pointerX * 0.25 - group.rotation.y) * 0.02;
        group.rotation.x += (-pointerY * 0.12 - group.rotation.x) * 0.02;

        renderer.render(scene, camera);
    }

    if (prefersReducedMotion) {
        render();
    } else {
        const loop = () => {
            render();
            frameId = requestAnimationFrame(loop);
        };
        loop();
    }

    return {
        destroy() {
            if (frameId !== null) cancelAnimationFrame(frameId);
            window.removeEventListener('pointermove', handlePointerMove);
            resizeObserver.disconnect();
            meshes.forEach(({ mesh }) => {
                mesh.geometry.dispose();
                mesh.material.dispose();
            });
            renderer.dispose();
            renderer.forceContextLoss();
            if (renderer.domElement.parentNode) {
                renderer.domElement.parentNode.removeChild(renderer.domElement);
            }
        },
    };
}
