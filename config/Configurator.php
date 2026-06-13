<?php
class Configurator
{

    private $config;

    public function __construct()
    {
        $this->config = parse_ini_file("config/config.ini");
    }

    public function getLobbyController()
    {
        return new LobbyController($this->getRenderer());
    }

    public function getPerfilController(){
        return new PerfilController($this->getUsuarioModel(),$this->getRenderer(),new Request());
    }

    public function getLoginController()
    {
        return new LoginController($this->getLoginModel(), $this->getRenderer(), new Request());
    }

    public function getPreguntaController(){
        return new PreguntaController($this->getPreguntaModel(), $this->getRenderer(), new Request());
    }

    private function getPreguntaModel()
    {
        return new PreguntaModel($this->getDatabase());
    }

    private function getDatabase()
    {
        return new MyDatabase(
            $this->config['hostname'],
            $this->config['username'],
            $this->config['password'],
            $this->config['database']
        );
    }

    private function getRenderer()
    {
        return new MustacheRenderer(__DIR__ . '/../view');
    }

//Dejo comentada esta parte que dejo bruno por si tira algún error si no tienen errores se puede borrar
//    private function getRendeder(){
//        return new MustacheRenderer("view");
//    }

    private function getLoginModel()
    {
        return new LoginModel($this->getDatabase());
    }

    private function getRegistroModel()
    {
        return new RegistroModel($this->getDatabase());
    }

    private function getUsuarioModel(){
        return new UsuarioModel($this->getDatabase());
    }

    public function getRegistroController()
    {
        return new RegistroController($this->getRegistroModel(), $this->getRenderer(), new Request());
    }

    public function getRouter()
    {
        return new Router($this, 'lobby', 'ver');
    }

    public function getOrDefault($controllerName, $defaultControllerName)
    {

        $getter = 'get' . ucfirst($controllerName) . 'Controller';
        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }

        $defaultGetter = 'get' . ucfirst($defaultControllerName) . 'Controller';
        return $this->{$defaultGetter}();
    }



}
