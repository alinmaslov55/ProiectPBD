-- mysql -u root
CREATE DATABASE IF NOT EXISTS fitness_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fitness_db;

CREATE TABLE IF NOT EXISTS clienti (
    CNP CHAR(13) NOT NULL,
    nume VARCHAR(15) NOT NULL,
    prenume VARCHAR(20) NOT NULL,
    adresa VARCHAR(50),
    telefon VARCHAR(15),
    disponibil DECIMAL(10, 2) DEFAULT 0,
    PRIMARY KEY (CNP),
    CONSTRAINT chk_disponibil_pozitiv CHECK (disponibil >= 0)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS abonamente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    CNP CHAR(13) NOT NULL,
    serviciu VARCHAR(20) NOT NULL,
    data_achizitie DATE NOT NULL,
    pret DECIMAL(10, 2) NOT NULL,
    suma_incasata DECIMAL(10, 2) DEFAULT 0,
    CONSTRAINT fk_client_cnp FOREIGN KEY (CNP) 
        REFERENCES clienti(CNP) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB;