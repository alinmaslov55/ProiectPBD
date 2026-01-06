-- Selectam baza de date
USE fitness_db;

-- Stergem triggerul daca exista
DROP TRIGGER IF EXISTS trg_calculeaza_automat;

DELIMITER //

-- Trigger care calculeaza automat suma incasata si actualizeaza disponibilul
CREATE TRIGGER trg_calculeaza_automat
BEFORE INSERT ON abonamente
FOR EACH ROW
BEGIN
    DECLARE v_disponibil DECIMAL(10,2);

    -- Preluam disponibilul clientului
    SELECT disponibil
    INTO v_disponibil
    FROM clienti
    WHERE CNP = NEW.CNP;

    -- Calculam suma incasata si actualizam disponibilul
    IF v_disponibil >= NEW.pret THEN
        SET NEW.suma_incasata = NEW.pret;
        UPDATE clienti
        SET disponibil = disponibil - NEW.pret
        WHERE CNP = NEW.CNP;
    ELSE
        SET NEW.suma_incasata = v_disponibil;
        UPDATE clienti
        SET disponibil = 0
        WHERE CNP = NEW.CNP;
    END IF;
END //

DELIMITER ;
