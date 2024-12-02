
-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS onfirebd;

-- Seleccionar la base de datos para trabajar
USE onfirebd;

-- Configuraciones iniciales
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- tables
-- Table: flama
CREATE TABLE flama (
    flama_id int AUTO_INCREMENT NOT NULL,
    fecha date  NOT NULL,
    hora time  NOT NULL,
    valor double(30,3)  NOT NULL,
    microcontroladores_id int  NOT NULL,
    CONSTRAINT flama_pk PRIMARY KEY (flama_id)
);

-- Table: humedad
CREATE TABLE humedad (
    humedad_id int AUTO_INCREMENT NOT NULL,
    fecha date  NOT NULL,
    hora time  NOT NULL,
    valor double(30,3)  NOT NULL,
    microcontroladores_id int  NOT NULL,
    CONSTRAINT humedad_pk PRIMARY KEY (humedad_id)
);

-- Table: humo
CREATE TABLE humo (
    humo_id int  AUTO_INCREMENT NOT NULL,
    fecha date  NOT NULL,
    hora time  NOT NULL,
    valor double(30,3)  NOT NULL,
    microcontroladores_id int  NOT NULL,
    CONSTRAINT humo_pk PRIMARY KEY (humo_id)
);

-- Table: microcontroladores
CREATE TABLE microcontroladores (
    microcontroladores_id int AUTO_INCREMENT NOT NULL,
    nombre varchar(50)  NOT NULL,
    ubicaciones_id int  NOT NULL,
    CONSTRAINT microcontroladores_pk PRIMARY KEY (microcontroladores_id)
);

-- Table: temperatura
CREATE TABLE temperatura (
    temperatura_id int AUTO_INCREMENT NOT NULL,
    fecha date  NOT NULL,
    hora time  NOT NULL,
    valor double(30,3)  NOT NULL,
    microcontroladores_id int  NOT NULL,
    CONSTRAINT temperatura_pk PRIMARY KEY (temperatura_id)
);

-- Table: ubicaciones
CREATE TABLE ubicaciones (
    ubicaciones_id int AUTO_INCREMENT NOT NULL,
    descripcion varchar(200)  NOT NULL,
    latitud float  NOT NULL,
    longitud float  NOT NULL,
    CONSTRAINT ubicaciones_pk PRIMARY KEY (ubicaciones_id)
);

-- Table: usuarios
CREATE TABLE usuarios (
    usuarios_id int AUTO_INCREMENT NOT NULL,
    usuario varchar(50)  NOT NULL,
    password varchar(30)  NOT NULL,
    CONSTRAINT usuarios_pk PRIMARY KEY (usuarios_id)
);
