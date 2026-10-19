-- PASO FINAL MANUAL Y OPCIONAL PARA HOSTING COMPARTIDO.
-- Ejecutar después de 006_vista_catalogo_phpmyadmin_up.sql.
-- No contiene DEFINER: los objetos pertenecerán al usuario conectado.
-- Si cPanel informa CREATE ROUTINE/TRIGGER denied, solicitar esos privilegios
-- al proveedor. Las tablas y los módulos ya quedan instalados sin este paso.

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP TRIGGER IF EXISTS trg_catalogo_categorias_bi;
DROP TRIGGER IF EXISTS trg_catalogo_categorias_bu;
DROP TRIGGER IF EXISTS trg_ofertas_bi;
DROP TRIGGER IF EXISTS trg_ofertas_bu;
DROP PROCEDURE IF EXISTS catalogo_crear_categoria;
DROP PROCEDURE IF EXISTS catalogo_mover_categoria;

DELIMITER $$

CREATE TRIGGER trg_catalogo_categorias_bi
BEFORE INSERT ON catalogo_categorias FOR EACH ROW
BEGIN
    DECLARE v_nivel BIGINT UNSIGNED;
    DECLARE v_profundidad TINYINT UNSIGNED;
    DECLARE v_admite TINYINT UNSIGNED;
    IF NEW.parent_id IS NOT NULL THEN
        SELECT nivel_id, profundidad, admite_ofertas INTO v_nivel, v_profundidad, v_admite
        FROM catalogo_categorias WHERE id=NEW.parent_id;
        IF v_nivel<>NEW.nivel_id THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La categoria hija debe pertenecer al mismo nivel que su padre';
        END IF;
        IF v_profundidad>=3 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El catalogo admite como maximo tres niveles de categorias';
        END IF;
        IF v_admite=1 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Una categoria que admite ofertas no puede recibir subcategorias';
        END IF;
    END IF;
END$$

