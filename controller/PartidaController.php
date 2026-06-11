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
        $preguntaMock = [
            'id' => 1,
            'pregunta' => '¿Cuál es el río más largo del mundo?',
            'opcion_a' => 'Río Paraná',
            'opcion_b' => 'Río Nilo',
            'opcion_c' => 'Río Amazonas',
            'opcion_d' => 'Río Misisipi',
            'respuesta_correcta' => 'C',
            'nombre_categoria' => 'Geografía',
            'color_hexadecimal' => '#3498db'
        ];

        if(!isset($_SESSION['preguntas_vistas'])){
            $_SESSION['preguntas_vistas'] = [];
        }
        $_SESSION['preguntas_vistas'] [] = $preguntaMock['id'];

        return $this->renderer->render("partidaView", $preguntaMock);
    }

    public function validarRespuesta(){
        $id_pregunta = $this->request->post("id_pregunta");
        $opcionElegida = $this->request->post("opcion_elegida");

        if($opcionElegida == "C"){
            if(!isset($_SESSION['puntaje_actual'])){
                $_SESSION['puntaje_actual'] = 0;
            }
            $_SESSION['puntaje_actual'] += 1;
            $_SESSION['puntaje_final'] = $_SESSION['puntaje_actual'];
            header("Location:/partida/jugar");
            exit();
        }else{
            //$this->model->guardarPartida(); Agregarlo cuando este listo el metodo
            $_SESSION['puntaje_final'] = $_SESSION['puntaje_actual'] ?? 0;
            $_SESSION['texto_correcta'] = "C. Río Amazonas";
            unset($_SESSION['puntaje_actual']);
            header("Location:/partida/terminada");
            exit();
        }
    }

    public function terminada(){
        $data['puntaje_final'] = $_SESSION['puntaje_final'] ?? 0;
        $data['texto_correcta'] = $_SESSION['texto_correcta'] ?? "";
        $this->renderer->render("terminadaView", $data);
        unset($_SESSION['puntaje_final']);
        unset($_SESSION['texto_correcta']);
    }

}

