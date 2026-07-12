<?php

class LoginModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function autenticar($username, $password) {
        $sql = "SELECT * FROM usuarios WHERE nombre_usuario = ? AND cuenta_validada = 1";
        $filas = $this->database->query($sql, [$username]);
        if (empty($filas)) return false;
        $usuario = $filas[0];
        return password_verify($password, $usuario['contrasenia']) ? $usuario : false;
    }
}
