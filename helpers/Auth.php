<?php

class Auth
{

    private static $reglas = [

        'admin' => [
            'ver' => ['Administrador'],
            'usuarios' => ['Administrador'],
            'cambiarRol' => ['Administrador'],
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

        // Rutas públicas: no requieren sesión.
        if (in_array($controllerName, ['login', 'lobby', 'registro'])) {
            return;
        }
        // Sin sesión -> al login.
        if (!isset($_SESSION['usuario'])) {
            Redirect::to('/login/ver');
            exit();
        }

        // Sin regla definida para esta ruta -> se permite.
        if (!isset(self::$reglas[$controllerName][$methodName])) {
            return;
        }

        $rolesPermitidos = self::$reglas[$controllerName][$methodName];
        $rolUsuario      = $_SESSION['usuario']['rol'];

        // Rol no permitido -> al lobby.
        if (!in_array($rolUsuario, $rolesPermitidos)) {
            Redirect::to('/lobby/ver');
            exit();
        }
    }


}