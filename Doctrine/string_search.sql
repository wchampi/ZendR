-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.5.16 - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL version:             7.0.0.4053
-- Date/time:                    2013-01-30 10:12:48
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

-- Dumping structure for function publikradio.prepare_string_search
DELIMITER //
CREATE DEFINER=`root`@`localhost` FUNCTION `prepare_string_search`(`str` VARCHAR(5000)) RETURNS varchar(5000) CHARSET latin1
RETURN REPLACE(LOWER(remove_accent(str)), ' ','')//
DELIMITER ;


-- Dumping structure for function publikradio.regex_replace
DELIMITER //
CREATE DEFINER=`root`@`localhost` FUNCTION `regex_replace`(`pattern` VARCHAR(1000), `replacement` VARCHAR(1000), `original` VARCHAR(1000)) RETURNS varchar(1000) CHARSET latin1
    DETERMINISTIC
BEGIN 
 DECLARE temp VARCHAR(1000);
 DECLARE ch VARCHAR(1);
 DECLARE i INT;
 SET i = 1;
 SET temp = '';
 IF original REGEXP pattern THEN 
  loop_label: LOOP 
   IF i>CHAR_LENGTH(original) THEN
    LEAVE loop_label;
   END IF;
   SET ch = SUBSTRING(original,i,1);
   IF NOT ch REGEXP pattern THEN
    SET temp = CONCAT(temp,ch);
   ELSE
    SET temp = CONCAT(temp,replacement);
   END IF;
   SET i=i+1;
  END LOOP;
 ELSE
  SET temp = original;
 END IF;
 RETURN temp;
END//
DELIMITER ;


-- Dumping structure for function publikradio.remove_accent
DELIMITER //
CREATE DEFINER=`root`@`localhost` FUNCTION `remove_accent`(`str` VARCHAR(5000)) RETURNS varchar(5000) CHARSET utf8
RETURN REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(str,'a','a'),'Ã¡','a'),'Ã¡','a'),'Ã ','a'),'Ã ','a'),'Ã£','a'),'Ã£','a'),'Ã¢','a'),'Ã¢','a'),'Ã¤','a'),'Ã¤','a'),'Ã©','e'),'Ã©','e'),'Ã¨','e'),'Ã¨','e'),'Ãª','e'),'Ãª','e'),'Ã«','e'),'Ã«','e'),'Ã­','i'),'Ã­','i'),'Ã¬','i'),'Ã¬','i'),'Ã®','i'),'Ã®','i'),'Ã¯','i'),'Ã¯','i'),'Ã³','o'),'Ã³','o'),'Ã²','o'),'Ã²','o'),'Ãµ','o'),'Ãµ','o'),'Ã´','o'),'Ã´','o'),'Ã¶','o'),'Ã¶','o'),'Ãº','u'),'Ãº','u'),'Ã¹','u'),'Ã¹','u'),'Ã»','u'),'Ã»','u'),'Ã¼','u'),'Ã¼','u'),'Ã§','c'),'Ã§','c'),'Ã±','n'),'Ã±','n')//
DELIMITER ;
/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
