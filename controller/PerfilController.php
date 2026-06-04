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

    public function verPerfil()
    {
        $nombre = $this->request->get("nombre");
        $datoUsuario = $this->model->getDatosPerfil($nombre);

        if (!$datoUsuario) {
            return $this->renderer->render("lobby");
        }

        $rutaFisicaFoto = "public/fotos/" . $datoUsuario['foto_perfil'];

        if (empty($datoUsuario['foto_perfil']) || !file_exists($rutaFisicaFoto)) {
            $datoUsuario['foto_perfil'] = 'default-user.webp';
        }

        $urlPerfil = "http://localhost/perfil/verPerfil?nombre=" . $datoUsuario['nombre_usuario'];
        $datoUsuario['url_qr'] = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($urlPerfil);

        return $this->renderer->render("perfil", $datoUsuario);
    }
}