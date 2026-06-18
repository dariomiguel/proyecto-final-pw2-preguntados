<?php

class RankingController{

    private $model;
    private $renderer;
    private $request;

    public function __construct($model,$renderer,$request)
    {
        $this->renderer = $renderer;
        $this->model = $model;
        $this->request = $request;
    }

    public function ver(){

        $data['usuarios'] = $this->model->getRankingGlobal();

        $rankingModificado = [];
        foreach ($data['usuarios'] as $index => $usuario) {
            $usuario['puesto'] = $index + 1;
            $rankingModificado[] = $usuario;
        }
        $data['usuarios'] = $rankingModificado;

        return $this->renderer->render("rankingView", $data);
    }

}