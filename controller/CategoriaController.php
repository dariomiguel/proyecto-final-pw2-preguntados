<?php

class CategoriaController
{

    private $model;
    private $view;
    private $request;

    public function __construct($model, $view, $request)
    {
        $this->model = $model;
        $this->view = $view;
        $this->request = $request;
    }


    public function listar() {
        $categorias = $this->model->getAllcategorias();
        $creada = $this->request->get('creada') === '1';
        $eliminada = $this->request->get('eliminada') === '1';
        $datos = [
            'categorias' => $categorias,
            'creada'     => $creada,
            'eliminada'  => $eliminada,
            'sesionIniciada' => isset($_SESSION['usuario']),
            'esAdmin' => in_array($_SESSION['usuario']['rol'] ?? '', ['Administrador', 'Editor']),
            'nombre_usuario' => $_SESSION['usuario']['nombre_usuario'] ?? ''
        ];
        $this->view->render('categoriaView', $datos);
    }

    public function guardar(){
        $nombre = trim($this->request->post('nombre'));
        $color  = $this->request->post('color');
        $color_secundario = $this->request->post('color_secundario');

        $errores = [];
        $datos = compact('nombre', 'color', 'color_secundario');

        if(empty($nombre)){$errores['nombre'] = 'El nombre es obligatorio';}

        if (!empty($errores)) {

            Log::warning("CategoraController::guardar - errores de validación");
            $this->view->render('categoriaView', ['errores' => $errores, 'datos' => $datos]);
            return;

        }

        $this->model->crear($datos);
        Redirect::to('/categoria/listar?creada=1');
    }

    public function baja(){
        $idCategoria = $this->request->get('id');
        $this->model->eliminar($idCategoria);
        Redirect::to('/categoria/listar?eliminada=1');
    }


}