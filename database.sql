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
                          mail VARCHAR(100) UNIQUE NOT NULL,
                          contrasenia VARCHAR(255) NOT NULL,
                          nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
                          foto_perfil VARCHAR(255) NULL,
                          puntaje_total INT DEFAULT 0 NOT NULL,
                          nivel ENUM('Malo', 'Medio', 'Bueno') DEFAULT 'Medio' NOT NULL,
                          cuenta_validada TINYINT(1) DEFAULT 0,
                          hash_validacion VARCHAR(255) NULL,
                          rol ENUM('Jugador', 'Editor', 'Administrador') DEFAULT 'Jugador'
);

CREATE TABLE categorias (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            nombre VARCHAR(50) NOT NULL,
                            color VARCHAR(7) NOT NULL DEFAULT '#000000'
);

CREATE TABLE preguntas (
                           id INT AUTO_INCREMENT PRIMARY KEY,
                           enunciado TEXT NOT NULL,
                           categoria_id INT NOT NULL,
                           estado ENUM('pendiente', 'aprobada', 'baja') DEFAULT 'pendiente' NOT NULL,
                           total_respuestas INT DEFAULT 0 NOT NULL,
                           total_aciertos INT DEFAULT 0 NOT NULL,
                           creado_por_usuario_id INT NOT NULL,
                           FOREIGN KEY (categoria_id) REFERENCES categorias(id),
                           FOREIGN KEY (creado_por_usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE respuestas (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            pregunta_id INT NOT NULL,
                            texto VARCHAR(255) NOT NULL,
                            es_correcta TINYINT(1) DEFAULT 0 NOT NULL,
                            FOREIGN KEY (pregunta_id) REFERENCES preguntas(id)
);

CREATE TABLE partidas (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          usuario_id INT NOT NULL,
                          preguntas_respondidas INT DEFAULT 0 NOT NULL,
                          aciertos INT DEFAULT 0 NOT NULL,
                          puntaje INT DEFAULT 0 NOT NULL,
                          fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE usuarios_preguntas_vistas (
                                           usuario_id INT NOT NULL,
                                           pregunta_id INT NOT NULL,
                                           PRIMARY KEY (usuario_id, pregunta_id),
                                           FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
                                           FOREIGN KEY (pregunta_id) REFERENCES preguntas(id)
);

INSERT INTO usuarios
(nombre, apellido, anio_nacimiento, sexo, pais, ciudad, mail, contrasenia, nombre_usuario, cuenta_validada, rol)
VALUES ('Admin', 'Admin', 2000, 'Prefiero no cargarlo', 'Argentina', 'Buenos Aires',
        'admin@admin.com',
        '$2y$10$77mq11Fvv5ktPsFV95awEe4vW7v8oRLOVA3g0S24TZGAU2QnI2OES',
        'admin', 1, 'Administrador');