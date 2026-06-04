<?php


class UsuarioModel
{

    private $database;

    public function __construct($database){
        $this->database = $database;
    }

    public function getDatosPerfil($nombreUsuario){

        $sql = "SELECT * FROM usuarios WHERE nombre_usuario = ?";
        $resultado = $this->database->query($sql, [$nombreUsuario]);
        return !empty($resultado) ? $resultado[0] : null;
    }
}