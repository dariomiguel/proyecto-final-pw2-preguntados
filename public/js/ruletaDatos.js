/* ============================================================
 * MÓDULO 1: DATOS Y ESTADO
 * ------------------------------------------------------------
 * Única fuente de verdad de la ruleta. Lee las categorías que
 * el servidor inyectó en window.RULETA_STATE.categorias (que a
 * su vez salen de la tabla `categorias` de la BD).
 * ============================================================ */
window.Ruleta = (function () {

    const categorias = window.RULETA_STATE.categorias || [];

    return {
        // Listas derivadas de la BD
        nombres: categorias.map(function (c) { return c.nombre; }),
        colores: categorias.map(function (c) { return c.color_secundario; }),
        cantidad: categorias.length,

        // Geometría de la ruleta (depende de cuántas categorías haya)
        anguloSegmento: (Math.PI * 2) / categorias.length,
        anguloInicial: -Math.PI / 2, // el sector 0 arranca arriba, donde está el puntero

        // Estado compartido entre módulos
        rotacion: 0,      // ángulo actual de la rueda
        girando: false    // evita doble click en GIRAR
    };
})();
