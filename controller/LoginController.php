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

    public function procesar()
    {
        Log::info("LoginController::procesar");

        $username = $this->request->post('username');
        $password = $this->request->post('password');

        $usuario = $this->model->autenticar($username, $password);

        if ($usuario) {
            $_SESSION['usuario'] = $usuario;
            Redirect::to('/lobby/ver');
        } else {
            $this->renderer->render("loginView", [
                'verificado_ok'    => false,
                'verificado_error' => false,
                'error_login'      => true,
            ]);
        }
    }

    public function logout()
    {
        $_SESSION = [];

        session_destroy();

        Redirect::to('/login');
    }
}
