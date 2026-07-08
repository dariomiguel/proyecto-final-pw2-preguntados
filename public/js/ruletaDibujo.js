/* ============================================================
 * MÓDULO 2: DIBUJO (ruletaDibujo.js)
 * ------------------------------------------------------------
 * Todo lo que toca el <canvas>. No sabe nada del juego ni del
 * servidor: solo sabe dibujar la rueda en un ángulo dado.
 *
 * Funciones públicas:
 *   - redimensionar(): ajusta el canvas al tamaño del contenedor
 *     y a la densidad de píxeles de la pantalla (dpr).
 *   - dibujar(rot): dibuja la rueda completa rotada `rot` radianes.
 *   - normalizarAngulo(a): lleva cualquier ángulo al rango [0, 2π).
 * ============================================================ */
Ruleta.dibujo = (function () {

    const canvas = document.getElementById('wheel');
    const ctx = canvas.getContext('2d');
    const dpr = Math.max(1, window.devicePixelRatio || 1);

    function normalizarAngulo(a) {
        const dosPi = Math.PI * 2;
        return ((a % dosPi) + dosPi) % dosPi;
    }

    function redimensionar() {
        const contenedor = canvas.parentElement;
        const rect = contenedor.getBoundingClientRect();
        const lado = Math.floor(Math.min(rect.width, rect.height));
        canvas.width = Math.floor(lado * dpr);
        canvas.height = Math.floor(lado * dpr);
        canvas.style.width = lado + 'px';
        canvas.style.height = lado + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        dibujar(Ruleta.rotacion);
    }

    function dibujar(rot) {
        const w = canvas.width / dpr;
        const h = canvas.height / dpr;
        const cx = w / 2;
        const cy = h / 2;
        const radio = Math.min(w, h) * 0.42;
        ctx.clearRect(0, 0, w, h);

        // Fondo con degradado radial suave detrás de la rueda
        const fondo = ctx.createRadialGradient(cx, cy - 40, 20, cx, cy, radio * 1.55);
        fondo.addColorStop(0, 'rgba(255,255,255,.95)');
        fondo.addColorStop(1, 'rgba(240,245,255,.55)');
        ctx.fillStyle = fondo;
        ctx.beginPath();
        ctx.arc(cx, cy, radio * 1.23, 0, Math.PI * 2);
        ctx.fill();

        // --- Sectores de colores (uno por categoría de la BD) ---
        ctx.save();
        ctx.translate(cx, cy);
        ctx.rotate(rot);
        ctx.shadowColor = 'rgba(45, 60, 100, .15)';
        ctx.shadowBlur = 18;
        ctx.shadowOffsetY = 10;

        for (let i = 0; i < Ruleta.cantidad; i++) {
            const desde = Ruleta.anguloInicial + i * Ruleta.anguloSegmento;
            const hasta = desde + Ruleta.anguloSegmento;
            ctx.beginPath();
            ctx.moveTo(0, 0);
            ctx.arc(0, 0, radio, desde, hasta);
            ctx.closePath();
            ctx.fillStyle = Ruleta.colores[i];
            ctx.fill();
            ctx.shadowColor = 'transparent';
            ctx.lineWidth = 8;
            ctx.strokeStyle = 'rgba(255,255,255,.88)';
            ctx.stroke();
        }

        // Círculo blanco del centro
        ctx.beginPath();
        ctx.arc(0, 0, radio * 0.18, 0, Math.PI * 2);
        ctx.fillStyle = '#ffffff';
        ctx.fill();
        ctx.lineWidth = 8;
        ctx.strokeStyle = 'rgba(120, 140, 190, .18)';
        ctx.stroke();

        // --- Nombres de las categorías sobre cada sector ---
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = '#24324a';
        ctx.font = '800 ' + Math.max(14, radio * 0.085) + 'px system-ui, sans-serif';

        for (let i = 0; i < Ruleta.cantidad; i++) {
            const anguloMedio = Ruleta.anguloInicial + (i + 0.5) * Ruleta.anguloSegmento;
            const radioTexto = radio * 0.67;
            ctx.save();
            ctx.rotate(anguloMedio);
            ctx.translate(radioTexto, 0);

            // Si el texto quedaría "cabeza abajo" (mitad izquierda de la
            // rueda), se lo rota 180° extra para que siempre se lea bien.
            let rotacionTexto = Math.PI / 2;
            const medioNormalizado = normalizarAngulo(anguloMedio);
            if (medioNormalizado > Math.PI / 2 && medioNormalizado < Math.PI * 1.5) {
                rotacionTexto += Math.PI;
            }
            ctx.rotate(rotacionTexto);
            ctx.fillText(Ruleta.nombres[i], 0, 0);
            ctx.restore();
        }

        ctx.restore();

        // Aro blanco exterior
        ctx.beginPath();
        ctx.arc(cx, cy, radio, 0, Math.PI * 2);
        ctx.lineWidth = 10;
        ctx.strokeStyle = 'rgba(255,255,255,.95)';
        ctx.stroke();

        // Tapa del centro con degradado (arriba del botón GIRAR)
        const centro = ctx.createRadialGradient(cx - 8, cy - 10, 8, cx, cy, radio * 0.24);
        centro.addColorStop(0, '#ffffff');
        centro.addColorStop(1, '#edf3ff');
        ctx.beginPath();
        ctx.arc(cx, cy, radio * 0.18, 0, Math.PI * 2);
        ctx.fillStyle = centro;
        ctx.fill();
    }

    return {
        redimensionar: redimensionar,
        dibujar: dibujar,
        normalizarAngulo: normalizarAngulo
    };
})();
