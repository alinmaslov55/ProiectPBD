DELIMITER //
--Procedura verifică disponibilul și actualizează suma încasată la adăugarea unui abonament
CREATE OR REPLACE PROCEDURE adauga_abonament(
    IN p_cnp CHAR(13),
    IN p_serviciu VARCHAR(20),
    IN p_pret DECIMAL(10,2)
)
BEGIN
    DECLARE v_disponibil DECIMAL(10,2);
    DECLARE v_suma_incasata DECIMAL(10,2);
    DECLARE v_nou_disponibil DECIMAL(10,2);
    
    -- VERIFICĂ disponibilul clientului
    SELECT disponibil INTO v_disponibil 
    FROM clienti 
    WHERE CNP = p_cnp;
    
    IF v_disponibil IS NULL THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Clientul nu există';
    END IF;
    
    -- CALCULEAZĂ suma încasată conform disponibilului
    IF v_disponibil >= p_pret THEN
        -- Are destui bani: plătește integral, surplusul rămâne în disponibil
        SET v_suma_incasata = p_pret;
        SET v_nou_disponibil = v_disponibil - p_pret;
    ELSE
        -- Nu are destui bani: ia tot ce are, disponibil devine 0
        SET v_suma_incasata = v_disponibil;
        SET v_nou_disponibil = 0;
    END IF;
    
    -- INSEREAZĂ abonamentul cu suma încasată calculată
    INSERT INTO abonamente (CNP, serviciu, data_achizitie, pret, suma_incasata) 
    VALUES (p_cnp, p_serviciu, CURDATE(), p_pret, v_suma_incasata);
    
    -- ACTUALIZEAZĂ disponibilul clientului
    UPDATE clienti 
    SET disponibil = v_nou_disponibil 
    WHERE CNP = p_cnp;
    
    -- RETURNEAZĂ rezultatul
    SELECT 
        'Abonament adăugat cu succes' AS status,
        p_cnp AS cnp_client,
        p_serviciu AS serviciu,
        p_pret AS pret_total,
        v_suma_incasata AS suma_incasata,
        v_nou_disponibil AS disponibil_ramas,
        (p_pret - v_suma_incasata) AS rest_de_plata;
END //

DELIMITER ;