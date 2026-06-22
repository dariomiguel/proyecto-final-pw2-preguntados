(() => {
    const canvas = document.getElementById("wheel");
    const ctx = canvas.getContext("2d");
    const spinBtn = document.getElementById("spinBtn");
    const modal = document.getElementById("modal");
    const closeModal = document.getElementById("closeModal");
    const resultBadge = document.getElementById("resultBadge");

    const options = [
        "Geografía",
        "Historia",
        "Arte",
        "Ciencia",
        "Entretenimiento",
        "Deportes",
        "Programación"
    ];

    const colors = [
        "#62AFE3",
        "#F4D03F",
        "#B27FC7",
        "#54D88C",
        "#EB9A51",
        "#E74C3C",
        "#9F94EF"
    ];

    const segmentCount = options.length;
    const segmentAngle = (Math.PI * 2) / segmentCount;
    const startAngle = -Math.PI / 2;
    const centerX = () => canvas.width / 2;
    const centerY = () => canvas.height / 2;

    let rotation = 0;        // radianes
    let spinning = false;
    let dpr = Math.max(1, window.devicePixelRatio || 1);

    function resizeCanvas() {
        const shell = canvas.parentElement;
        const rect = shell.getBoundingClientRect();
        const size = Math.floor(Math.min(rect.width, rect.height));
        canvas.width = Math.floor(size * dpr);
        canvas.height = Math.floor(size * dpr);
        canvas.style.width = size + "px";
        canvas.style.height = size + "px";
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        drawWheel(rotation);
    }

    function normalizeAngle(a) {
        const twoPi = Math.PI * 2;
        return ((a % twoPi) + twoPi) % twoPi;
    }

    function drawWheel(rot) {
        const w = canvas.width / dpr;
        const h = canvas.height / dpr;
        const cx = w / 2;
        const cy = h / 2;
        const radius = Math.min(w, h) * 0.42;
        ctx.clearRect(0, 0, w, h);

        // Fondo suave
        const bg = ctx.createRadialGradient(cx, cy - 40, 20, cx, cy, radius * 1.55);
        bg.addColorStop(0, "rgba(255,255,255,.95)");
        bg.addColorStop(1, "rgba(240,245,255,.55)");
        ctx.fillStyle = bg;
        ctx.beginPath();
        ctx.arc(cx, cy, radius * 1.23, 0, Math.PI * 2);
        ctx.fill();

        ctx.save();
        ctx.translate(cx, cy);
        ctx.rotate(rot);

        // Sombra suave del disco
        ctx.shadowColor = "rgba(45, 60, 100, .15)";
        ctx.shadowBlur = 18;
        ctx.shadowOffsetY = 10;

        // Segmentos
        for (let i = 0; i < segmentCount; i++) {
            const a0 = startAngle + i * segmentAngle;
            const a1 = a0 + segmentAngle;

            ctx.beginPath();
            ctx.moveTo(0, 0);
            ctx.arc(0, 0, radius, a0, a1);
            ctx.closePath();
            ctx.fillStyle = colors[i % colors.length];
            ctx.fill();

            ctx.shadowColor = "transparent";
            ctx.lineWidth = 8;
            ctx.strokeStyle = "rgba(255,255,255,.88)";
            ctx.stroke();
        }

    // Centro del disco
    ctx.beginPath();
    ctx.arc(0, 0, radius * 0.18, 0, Math.PI * 2);
    ctx.fillStyle = "#ffffff";
    ctx.fill();
    ctx.lineWidth = 8;
    ctx.strokeStyle = "rgba(120, 140, 190, .18)";
    ctx.stroke();

    // Etiquetas
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillStyle = "#24324a";
    ctx.font = `800 ${Math.max(14, radius * 0.085)}px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif`;

    for (let i = 0; i < segmentCount; i++) {
        const mid = startAngle + (i + 0.5) * segmentAngle;
        const labelRadius = radius * 0.67;

        ctx.save();
        ctx.rotate(mid);
        ctx.translate(labelRadius, 0);

        // Mantener el texto legible en la mitad inferior
        let labelRotation = Math.PI / 2;
        const normalizedMid = normalizeAngle(mid);
        if (normalizedMid > Math.PI / 2 && normalizedMid < (Math.PI * 1.5)) {
        labelRotation += Math.PI;
    }

    ctx.rotate(labelRotation);
    ctx.fillText(options[i], 0, 0);
    ctx.restore();
}

    ctx.restore();

    // Anillo exterior
    ctx.beginPath();
    ctx.arc(cx, cy, radius, 0, Math.PI * 2);
    ctx.lineWidth = 10;
    ctx.strokeStyle = "rgba(255,255,255,.95)";
    ctx.stroke();

    // Centro brillante
    const hub = ctx.createRadialGradient(cx - 8, cy - 10, 8, cx, cy, radius * 0.24);
        hub.addColorStop(0, "#ffffff");
        hub.addColorStop(1, "#edf3ff");
        ctx.beginPath();
        ctx.arc(cx, cy, radius * 0.18, 0, Math.PI * 2);
        ctx.fillStyle = hub;
        ctx.fill();
    }

    function easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }

    function openModal(resultIndex) {
        resultBadge.textContent = options[resultIndex];
        modal.classList.add("open");

        let idCategoria = parseInt(resultIndex) + 1;
        console.log("La id de la categoría es: " + idCategoria);
        console.log("La categoría fue: " + options[resultIndex]);


        // Actualizar el enlace con el idCategoria
        const closeLink = document.getElementById('closeModal');
        closeLink.href = '/partida/jugar?idCategoria=' + idCategoria;
        // Si tu ruta espera el id como parte de la URL, usa:
        // closeLink.href = '/partida/jugar/' + idCategoria
    }

    function closeResult() {
        modal.classList.remove("open");

    }

    function spin() {
    if (spinning) return;
    spinning = true;
    spinBtn.disabled = true;

    const selectedIndex = Math.floor(Math.random() * segmentCount);
    const currentMod = normalizeAngle(rotation);
    const targetMod = normalizeAngle(-(selectedIndex + 0.5) * segmentAngle);

    const extraTurns = 6 + Math.floor(Math.random() * 2); // 6 o 7 vueltas completas
    const deltaToTarget = normalizeAngle(targetMod - currentMod);
    const totalDelta = deltaToTarget + extraTurns * Math.PI * 2;

    const startRotation = rotation;
    const finalRotation = rotation + totalDelta;
    const duration = 5000 + Math.random() * 900;
    const startTime = performance.now();

    function animate(now) {
    const elapsed = now - startTime;
    const t = Math.min(1, elapsed / duration);
    const eased = easeOutCubic(t);

    rotation = startRotation + (finalRotation - startRotation) * eased;
    drawWheel(rotation);

    if (t < 1) {
        requestAnimationFrame(animate);
    } else {
        rotation = finalRotation;
        drawWheel(rotation);
        spinning = false;
        spinBtn.disabled = false;
        openModal(selectedIndex);
    }
}

    requestAnimationFrame(animate);
}

    spinBtn.addEventListener("click", spin);
    closeModal.addEventListener("click", closeResult);
    modal.addEventListener("click", (e) => {
    if (e.target === modal) closeResult();
});

    window.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeResult();
});

    window.addEventListener("resize", resizeCanvas);

    // Primer render
    resizeCanvas();
    drawWheel(rotation);
})();
