<?php

class CategoriaModel
{


    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function getAllCategorias(){
        $sql = "SELECT * FROM categorias ORDER BY nombre ASC";
        return $resultado = $this->database->query($sql);
    }

    public function crear($datos){

        $sql = "INSERT INTO categorias (nombre, color, color_secundario) VALUES (?, ?, ?)";
        return $resultado = $this->database->execute($sql, [
            $datos['nombre'],
            $datos['color'],
            $datos['color_secundario']
        ]);
    }

    public function eliminar($id){
        $sql = "DELETE FROM categorias WHERE id = ?";
        return $resultado = $this->database->execute($sql, [$id]);
    }
}