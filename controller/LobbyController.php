<?php

class LobbyController{

    private $renderer;
    //private $model;

    public function __construct($renderer){
        $this->renderer = $renderer;
    }

    public function ver(){
        $datos = [
            'sesionIniciada' => isset($_SESSION["usuario"]),
            'nombre_usuario' => $_SESSION["usuario"]["nombre_usuario"] ??  'user_test',
            'puntaje_total' => '350',
            'partidas' => [
                ['id' => '101', 'resultado' => '15'],
                ['id' => '102', 'resultado' => '8'],
                ['id' => '102', 'resultado' => '8']
            ]
        ];
        $this->renderer->render('lobby', $datos);
    }
}