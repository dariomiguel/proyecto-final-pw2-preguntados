<?php

class PartidaModel
{

    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function obtenerPreguntaAleatoria($usuarioId, $categoriaId, $nivelUsuario)
    {

        $pregunta = $this->obtenerPreguntaNoVistaAleatoria($usuarioId, $categoriaId, $nivelUsuario);

        if ($pregunta == null) {

            $nivelDificultad = "1=1";
            if($nivelUsuario === "fácil"){
                $nivelDificultad = "(p.total_aciertos / NULLIF(p.total_respuestas, 0)) * 100 > 70";
            }elseif ($nivelUsuario === "difícil"){
                $nivelDificultad = "(p.total_aciertos / NULLIF(p.total_respuestas, 0)) * 100 < 30";
            }else{
                $nivelDificultad = "((p.total_aciertos / NULLIF(p.total_respuestas, 0)) * 100 BETWEEN 30 AND 70) OR p.total_respuestas = 0";
            }

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
            AND c.id = $categoriaId
            AND ($nivelDificultad)
            ORDER BY RAND()
            LIMIT 1
        ";

            $pregunta = $this->database->query($sql);

            if (empty($pregunta)) {
                $sqlFallback = "SELECT p.id, p.enunciado, c.nombre AS nombre_categoria, c.color AS color_categoria, p.total_respuestas, p.total_aciertos, c.color_secundario AS color_categoria_sec 
                                FROM preguntas p 
                                INNER JOIN categorias c ON p.categoria_id = c.id 
                                WHERE p.estado = 'aprobada' AND c.id = $categoriaId 
                                ORDER BY RAND() LIMIT 1";
                $pregunta = $this->database->query($sqlFallback);
            }
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
        WHERE pregunta_id = $idRespuesta
          AND es_correcta = 1
        LIMIT 1
    ";

        $resultado = $this->database->query($sql);

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
            p.total_respuestas, 
            p.total_aciertos,
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

    public function obtenerPreguntaNoVistaAleatoria($usuarioId, $categoriaId,$nivelUsuario)
    {

        $nivelDificultad = "1=1";
        if($nivelUsuario === "fácil"){
            $nivelDificultad = "(p.total_aciertos / NULLIF(p.total_respuestas, 0)) * 100 > 70";
        }elseif ($nivelUsuario === "díficil"){
            $nivelDificultad = "(p.total_aciertos / NULLIF(p.total_respuestas, 0)) * 100 < 30";
        }else{
            $nivelDificultad = "((p.total_aciertos / NULLIF(p.total_respuestas, 0)) * 100 BETWEEN 30 AND 70) OR p.total_respuestas = 0";
        }

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
        LEFT JOIN usuarios_preguntas_vistas upv 
            ON p.id = upv.pregunta_id AND upv.usuario_id = $usuarioId
        WHERE p.estado = 'aprobada'
        AND c.id = $categoriaId
        AND upv.pregunta_id IS NULL
        AND $nivelDificultad      
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

    public function obtenerRespuestaCorrecta($idPregunta)
    {
        $sql = "
        SELECT id
        FROM respuestas
        WHERE pregunta_id = $idPregunta
          AND es_correcta = 1
        LIMIT 1
    ";

        $resultado = $this->database->query($sql);

        return $resultado[0];
    }

    public function esPreguntaVistaPorUsuario($id_usuario, $id_pregunta) {
        $sql = "SELECT COUNT(*) as total 
            FROM usuarios_preguntas_vistas 
            WHERE usuario_id = $id_usuario AND pregunta_id = $id_pregunta";

        $resultado = $this->database->query($sql);

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
            WHERE p.id = $preguntaId
            AND p.estado = 'aprobada'
            LIMIT 1
        ";

        $resultado = $this->database->query($sql);
        return $resultado ? $resultado[0] : null;
    }

    public function obtenerRespuestasDePregunta($preguntaId)
    {
        $sql = "
            SELECT id, texto, es_correcta
            FROM respuestas
            WHERE pregunta_id = $preguntaId
            ORDER BY RAND()
        ";

        return $this->database->query($sql);
    }

    public function guardarHistorialPartida($idUsuario, $preguntasRespondidas, $aciertos, $puntaje){
        $sqlInsertarPartida = "INSERT INTO partidas (usuario_id, preguntas_respondidas, aciertos, puntaje)
                VALUES(?,?,?,?)";
        $this->database->execute($sqlInsertarPartida,[$idUsuario, $preguntasRespondidas, $aciertos, $puntaje]);

        $sqlActualizarPuntajeUsuario = "UPDATE usuarios
                                        SET puntaje_total = puntaje_total + ?
                                        WHERE id = ?";
        $this->database->execute($sqlActualizarPuntajeUsuario,[$puntaje,$idUsuario]);
    }
}