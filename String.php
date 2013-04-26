<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

define("UTF_8", 1);
define("ASCII", 2);
define("ISO_8859_1", 3);

class ZendR_String
{
	protected $_string = '';

    public function __construct($string = '')
    {
        $this->_string = $string;
    }

    /**
     *
     * @return ZendR_String
     */
    public static function parseString($string)
    {
        return new ZendR_String($string);
    }

    public function strCmp($string)
    {
        if ($this->_string == $string) {
            return true;
        }
        return false;
    }

	public function equals($string)
    {
        if ($this->_string == $string) {
            return true;
        }
        return false;
    }

    public function isVacio()
    {
        if ($this->_string == '') {
            return true;
        }
        return false;
    }

	public function encode()
	{
		$c		= 0;
		$ascii	= true;

		$i 				= 0;
		$numberCharacters	= strlen($this->_string);
		if ($numberCharacters > 0) {
			do {
				$byte = ord($this->_string[$i]);
				if ($c > 0) {
				   if (($byte>>6) != 0x2) {
						return ISO_8859_1;
				   } else {
						$c--;
				   }
				} elseif ($byte&0x80) {
				  $ascii = false;
				  if (($byte>>5) == 0x6) {
					 $c = 1;
				  } elseif (($byte>>4) == 0xE) {
					 $c = 2;
				  } elseif (($byte>>3) == 0x14) {
					 $c = 3;
				  } else {
					 return ISO_8859_1;
				  }
				}
				++$i;
			} while ($i < $numberCharacters);
		}
		return ($ascii) ? ASCII : UTF_8;
	}

     /**
     *
     * @return ZendR_String
     */
	public function toISO()
	{
        $string = in_array($this->encode(), array(ISO_8859_1, ASCII)) ? $this->_string : @iconv("UTF-8", "ISO-8859-1//TRANSLIT", $this->_string);
		return new ZendR_String($string . "");
	}

     /**
     * @return ZendR_String
     */
	public function toUTF8($force = false)
	{
        if ($force) {
            $string = utf8_encode($this->_string);
        } else {
            $string = in_array($this->encode(), array(ISO_8859_1, ASCII)) ? @iconv("ISO-8859-1", "UTF-8//TRANSLIT", $this->_string) : $this->_string;
        }
		
        return new ZendR_String($string . "");
	}

    /**
     * @return ZendR_String
     */
	public function toLower()
	{
		$string = utf8_encode(strtolower(utf8_decode($this->_string)));
		return ZendR_String::parseString($string)->replace(array('Ñ', 'Á', 'É', 'Í', 'Ó', 'Ú'), array('ñ', 'á', 'é', 'í', 'ó', 'ú'));
	}

    /**
     * @return ZendR_String
     */
	public function toUpper()
	{
        $string = utf8_encode(strtoupper(utf8_decode($this->_string)));
		return new ZendR_String($string);
	}

    /**
     * @return ZendR_String
     */
	public function toUcWords()
	{
        $string = utf8_encode(ucwords(utf8_decode($this->_string)));
        return new ZendR_String($string);
	}
	 /**
     * @return ZendR_String
     */
    public function toUcFirst()
	{
        $string = utf8_encode(ucfirst(utf8_decode($this->_string)));
        return new ZendR_String($string);
	}

    /**
     * @return ZendR_String
     */
	public function replace($strBusqueda, $strReplace)
	{
		$string = str_replace($strBusqueda, $strReplace, $this->_string);
        return new ZendR_String($string);
	}

    /**
     * @return ZendR_String
     */
    public function trim()
    {
        $string = trim($this->_string);
        return new ZendR_String($string);
    }

    /**
     * @return ZendR_String
     */
    public function subStr($inicio, $tamanio = null)
    {
        if ($tamanio === null) {
            $string = utf8_encode(substr(utf8_decode($this->_string, $inicio)));
        } else {
            $string = utf8_encode(substr(utf8_decode($this->_string), $inicio, $tamanio));
        }

        return new ZendR_String($string);
    }

    /**
     * @return ZendR_String
     */
	public function forDB()
	{
		$strBusqueda    = array("\'", '\"', '"', "'");
		$strReplace     = array("''", "''", "''", "''");

		return $this->replace($strBusqueda, $strReplace)->toUTF8();
	}

    /**
     * @return ZendR_String
     */
    public function toStringSearch()
	{
		$dirty	= array("á", "é", "í", "ó", "ú", "'", '"', 'ü');
        $clean	= array("a", "e", "i", "o", "u", "", "", 'u');

        $string = ZendR_String::parseString($this->_string)
			->toLower()
			->replace($dirty, $clean)
			->forDB();

        return $string;
	}

    /**
     * @return ZendR_String
     */
    public function toStringUrl()
	{
		$dirty	= array("á", "é", "í", "ó", "ú", " ", "/", ",", "ñ");
        $clean	= array("a", "e", "i", "o", "u", "-", "-", "-", "n");

        $string = ZendR_String::parseString($this->_string)
			->toLower()
            ->trim()
			->replace($dirty, $clean);

        return $string;
	}

    /**
     * @return ZendR_String
     */
	public function toLimpiar()
	{
		$dirty	= array("Á", "É", "Í", "Ó", "Ú", "á", "é", "í", "ó", "ú", "'", '"', 'ü');
        $clean	= array("A", "E", "I", "O", "U", "a", "e", "i", "o", "u", "", "", 'u');

        return $this->trim()->replace($dirty, $clean);
	}

    /**
     * @return ZendR_String
     */
    public function toPlural()
    {
        $stringClean = $this->toStringSearch();
        $indexLastLetter = $stringClean->len() - 1;

        $lastLetter = $stringClean->subStr($indexLastLetter, 1);

        if (!$lastLetter->searchOut("a e i o u")->isVacio()) {
            return new ZendR_String($this->_string . 's');
        } else {
            return new ZendR_String($this->_string . 'es');
        }
    }

	public function  __toString()
    {
		return $this->_string;
	}

    public function len()
    {
        return strlen($this->_string);
    }
	/**
     * @return ZendR_String
     */
    public function searchOut($string)
    {
        return new ZendR_String(strstr($string, $this->_string));
    }
	/**
     * @return ZendR_String
     */
    public function searchIn($string)
    {
        return new ZendR_String(strstr($this->_string, $string));
    }
	/**
     * @return ZendR_String
     */
    public function set($string)
    {
        $this->_string = $string;
        return $this;
    }
    /**
     * @return ZendR_String
     */
    public function removeAccent()
    {
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'); 
        
        return $this->toUTF8()->replace($a, $b);
    }
    
    public function prepareStringSearch()
    {
        return $this->removeAccent()->toLower()->replace(' ', '');
    }

    public static function webDescription($description, $numberChars = null)
    {
        $description = ZendR_String::parseString($description)->toLower()->toUcWords()->__toString();
        return self::cutDescription($description, $numberChars);
    }
    
    public static function cutDescription($description, $numberChars = null)
    {
        if ((int)$numberChars > 0)  {
            if (substr($description, $numberChars) != '') {
                $description = ZendR_String::parseString($description)->subStr(0, (int)$numberChars - 3) . '...';
            }
        }    
        return $description;
    }
    
    public static function strip($string)
    {
        return preg_replace('!\s+!u', ' ', $string);
    }
}