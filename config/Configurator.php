<?php
class Configurator{

    private $config;

    public function __construct(){
        $this->config = parse_ini_file("config/config.ini");
    }

    private function getDatabase(){
        return new MyDatabase(
            $this->config['hostname'],
            $this->config['username'],
            $this->config['password'],
            $this->config['database']
        );
    }

    public function getRouter(){
        return new Router($this, 'lobby', 'ver');
    }

    public function getOrDefault($controllerName, $defaultControllerName){
        $getter = 'get' . ucfirst($controllerName) . 'Controller';
        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }
        $defaultGetter = 'get' . ucfirst($defaultControllerName) . 'Controller';
        return $this->{$defaultGetter}();
    }

    public function getLobbyController(){
        return new LobbyController($this->getUsuarioModel(), $this->getRenderer());
    }

    public function getPerfilController(){
        return new PerfilController($this->getUsuarioModel(),$this->getRenderer(),new Request());
    }

    public function getLoginController(){
        return new LoginController($this->getLoginModel(), $this->getRenderer(), new Request());
    }

    public function getRegistroController(){
        return new RegistroController($this->getRegistroModel(), $this->getRenderer(), new Request());
    }

    public function getPartidaController(){
        return new PartidaController($this->getPartidaModel(),$this->getPreguntaModel(), $this->getUsuarioModel() ,$this->getRenderer(), new Request());
    }

    private function getUsuarioModel(){
        return new UsuarioModel($this->getDatabase());
    }

    private function getLoginModel(){
        return new LoginModel($this->getDatabase());
    }

    private function getRegistroModel(){
        return new RegistroModel($this->getDatabase());
    }

    private function getRenderer(){
        return new MustacheRenderer(__DIR__ . '/../view');
    }













    private function getPartidaModel(){
        return new PartidaModel($this->getDatabase());
    }

    public function getPreguntaController(){
        return new PreguntaController($this->getPreguntaModel(), $this->getRenderer(), new Request());
    }

    private function getPreguntaModel(){
        return new PreguntaModel($this->getDatabase());
    }

    private function getCategoriaController(){
        return new CategoriaController($this->getCategoriaModel(), $this->getRenderer(), new Request());
    }

    private function getCategoriaModel()
    {
        return new CategoriaModel($this->getDatabase());
    }

    public function getRankingController(){
        return new RankingController($this->getUsuarioModel(), $this->getRenderer(), new Request());
    }

    public function getAdminController(){
        return new AdminController($this->getEstadisticaModel(), $this->getRenderer(), new Request());
    }

    public function getEstadisticaModel()
    {
        return new EstadisticaModel($this->getDatabase());
    }



}
