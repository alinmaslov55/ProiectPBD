DELIMITER //

CREATE TRIGGER trg_procesare_plata
BEFORE INSERT ON abonamente
FOR EACH ROW
BEGIN
    DECLARE v_disponibil DECIMAL(10,2);

    -- 1. Citim cati bani are clientul in acest moment
    SELECT disponibil INTO v_disponibil 
    FROM clienti 
    WHERE CNP = NEW.CNP;

    -- 2. Logica de plata
    IF v_disponibil >= NEW.pret THEN
        -- Cazul fericit: Are destui bani pentru plata integrala
        SET NEW.suma_incasata = NEW.pret;
        
        -- Scadem banii din contul clientului
        UPDATE clienti 
        SET disponibil = disponibil - NEW.pret 
        WHERE CNP = NEW.CNP;
        
    ELSE
        -- Cazul nefericit: Nu are destui bani (plateste partial sau deloc)
        -- Ii luam tot ce are (v_disponibil), chiar daca e 0
        SET NEW.suma_incasata = v_disponibil;
        
        -- Soldul clientului devine 0
        UPDATE clienti 
        SET disponibil = 0 
        WHERE CNP = NEW.CNP;
    END IF;

END;
//

DELIMITER ;


DELIMITER //

CREATE FUNCTION get_rest_plata(p_cnp CHAR(13), p_serviciu VARCHAR(20)) 
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE v_rest DECIMAL(10,2);

    -- Calculam total pret - total incasat pentru serviciul respectiv
    -- Folosim SUM() pentru ca un client poate avea acelasi serviciu cumparat de mai multe ori
    SELECT SUM(pret - suma_incasata) INTO v_rest
    FROM abonamente
    WHERE CNP = p_cnp AND serviciu = p_serviciu;

    -- Daca nu gaseste nimic, returneaza 0
    IF v_rest IS NULL THEN
        RETURN 0.00;
    END IF;

    RETURN v_rest;
END;
//

DELIMITER ;