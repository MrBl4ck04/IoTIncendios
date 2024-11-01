-- NOMBRE BD: onfireBD

-- tables
-- Table: co2
CREATE TABLE co2 (
    id int  NOT NULL,
    fecha date  NOT NULL,
    hora time  NOT NULL,
    valor double(30,3)  NOT NULL,
    CONSTRAINT co2_pk PRIMARY KEY (id)
);

-- Table: humedad
CREATE TABLE humedad (
    id int  NOT NULL,
    fecha date  NOT NULL,
    hora time  NOT NULL,
    valor double(30,3)  NOT NULL,
    CONSTRAINT humedad_pk PRIMARY KEY (id)
);

-- Table: humo
CREATE TABLE humo (
    id int  NOT NULL,
    fecha date  NOT NULL,
    hora time  NOT NULL,
    valor double(30,3)  NOT NULL,
    CONSTRAINT humo_pk PRIMARY KEY (id)
);

-- Table: temperatura
CREATE TABLE temperatura (
    id int  NOT NULL,
    fecha date  NOT NULL,
    hora time  NOT NULL,
    valor double(30,3)  NOT NULL,
    CONSTRAINT temperatura_pk PRIMARY KEY (id)
);

-- Table: usuarios
CREATE TABLE usuarios (
    id int  NOT NULL,
    usuario varchar(50)  NOT NULL,
    password varchar(30)  NOT NULL,
    CONSTRAINT usuarios_pk PRIMARY KEY (id)
);

-- End of file.

