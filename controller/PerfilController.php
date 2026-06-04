<?php

class PerfilController{

    private $model;
    private $renderer;
    private $request;

    public function __construct($model,$renderer,$request){
        $this->model = $model;
        $this->renderer = $renderer;
        $this->request = $request;
    }

    public function verPerfil(){
        $nombre = $this->request->get("nombre");
        $datoUsuario = $this->model->getDatosPerfil($nombre);

        if(!$datoUsuario){
            return $this->renderer->render("lobby");
        }
        return $this->renderer->render("perfil",$datoUsuario);
    }
}