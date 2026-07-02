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
        $id_categoria = (int)$_GET['idCategoria'];
        $_SESSION['idCategoria'] = $id_categoria;

//        var_dump($id_categoria);

        $id_usuario = $_SESSION["usuario"]["id"];
        $pregunta = $this->model->obtenerPreguntaAleatoria($id_usuario, $id_categoria);

        $pregunta['sesionIniciada'] = isset($_SESSION["usuario"]);
        $pregunta['esAdmin'] = in_array($_SESSION["usuario"]["rol"] ?? '', ['Administrador', 'Editor']);
        $pregunta['nombre_usuario'] = $_SESSION["usuario"]["nombre_usuario"] ??  'user_test';
        $pregunta['yaVistaTodas'] = false;

        if(!isset($_SESSION['preguntas_vistas'])){
            $_SESSION['preguntas_vistas'] = [];
        }
        foreach ($pregunta['respuestas'] as &$respuesta) {
            $respuesta['pregunta_id'] = $pregunta['id'];
        }

        $_SESSION['preguntas_vistas'][] = $pregunta['id'];

        $yaVioEstaPregunta = $this->model->esPreguntaVistaPorUsuario($id_usuario, $pregunta['id']);

        if (!$yaVioEstaPregunta) {
            // Si no la vio, la guardamos en la base de datos de forma segura
            $this->model->guardarPreguntaVista($id_usuario, $pregunta['id']);
        }

        $cantPreguntasEnBD = $this->model->cantidadPreguntasEnBD($id_usuario);
        $cantPreguntasYaEchasAUsuario = $this->model->cantidadPreguntasYaEchasAlUsuario($id_usuario);

        $vistasCount = $cantPreguntasYaEchasAUsuario[0]['vistas'];
        $totalCount  = $cantPreguntasEnBD[0]['total'];

        if ($vistasCount == $totalCount) {
            $pregunta['yaVistaTodas'] = true;
        }

        return $this->renderer->render("partidaView", $pregunta);
    }

    public function validarRespuesta(){

        $idPregunta = $_POST['id_pregunta'];
        $idRespuesta = $_POST['respuesta_id'] ?? null;

        // respuesta correcta (ID)
        $respuestaCorrecta = $this->model->obtenerRespuestaCorrecta($idPregunta);
        $texto = $this->model->obtenerTextoRespuestaCorrecta($idPregunta);
        $textoCorrecto = $texto['texto'];
        $_SESSION['texto_correcta'] = $textoCorrecto;

        // timeout o inválido
        if ( $idRespuesta == -1 || $idRespuesta == '') {
            $esCorrecta = false;
        } else {
            $esCorrecta = ($idRespuesta == $respuestaCorrecta['id']);
        }

        if ($esCorrecta) {
            if (!isset($_SESSION['puntaje_actual'])) {
                $_SESSION['puntaje_actual'] = 0;
            }

            $_SESSION['puntaje_actual']++;
            $_SESSION['puntaje_final'] = $_SESSION['puntaje_actual'];

            header("Location:/partida/verRuleta");


        }else{
            $_SESSION['puntaje_final'] = $_SESSION['puntaje_actual'] ?? 0;

            unset($_SESSION['puntaje_actual']);

            header("Location:/partida/terminada");
        }
        exit();
    }

    public function terminada(){
        $data['puntaje_final'] = $_SESSION['puntaje_final'] ?? 0;
        $data['texto_correcta'] = $_SESSION['texto_correcta'] ?? "";
        $data['sesionIniciada'] = isset($_SESSION["usuario"]);
        $data['esAdmin'] = in_array($_SESSION["usuario"]["rol"] ?? '', ['Administrador', 'Editor']);
        $data['nombre_usuario'] = $_SESSION["usuario"]["nombre_usuario"] ??  'user_test';


        unset($_SESSION['puntaje_final']);
        unset($_SESSION['texto_correcta']);

        $this->renderer->render("terminadaView", $data);
    }

    public function verRuleta()
    {
        $this->renderer->render("mostrarRuletaView", []);
    }


}

