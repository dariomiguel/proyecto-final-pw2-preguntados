<?php

class PartidaController
{

    private $model;
    private $preguntaModel;
    private $renderer;
    private $request;
    private const TIEMPO_LIMITE = 15;
    private const CATEGORIAS = [
        1 => "Geografía",
        2 => "Historia",
        3 => "Arte",
        4 => "Ciencia",
        5 => "Entretenimiento",
        6 => "Deportes",
        7 => "Programación",
    ];

    public function __construct($model,$preguntaModel, $renderer, $request)
    {
        $this->model = $model;
        $this->preguntaModel = $preguntaModel;
        $this->renderer = $renderer;
        $this->request = $request;
    }

    public function girarRuleta()
    {
        if (isset($_SESSION["pregunta_activa_id"], $_SESSION["idCategoria"])) {
            $idCategoria = (int)$_SESSION["idCategoria"];
            $nombreCategoria = self::CATEGORIAS[$idCategoria] ?? "Desconocida";

            header("Content-Type: application/json; charset=utf-8");
            echo json_encode([
                "idCategoria" => $idCategoria,
                "nombreCategoria" => $nombreCategoria,
                "indiceRuleta" => $idCategoria - 1,
                "bloqueada" => true,
            ]);
            exit();
        }

        $idCategoria = array_rand(self::CATEGORIAS);
        $nombreCategoria = self::CATEGORIAS[$idCategoria];

        $_SESSION["idCategoria"] = $idCategoria;

        unset($_SESSION["timer_inicio"]);

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            "idCategoria" => $idCategoria,
            "nombreCategoria" => $nombreCategoria,
            "indiceRuleta" => $idCategoria - 1,
            "bloqueada" => false,
        ]);
        exit();
    }

    public function jugar()
    {
        if (!isset($_SESSION["idCategoria"])) {
            header("Location:/partida/verRuleta");
            exit();
        }

        $id_categoria = (int)$_SESSION["idCategoria"];
        $id_usuario = $_SESSION["usuario"]["id"];

        if (isset($_SESSION["pregunta_activa_id"])) {
            $pregunta = $this->model->obtenerPreguntaPorId($_SESSION["pregunta_activa_id"]);
        } else {
            $pregunta = $this->model->obtenerPreguntaAleatoria($id_usuario, $id_categoria);

        $pregunta['sesionIniciada'] = isset($_SESSION["usuario"]);
        $pregunta['esAdmin'] = in_array($_SESSION["usuario"]["rol"] ?? '', ['Administrador', 'Editor']);
        $pregunta['nombre_usuario'] = $_SESSION["usuario"]["nombre_usuario"] ??  'user_test';
        $pregunta['yaVistaTodas'] = false;

        if(!isset($_SESSION['preguntas_vistas'])){
            $_SESSION['preguntas_vistas'] = [];
        }
        foreach ($pregunta['respuestas'] as &$respuesta) {
            $respuesta['pregunta_id'] = $pregunta['id'];
        }
            if (!$pregunta) {
                header("Location:/partida/verRuleta");
                exit();
            }

            $yaVioEstaPregunta = $this->model->esPreguntaVistaPorUsuario($id_usuario, $pregunta["id"]);
            if (!$yaVioEstaPregunta) {
                $this->model->guardarPreguntaVista($id_usuario, $pregunta["id"]);
            }

            $_SESSION["pregunta_activa_id"] = $pregunta["id"];
        }

        $pregunta["respuestas"] = $this->model->obtenerRespuestasDePregunta($pregunta["id"]);

        foreach ($pregunta["respuestas"] as &$respuesta) {
            $respuesta["pregunta_id"] = $pregunta["id"];
        }

        $pregunta["sesionIniciada"] = isset($_SESSION["usuario"]);
        $pregunta["esAdmin"] = ($_SESSION["usuario"]["rol"] ?? "") === "Administrador";
        $pregunta["nombre_usuario"] = $_SESSION["usuario"]["nombre_usuario"] ?? "user_test";
        $pregunta["yaVistaTodas"] = false;

        $cantPreguntasEnBD = $this->model->cantidadPreguntasEnBD($id_usuario);
        $cantPreguntasYaEchasAUsuario = $this->model->cantidadPreguntasYaEchasAlUsuario($id_usuario);
        $vistasCount = $cantPreguntasYaEchasAUsuario[0]["vistas"];
        $totalCount = $cantPreguntasEnBD[0]["total"];

        if ($vistasCount == $totalCount) {
            $pregunta["yaVistaTodas"] = true;
        }

        if (!isset($_SESSION["timer_inicio"])) {
            $_SESSION["timer_inicio"] = time();
        }

        $pregunta["timer_restante"] = max(0, self::TIEMPO_LIMITE - (time() - $_SESSION["timer_inicio"]));

        $pregunta = $this->preguntaModel->calcularDificultad($pregunta);

        return $this->renderer->render("partidaView", $pregunta);
    }

    public function validarRespuesta()
    {

        $idPregunta = $_POST["id_pregunta"];
        $idRespuesta = $_POST["respuesta_id"] ?? null;

        if (!isset($_SESSION["timer_inicio"])) {
            header("Location:/partida/verRuleta");
            exit();
        }

        $tiempoTranscurrido = time() - $_SESSION["timer_inicio"];
        $timeoutServidor = $tiempoTranscurrido > self::TIEMPO_LIMITE;

        unset($_SESSION["timer_inicio"]);
        unset($_SESSION["pregunta_activa_id"]);
        unset($_SESSION["idCategoria"]);

        $respuestaCorrecta = $this->model->obtenerRespuestaCorrecta($idPregunta);
        $texto = $this->model->obtenerTextoRespuestaCorrecta($idPregunta);
        $_SESSION["texto_correcta"] = $texto["texto"];

        if ($timeoutServidor || $idRespuesta == -1 || $idRespuesta == "") {
            $esCorrecta = false;
        } else {
            $esCorrecta = ($idRespuesta == $respuestaCorrecta["id"]);
        }

        $this->preguntaModel->actualizarEstadisticasPregunta($idPregunta, $esCorrecta ? 1 : 0);

        if ($esCorrecta) {
            if (!isset($_SESSION["puntaje_actual"])) {
                $_SESSION["puntaje_actual"] = 0;
            }

            $_SESSION["puntaje_actual"]++;
            $_SESSION["puntaje_final"] = $_SESSION["puntaje_actual"];

            header("Location:/partida/verRuleta");


        } else {
            $_SESSION["puntaje_final"] = $_SESSION["puntaje_actual"] ?? 0;

            unset($_SESSION["puntaje_actual"]);

            header("Location:/partida/terminada");
        }
        exit();
    }

    public function terminada(){
        $data['puntaje_final'] = $_SESSION['puntaje_final'] ?? 0;
        $data['texto_correcta'] = $_SESSION['texto_correcta'] ?? "";
        $data['sesionIniciada'] = isset($_SESSION["usuario"]);
        $data['esAdmin'] = in_array($_SESSION["usuario"]["rol"] ?? '', ['Administrador', 'Editor']);
        $data['nombre_usuario'] = $_SESSION["usuario"]["nombre_usuario"] ??  'user_test';

        unset($_SESSION["puntaje_final"]);
        unset($_SESSION["texto_correcta"]);
        unset($_SESSION["idCategoria"]);

        $this->renderer->render("terminadaView", $data);
    }

    public function verRuleta()
    {
        $data = [];

        if (isset($_SESSION["idCategoria"])) {
            $idCategoria = (int)$_SESSION["idCategoria"];
            $data["categoriaFijada"] = true;
            $data["idCategoriaFijada"] = $idCategoria;
            $data["indiceRuletaFijado"] = $idCategoria - 1;
            $data["nombreCategoriaFijada"] = self::CATEGORIAS[$idCategoria] ?? "";
        }

        $this->renderer->render("mostrarRuletaView", $data);
    }
}