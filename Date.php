<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Date extends Zend_Date
{
    private $_dateFormat = 'Y-m-d';
    private $_timeFormat = 'H:i:s';

    public static function fetchLastDay($month, $year)
    {
        return strftime("%d", mktime(0, 0, 0, $month + 1, 0, $year));
    }

    public static function toTime($date)
	{
        return strtotime(self::parseToFormat($date, 'Y-m-d'));
	}

    public static function diffDays($date1, $date2)
	{
        $date1 = new Zend_Date($date1);
        $date2 = new Zend_Date($date2);

		$nroSeconds1 = strtotime($date1->toString('Y-m-d'));
        $nroSeconds2 = strtotime($date2->toString('Y-m-d'));
        if ($nroSeconds1 < $nroSeconds2) {
            $diffSeconds = $nroSeconds2 - $nroSeconds1;
        } else {
            $diffSeconds = $nroSeconds1 - $nroSeconds2;
        }
        return intval($diffSeconds / 86400);
	}

	public static function findAge($date)
	{
		$day = date('j');
		$month = date('n');
		$year = date('Y');

        $date = new Zend_Date($date);
		$dayn   = $date->toString('j');
		$monthn = $date->toString('n');
		$yearn  = $date->toString('Y');

		if ($monthn == $month && $dayn > $day) {
			$year = $year - 1;
		}

		if ($monthn > $month) {
			$year = $year - 1;
		}

		$age = $year - $yearn;
		return $age;
	}

    public static function parseToFormat($date, $formatOutput, $formatInput = null)
    {
        if ($formatInput == null && Zend_Registry::isRegistered('date_format')) {
            $formatInput = Zend_Registry::get('date_format');
        }
        $date = new Zend_Date($date, $formatInput);
        return $date->toString($formatOutput);
    }

    public static function currentDate($format = null)
    {
        $currentDate = new ZendR_Date(time(), self::TIMESTAMP);
        if ($format !== null) {
            return $currentDate->toString($format);
        } else {
            if (Zend_Registry::isRegistered('date_format')) {
                return $currentDate->toString(Zend_Registry::get('date_format'));
            } else {
                return $currentDate->toString($currentDate->getDateFormat());
            }
        }
    }

    public static function currentTimestamp()
    {
        $currentDate = new ZendR_Date(time(), self::TIMESTAMP);
        if (Zend_Registry::isRegistered('date_format')) {
            return $currentDate->toString(Zend_Registry::get('date_format') . ' ' . $currentDate->getTimeFormat());
        } else {
            return $currentDate->toString($currentDate->getDateFormat()  . ' ' . $currentDate->getTimeFormat());
        }
    }

    public function getDateFormat()
    {
        return $this->_dateFormat;
    }

    public function getTimeFormat()
    {
        return $this->_timeFormat;
    }
}
