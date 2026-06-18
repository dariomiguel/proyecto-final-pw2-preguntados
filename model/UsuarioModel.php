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

    public function editarPerfil($nombre, $segundoNombre, $apellido, $anioNacimiento, $sexo, $pais, $ciudad, $mail, $contrasenia, $nuevoNombreUsuario, $nombreUsuarioActual){

        $sql = "UPDATE usuarios 
            SET nombre = ?, segundo_nombre = ?, apellido = ?, anio_nacimiento = ?, sexo = ?, pais = ?, ciudad = ?, mail = ?, contrasenia = ?, nombre_usuario = ? 
            WHERE nombre_usuario = ?";

        $parametros = [$nombre, $segundoNombre, $apellido, $anioNacimiento, $sexo, $pais, $ciudad, $mail, $contrasenia, $nuevoNombreUsuario, $nombreUsuarioActual];

        return $this->database->execute($sql, $parametros);
    }
}