<?php

class PartidaModel{

    private $database;

    public function __construct($database){
        $this->database = $database;
    }

    public function obtenerPreguntaAleatoria(){

        $sql = "
            SELECT
                p.id,
                p.enunciado,
                c.nombre AS nombre_categoria,
                c.color AS color_categoria
            FROM preguntas p
            INNER JOIN categorias c
                ON p.categoria_id = c.id
            WHERE p.estado = 'aprobada'
            ORDER BY RAND()
            LIMIT 1
            ";

        $pregunta = $this->database->query($sql);

        if(empty($pregunta)){
            return null;
        }

        $pregunta = $pregunta[0];

        $sqlRespuestas = "
            SELECT
                id,
                texto,
                es_correcta
            FROM respuestas
            WHERE pregunta_id = {$pregunta['id']}
            ORDER BY RAND()
        ";

        $respuestas = $this->database->query($sqlRespuestas);

        $pregunta['respuestas'] = $respuestas;

        return $pregunta;
    }

    public function obtenerRespuesta($idRespuesta){
        $sql = "
        SELECT
            es_correcta
        FROM respuestas
        WHERE id = $idRespuesta
    ";

        $resultado = $this->database->query($sql);

        if(empty($resultado)){
            return null;
        }

        return $resultado[0];
    }

    public function obtenerTextoRespuestaCorrecta($idRespuesta){

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

        if(empty($resultado)){
            return "";
        }

        return $resultado[0]['texto'];
    }
}