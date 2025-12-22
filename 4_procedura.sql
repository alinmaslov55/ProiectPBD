DELIMITER //

CREATE PROCEDURE adauga_abonament(
    IN p_cnp CHAR(13),
    IN p_serviciu VARCHAR(20),
    IN p_pret DECIMAL(10,2)
)
BEGIN
    -- Procedura doar incearca sa insereze. 
    -- Trigger-ul 'trg_procesare_plata' va intercepta si va ajusta suma_incasata automat.
    INSERT INTO abonamente (CNP, serviciu, data_achizitie, pret) 
    VALUES (p_cnp, p_serviciu, CURDATE(), p_pret);
END //

DELIMITER ;