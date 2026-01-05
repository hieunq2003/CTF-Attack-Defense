document.addEventListener("DOMContentLoaded", () => {
    const canvas = document.getElementById("particles-bg");
    if (!canvas || !canvas.getContext) return;

    const ctx = canvas.getContext("2d");
    const particles = [];
    const NUM = 70;

    function resize() {
        const rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
    }

    window.addEventListener("resize", resize);
    resize();

    for (let i = 0; i < NUM; i++) {
        particles.push({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            vx: (Math.random() - 0.5) * 0.4,
            vy: (Math.random() - 0.5) * 0.4,
            r: 1 + Math.random() * 2
        });
    }

    function tick() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        ctx.fillStyle = "rgba(20, 255, 236, 0.8)";
        for (const p of particles) {
            p.x += p.vx;
            p.y += p.vy;

            if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
            if (p.y < 0 || p.y > canvas.height) p.vy *= -1;

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fill();
        }

        requestAnimationFrame(tick);
    }

    tick();
});
