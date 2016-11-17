<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Validate_Telephone extends Zend_Validate_Abstract
{
    const TELEPHONE = 'telephoneIsValid';
    const PAIS = 'telephonePaisIsValid';
    const AREA = 'telephoneAreaIsValid';
    const NUMBER_LENGTH = 'telephoneNumberLengthIsValid';

    /**
     * Number length
     *
     * @var integer
     */
    protected $_numberLength;

    /**
     * Number length
     *
     * @var integer
     */
    protected $_phoneNumber;

    protected $_messageTemplates = array (
        self::TELEPHONE => "'%value%' is not a not have the phone format '51-1-954685288'",
        self::PAIS => "Country code must be 4 digits max",
        self::AREA => "Area code must be 3 digits max",
        self::NUMBER_LENGTH => "'%phoneNumber%' must be between 6 and %numberLength% digits",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'numberLength' => '_numberLength',
        'phoneNumber' => '_phoneNumber'
    );
    
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options     = func_get_args();
            $temp['numberLength'] = array_shift($options);
            $options = $temp;
        }

        if (!array_key_exists('numberLength', $options)) {
            $options['numberLength'] = 9;
        }

        $this->setNumberLength($options['numberLength']);
    }

    /**
     * Sets the min option
     *
     * @param  integer $numberLength
     * @throws Zend_Validate_Exception
     * @return ZendR_Validate_Telephone Provides a fluent interface
     */
    public function setNumberLength($numberLength)
    {
        $this->_numberLength = max(0, (integer) $numberLength);
        return $this;
    }
    
    public function isValid($value)
    {
        $this->_setValue($value);

        $valueArr = explode('-', $value);

        if (count($valueArr) != 3) {
            $this->_error(self::TELEPHONE);
            return false;
        }
        
        $validDigits = new Zend_Validate_Digits();
        $validPais = $validDigits->isValid($valueArr[0]) ? true : false;
        $validArea = $validDigits->isValid($valueArr[1]) ? true : false;
        $validNumero = $validDigits->isValid($valueArr[2]) ? true : false;

        if ($validPais && $validArea && $validNumero) {
            if (strlen($valueArr[0]) > 4) {
                $this->_error(self::PAIS);
            }
            if (strlen($valueArr[1]) > 3) {
                $this->_error(self::AREA);
            }
            if (strlen($valueArr[2]) < 6 || strlen($valueArr[2]) > $this->_numberLength) {
                $this->_phoneNumber = $valueArr[2];
                $this->_error(self::NUMBER_LENGTH);
            }
        } else {
            $this->_error(self::TELEPHONE);
        }

        if (count($this->_messages)) {
            return false;
        } else {
            return true;
        }
    }
}