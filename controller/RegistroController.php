<?php

class RegistroController
{
    private $model;
    private $view;
    private $request;

    public function __construct($model, $renderer, $request)
    {
        $this->model = $model;
        $this->view = $renderer;
        $this->request = $request;

    }

    public function ver()
    {
        Log::info("RegistroController::ver");
        $this->view->render("registroView");
    }

    public function procesar(){

        $nombre = $this->request->post('nombre');
        $segundo_nombre = $this->request->post('segundo_nombre');
        $apellido = $this->request->post('apellido');
        $anio_nacimiento = $this->request->post('anio_nacimiento');
        $sexo = $this->request->post('sexo');
        $pais = $this->request->post('pais');
        $ciudad = $this->request->post('ciudad');
        $mail = $this->request->post('mail');
        $contrasenia = $this->request->post('contrasenia');
        $nombre_usuario = $this->request->post('nombre_usuario');
        $latitud  = $this->request->post('latitud');
        $longitud = $this->request->post('longitud');

        $errores = [];
        $datos = compact('nombre', 'segundo_nombre', 'apellido',
        'anio_nacimiento', 'sexo', 'pais', 'ciudad', 'mail'
        , 'contrasenia', 'nombre_usuario', 'latitud', 'longitud');


        if (empty($nombre)) $errores['nombre'] = "El nombre es requerido";
        if (empty($apellido)) $errores['apellido'] = "El apellido es requerido";
        if (empty($anio_nacimiento) || !is_numeric($anio_nacimiento) || $anio_nacimiento < 1900 || $anio_nacimiento > date('Y')){
            $errores['anio_nacimiento'] = "Ingresá un año de nacimiento válido.";
        }
        if (empty($sexo)) $errores['sexo'] = "Seleccione una opcion";
        if (empty($pais)) $errores['pais'] = "El país es requerido.";
        if (empty($ciudad)) $errores['ciudad'] = "La ciudad es requerida.";
        if (empty($mail)) $errores['mail'] = "El mail es requerido";
        if(strlen($contrasenia) < 6){
            $errores['contrasenia'] = "La contraseña debe tener al menos 6 caracteres.";
        }
        if (empty($nombre_usuario)) $errores['nombre_usuario'] = "El nombre de usuario es obligatorio.";

        if (empty($latitud) || empty($longitud)) {
            $errores['latitud'] = 'Seleccioná tu ubicación en el mapa.';
        }

        $foto_perfil = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
            $maxTamano       = 2 * 1024 * 1024;
            $finfo           = finfo_open(FILEINFO_MIME_TYPE);
            $mime            = finfo_file($finfo, $_FILES['foto_perfil']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $tiposPermitidos)) {
                $errores['foto_perfil'] = 'Solo se permiten imágenes JPG, PNG o GIF.';
            } elseif ($_FILES['foto_perfil']['size'] > $maxTamano) {
                $errores['foto_perfil'] = 'La imagen no puede superar los 2MB.';
            }
        }

        if ($this->model->existeNombreUsuario($nombre_usuario)) {
            $errores['nombre_usuario'] = 'Ese nombre de usuario ya está en uso.';
        }
        if ($this->model->existeMail($mail)) {
            $errores['mail'] = 'Ese mail ya está registrado.';
        }

        if (!empty($errores)) {
            Log::warning("RegistroController::procesar - errores de validación");
            $this->view->render('registroView', ['errores' => $errores, 'datos' => $datos]);
            return;
        }

        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $ext        = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
            $nombreFoto = uniqid('foto_', true) . '.' . $ext;
            $destino    = __DIR__ . '/../public/uploads/' . $nombreFoto;
            if (!is_dir(__DIR__ . '/../public/uploads/')) {
                mkdir(__DIR__ . '/../public/uploads/', 0755, true);
            }
            move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $destino);
            $foto_perfil = $nombreFoto;
        }

        $datos['foto_perfil']     = $foto_perfil;
        $hash = md5(uniqid($mail, true));
        $datos['hash_validacion'] = $hash;

        $this->model->crear($datos);
        Log::info("RegistroController::procesar - usuario creado: $mail");

        Redirect::to('/registro/exito?token=' . $hash);
    }

    public function exito(){

    $token = $this->request->get('token', '');
    $this->view->render('registroExitoView', ['token' => $token]);

    }

    public function verificar(){
        $token = $this->request->get('token', '');

        $resultado = $this->model->verificar($token);

        if( $resultado > 0 ){
            Redirect::to('/login/ver?verificado=1');
        }else{
            Redirect::to('/login/ver?verificado=0');
        }


    }
}