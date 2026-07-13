<?php

require_once(__DIR__ . '/../vendor/mustache/mustache.php-2.14.2/src/Mustache/Autoloader.php');

class MustacheRenderer {
    private $mustache;

    public function __construct($viewsFolder) {
        Mustache_Autoloader::register();
        $this->mustache = new Mustache_Engine([
            'loader'          => new Mustache_Loader_FilesystemLoader($viewsFolder),
            'partials_loader'  => new Mustache_Loader_FilesystemLoader($viewsFolder),
        ]);
    }

    public function render($viewName, $data = []) {
        // Datos globales de sesión, disponibles en todas las vistas.
        $data['sesionIniciada'] = isset($_SESSION['usuario']);
        $data['esAdmin'] = in_array($_SESSION['usuario']['rol'] ?? '', ['Administrador', 'Editor']);
        $data['nombre_usuario'] = $data['nombre_usuario'] ?? ($_SESSION['usuario']['nombre_usuario'] ?? '');

        $template = $this->mustache->loadTemplate($viewName);
        echo $template->render($data);
    }
}