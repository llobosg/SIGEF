-- Tabla VEHICULOS
/Applications/XAMPP/xamppfiles/bin/mysql \
  --host=crossover.proxy.rlwy.net \
  --port=27546 \
  --user=root \
  --password=BsyHHoiCkOcAHRulqExPQNqlzhIgHWGx \
  railway -e "CREATE TABLE IF NOT EXISTS VEHICULOS (
    id_vehiculo INT AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    patente VARCHAR(10) NOT NULL UNIQUE,
    nombre_vehiculo VARCHAR(50) NOT NULL,
    rev_tecnica DATE,
    permiso_circ DATE,
    nro_soap INT,
    seguro ENUM('Si', 'No') NOT NULL DEFAULT 'No',
    aseguradora VARCHAR(50),
    nro_poliza VARCHAR(20)
);"

-- Tabla PERSONAL
/Applications/XAMPP/xamppfiles/bin/mysql \
  --host=crossover.proxy.rlwy.net \
  --port=27546 \
  --user=root \
  --password=BsyHHoiCkOcAHRulqExPQNqlzhIgHWGx \
  railway -e "CREATE TABLE IF NOT EXISTS PERSONAL (
    id_personal INT AUTO_INCREMENT PRIMARY KEY,
    rut VARCHAR(15) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    fecha_nac DATE,
    direccion VARCHAR(100),
    comuna VARCHAR(50),
    celular VARCHAR(50),
    email VARCHAR(50),
    tipo_licencia ENUM('A1', 'A2', 'B') NOT NULL,
    fecha_venc_lic lic DATE
);"

-- Tabla MONTO (catálogo de tipos de monto)
/Applications/XAMPP/xamppfiles/bin/mysql \
  --host=crossover.proxy.rlwy.net \
  --port=27546 \
  --user=root \
  --password=BsyHHoiCkOcAHRulqExPQNqlzhIgHWGx \
  railway -e "CREATE TABLE MONTO (
    id_monto INT AUTO_INCREMENT PRIMARY KEY,
    id_vehiculo INT,
    nombre_vehiculo VARCHAR(50) NOT NULL,
    tipo_monto VARCHAR(50) NOT NULL UNIQUE,
    monto INT
);"

/Applications/XAMPP/xamppfiles/bin/mysql \
  --host=crossover.proxy.rlwy.net \
  --port=27546 \
  --user=root \
  --password=BsyHHoiCkOcAHRulqExPQNqlzhIgHWGx \
  railway -e "INSERT INTO MONTO (tipo_monto) VALUES ('Guía'), ('Distancia'), ('día')
ON DUPLICATE KEY UPDATE tipo_monto = tipo_monto
);"

-- Insertar valores iniciales en MONTO
INSERT INTO MONTO (tipo_monto) VALUES ('Guía'), ('Distancia'), ('día')
ON DUPLICATE KEY UPDATE tipo_monto = tipo_monto;

-- Tabla FACTURACION
/Applications/XAMPP/xamppfiles/bin/mysql \
  --host=crossover.proxy.rlwy.net \
  --port=27546 \
  --user=root \
  --password=BsyHHoiCkOcAHRulqExPQNqlzhIgHWGx \
  railway -e "CREATE TABLE IF NOT EXISTS FACTURACION (
    id_factura INT AUTO_INCREMENT PRIMARY KEY,
    nro_factura INT NOT NULL,
    id_vehiculo INT NOT NULL,
    nombre_vehiculo VARCHAR(50) NOT NULL,
    fecha DATE NOT NULL,
    tipo_monto VARCHAR(50) NOT NULL,
    qty_tipo_monto INT NOT NULL DEFAULT 1,
    monto INT NOT NULL,
    llave_mes VARCHAR(50) NOT NULL,
    FOREIGN KEY (id_vehiculo) REFERENCES VEHICULOS(id_vehiculo) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (tipo_monto) REFERENCES MONTO(tipo_monto) ON DELETE RESTRICT ON UPDATE CASCADE
);"

-- Tabla PAGOS
/Applications/XAMPP/xamppfiles/bin/mysql \
  --host=crossover.proxy.rlwy.net \
  --port=27546 \
  --user=root \
  --password=BsyHHoiCkOcAHRulqExPQNqlzhIgHWGx \
  railway -e "CREATE TABLE PAGOS (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    llave_mes VARCHAR(50) NOT NULL,
    nro_factura INT NOT NULL,
    id_vehiculo INT NOT NULL,
    nombre_vehiculo VARCHAR(50) NOT NULL,
    fecha DATE NOT NULL,
    id_personal INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo_monto VARCHAR(50) NOT NULL,
    qty_pago_tipo_monto INT NOT NULL DEFAULT 1,
    monto INT NOT NULL,
    FOREIGN KEY (id_vehiculo) REFERENCES VEHICULOS(id_vehiculo) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_personal) REFERENCES PERSONAL(id_personal) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (tipo_monto) REFERENCES MONTO(tipo_monto) ON DELETE RESTRICT ON UPDATE CASCADE
);"

-- Tabla MANTENCION
/Applications/XAMPP/xamppfiles/bin/mysql \
  --host=crossover.proxy.rlwy.net \
  --port=27546 \
  --user=root \
  --password=BsyHHoiCkOcAHRulqExPQNqlzhIgHWGx \
  railway -e "CREATE TABLE MANTENCION (
    id_mantencion INT AUTO_INCREMENT PRIMARY KEY,
    id_vehiculo INT NOT NULL,
    nombre_vehiculo VARCHAR(50) NOT NULL,
    fecha_mant DATE NOT NULL,
    kilometraje INT NOT NULL,
    tipo_mant ENUM('correctiva', 'programada') NOT NULL,
    reparación VARCHAR(100),
    taller VARCHAR(50),
    costo INT NOT NULL,
    FOREIGN KEY (id_vehiculo) REFERENCES VEHICULOS(id_vehiculo) ON DELETE CASCADE ON UPDATE CASCADE
);"