DELIMITER //

DROP PROCEDURE IF EXISTS adauga_abonament //

-- Procedura adauga un abonament si calculeaza automat plata
CREATE PROCEDURE adauga_abonament(
    IN p_cnp CHAR(13),
    IN p_serviciu VARCHAR(20),
    IN p_pret DECIMAL(10,2)
)
BEGIN
    DECLARE v_disponibil DECIMAL(10,2);
    DECLARE v_suma_incasata DECIMAL(10,2);
    DECLARE v_nou_disponibil DECIMAL(10,2);

    -- Preluam disponibilul clientului
    SELECT disponibil
    INTO v_disponibil
    FROM clienti
    WHERE CNP = p_cnp;

    -- Verificam existenta clientului
    IF v_disponibil IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Clientul nu exista';
    END IF;

    -- Calculam suma incasata
    IF v_disponibil >= p_pret THEN
        SET v_suma_incasata = p_pret;
        SET v_nou_disponibil = v_disponibil - p_pret;
    ELSE
        SET v_suma_incasata = v_disponibil;
        SET v_nou_disponibil = 0;
    END IF;

    -- Inseram abonamentul
    INSERT INTO abonamente (CNP, serviciu, data_achizitie, pret, suma_incasata)
    VALUES (p_cnp, p_serviciu, CURDATE(), p_pret, v_suma_incasata);

    -- Actualizam disponibilul clientului
    UPDATE clienti
    SET disponibil = v_nou_disponibil
    WHERE CNP = p_cnp;

    -- Returnam informatii utile
    SELECT
        p_serviciu AS serviciu,
        p_pret AS pret_total,
        v_suma_incasata AS suma_incasata,
        v_nou_disponibil AS disponibil_ramas,
        (p_pret - v_suma_incasata) AS rest_de_plata;
END //

DELIMITER ;