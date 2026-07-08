/* ============================================================
 * MÓDULO 3: ANIMACIÓN (ruletaAnimacion.js)
 * ------------------------------------------------------------
 * La matemática del giro. Tampoco sabe nada del servidor:
 * recibe un índice de sector y lleva la rueda hasta ahí.
 *
 * Funciones públicas:
 *   - posicionarEnIndice(i): salta SIN animar al sector i
 *     (se usa cuando ya había una categoría fijada en sesión).
 *   - animarHaciaIndice(i): gira con animación hasta el sector i
 *     y devuelve una Promise que se resuelve al terminar.
 * ============================================================ */
Ruleta.animacion = (function () {

    /*
     * Curva de desaceleración: arranca rápido y frena suave,
     * como una ruleta real. t va de 0 a 1.
     */
    function easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }

    /*
     * Ángulo en el que el CENTRO del sector i queda bajo el puntero.
     * Es negativo porque la rueda gira en sentido contrario para
     * "traer" el sector hacia arriba.
     */
    function anguloDelIndice(indice) {
        return -(indice + 0.5) * Ruleta.anguloSegmento;
    }

    function posicionarEnIndice(indice) {
        Ruleta.rotacion = anguloDelIndice(indice);
        Ruleta.dibujo.dibujar(Ruleta.rotacion);
    }

    function animarHaciaIndice(indice) {
        const norm = Ruleta.dibujo.normalizarAngulo;

        const rotacionActual = norm(Ruleta.rotacion);
        const rotacionObjetivo = norm(anguloDelIndice(indice));

        // Cuánto falta para llegar al objetivo + 6 o 7 vueltas
        // completas extra para que el giro se sienta largo.
        const vueltasExtra = 6 + Math.floor(Math.random() * 2);
        const faltante = norm(rotacionObjetivo - rotacionActual);
        const recorridoTotal = faltante + vueltasExtra * Math.PI * 2;

        const rotacionInicio = Ruleta.rotacion;
        const rotacionFinal = Ruleta.rotacion + recorridoTotal;
        const duracion = 5000 + Math.random() * 900; // 5 a 5,9 segundos
        const tiempoInicio = performance.now();

        return new Promise(function (resolve) {
            function cuadro(ahora) {
                const transcurrido = ahora - tiempoInicio;
                const t = Math.min(1, transcurrido / duracion);

                Ruleta.rotacion = rotacionInicio + (rotacionFinal - rotacionInicio) * easeOutCubic(t);
                Ruleta.dibujo.dibujar(Ruleta.rotacion);

                if (t < 1) {
                    requestAnimationFrame(cuadro);
                } else {
                    Ruleta.rotacion = rotacionFinal;
                    Ruleta.dibujo.dibujar(Ruleta.rotacion);
                    resolve();
                }
            }
            requestAnimationFrame(cuadro);
        });
    }

    return {
        posicionarEnIndice: posicionarEnIndice,
        animarHaciaIndice: animarHaciaIndice
    };
})();
