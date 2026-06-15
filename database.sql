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
                            color VARCHAR(7) NOT NULL DEFAULT '#000000',
                            color_secundario VARCHAR(7) NOT NULL DEFAULT '#000000'
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

INSERT INTO categorias (nombre, color, color_secundario)
VALUES
    ('Geografía', '#3498DB', '#62AFE3'),
    ('Historia', '#F1C40F', '#F4D03F'),
    ('Arte', '#9B59B6','#B27FC7'),
    ('Ciencia', '#2ECC71','#54D88C'),
    ('Entretenimiento', '#E67E22','#EB9A51'),
    ('Deportes', '#E74C3C','#ED7A6E'),
    ('Programación', '#6C5CE7','#9F94EF');

-- Pregunta 1
INSERT INTO preguntas (
    enunciado,
    categoria_id,
    estado,
    creado_por_usuario_id
)
VALUES (
           '¿Cuál es la capital de Argentina?',
           1,
           'aprobada',
           1
       );

INSERT INTO respuestas (pregunta_id, texto, es_correcta)
VALUES
    (1, 'Buenos Aires', 1),
    (1, 'Córdoba', 0),
    (1, 'Rosario', 0),
    (1, 'Mendoza', 0);


-- Pregunta 2
INSERT INTO preguntas (
    enunciado,
    categoria_id,
    estado,
    creado_por_usuario_id
)
VALUES (
           '¿Quién escribió Don Quijote de la Mancha?',
           3,
           'aprobada',
           1
       );

INSERT INTO respuestas (pregunta_id, texto, es_correcta)
VALUES
    (2, 'Miguel de Cervantes', 1),
    (2, 'Gabriel García Márquez', 0),
    (2, 'Jorge Luis Borges', 0),
    (2, 'Pablo Neruda', 0);


-- Pregunta 3
INSERT INTO preguntas (
    enunciado,
    categoria_id,
    estado,
    creado_por_usuario_id
)
VALUES (
           '¿Cuál es el planeta más grande del sistema solar?',
           4,
           'aprobada',
           1
       );

INSERT INTO respuestas (pregunta_id, texto, es_correcta)
VALUES
    (3, 'Júpiter', 1),
    (3, 'Saturno', 0),
    (3, 'Marte', 0),
    (3, 'Venus', 0);


-- Pregunta 4
INSERT INTO preguntas (
    enunciado,
    categoria_id,
    estado,
    creado_por_usuario_id
)
VALUES (
           '¿En qué lenguaje está escrito principalmente PHP?',
           7,
           'aprobada',
           1
       );

INSERT INTO respuestas (pregunta_id, texto, es_correcta)
VALUES
    (4, 'C', 1),
    (4, 'Java', 0),
    (4, 'Python', 0),
    (4, 'JavaScript', 0);


-- Pregunta 5
INSERT INTO preguntas (
    enunciado,
    categoria_id,
    estado,
    creado_por_usuario_id
)
VALUES (
           '¿Cuánto es 7 x 8?',
           4,
           'aprobada',
           1
       );

INSERT INTO respuestas (pregunta_id, texto, es_correcta)
VALUES
    (5, '56', 1),
    (5, '48', 0),
    (5, '54', 0),
    (5, '64', 0);

-- Pregunta 6 (Categoría: Historia)
INSERT INTO preguntas (
    enunciado,
    categoria_id,
    estado,
    creado_por_usuario_id
)
VALUES (
           '¿En qué año llegó el hombre a la Luna por primera vez?',
           2,  -- Historia
           'aprobada',
           1
       );

INSERT INTO respuestas (pregunta_id, texto, es_correcta)
VALUES
    (6, '1969', 1),
    (6, '1965', 0),
    (6, '1972', 0),
    (6, '1961', 0);

-- Pregunta 7 (Categoría: Entretenimiento)
INSERT INTO preguntas (
    enunciado,
    categoria_id,
    estado,
    creado_por_usuario_id
)
VALUES (
           '¿Quién interpreta a Iron Man en el Universo Cinematográfico de Marvel?',
           5,  -- Entretenimiento
           'aprobada',
           1
       );

INSERT INTO respuestas (pregunta_id, texto, es_correcta)
VALUES
    (7, 'Robert Downey Jr.', 1),
    (7, 'Chris Evans', 0),
    (7, 'Scarlett Johansson', 0),
    (7, 'Chris Hemsworth', 0);

-- Pregunta 8 (Categoría: Deportes)
INSERT INTO preguntas (
    enunciado,
    categoria_id,
    estado,
    creado_por_usuario_id
)
VALUES (
           '¿Cuál es el país con más títulos de la Copa Mundial de Fútbol masculina?',
           6,  -- Deportes
           'aprobada',
           1
       );

INSERT INTO respuestas (pregunta_id, texto, es_correcta)
VALUES
    (8, 'Brasil', 1),
    (8, 'Alemania', 0),
    (8, 'Argentina', 0),
    (8, 'Italia', 0);

-- Pregunta 9 (Categoría: Ciencia)
INSERT INTO preguntas (
    enunciado,
    categoria_id,
    estado,
    creado_por_usuario_id
)
VALUES (
           '¿Qué órgano del cuerpo humano es el principal encargado de bombear la sangre?',
           4,  -- Ciencia
           'aprobada',
           1
       );

INSERT INTO respuestas (pregunta_id, texto, es_correcta)
VALUES
    (9, 'El corazón', 1),
    (9, 'El cerebro', 0),
    (9, 'El hígado', 0),
    (9, 'Los pulmones', 0);

-- Pregunta 10 (Categoría: Arte)
INSERT INTO preguntas (
    enunciado,
    categoria_id,
    estado,
    creado_por_usuario_id
)
VALUES (
           '¿Quién pintó "La noche estrellada"?',
           3,  -- Arte
           'aprobada',
           1
       );

INSERT INTO respuestas (pregunta_id, texto, es_correcta)
VALUES
    (10, 'Vincent van Gogh', 1),
    (10, 'Pablo Picasso', 0),
    (10, 'Claude Monet', 0),
    (10, 'Salvador Dalí', 0);