DELIMITER //

-- Stergem functia daca exista
DROP FUNCTION IF EXISTS get_rest_plata;

-- Functie care returneaza restul de plata pentru un serviciu
CREATE FUNCTION get_rest_plata(
    p_cnp CHAR(13),
    p_serviciu VARCHAR(20)
)
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE v_total_pret DECIMAL(10,2);
    DECLARE v_total_incasat DECIMAL(10,2);

    -- Calculam totalul pret si totalul incasat pentru serviciul respectiv
    SELECT 
        COALESCE(SUM(pret), 0),
        COALESCE(SUM(suma_incasata), 0)
    INTO 
        v_total_pret, 
        v_total_incasat
    FROM abonamente
    WHERE CNP = p_cnp 
      AND serviciu = p_serviciu;

    -- Returnam restul de plata, minim 0
    RETURN GREATEST(v_total_pret - v_total_incasat, 0);
END //

DELIMITER ;
