<?php

class PartidaModel
{

    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function obtenerPreguntaAleatoria($usuarioId, $categoriaId)
    {

        $pregunta = $this->obtenerPreguntaNoVistaAleatoria($usuarioId, $categoriaId);

        if ($pregunta == null) {

            $sql = "
            SELECT
                p.id,
                p.enunciado,
                c.nombre AS nombre_categoria,
                c.color AS color_categoria,
                p.total_respuestas,
                p.total_aciertos,
                c.color_secundario AS color_categoria_sec
            FROM preguntas p
            INNER JOIN categorias c
                ON p.categoria_id = c.id
            WHERE p.estado = 'aprobada'
            AND c.id = ?
            ORDER BY RAND()
            LIMIT 1
        ";

            $pregunta = $this->database->query($sql, [$categoriaId]);

            if (empty($pregunta)) {
                return null;
            }

            $pregunta = $pregunta[0];
        }

        $sqlRespuestas = "
        SELECT
            id,
            texto,
            es_correcta
        FROM respuestas
        WHERE pregunta_id = ?
        ORDER BY RAND()
    ";

        $pregunta['respuestas'] = $this->database->query($sqlRespuestas, [$pregunta['id']]);

        return $pregunta;
    }

    public function obtenerRespuesta($idRespuesta)
    {
        $sql = "
        SELECT
            es_correcta
        FROM respuestas
        WHERE id = ?
    ";

        $resultado = $this->database->query($sql, [$idRespuesta]);

        if (empty($resultado)) {
            return null;
        }

        return $resultado[0];
    }

    public function obtenerTextoRespuestaCorrecta($idRespuesta)
    {

        $sql = "
        SELECT texto
        FROM respuestas
        WHERE pregunta_id = ?
          AND es_correcta = 1
        LIMIT 1
    ";

        $resultado = $this->database->query($sql, [$idRespuesta]);

        if (empty($resultado)) {
            return "";
        }

        return $resultado[0];
    }

    public function guardarPreguntaVista($usuarioId, $preguntaId)
    {

        $sql = "
        INSERT INTO usuarios_preguntas_vistas
            (usuario_id, pregunta_id)
        VALUES
            (?, ?)
    ";

        $this->database->execute($sql, [$usuarioId, $preguntaId]);
    }

    public function buscarPreguntasNoVistas($usuarioId)
    {

        $sql = "
        SELECT
            p.id,
            p.enunciado,
            p.total_respuestas, 
            p.total_aciertos,
            c.nombre AS nombre_categoria,
            c.color AS color_categoria,
            c.color_secundario AS color_categoria_sec
        FROM preguntas p
        INNER JOIN categorias c
            ON p.categoria_id = c.id
        WHERE p.estado = ?
        AND p.id NOT IN (
            SELECT pregunta_id
            FROM usuarios_preguntas_vistas
            WHERE usuario_id = ?
        )
    ";

        return $this->database->query($sql, ['aprobada', $usuarioId]);
    }

    public function obtenerPreguntaNoVistaAleatoria($usuarioId, $categoriaId)
    {

        $sql = "
        SELECT
            p.id,
            p.enunciado,
            p.total_respuestas, 
            p.total_aciertos,
            c.nombre AS nombre_categoria,
            c.color AS color_categoria,
            c.color_secundario AS color_categoria_sec
        FROM preguntas p
        INNER JOIN categorias c
            ON p.categoria_id = c.id
        WHERE p.estado = 'aprobada'
        AND c.id = ?
        AND p.id NOT IN (
            SELECT pregunta_id
            FROM usuarios_preguntas_vistas
            WHERE usuario_id = ?
        )
        ORDER BY RAND()
        LIMIT 1
    ";

        $pregunta = $this->database->query($sql,[$categoriaId, $usuarioId]);
        if (empty($pregunta)) {
            return null;
        }

        return $pregunta[0];
    }

    public function verPreguntasYaEchasAlUsuario($usuarioId){

        $sql = "
        SELECT pregunta_id
        FROM usuarios_preguntas_vistas
        WHERE usuario_id = ?
    ";

        return $this->database->query($sql, [$usuarioId]);
    }

    public function cantidadPreguntasYaEchasAlUsuario($usuarioId)
    {
        $sql = "
        SELECT COUNT(*) AS vistas
        FROM usuarios_preguntas_vistas
        WHERE usuario_id = ?;
        ";

        return $this->database->query($sql, [$usuarioId]);
    }

    public function cantidadPreguntasEnBD($usuarioId)
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM preguntas
            WHERE estado = 'aprobada';
        ";

        return $this->database->query($sql);
    }

    public function obtenerRespuestaCorrecta($idPregunta)
    {
        $sql = "
        SELECT id
        FROM respuestas
        WHERE pregunta_id = ?
          AND es_correcta = 1
        LIMIT 1
    ";

        $resultado = $this->database->query($sql, [$idPregunta]);

        return $resultado[0];
    }

    public function esPreguntaVistaPorUsuario($id_usuario, $id_pregunta) {
        $sql = "SELECT COUNT(*) as total 
            FROM usuarios_preguntas_vistas 
            WHERE usuario_id = ? AND pregunta_id = ? ";

        $resultado = $this->database->query($sql, [$id_usuario, $id_pregunta]);

        return $resultado[0]['total'] > 0;
    }


    public function obtenerPreguntaPorId($preguntaId)
    {
        $sql = "
            SELECT
                p.id,
                p.enunciado,
                c.nombre AS nombre_categoria,
                c.color AS color_categoria,
                p.total_respuestas, 
                p.total_aciertos,
                c.color_secundario AS color_categoria_sec
            FROM preguntas p
            INNER JOIN categorias c ON p.categoria_id = c.id
            WHERE p.id = ?
            AND p.estado = 'aprobada'
            LIMIT 1
        ";

        $resultado = $this->database->query($sql, [$preguntaId]);
        return $resultado ? $resultado[0] : null;
    }

    public function obtenerRespuestasDePregunta($preguntaId)
    {
        $sql = "
            SELECT id, texto, es_correcta
            FROM respuestas
            WHERE pregunta_id = ?
            ORDER BY RAND()
        ";

        return $this->database->query($sql, [$preguntaId]);
    }
}