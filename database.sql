create database if not exists juego_preguntas_db;
use juego_preguntas_db;

create table juego_preguntas_db.usuarios
(
    id int auto_increment primary key,
    nombre varchar(50) not null,
    segundo_nombre varchar(50) null,
    apellido varchar(50) not null,
    anio_nacimiento int not null,
    sexo ENUM('Masculino', 'Femenino', 'Prefiero no cargarlo') not null,
    pais varchar(50) not null,
    ciudad varchar(50) not null
);
