<?php

class LobbyController{

    private $model;
    private $renderer;


    public function __construct($model, $renderer){

        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function ver(){

        $idUsuario = ($_SESSION["usuario"]["id"]);

        $perfil = $this->model->getPuntajePorUsuario($idUsuario);
        $historialPartidas = $this->model->getPartidasPorUsuario($idUsuario);

        $datos = [
            'nombre_usuario' => $perfil['nombre_usuario'],
            'puntaje_total' => $perfil['puntaje_total'],
            'partidas' => $historialPartidas,
        ];

        $this->renderer->render('lobbyView', $datos);
    }
}