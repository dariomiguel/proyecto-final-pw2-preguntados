<?php

class PartidaController{

    private $model;
    private $renderer;
    private $request;

    public function __construct($model, $renderer, $request){
        $this->model = $model;
        $this->renderer = $renderer;
        $this->request = $request;
    }

    public function jugar(){
        $id_usuario = $_SESSION["usuario"]["id"];
        $pregunta = $this->model->obtenerPreguntaAleatoria($id_usuario);
        $pregunta['sesionIniciada'] = isset($_SESSION["usuario"]);
        $pregunta['esAdmin'] = ($_SESSION["usuario"]["rol"] ?? '' ) === 'Administrador';
        $pregunta['nombre_usuario'] = $_SESSION["usuario"]["nombre_usuario"] ??  'user_test';
        $pregunta['yaVistaTodas'] = false;

        if(!isset($_SESSION['preguntas_vistas'])){
            $_SESSION['preguntas_vistas'] = [];
        }

        $_SESSION['preguntas_vistas'] [] = $pregunta ['id'];

        $cantPreguntasEnBD = $this->model->cantidadPreguntasEnBD($id_usuario);
        $cantPreguntasYaEchasAUsuario = $this->model->cantidadPreguntasYaEchasAlUsuario($id_usuario);

        $vistasCount = $cantPreguntasYaEchasAUsuario[0]['vistas'];
        $totalCount  = $cantPreguntasEnBD[0]['total'];

        if ($vistasCount != $totalCount) {
            $this->model->guardarPreguntaVista($id_usuario, $pregunta ['id']);
        } else {
            $pregunta['yaVistaTodas'] = true;
        }

        return $this->renderer->render("partidaView", $pregunta);
    }

    public function validarRespuesta(){

        $idRespuesta = $this->request->post("respuesta_id");
        $respuesta = $this->model->obtenerRespuesta($idRespuesta);

        if($respuesta['es_correcta']){
            if(!isset($_SESSION['puntaje_actual']) ){
                $_SESSION['puntaje_actual'] = 0;
            }

            $_SESSION['puntaje_actual']++;
            $_SESSION['puntaje_final'] = $_SESSION['puntaje_actual'];

            header(
                "Location:/partida/jugar"
            );

            exit();

        }else{
            $_SESSION['puntaje_final'] = $_SESSION['puntaje_actual'] ?? 0;
            $_SESSION['texto_correcta'] = $this->model->obtenerTextoRespuestaCorrecta($idRespuesta);

            unset($_SESSION['puntaje_actual']);

            header("Location:/partida/terminada");

            exit();
        }
    }

    public function terminada(){
        $data['puntaje_final'] = $_SESSION['puntaje_final'] ?? 0;
        $data['texto_correcta'] = $_SESSION['texto_correcta'] ?? "";
        $data['sesionIniciada'] = isset($_SESSION["usuario"]);
        $data['esAdmin'] = ($_SESSION["usuario"]["rol"] ?? '' ) === 'Administrador';
        $data['nombre_usuario'] = $_SESSION["usuario"]["nombre_usuario"] ??  'user_test';
        $this->renderer->render("terminadaView", $data);
        unset($_SESSION['puntaje_final']);
        unset($_SESSION['texto_correcta']);
    }

}

