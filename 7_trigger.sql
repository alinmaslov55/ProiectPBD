USE fitness_db;

DELIMITER //

DROP TRIGGER IF EXISTS trg_calculeaza_automat //

CREATE TRIGGER trg_calculeaza_automat
BEFORE INSERT ON abonamente
FOR EACH ROW
BEGIN
    DECLARE v_disponibil DECIMAL(10,2);

    -- Preluam disponibilul actual al clientului
    SELECT disponibil INTO v_disponibil FROM clienti WHERE CNP = NEW.CNP;

    -- Logica: calculam suma incasata din disponibil
    IF v_disponibil >= NEW.pret THEN
        SET NEW.suma_incasata = NEW.pret;
        -- Actualizam disponibilul: surplusul ramane in cont
        UPDATE clienti SET disponibil = disponibil - NEW.pret WHERE CNP = NEW.CNP;
    ELSE
        -- Daca nu are destui bani, incasam tot ce are
        SET NEW.suma_incasata = v_disponibil;
        UPDATE clienti SET disponibil = 0 WHERE CNP = NEW.CNP;
    END IF;
END //

DELIMITER ;