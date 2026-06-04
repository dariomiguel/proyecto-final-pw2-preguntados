CREATE DATABASE IF NOT EXISTS juego_preguntas_db;
USE juego_preguntas_db;

CREATE TABLE usuarios (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          nombre VARCHAR(50) NOT NULL,
                          segundo_nombre VARCHAR(50) NULL,
                          apellido VARCHAR(50) NOT NULL,
                          anio_nacimiento INT NOT NULL,
                          sexo ENUM('Masculino', 'Femenino', 'Prefiero no cargarlo') NOT NULL,
                          pais VARCHAR(50) NOT NULL,
                          ciudad VARCHAR(50) NOT NULL,
                          puntaje_total INT DEFAULT 0 NOT NULL,

                          mail VARCHAR(100) UNIQUE NOT NULL,
                          contrasenia VARCHAR(255) NOT NULL,
                          nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
                          foto_perfil VARCHAR(255) NULL,

                          cuenta_validada TINYINT(1) DEFAULT 0,
                          hash_validacion VARCHAR(255) NULL,

                          rol ENUM('Jugador', 'Editor', 'Administrador') DEFAULT 'Jugador'
);
