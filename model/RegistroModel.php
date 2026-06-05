<?php

class RegistroModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function crear($datos)
    {
        $sql = "INSERT INTO usuarios
                    (nombre, segundo_nombre, apellido, anio_nacimiento,
                     sexo, pais, ciudad, mail, contrasenia, nombre_usuario,
                     foto_perfil, hash_validacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $hash = password_hash($datos['contrasenia'], PASSWORD_BCRYPT);

        return $this->database->execute($sql, [
            $datos['nombre'],
            $datos['segundo_nombre'] ?? null,
            $datos['apellido'],
            $datos['anio_nacimiento'],
            $datos['sexo'],
            $datos['pais'],
            $datos['ciudad'],
            $datos['mail'],
            $hash,
            $datos['nombre_usuario'],
            $datos['foto_perfil'] ?? null,
            $datos['hash_validacion'] ?? null,
        ]);
    }

    public function verificar($token){
        $sql = "UPDATE usuarios 
            SET cuenta_validada = 1, hash_validacion = NULL 
            WHERE hash_validacion = ? AND cuenta_validada = 0";

        return $this->database->execute($sql, [$token]);

    }
}