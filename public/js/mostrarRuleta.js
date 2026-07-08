/* ============================================================
 * MÓDULO 4: ORQUESTADOR (mostrarRuleta.js)
 * ------------------------------------------------------------
 * Conecta el servidor, la animación y la interfaz. Es el ÚNICO 
 * módulo que habla con el backend y el único que toca el modal
 * y los eventos.
 *
 * Flujo del giro:
 *   1. Click en GIRAR → fetch a /partida/girarRuleta
 *   2. El SERVIDOR elige la categoría (la lógica está en PHP,
 *      el cliente solo anima)
 *   3. Se anima la rueda hasta el índice que respondió el server
 *   4. Se muestra el modal con la categoría → link a /partida/jugar
 * ============================================================ */
(function () {

    const botonGirar = document.getElementById('spinBtn');
    const modal = document.getElementById('modal');
    const cerrarModal = document.getElementById('closeModal');
    const etiquetaResultado = document.getElementById('resultBadge');

    /* ---------- Modal de resultado ---------- */

    function abrirModal(nombreCategoria) {
        etiquetaResultado.textContent = nombreCategoria;
        modal.classList.add('open');
    }

    function cerrarResultado() {
        modal.classList.remove('open');
    }

    /* ---------- Comunicación con el servidor ---------- */

    /*
     * Pide al backend que elija (o devuelva la ya elegida) categoría.
     * Respuesta esperada:
     *   { idCategoria, nombreCategoria, indiceRuleta, bloqueada }
     * - indiceRuleta: posición del sector en la rueda (0..n-1)
     * - bloqueada: true si ya había una categoría fijada en sesión
     */
    async function pedirCategoriaAlServidor() {
        const respuesta = await fetch('/partida/girarRuleta', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!respuesta.ok) {
            throw new Error('Error del servidor');
        }
        return respuesta.json();
    }

    /* ---------- Acción principal: girar ---------- */

    async function girar() {
        if (Ruleta.girando) return;
        Ruleta.girando = true;
        botonGirar.disabled = true;

        let datos;
        try {
            datos = await pedirCategoriaAlServidor();
        } catch (error) {
            console.error('No se pudo obtener la categoría:', error);
            Ruleta.girando = false;
            botonGirar.disabled = false;
            return;
        }

        // Si la sesión ya tenía categoría, no se gira: se posiciona
        // la rueda ahí y se informa al usuario.
        if (datos.bloqueada) {
            Ruleta.animacion.posicionarEnIndice(datos.indiceRuleta);
            Ruleta.girando = false;
            botonGirar.disabled = false;
            abrirModal(datos.nombreCategoria);
            return;
        }

        await Ruleta.animacion.animarHaciaIndice(datos.indiceRuleta);
        Ruleta.girando = false;
        botonGirar.disabled = false;
        abrirModal(datos.nombreCategoria);
    }

    /* ---------- Inicialización y eventos ---------- */

    function inicializar() {
        Ruleta.dibujo.redimensionar();

        const estado = window.RULETA_STATE;

        // Al recargar la página con una categoría ya fijada en sesión,
        // la rueda aparece directamente en ese sector.
        if (estado.categoriaFijada && estado.indiceRuleta !== null) {
            Ruleta.animacion.posicionarEnIndice(estado.indiceRuleta);
            setTimeout(function () { abrirModal(estado.nombreCategoria); }, 300);
            botonGirar.disabled = true;
            botonGirar.title = 'Ya hay una categoría seleccionada';
        }
    }

    botonGirar.addEventListener('click', girar);
    cerrarModal.addEventListener('click', cerrarResultado);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) cerrarResultado();
    });
    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') cerrarResultado();
    });
    window.addEventListener('resize', function () {
        Ruleta.dibujo.redimensionar();
        if (window.RULETA_STATE.categoriaFijada) {
            Ruleta.animacion.posicionarEnIndice(window.RULETA_STATE.indiceRuleta);
        }
    });

    inicializar();
})();
