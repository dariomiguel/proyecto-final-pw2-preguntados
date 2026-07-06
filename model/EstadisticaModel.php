<?php

class EstadisticaModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    /*
     * Mapea el filtro (dia/semana/mes/anio) a un intervalo de MySQL.
     * Lista blanca: el valor que viene del usuario nunca se concatena
     * directo en la query, así evitamos inyección SQL.
     */
    private function obtenerIntervalo($filtro)
    {
        switch ($filtro) {
            case 'dia':
                return 'INTERVAL 1 DAY';
            case 'semana':
                return 'INTERVAL 1 WEEK';
            case 'mes':
                return 'INTERVAL 1 MONTH';
            case 'anio':
                return 'INTERVAL 1 YEAR';
            default:
                return null; // sin filtro: histórico completo
        }
    }

    private function condicionFecha($columna, $filtro)
    {
        $intervalo = $this->obtenerIntervalo($filtro);
        if ($intervalo === null) {
            return '1 = 1';
        }
        return $columna . ' >= DATE_SUB(NOW(), ' . $intervalo . ')';
    }

    public function cantidadJugadores($filtro)
    {
        $sql = "SELECT COUNT(*) AS cantidad
                FROM usuarios
                WHERE rol = 'Jugador'
                  AND " . $this->condicionFecha('fecha_registro', $filtro);
        $resultado = $this->database->query($sql);
        return $resultado[0]['cantidad'];
    }

    public function cantidadPartidas($filtro)
    {
        $sql = "SELECT COUNT(*) AS cantidad
                FROM partidas
                WHERE " . $this->condicionFecha('fecha', $filtro);
        $resultado = $this->database->query($sql);
        return $resultado[0]['cantidad'];
    }

    public function cantidadPreguntasActivas($filtro)
    {
        $sql = "SELECT COUNT(*) AS cantidad
                FROM preguntas
                WHERE estado = 'aprobada'
                  AND " . $this->condicionFecha('fecha_creacion', $filtro);
        $resultado = $this->database->query($sql);
        return $resultado[0]['cantidad'];
    }

    /*
     * Preguntas sugeridas por jugadores comunes (no editores ni admin),
     * sin importar su estado (pendiente, aprobada o baja).
     */
    public function cantidadPreguntasCreadasPorUsuarios($filtro)
    {
        $sql = "SELECT COUNT(*) AS cantidad
                FROM preguntas p
                JOIN usuarios u ON u.id = p.creado_por_usuario_id
                WHERE u.rol = 'Jugador'
                  AND " . $this->condicionFecha('p.fecha_creacion', $filtro);
        $resultado = $this->database->query($sql);
        return $resultado[0]['cantidad'];
    }

    public function cantidadUsuariosNuevos($filtro)
    {
        $sql = "SELECT COUNT(*) AS cantidad
                FROM usuarios
                WHERE " . $this->condicionFecha('fecha_registro', $filtro);
        $resultado = $this->database->query($sql);
        return $resultado[0]['cantidad'];
    }

    /*
     * Porcentaje de preguntas respondidas correctamente por usuario.
     * Se calcula desde partidas, que ya guarda aciertos y
     * preguntas_respondidas por cada partida jugada.
     */
    public function porcentajeCorrectasPorUsuario($filtro)
    {
        $sql = "SELECT u.nombre_usuario,
                       SUM(p.preguntas_respondidas) AS respondidas,
                       SUM(p.aciertos) AS correctas,
                       ROUND(SUM(p.aciertos) * 100.0 / SUM(p.preguntas_respondidas), 1) AS porcentaje
                FROM partidas p
                JOIN usuarios u ON u.id = p.usuario_id
                WHERE p.preguntas_respondidas > 0
                  AND " . $this->condicionFecha('p.fecha', $filtro) . "
                GROUP BY u.id, u.nombre_usuario
                ORDER BY porcentaje DESC";
        return $this->database->query($sql);
    }

    public function usuariosPorPais($filtro)
    {
        $sql = "SELECT pais AS etiqueta, COUNT(*) AS cantidad
                FROM usuarios
                WHERE " . $this->condicionFecha('fecha_registro', $filtro) . "
                GROUP BY pais
                ORDER BY cantidad DESC";
        return $this->database->query($sql);
    }

    public function usuariosPorSexo($filtro)
    {
        $sql = "SELECT sexo AS etiqueta, COUNT(*) AS cantidad
                FROM usuarios
                WHERE " . $this->condicionFecha('fecha_registro', $filtro) . "
                GROUP BY sexo
                ORDER BY cantidad DESC";
        return $this->database->query($sql);
    }

    /*
     * Grupos de edad pedidos por el enunciado:
     *   Menores   -> menos de 18 años
     *   Jubilados -> 65 años o más
     *   Medio     -> el resto
     * La edad se calcula con el anio_nacimiento del registro.
     */
    public function usuariosPorGrupoEdad($filtro)
    {
        $sql = "SELECT
                    CASE
                        WHEN (YEAR(CURDATE()) - anio_nacimiento) < 18 THEN 'Menores'
                        WHEN (YEAR(CURDATE()) - anio_nacimiento) >= 65 THEN 'Jubilados'
                        ELSE 'Medio'
                    END AS etiqueta,
                    COUNT(*) AS cantidad
                FROM usuarios
                WHERE " . $this->condicionFecha('fecha_registro', $filtro) . "
                GROUP BY etiqueta
                ORDER BY cantidad DESC";
        return $this->database->query($sql);
    }
}