CREATE TRIGGER trg_catalogo_categorias_bu
BEFORE UPDATE ON catalogo_categorias FOR EACH ROW
BEGIN
    IF COALESCE(@catalogo_movimiento_permitido,0)<>1 AND
       (NOT (NEW.parent_id<=>OLD.parent_id) OR NEW.nivel_id<>OLD.nivel_id OR NEW.ruta<>OLD.ruta OR NEW.profundidad<>OLD.profundidad) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Use catalogo_mover_categoria para modificar la jerarquia';
    END IF;
    IF NEW.admite_ofertas=1 AND EXISTS (SELECT 1 FROM catalogo_categorias WHERE parent_id=OLD.id LIMIT 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Una categoria con subcategorias no puede admitir ofertas';
    END IF;
    IF NEW.admite_ofertas=0 AND OLD.admite_ofertas=1 AND EXISTS (SELECT 1 FROM ofertas WHERE categoria_id=OLD.id LIMIT 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Una categoria con ofertas asociadas debe continuar admitiendo ofertas';
    END IF;
END$$

CREATE TRIGGER trg_ofertas_bi
BEFORE INSERT ON ofertas FOR EACH ROW
BEGIN
    IF NOT EXISTS (SELECT 1 FROM catalogo_categorias WHERE id=NEW.categoria_id AND activo=1 AND admite_ofertas=1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La oferta debe asociarse a una categoria hoja activa';
    END IF;
END$$

CREATE TRIGGER trg_ofertas_bu
BEFORE UPDATE ON ofertas FOR EACH ROW
BEGIN
    IF NOT EXISTS (SELECT 1 FROM catalogo_categorias WHERE id=NEW.categoria_id AND activo=1 AND admite_ofertas=1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='La oferta debe asociarse a una categoria hoja activa';
    END IF;
END$$

CREATE PROCEDURE catalogo_crear_categoria(
    IN p_nivel_id BIGINT UNSIGNED, IN p_parent_id BIGINT UNSIGNED,
    IN p_slug VARCHAR(120), IN p_nombre VARCHAR(150),
    IN p_admite_ofertas TINYINT, IN p_orden SMALLINT UNSIGNED
)
BEGIN
    DECLARE v_id BIGINT UNSIGNED;
    DECLARE v_profundidad TINYINT UNSIGNED DEFAULT 1;
    DECLARE v_ruta_padre VARCHAR(255) DEFAULT '/';
    IF p_parent_id IS NOT NULL THEN
        SELECT profundidad+1,ruta INTO v_profundidad,v_ruta_padre
        FROM catalogo_categorias WHERE id=p_parent_id AND nivel_id=p_nivel_id;
    END IF;
    IF v_profundidad>3 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El catalogo admite como maximo tres niveles de categorias';
    END IF;
    INSERT INTO catalogo_categorias
        (nivel_id,parent_id,slug,nombre,profundidad,ruta,admite_ofertas,orden)
    VALUES (p_nivel_id,p_parent_id,p_slug,p_nombre,v_profundidad,'/',p_admite_ofertas,p_orden);
    SET v_id=LAST_INSERT_ID();
    SET @catalogo_movimiento_permitido=1;
    UPDATE catalogo_categorias SET ruta=CONCAT(v_ruta_padre,v_id,'/') WHERE id=v_id;
    SET @catalogo_movimiento_permitido=NULL;
END$$

CREATE PROCEDURE catalogo_mover_categoria(IN p_categoria_id BIGINT UNSIGNED, IN p_nuevo_parent_id BIGINT UNSIGNED)
BEGIN
    DECLARE v_nivel BIGINT UNSIGNED;
    DECLARE v_old_route VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_old_depth TINYINT UNSIGNED;
    DECLARE v_new_route VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_new_depth TINYINT UNSIGNED;
    DECLARE v_delta INT;
    DECLARE v_max_depth INT;
    SELECT nivel_id,ruta,profundidad INTO v_nivel,v_old_route,v_old_depth
    FROM catalogo_categorias WHERE id=p_categoria_id FOR UPDATE;
    IF p_nuevo_parent_id IS NULL THEN
        SET v_new_depth=1;
        SET v_new_route=CONCAT('/',p_categoria_id,'/');
    ELSE
        IF NOT EXISTS (SELECT 1 FROM catalogo_categorias WHERE id=p_nuevo_parent_id AND nivel_id=v_nivel AND activo=1 AND admite_ofertas=0) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El nuevo padre debe estar activo, pertenecer al mismo nivel y no admitir ofertas';
        END IF;
        IF EXISTS (SELECT 1 FROM catalogo_categorias WHERE id=p_nuevo_parent_id AND ruta LIKE CONCAT(v_old_route,'%')) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El movimiento generaria un ciclo';
        END IF;
        SELECT profundidad+1,CONCAT(ruta,p_categoria_id,'/') INTO v_new_depth,v_new_route
        FROM catalogo_categorias WHERE id=p_nuevo_parent_id;
    END IF;
    SET v_delta=v_new_depth-v_old_depth;
    SELECT MAX(profundidad+v_delta) INTO v_max_depth
    FROM catalogo_categorias WHERE ruta LIKE CONCAT(v_old_route,'%');
    IF v_max_depth>3 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El movimiento supera la profundidad maxima del catalogo';
    END IF;
    SET @catalogo_movimiento_permitido=1;
    UPDATE catalogo_categorias
    SET parent_id=IF(id=p_categoria_id,p_nuevo_parent_id,parent_id),
        profundidad=profundidad+v_delta,
        ruta=CONCAT(v_new_route,SUBSTRING(ruta,CHAR_LENGTH(v_old_route)+1))
    WHERE ruta LIKE CONCAT(v_old_route,'%') ORDER BY profundidad;
    SET @catalogo_movimiento_permitido=NULL;
END$$

DELIMITER ;
