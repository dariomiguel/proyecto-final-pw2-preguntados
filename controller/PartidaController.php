<?php

class PartidaController
{

    private $model;
    private $preguntaModel;
    private $usuarioModel;
    private $renderer;
    private $request;
    private const TIEMPO_LIMITE = 15;
    /*
     * NOTA: se eliminó la constante CATEGORIAS hardcodeada.
     * Ahora las categorías salen de la tabla `categorias` mediante
     * $this->preguntaModel->obtenerCategorias() (ver INSTRUCCIONES.md),
     * así agregar una categoría en la BD alcanza para que aparezca
     * en la ruleta sin tocar código.
     */

    public function __construct($model,$preguntaModel,$usuarioModel, $renderer, $request)
    {
        $this->model = $model;
        $this->preguntaModel = $preguntaModel;
        $this->usuarioModel = $usuarioModel;
        $this->renderer = $renderer;
        $this->request = $request;
    }

    /*
     * Devuelve la posición (0..n-1) que ocupa la categoría $idCategoria
     * dentro de la lista ordenada. Ese índice es el sector de la ruleta.
     * Antes se usaba "id - 1", que se rompe si algún id no es consecutivo
     * (por ejemplo, si se borra una categoría del medio).
     */
    private function indiceDeCategoria($categorias, $idCategoria)
    {
        foreach ($categorias as $indice => $categoria) {
            if ((int)$categoria['id'] === (int)$idCategoria) {
                return $indice;
            }
        }
        return 0;
    }

    public function girarRuleta()
    {
        $categorias = $this->preguntaModel->obtenerCategorias();

        // Si ya había una categoría fijada en la sesión, se devuelve esa
        if (isset($_SESSION["pregunta_activa_id"], $_SESSION["idCategoria"])) {
            $idCategoria = (int)$_SESSION["idCategoria"];
            $indice = $this->indiceDeCategoria($categorias, $idCategoria);

            header("Content-Type: application/json; charset=utf-8");
            echo json_encode([
                "idCategoria" => $idCategoria,
                "nombreCategoria" => $categorias[$indice]['nombre'] ?? "Desconocida",
                "indiceRuleta" => $indice,
                "bloqueada" => true,
            ]);
            exit();
        }

        // Elección aleatoria DEL SERVIDOR sobre las categorías de la BD
        $indice = array_rand($categorias);
        $categoria = $categorias[$indice];

        $_SESSION["idCategoria"] = (int)$categoria['id'];

        unset($_SESSION["timer_inicio"]);

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            "idCategoria" => (int)$categoria['id'],
            "nombreCategoria" => $categoria['nombre'],
            "indiceRuleta" => $indice,
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
        $nivelUsuario = $this->usuarioModel->getNivelUsuario($id_usuario);

        if (isset($_SESSION["pregunta_activa_id"])) {
            $pregunta = $this->model->obtenerPreguntaPorId($_SESSION["pregunta_activa_id"]);
        } else {
            $pregunta = $this->model->obtenerPreguntaAleatoria($id_usuario, $id_categoria, $nivelUsuario);

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
        $pregunta["esAdmin"] = in_array($_SESSION["usuario"]["rol"] ?? '', ['Administrador', 'Editor']);
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

        $pregunta["puntaje_actual"] = $_SESSION["puntaje_actual"] ?? 0;

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

            //

            header("Location:/partida/verRuleta");


        } else {
            $idUsuario = $_SESSION["usuario"]["id"];
            $aciertos = $_SESSION["puntaje_actual"] ?? 0;
            $preguntasRespondidas = $aciertos + 1;
            $puntaje = $aciertos;

            $this->model->guardarHistorialPartida($idUsuario, $preguntasRespondidas, $aciertos, $puntaje);


            $_SESSION["puntaje_final"] = $aciertos;

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
        $categorias = $this->preguntaModel->obtenerCategorias();

        // JSON con las categorías de la BD para que el JS arme la ruleta
        $data['categorias_json'] = json_encode(
            $categorias,
            JSON_UNESCAPED_UNICODE // conserva tildes: "Programación"
        );

        if (isset($_SESSION["idCategoria"])) {
            $idCategoria = (int)$_SESSION["idCategoria"];
            $indice = $this->indiceDeCategoria($categorias, $idCategoria);

            $data["categoriaFijada"] = true;
            $data["idCategoriaFijada"] = $idCategoria;
            $data["indiceRuletaFijado"] = $indice;
            $data["nombreCategoriaFijada"] = $categorias[$indice]['nombre'] ?? "";
        }

        $this->renderer->render("mostrarRuletaView", $data);
    }
}
