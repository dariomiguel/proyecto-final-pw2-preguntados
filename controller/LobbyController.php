<?php

class LobbyController{

    private $model;
    private $renderer;


    public function __construct($model, $renderer){

        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function ver(){

        if (!isset($_SESSION["usuario"])) {
            $datos = [
                'sesionIniciada' => false,
                'esAdmin' => false,
                'nombre_usuario' => '',
                'puntaje_total' => 0,
                'partidas' => [],
            ];

            $this->renderer->render('lobbyView', $datos);
            return;
        }

        $idUsuario = ($_SESSION["usuario"]["id"]);

        $perfil = $this->model->getPuntajePorUsuario($idUsuario);
        $historialPartidas = $this->model->getPartidasPorUsuario($idUsuario);

        $datos = [
            'sesionIniciada' => true,
            'esAdmin' => in_array($_SESSION["usuario"]["rol"] ?? '', ['Administrador', 'Editor']),
            'nombre_usuario' => $perfil['nombre_usuario'],
            'puntaje_total' => $perfil['puntaje_total'],
            'partidas' => $historialPartidas,
        ];

        $this->renderer->render('lobbyView', $datos);
    }
}