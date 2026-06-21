<?php

class PartidaModel
{

    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function obtenerPreguntaAleatoria($usuarioId)
    {

        $pregunta = $this->obtenerPreguntaNoVistaAleatoria($usuarioId);

        if ($pregunta == null) {

            $sql = "
            SELECT
                p.id,
                p.enunciado,
                c.nombre AS nombre_categoria,
                c.color AS color_categoria,
                c.color_secundario AS color_categoria_sec
            FROM preguntas p
            INNER JOIN categorias c
                ON p.categoria_id = c.id
            WHERE p.estado = 'aprobada'
            ORDER BY RAND()
            LIMIT 1
        ";

            $pregunta = $this->database->query($sql);

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
        WHERE pregunta_id = {$pregunta['id']}
        ORDER BY RAND()
    ";

        $pregunta['respuestas'] = $this->database->query($sqlRespuestas);

        return $pregunta;
    }

    public function obtenerRespuesta($idRespuesta)
    {
        $sql = "
        SELECT
            es_correcta
        FROM respuestas
        WHERE id = $idRespuesta
    ";

        $resultado = $this->database->query($sql);

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
        WHERE pregunta_id = (
            SELECT pregunta_id
            FROM respuestas
            WHERE id = $idRespuesta)
          AND es_correcta = 1
    ";

        $resultado = $this->database->query($sql);

        if (empty($resultado)) {
            return "";
        }

        return $resultado[0]['texto'];
    }

    public function guardarPreguntaVista($usuarioId, $preguntaId)
    {

        $sql = "
        INSERT INTO usuarios_preguntas_vistas
            (usuario_id, pregunta_id)
        VALUES
            ($usuarioId, $preguntaId)
    ";

        $this->database->execute($sql);
    }

    public function buscarPreguntasNoVistas($usuarioId)
    {

        $sql = "
        SELECT
            p.id,
            p.enunciado,
            c.nombre AS nombre_categoria,
            c.color AS color_categoria,
            c.color_secundario AS color_categoria_sec
        FROM preguntas p
        INNER JOIN categorias c
            ON p.categoria_id = c.id
        WHERE p.estado = 'aprobada'
        AND p.id NOT IN (
            SELECT pregunta_id
            FROM usuarios_preguntas_vistas
            WHERE usuario_id = $usuarioId
        )
    ";

        return $this->database->query($sql);
    }

    public function obtenerPreguntaNoVistaAleatoria($usuarioId)
    {

        $sql = "
        SELECT
            p.id,
            p.enunciado,
            c.nombre AS nombre_categoria,
            c.color AS color_categoria,
            c.color_secundario AS color_categoria_sec
        FROM preguntas p
        INNER JOIN categorias c
            ON p.categoria_id = c.id
        WHERE p.estado = 'aprobada'
        AND p.id NOT IN (
            SELECT pregunta_id
            FROM usuarios_preguntas_vistas
            WHERE usuario_id = $usuarioId
        )
        ORDER BY RAND()
        LIMIT 1
    ";

        $pregunta = $this->database->query($sql);

        if (empty($pregunta)) {
            return null;
        }

        return $pregunta[0];
    }

    public function verPreguntasYaEchasAlUsuario($usuarioId){



        $sql = "
        SELECT pregunta_id
        FROM usuarios_preguntas_vistas
        WHERE usuario_id = $usuarioId
    ";

        return $this->database->query($sql);
    }

    public function cantidadPreguntasYaEchasAlUsuario($usuarioId)
    {
        $sql = "
        SELECT COUNT(*) AS vistas
        FROM usuarios_preguntas_vistas
        WHERE usuario_id = $usuarioId;
        ";

        return $this->database->query($sql);
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
}