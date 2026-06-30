<?php

class PreguntaModel
{

    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }


    public function crear($datos){

        //$pregunta['estado'] = ($pregunta['rol' === 'Administrador']) ? 'aprobada' : 'pendiente';

        $sql = "INSERT INTO preguntas 
                (enunciado,
                 categoria_id,
                 estado,
                 creado_por_usuario_id
                 ) VALUES (?, ?, ?, ?)";


       $resultado = $this->database->execute($sql, [
            $datos['enunciado'],
            $datos['categoria_id'],
            $datos['estado'],
            $datos['creado_por_usuario_id']
        ]);


       $idPregunta = $this->database->lastInsertId();

       $sqlRespuesta = "INSERT INTO respuestas 
                        (pregunta_id,
                         texto,
                         es_correcta
                         ) VALUES (?, ?, ?)";

       foreach ($datos['respuestas'] as $respuesta) {
           $this->database->execute($sqlRespuesta, [
               $idPregunta,
               $respuesta['texto'],
               $respuesta['es_correcta']
           ]);
       }


    }

    public function getCategorias() {
        return $this->database->query("SELECT id, nombre FROM categorias ORDER BY nombre");
    }

    public function getPreguntasPendientes() {
        $sql = "SELECT p.id, p.enunciado, c.nombre AS categoria, u.nombre_usuario
                FROM preguntas p
                JOIN categorias c ON p.categoria_id = c.id
                JOIN usuarios u ON p.creado_por_usuario_id = u.id
                WHERE p.estado = 'pendiente'";
        $preguntas = $this->database->query($sql);

        foreach ($preguntas as &$pregunta) {
            $pregunta['respuestas'] = $this->database->query(
                "SELECT texto, es_correcta FROM respuestas WHERE pregunta_id = ?",
                [$pregunta['id']]
            );
        }
        unset($pregunta);
        return $preguntas;
    }
    public function getPreguntasAprobadas() {

        $sql = "SELECT p.id, p.enunciado, c.nombre AS categoria
                FROM preguntas p
                JOIN categorias c ON p.categoria_id = c.id
                WHERE p.estado = 'aprobada'";

       $preguntasAprobadas = $this->database->query("$sql");

        foreach ($preguntasAprobadas as &$pregunta) {
            $pregunta['respuestas'] = $this->database->query(
                "SELECT texto, es_correcta FROM respuestas WHERE pregunta_id = ?",
                [$pregunta['id']]
            );
        }
        unset($pregunta);
        return $preguntasAprobadas;
    }

    public function cambiarEstadoPregunta($idPregunta, $estado) {

        $sql = "UPDATE preguntas 
                SET estado = ?
                WHERE id = ?";
        $this->database->execute($sql, [$estado, $idPregunta]);

    }

    public function getPregunta($idPregunta) {
       $pregunta =  $this->database->query( "
        SELECT p.id, p.enunciado, p.categoria_id, c.nombre AS categoria
        FROM preguntas p
        JOIN categorias c ON p.categoria_id = c.id
        WHERE p.id = ?", [$idPregunta])[0];

        $categorias = $this->getCategorias();
        foreach ($categorias as &$cat) {
            $cat['seleccionada'] = ($cat['id'] == $pregunta['categoria_id']);
        }
        $pregunta['categorias'] = $categorias;

        $pregunta['respuestas'] = $this->database->query("
        SELECT id, texto, es_correcta FROM respuestas
        WHERE pregunta_id = ?", [$idPregunta]);

        return $pregunta;
    }

    public function actualizar($idPregunta, $enunciado, $categoria_id, $respuestas) {
        $this->database->execute(
            "UPDATE preguntas 
            SET enunciado = ?, categoria_id = ? 
            WHERE id = ?", [$enunciado, $categoria_id, $idPregunta]);

        foreach ($respuestas as $respuesta) {
            $this->database->execute(
                "UPDATE respuestas SET texto = ?, es_correcta = ? WHERE id = ?",
                [$respuesta['texto'], $respuesta['es_correcta'], $respuesta['id']]
            );
        }
    }

    public function calcularDificultad($preguntas){
        $totalRespuestas = $preguntas['total_respuestas'];
        $totalAciertos = $preguntas['total_aciertos'];

        $dificultadPregunta = "media";

        if($totalRespuestas > 0){
            $porcetajeAciertos = ($totalAciertos / $totalRespuestas) * 100;

            if($porcetajeAciertos > 70){
                $dificultadPregunta = "fácil";
            }elseif ($porcetajeAciertos < 30) {
                $dificultadPregunta = "díficil";
            }
        }
        $preguntas['dificultad'] = $dificultadPregunta;

        return $preguntas;

    }

    public function actualizarEstadisticasPregunta($idPregunta, $esCorrecta){

        $sql = "UPDATE preguntas
                SET total_respuestas = total_respuestas + 1,
                    total_aciertos = total_aciertos + ?
                WHERE id = ? ";

        return $this->database->execute($sql, [$esCorrecta, $idPregunta]);
    }

}