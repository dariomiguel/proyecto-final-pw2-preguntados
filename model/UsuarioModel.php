<?php


class UsuarioModel
{

    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function getDatosPerfil($nombreUsuario)
    {

        $sql = "SELECT * 
                FROM usuarios 
                WHERE nombre_usuario = ?";

        $resultado = $this->database->query($sql, [$nombreUsuario]);
        return !empty($resultado) ? $resultado[0] : null;
    }

    public function editarPerfil($nombre, $segundoNombre, $apellido, $anioNacimiento, $sexo, $pais, $ciudad, $mail, $contrasenia, $nuevoNombreUsuario, $nombreUsuarioActual, $latitud, $longitud)
    {

        $sql = "UPDATE usuarios 
                SET nombre = ?, segundo_nombre = ?, apellido = ?, anio_nacimiento = ?, sexo = ?, pais = ?, ciudad = ?, mail = ?, contrasenia = ?, nombre_usuario = ?, latitud = ?, longitud = ? 
                WHERE nombre_usuario = ?";

        $parametros = $parametros = [$nombre, $segundoNombre, $apellido, $anioNacimiento, $sexo, $pais, $ciudad, $mail, $contrasenia, $nuevoNombreUsuario, $latitud, $longitud, $nombreUsuarioActual];

        return $this->database->execute($sql, $parametros);
    }

    public function getRankingGlobal()
    {
        $sql = "SELECT nombre_usuario, puntaje_total, foto_perfil 
                FROM usuarios 
                WHERE rol = 'Jugador' 
                ORDER BY puntaje_total DESC";

        $resultado = $this->database->query($sql);
        return !empty($resultado) ? $resultado : null;
    }

    public function getPartidasPorUsuario($idUsuario)
    {
        $sql = "SELECT id,puntaje,fecha 
                FROM partidas 
                WHERE usuario_id = ? 
                ORDER BY fecha DESC";

        $resultado = $this->database->query($sql, array($idUsuario));
        return !empty($resultado) ? $resultado : null;
    }

    public function getPuntajePorUsuario($idUsuario){
        $sql = "SELECT nombre_usuario, puntaje_total
                FROM usuarios
                WHERE id = ?";

        $resultado = $this->database->query($sql, array($idUsuario));
        return !empty($resultado) ? $resultado[0] : null;
    }
}