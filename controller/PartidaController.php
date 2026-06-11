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
}

