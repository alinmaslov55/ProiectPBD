-- mysql -u root
USE fitness_db;

-- Stergem datele vechi daca exista (pentru a nu avea duplicate la re-rulare)
SET FOREIGN_KEY_CHECKS = 0; -- Dezactivăm verificarea cheilor externe pentru a putea goli tabelele

TRUNCATE TABLE abonamente;
TRUNCATE TABLE clienti;

SET FOREIGN_KEY_CHECKS = 1; -- Reactivăm verificările

-- 1. Inserare Clienti (CNP trebuie sa aiba 13 caractere fix)
INSERT INTO clienti (CNP, nume, prenume, adresa, telefon, disponibil) VALUES
('1900101123456', 'Popescu', 'Ion', 'Str. Libertatii 10', '0722111111', 500.00),
('2920505123456', 'Ionescu', 'Maria', 'Bd. Unirii 20', '0744222222', 1550.00),
('1880808123456', 'Georgescu', 'Vlad', 'Aleea Rozelor 5', '0755333333', 100.00),
('2951212123456', 'Dumitrescu', 'Ana', 'Calea Victoriei 100', '0766444444', 4000.00);

-- 2. Inserare Abonamente (Date strategice)

-- Client 1 (Popescu) - Totul achitat
INSERT INTO abonamente (CNP, serviciu, data_achizitie, pret, suma_incasata) VALUES
('1900101123456', 'Fitness Luni', '2023-01-10', 100.00, 100.00),
('1900101123456', 'Sauna', '2023-02-15', 50.00, 50.00);

-- Client 2 (Ionescu) - Unul achitat, unul partial
INSERT INTO abonamente (CNP, serviciu, data_achizitie, pret, suma_incasata) VALUES
('2920505123456', 'Fitness Anual', '2023-03-01', 1200.00, 1200.00),
('2920505123456', 'Antrenor Personal', '2023-04-01', 500.00, 200.00); -- Rest de plata 300

-- Client 3 (Georgescu) - Datornicul suprem (Cerința 10)
INSERT INTO abonamente (CNP, serviciu, data_achizitie, pret, suma_incasata) VALUES
('1880808123456', 'Fitness Lunar', '2023-05-01', 150.00, 50.00),
('1880808123456', 'Masaj', '2023-05-05', 100.00, 0.00),
('1880808123456', 'Suplimente', '2023-05-10', 200.00, 50.00);

-- Client 4 (Dumitrescu) - VIP (Cerința 9: servicii > 1000 in 2 ani)
INSERT INTO abonamente (CNP, serviciu, data_achizitie, pret, suma_incasata) VALUES
('2951212123456', 'VIP Gold', '2023-06-01', 1500.00, 1500.00),
('2951212123456', 'VIP Platinum', '2024-01-01', 1600.00, 1600.00);