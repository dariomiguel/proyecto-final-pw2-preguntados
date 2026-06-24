<?php

class Auth
{

    private static $reglas = [

        'pregunta' => [
            'ver'        => ['Jugador', 'Editor', 'Administrador'],
            'guardar'    => ['Jugador', 'Editor', 'Administrador'],
            'pendientes' => ['Editor', 'Administrador'],
            'aprobar'    => ['Editor', 'Administrador'],
            'rechazar'   => ['Editor', 'Administrador'],
            'eliminar'   => ['Editor', 'Administrador'],
            'actualizar' => ['Editor', 'Administrador'],
        ],
        'perfil' => [
            'ver'               => ['Editor', 'Administrador', 'Jugador'],
            'editarPerfil'      => ['Editor', 'Administrador', 'Jugador'],
            'actualizarPerfil'  => ['Editor', 'Administrador', 'Jugador'],
            'verPublico'        => ['Editor', 'Administrador', 'Jugador'],
        ],



    ];


    public static function verificar($controller,$method)
    {
        $controllerName = strtolower($controller);
        //no hay sesion -> al login

        if (in_array($controllerName, ['login', 'lobby', 'registro'])) {
            return;
        }
        if(!isset($_SESSION['usuario'])){
            Redirect::to('/login/ver');
            exit();
        }
        if (!isset(self::$reglas[$controllerName][$method])) {
            return;
        }


        $rolesPemitidos = self::$reglas[$controller][$method];
        $rolUsuario = $_SESSION['usuario']['rol'];

        //no esta en el array de roles permitidos -> lobby
        if (!in_array($rolUsuario, $rolesPemitidos)) {
            Redirect::to('/lobby/ver');
            exit();
        }
    }

}