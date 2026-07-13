<?php

class Auth
{

    private static $reglas = [

        'admin' => [
            'ver' => ['Administrador'],
        ],

        'pregunta' => [
            'ver'        => ['Jugador', 'Editor', 'Administrador'],
            'guardar'    => ['Jugador', 'Editor', 'Administrador'],
            'pendientes' => ['Editor', 'Administrador'],
            'aprobar'    => ['Editor', 'Administrador'],
            'rechazar'   => ['Editor', 'Administrador'],
            'eliminar'   => ['Editor', 'Administrador'],
            'actualizar' => ['Editor', 'Administrador'],
            'editar'     => ['Editor', 'Administrador'],
            'quitarreporte' => ['Editor', 'Administrador'],
            'reportar'   => ['Jugador','Editor', 'Administrador'],

        ],
        'perfil' => [
            'ver'               => ['Editor', 'Administrador', 'Jugador'],
            'editarperfil'      => ['Editor', 'Administrador', 'Jugador'],
            'actualizarperfil'  => ['Editor', 'Administrador', 'Jugador'],
            'verpublico'        => ['Editor', 'Administrador', 'Jugador'],
        ],
        'categoria' => [
            'listar'               => ['Editor', 'Administrador'],
            'guardar'              => ['Editor', 'Administrador'],
            'baja'                 => ['Editor', 'Administrador'],
        ]

    ];


    public static function verificar($controller,$method)
    {
        $controllerName = strtolower($controller);
        $methodName     = strtolower($method);
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


        $rolesPermitidos = self::$reglas[$controllerName][$methodName];
        $rolUsuario = $_SESSION['usuario']['rol'];


        //no esta en el array de roles permitidos -> lobby
        if (!in_array($rolUsuario, $rolesPermitidos)) {
            Redirect::to('/lobby/ver');
            exit();
        }
    }

}