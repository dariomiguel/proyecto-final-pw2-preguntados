<?php

class Configurator {

    private $config;

    public function __construct(){
        $this->config = parse_ini_file("config/config.ini");
    }

    public function getRouter(){
        return new Router($this,'lobby', 'ver');
    }

    private function getDatabase(){
        return new MyDatabase(
            $this->config['hostname'],
            $this->config['username'],
            $this->config['password'],
            $this->config['database']
        );
    }

    public function getLobbyController(){
     return new LobbyController($this->getRendeder());
    }

    private function getRendeder(){
        return new MustacheRenderer("view");
    }

    public function getOrDefault($controllerName,$default){
        $getter ='get' . ucfirst($controllerName) . 'Controller';
        return method_exists($this, $getter) ? $this->{$getter}() : $default;

    }
}
