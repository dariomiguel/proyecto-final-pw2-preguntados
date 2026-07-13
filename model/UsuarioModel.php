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

    public function getNivelUsuario($idUsuario){

        $sql = "SELECT SUM(preguntas_respondidas)  as total_respondidas,
                       SUM(aciertos) as total_aciertos
                FROM partidas
                WHERE usuario_id = ?";

        $resultado = $this->database->query($sql, [$idUsuario]);

        $respondidas = $resultado[0]["total_respondidas"] ?? 0;
        $aciertos = $resultado[0]["total_aciertos"] ?? 0;

        $nivelUsuario = "medio";

        if($respondidas > 0){
            $porcetajeAciertos = ($aciertos / $respondidas) * 100;

            if($porcetajeAciertos > 70){
                $nivelUsuario = "difícil";
            }elseif ($porcetajeAciertos < 30) {
                $nivelUsuario = "fácil";
            }
        }
        return $nivelUsuario;
    }

    public function getAllUsuarios()
    {
        $sql = "SELECT id, nombre_usuario, mail, rol
            FROM usuarios
            ORDER BY nombre_usuario";
        return $this->database->query($sql);
    }

    public function cambiarRol($idUsuario, $rol)
    {
        $rolesValidos = ['Jugador', 'Editor', 'Administrador'];
        if (!in_array($rol, $rolesValidos)) {
            return false;
        }

        $sql = "UPDATE usuarios SET rol = ? WHERE id = ?";
        return $this->database->execute($sql, [$rol, $idUsuario]);
    }
}