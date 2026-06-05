<?php

class LoginController
{
    private $model;
    private $renderer;
    private $request;

    public function __construct($model, $renderer, $request)
    {
        $this->model    = $model;
        $this->renderer = $renderer;
        $this->request  = $request;
    }

    public function ver()
    {
        Log::info("LoginController::ver");
        $verificado = $this->request->get('verificado', '');
        $this->renderer->render("loginView", [
                'verificado_ok' => $verificado === '1',
                'verificado_error' => $verificado === '0'
            ]);
    }
//
//    public function alta()
//    {
//        Log::info("LobbyController::alta (form)");
//        $this->renderer->render("formAltaVikingoView");
//    }
//
//    public function procesarAlta()
//    {
//        $nombre = $this->request->post('nombre');
//        $apodo  = $this->request->post('apodo');
//        $clan   = $this->request->post('clan');
//        $fuerza = $this->request->post('fuerza');
//
//        if (!is_numeric($fuerza)) {
//            Log::warning("LobbyController::procesarAlta - fuerza invalida: $fuerza");
//            Redirect::toIndex();
//            return;
//        }
//
//        Log::info("LobbyController::procesarAlta - nombre=$nombre");
//        $this->model->alta($nombre, $apodo, $clan, (int) $fuerza);
//        Redirect::toIndex();
//    }
//
//    public function editar()
//    {
//        $id = $this->request->get('id');
//
//        if (!is_numeric($id)) {
//            Log::warning("LobbyController::editar - id invalido: $id");
//            Redirect::toIndex();
//            return;
//        }
//
//        $id = (int) $id;
//        Log::info("LobbyController::editar - id=$id");
//        $this->renderer->render("formEditarVikingoView", $this->model->getVikingo($id));
//    }
//
//    public function procesarEditar()
//    {
//        $id     = $this->request->post('id');
//        $fuerza = $this->request->post('fuerza');
//
//        if (!is_numeric($id) || !is_numeric($fuerza)) {
//            Log::warning("LobbyController::procesarEditar - parametros invalidos id=$id fuerza=$fuerza");
//            Redirect::toIndex();
//            return;
//        }
//
//        $id     = (int) $id;
//        $fuerza = (int) $fuerza;
//        $nombre = $this->request->post('nombre');
//        Log::info("LobbyController::procesarEditar - id=$id nombre=$nombre");
//        $this->model->editar($id, $nombre, $this->request->post('apodo'), $this->request->post('clan'), $fuerza);
//        Redirect::toIndex();
//    }
//
//    public function eliminar()
//    {
//        $id = $this->request->get('id');
//
//        if (!is_numeric($id)) {
//            Log::warning("LobbyController::eliminar - id invalido: $id");
//            Redirect::toIndex();
//            return;
//        }
//
//        $id = (int) $id;
//        Log::info("LobbyController::eliminar - id=$id");
//        $this->model->eliminar($id);
//        Redirect::toIndex();
//    }
}
