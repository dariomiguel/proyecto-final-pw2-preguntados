<?php

class PerfilController
{

    private $model;
    private $renderer;
    private $request;

    public function __construct($model, $renderer, $request)
    {
        $this->model = $model;
        $this->renderer = $renderer;
        $this->request = $request;
    }

    public function ver()
    {
        $nombre = $this->request->get("nombre");
        $datoUsuario = $this->model->getDatosPerfil($nombre);

        if (!$datoUsuario) {
            return $this->renderer->render("lobbyView");
        }


        if(isset($_SESSION["exito"])){
            $datoUsuario["mensaje_exito"] = $_SESSION["exito"];
            unset($_SESSION["exito"]);
        }



        $rutaFisicaFoto = "public/uploads/" . $datoUsuario['foto_perfil'];

        if (empty($datoUsuario['foto_perfil']) || !file_exists($rutaFisicaFoto)) {
            $datoUsuario['foto_perfil'] = 'default-user.webp';
        }

        $urlPerfil = "http://localhost/perfil/verPerfil?nombre=" . $datoUsuario['nombre_usuario'];
        $datoUsuario['url_qr'] = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($urlPerfil);

        return $this->renderer->render("perfilView", array_merge($datoUsuario, [
            'sesionIniciada' => isset($_SESSION["usuario"]),
            'esAdmin' => in_array($_SESSION["usuario"]["rol"] ?? '', ['Administrador', 'Editor']),
            'nombre_usuario' => $_SESSION["usuario"]["nombre_usuario"] ?? '',
            'usuario_logueado' => $_SESSION["usuario"] ?? null,
        ]));
    }

    public function editarPerfil(){
        if (!isset($_SESSION['usuario'])) {
            return $this->renderer->render("loginView");
        }

        $usuario = $_SESSION["usuario"]["nombre_usuario"];
        $datoUsuario = $this->model->getDatosPerfil($usuario);

        if (!$datoUsuario) {
            return $this->renderer->render("lobbyView");
        }
        return $this->renderer->render("editarPerfilView", $datoUsuario);
    }

    public function actualizarPerfil(){
        $nombre = $this->request->post("nombre");
        $segundoNombre = $this->request->post("segundo_nombre");
        $apellido = $this->request->post("apellido");
        $anioNacimiento = $this->request->post("anio_nacimiento");
        $sexo = $this->request->post("sexo");
        $pais = $this->request->post("pais");
        $ciudad = $this->request->post("ciudad");
        $mail = $this->request->post("mail");
        $contraseniaPost = $this->request->post("contrasenia");
        $nombreUsuario = $this->request->post("nombre_usuario");
        $latitud = $this->request->post("latitud");
        $longitud = $this->request->post("longitud");

        if(empty($nombreUsuario) || empty($mail) || empty($nombre) || empty($apellido) || empty($anioNacimiento) || empty($sexo) || empty($pais) || empty($ciudad)){
            $data["error"] = "Todos los campos marcados con * son obligatorios";
            $data = [
                "error" => "Todos los campos marcados con * son obligatorios",
                "nombre_usuario" => $nombreUsuario,
                "nombre" => $nombre,
                "segundo_nombre" => $segundoNombre,
                "apellido" => $apellido,
                "mail" => $mail,
                "anio_nacimiento" => $anioNacimiento,
                "pais" => $pais,
                "ciudad" => $ciudad
            ];

            return $this->renderer->render("editarPerfilView", $data);
        }


        if (empty($contraseniaPost)) {
            $contrasenia = $_SESSION["usuario"]["contrasenia"];
        } else {
            $contrasenia = password_hash($contraseniaPost, PASSWORD_DEFAULT);
        }

        $nombreUsuarioActual = $_SESSION["usuario"]["nombre_usuario"];


        $this->model->editarPerfil($nombre, $segundoNombre, $apellido, $anioNacimiento, $sexo, $pais, $ciudad, $mail, $contrasenia, $nombreUsuario, $nombreUsuarioActual, $latitud, $longitud);

        $_SESSION["usuario"]["nombre_usuario"] = $nombreUsuario;
        $_SESSION["usuario"]["contrasenia"] = $contrasenia;
        $_SESSION["exito"] = "¡Perfil actualizado con éxito!";

        header("Location: /perfil/ver?nombre=" . $nombreUsuario);
        exit();
    }

    public function verPublico(){
        $nombreUsuario = $this->request->get('nombre');
        $usuario = $this->model->getDatosPerfil($nombreUsuario);

        if (empty($usuario['foto_perfil'])) {
            $usuario['foto_perfil'] = 'default-user.webp';
        }

        $partidas = $this->model->getPartidaPorUsuario($usuario["id"]);

        $data = [
            "usuario" => $usuario,
            "partidas" => $partidas,
        ];
        return $this->renderer->render("perfilPublicoView",$data);

    }
}