-- NOMBRE BD: onfireBD

-- tables
-- Table: lecturas
CREATE TABLE lecturas (
    id int  NOT NULL AUTO_INCREMENT,
    fecha date  NOT NULL,
    hora time  NOT NULL,
    valor double(30,3)  NOT NULL,
    sensores_id int  NOT NULL,
    CONSTRAINT lecturas_pk PRIMARY KEY (id)
);

-- Table: microcontroladores
CREATE TABLE microcontroladores (
    id int  NOT NULL AUTO_INCREMENT,
    nombre varchar(50)  NOT NULL,
    ubicaciones_id int  NOT NULL,
    CONSTRAINT microcontroladores_pk PRIMARY KEY (id)
);

-- Table: sensores
CREATE TABLE sensores (
    id int  NOT NULL AUTO_INCREMENT,
    tipo varchar(100)  NOT NULL,
    microcontroladores_id int  NOT NULL,
    CONSTRAINT sensores_pk PRIMARY KEY (id)
);

-- Table: ubicaciones
CREATE TABLE ubicaciones (
    id int  NOT NULL AUTO_INCREMENT,
    descripcion varchar(200)  NOT NULL,
    CONSTRAINT ubicaciones_pk PRIMARY KEY (id)
);

-- Table: usuarios
CREATE TABLE usuarios (
    id int  NOT NULL AUTO_INCREMENT,
    usuario varchar(50)  NOT NULL,
    password varchar(30)  NOT NULL,
    CONSTRAINT usuarios_pk PRIMARY KEY (id)
);

-- foreign keys
-- Reference: lecturas_sensores (table: lecturas)
ALTER TABLE lecturas ADD CONSTRAINT lecturas_sensores FOREIGN KEY lecturas_sensores (sensores_id)
    REFERENCES sensores (id);

-- Reference: microcontroladores_ubicaciones (table: microcontroladores)
ALTER TABLE microcontroladores ADD CONSTRAINT microcontroladores_ubicaciones FOREIGN KEY microcontroladores_ubicaciones (ubicaciones_id)
    REFERENCES ubicaciones (id);

-- Reference: sensores_microcontroladores (table: sensores)
ALTER TABLE sensores ADD CONSTRAINT sensores_microcontroladores FOREIGN KEY sensores_microcontroladores (microcontroladores_id)
    REFERENCES microcontroladores (id);

-- End of file.

