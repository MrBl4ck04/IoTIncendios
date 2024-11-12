-- NOMBRE BD: onfireBD

-- tables
-- Table: co2
CREATE TABLE co2 (
    id int  NOT NULL AUTO_INCREMENT,
    fecha date  NOT NULL,
    hora time  NOT NULL,
    valor double(30,3)  NOT NULL,
    microcontroladores_id int  NOT NULL,
    CONSTRAINT co2_pk PRIMARY KEY (id)
);

-- Table: humedad
CREATE TABLE humedad (
    id int  NOT NULL AUTO_INCREMENT,
    fecha date  NOT NULL,
    hora time  NOT NULL,
    valor double(30,3)  NOT NULL,
    microcontroladores_id int  NOT NULL,
    CONSTRAINT humedad_pk PRIMARY KEY (id)
);

-- Table: humo
CREATE TABLE humo (
    id int  NOT NULL AUTO_INCREMENT,
    fecha date  NOT NULL,
    hora time  NOT NULL,
    valor double(30,3)  NOT NULL,
    microcontroladores_id int  NOT NULL,
    CONSTRAINT humo_pk PRIMARY KEY (id)
);

-- Table: microcontroladores
CREATE TABLE microcontroladores (
    id int  NOT NULL AUTO_INCREMENT,
    nombre varchar(50)  NOT NULL,
    ubicaciones_id int  NOT NULL,
    CONSTRAINT microcontroladores_pk PRIMARY KEY (id)
);

-- Table: temperatura
CREATE TABLE temperatura (
    id int  NOT NULL AUTO_INCREMENT,
    fecha date  NOT NULL,
    hora time  NOT NULL,
    valor double(30,3)  NOT NULL,
    microcontroladores_id int  NOT NULL,
    CONSTRAINT temperatura_pk PRIMARY KEY (id)
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
-- Reference: co2_microcontroladores (table: co2)
ALTER TABLE co2 ADD CONSTRAINT co2_microcontroladores FOREIGN KEY co2_microcontroladores (microcontroladores_id)
    REFERENCES microcontroladores (id);

-- Reference: humedad_microcontroladores (table: humedad)
ALTER TABLE humedad ADD CONSTRAINT humedad_microcontroladores FOREIGN KEY humedad_microcontroladores (microcontroladores_id)
    REFERENCES microcontroladores (id);

-- Reference: humo_microcontroladores (table: humo)
ALTER TABLE humo ADD CONSTRAINT humo_microcontroladores FOREIGN KEY humo_microcontroladores (microcontroladores_id)
    REFERENCES microcontroladores (id);

-- Reference: microcontroladores_ubicaciones (table: microcontroladores)
ALTER TABLE microcontroladores ADD CONSTRAINT microcontroladores_ubicaciones FOREIGN KEY microcontroladores_ubicaciones (ubicaciones_id)
    REFERENCES ubicaciones (id);

-- Reference: temperatura_microcontroladores (table: temperatura)
ALTER TABLE temperatura ADD CONSTRAINT temperatura_microcontroladores FOREIGN KEY temperatura_microcontroladores (microcontroladores_id)
    REFERENCES microcontroladores (id);

-- End of file.

