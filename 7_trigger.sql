DELIMITER //
--Calculează automat suma încasată și actualizează disponibilul clientului la inserarea unui abonament
CREATE OR REPLACE TRIGGER trg_calculeaza_automat
BEFORE INSERT ON abonamente
FOR EACH ROW
BEGIN
    DECLARE v_disponibil DECIMAL(10,2);
    DECLARE v_suma_calculata DECIMAL(10,2);
    
    -- Calculează disponibilul curent al clientului
    SELECT disponibil INTO v_disponibil 
    FROM clienti 
    WHERE CNP = NEW.CNP;
    
    -- Dacă clientul există, calculează suma încasată
    IF v_disponibil IS NOT NULL THEN
        IF v_disponibil >= NEW.pret THEN
            -- Are destui bani: plătește integral
            SET v_suma_calculata = NEW.pret;
            -- Actualizează disponibilul
            UPDATE clienti 
            SET disponibil = v_disponibil - NEW.pret 
            WHERE CNP = NEW.CNP;
        ELSE
            -- Nu are destui bani: ia tot ce are
            SET v_suma_calculata = v_disponibil;
            -- Disponibil devine 0
            UPDATE clienti 
            SET disponibil = 0 
            WHERE CNP = NEW.CNP;
        END IF;
        
        -- Setează suma încasată calculată
        SET NEW.suma_incasata = v_suma_calculata;
    ELSE
        -- Dacă clientul nu există, setează suma încasată la 0
        SET NEW.suma_incasata = 0;
    END IF;
END //

DELIMITER ;