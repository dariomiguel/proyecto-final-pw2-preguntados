<?php

class PreguntaController
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

    public function ver(){
        Log::info("PreguntaController::ver");
        $categorias = $this->model->getCategorias();
        $enviada = $this->request->get('enviada') === '1';
        $this->view->render('crearPreguntaView', [
            'categorias'     => $categorias,
            'enviada'        => $enviada,
            'sesionIniciada' => isset($_SESSION['usuario']),
            'esAdmin'        => ($_SESSION['usuario']['rol'] ?? '') === 'Administrador',
            'nombre_usuario' => $_SESSION['usuario']['nombre_usuario'] ?? '',
        ]);
    }

    public function guardar(){
        $enunciado    = $this->request->post('enunciado');
        $categoria_id = $this->request->post('categoria_id');

        $respuestas = [
            ['texto' => $this->request->post('respuesta_1'), 'es_correcta' => 0],
            ['texto' => $this->request->post('respuesta_2'), 'es_correcta' => 0],
            ['texto' => $this->request->post('respuesta_3'), 'es_correcta' => 0],
            ['texto' => $this->request->post('respuesta_4'), 'es_correcta' => 0],
        ];

        $correcta = $this->request->post('correcta');
        $respuestas[$correcta - 1]['es_correcta'] = 1;

        $estado               = 'pendiente';
        $creado_por_usuario_id = $_SESSION['usuario']['id'];

        $errores = [];
        $datos = compact('enunciado', 'categoria_id', 'respuestas', 'estado', 'creado_por_usuario_id');

        if(empty($enunciado)){$errores['enunciado'] = 'El campo enunciado es obligatorio';}
        if(empty($categoria_id)){$errores['categoria_id'] = 'La categoria es obligatoria';}
        foreach ($respuestas as $i => $r) {
            if (empty($r['texto'])) $errores['respuesta_' . ($i + 1)] = 'La respuesta es requerida.';
        }
        if (empty($correcta)) {$errores['correcta'] = 'Debe marcar una respuesta correcta.';}

        if (!empty($errores)) {

            Log::warning("PreguntaController::procesar - errores de validación");
            $this->view->render('crearPreguntaView', ['errores' => $errores, 'datos' => $datos]);
            return;

        }

        $this->model->crear($datos);
        Redirect::to('/pregunta/ver?enviada=1');
    }


    public function pendientes(){

        $usuario = $_SESSION['usuario'];
        if ($usuario['rol'] === 'Jugador') {
            Redirect::to('/lobby/ver');
            return;
        }

       $preguntasPendientes = $this->model->getPreguntasPendientes();
       $this->view->render('preguntasPendientesView', [
           'preguntas'      => $preguntasPendientes,
           'sesionIniciada' => true,
           'esAdmin'        => true,
           'nombre_usuario' => $_SESSION['usuario']['nombre_usuario'],
       ]);

    }

    public function aprobar(){

        $usuario = $_SESSION['usuario'];
        if ($usuario['rol'] === 'Jugador') {
            Redirect::to('/lobby/ver');
            return;
        }

        $idPregunta = $this->request->post('id');
        $this->model->cambiarEstadoPregunta($idPregunta, 'aprobada');
        Redirect::to('/pregunta/pendientes');
    }

    public function rechazar(){

        $usuario = $_SESSION['usuario'];
        if ($usuario['rol'] === 'Jugador') {
            Redirect::to('/lobby/ver');
            return;
        }

        $idPregunta = $this->request->post('id');
        $this->model->cambiarEstadoPregunta($idPregunta, 'baja');
        Redirect::to('/pregunta/pendientes');
    }

    public function editar(){
        $idPreguntaAEditar = $this->request->get('id');
        $pregunta = $this->model->getPregunta($idPreguntaAEditar);
        $categorias = $this->model->getCategorias();

        foreach ($categorias as &$categoria) {
            $categoria['seleccionada'] = ($categoria['id'] == $pregunta['categoria_id']);
        }
        unset($categoria);

        foreach ($pregunta['respuestas'] as $i => &$respuesta) {
            $respuesta['indice'] = $i + 1;
        }
        unset($respuesta);

        $this->view->render('editarPreguntaView', [
            'pregunta'       => $pregunta,
            'categorias'     => $categorias,
            'sesionIniciada' => true,
            'esAdmin'        => true,
            'nombre_usuario' => $_SESSION['usuario']['nombre_usuario'],
        ]);
    }

    public function actualizar(){
        $errores = [];

        $idPregunta = $this->request->post('id');
        $enunciado    = $this->request->post('enunciado');
        $categoria_id = $this->request->post('categoria_id');

        $respuestas = [
            ['id' => $this->request->post('respuesta_id_1'), 'texto' => $this->request->post('respuesta_1'), 'es_correcta' => 0],
            ['id' => $this->request->post('respuesta_id_2'), 'texto' => $this->request->post('respuesta_2'), 'es_correcta' => 0],
            ['id' => $this->request->post('respuesta_id_3'), 'texto' => $this->request->post('respuesta_3'), 'es_correcta' => 0],
            ['id' => $this->request->post('respuesta_id_4'), 'texto' => $this->request->post('respuesta_4'), 'es_correcta' => 0],
        ];

        $correctaRaw = $this->request->post('correcta');

        if (empty($correctaRaw)) {
            $errores['correcta'] = 'Debe marcar una respuesta correcta.';
        } else {
            $respuestas[(int)$correctaRaw - 1]['es_correcta'] = 1;
        }

        if (empty($enunciado))   { $errores['enunciado']    = 'El enunciado es obligatorio.'; }
        if (empty($categoria_id)){ $errores['categoria_id'] = 'La categoría es obligatoria.'; }
        foreach ($respuestas as $i => &$r) {
            $r['indice'] = $i + 1;
            if (empty($r['texto'])) {
                $r['error'] = true;
                $errores['respuesta_' . ($i + 1)] = 'La respuesta es requerida.';
            }
        }
        unset($r);

        if (!empty($errores)) {
            $pregunta = $this->model->getPregunta($idPregunta);

            $categorias = $this->model->getCategorias();
            foreach ($categorias as &$cat) {
                $cat['seleccionada'] = ($cat['id'] == $categoria_id);
            }
            unset($cat);

            $pregunta['respuestas'] = $respuestas;

            Log::warning("PreguntaController::actualizar - errores de validación");
            $this->view->render('editarPreguntaView', [
                'errores'        => $errores,
                'pregunta'       => $pregunta,
                'categorias'     => $categorias,
                'sesionIniciada' => true,
                'esAdmin'        => true,
                'nombre_usuario' => $_SESSION['usuario']['nombre_usuario'],
            ]);
            return;
        }

        $correcta = (int) $correctaRaw;
        $this->model->actualizar($idPregunta, $enunciado, $categoria_id, $respuestas);
        Redirect::to('/pregunta/pendientes');
    }



}