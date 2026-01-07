USE fitness_db;

DELIMITER //

DROP PROCEDURE IF EXISTS adauga_abonament //

-- Procedura pentru cerinta 4: adauga abonamentul
CREATE PROCEDURE adauga_abonament(
    IN p_cnp CHAR(13),
    IN p_serviciu VARCHAR(20),
    IN p_pret DECIMAL(10,2)
)
BEGIN
    -- Verificam daca clientul exista inainte de a incerca inserarea
    IF NOT EXISTS (SELECT 1 FROM clienti WHERE CNP = p_cnp) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Eroare: Clientul nu exista!';
    END IF;

    -- Inseram abonamentul. Trigger-ul va prelua automat restul logicii
    INSERT INTO abonamente (CNP, serviciu, data_achizitie, pret)
    VALUES (p_cnp, p_serviciu, CURDATE(), p_pret);

    -- Afisam rezultatul final dupa ce Trigger-ul si-a facut treaba
    SELECT * FROM abonamente WHERE id = LAST_INSERT_ID();
END //

DELIMITER ;