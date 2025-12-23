DELIMITER //
--funcție care returnează restul de plată
CREATE OR REPLACE FUNCTION get_rest_plata(p_cnp CHAR(13), p_serviciu VARCHAR(20)) 
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE v_total_pret DECIMAL(10,2);
    DECLARE v_total_incasat DECIMAL(10,2);
    DECLARE v_rest DECIMAL(10,2);
    
    -- Calculează total preț și total încasat pentru serviciul respectiv
    SELECT 
        COALESCE(SUM(pret), 0),
        COALESCE(SUM(suma_incasata), 0)
    INTO 
        v_total_pret, 
        v_total_incasat
    FROM abonamente
    WHERE CNP = p_cnp 
      AND serviciu = p_serviciu;
    
    -- Calculează restul de plată
    SET v_rest = v_total_pret - v_total_incasat;
    
    -- Returnează restul (minimum 0)
    RETURN GREATEST(v_rest, 0);
END //

DELIMITER ;