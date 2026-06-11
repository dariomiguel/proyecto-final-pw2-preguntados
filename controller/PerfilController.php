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

        $rutaFisicaFoto = "public/uploads/" . $datoUsuario['foto_perfil'];

        if (empty($datoUsuario['foto_perfil']) || !file_exists($rutaFisicaFoto)) {
            $datoUsuario['foto_perfil'] = 'default-user.webp';
        }

        $urlPerfil = "http://localhost/perfil/verPerfil?nombre=" . $datoUsuario['nombre_usuario'];
        $datoUsuario['url_qr'] = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($urlPerfil);

        return $this->renderer->render("perfilView", array_merge($datoUsuario, [
            'sesionIniciada' => isset($_SESSION["usuario"]),
            'usuario_logueado' => $_SESSION["usuario"] ?? null
        ]));
    }
}