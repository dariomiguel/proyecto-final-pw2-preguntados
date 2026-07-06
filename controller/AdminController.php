<?php

class AdminController
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

    public function ver()
    {
        // Filtro de tiempo: dia | semana | mes | anio | todo
        $filtro = $this->request->get('filtro', 'todo');

        $datos = [
            'sesionIniciada' => true,
            'esAdmin' => true,
            'nombre_usuario' => $_SESSION['nombre_usuario'] ?? '',

            // Estado del filtro seleccionado
            'filtro_dia' => $filtro === 'dia',
            'filtro_semana' => $filtro === 'semana',
            'filtro_mes' => $filtro === 'mes',
            'filtro_anio' => $filtro === 'anio',
            'filtro_todo' => $filtro === 'todo',

            // Tarjetas con totales
            'cantidad_jugadores' => $this->model->cantidadJugadores($filtro),
            'cantidad_partidas' => $this->model->cantidadPartidas($filtro),
            'cantidad_preguntas' => $this->model->cantidadPreguntasActivas($filtro),
            'cantidad_preguntas_creadas' => $this->model->cantidadPreguntasCreadasPorUsuarios($filtro),
            'cantidad_usuarios_nuevos' => $this->model->cantidadUsuariosNuevos($filtro),

            // Gráficos de barras (etiqueta, cantidad, porcentaje)
            'usuarios_por_pais' => $this->conPorcentajes($this->model->usuariosPorPais($filtro)),
            'usuarios_por_sexo' => $this->conPorcentajes($this->model->usuariosPorSexo($filtro)),
            'usuarios_por_edad' => $this->conPorcentajes($this->model->usuariosPorGrupoEdad($filtro)),

            // Tabla de % de respuestas correctas por usuario
            'correctas_por_usuario' => $this->model->porcentajeCorrectasPorUsuario($filtro),
        ];

        $this->renderer->render('adminDashboardView', $datos);
    }

    /*
     * Calcula el porcentaje de cada fila respecto del máximo,
     * para dibujar las barras del gráfico desde el servidor.
     */
    private function conPorcentajes($filas)
    {
        $maximo = 0;
        foreach ($filas as $fila) {
            if ($fila['cantidad'] > $maximo) {
                $maximo = $fila['cantidad'];
            }
        }

        $resultado = [];
        foreach ($filas as $fila) {
            $porcentaje = 0;
            if ($maximo > 0) {
                $porcentaje = round($fila['cantidad'] * 100 / $maximo);
            }
            $resultado[] = [
                'etiqueta' => $fila['etiqueta'],
                'cantidad' => $fila['cantidad'],
                'porcentaje' => $porcentaje,
            ];
        }
        return $resultado;
    }
}
