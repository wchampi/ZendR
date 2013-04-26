<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_View_Helper_FormDate extends Zend_View_Helper_FormElement
{

    public function formDate($name, $value = null, $attribs = null,
        $options = null)
    {
        $valueDay   = '';
        $valueMonth = '';
        $valueYear  = '';

        if ($value !== null) {
            if (Zend_Date::isDate($value)) {
                $date = new Zend_Date($value);
                $valueDay   = $date->toString('d');
                $valueMonth = $date->toString('m');
                $valueYear  = $date->toString('Y');
            } else {
                $valueArray = explode('/', $value);
                if (count($valueArray) != 3) {
                    $valueArray = explode('-', $value);
                }

                if (count($valueArray) == 3) {
                    $valueDay   = $valueArray[0];
                    $valueMonth = $valueArray[1];
                    $valueYear  = $valueArray[2];
                }
            }
        }        
        
        $view = $this->view;

        $optionsDay[''] = 'Dia';
        for ($day = 1; $day <= 31; $day++) {
            if ($day <= 9) {
                $optionsDay['0' . $day] = '0' . $day;
            } else {
                $optionsDay[$day] = $day;
            }
        }

        $optionsMonth     = array(
            '' => 'Mes',
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre'
        );

        $minyear = 1920;
        if (isset($attribs['minyear'])) {
            $minyear = (int)$attribs['minyear'];
        }

        $maxyear = 2020;
        if (isset($attribs['maxyear'])) {
            $maxyear = (int)$attribs['maxyear'];
        }

        $optionsYear[''] = 'Año';
        for ($year = $minyear; $year <= $maxyear; $year++) {
            $optionsYear[$year] = $year;
        }
        
        $dateFormat = 'd/m/Y';
        if (Zend_Registry::isRegistered('date_format')) {
            $dateFormat = Zend_Registry::get('date_format');
        }
        
        $dateFormat = str_replace(
            array ('d', 'm', 'Y', '/', '-'),
            array ('[d]', '[m]', '[Y]', '&nbsp;', '&nbsp;'),
            $dateFormat
        );
        
        $xhtml = str_replace(
            array ('[d]', '[m]', '[Y]'),
            array(
                $view->formSelect($name . '[day]', $valueDay, null, $optionsDay),
                $view->formSelect($name . '[month]', $valueMonth, null, $optionsMonth),
                $view->formSelect($name . '[year]', $valueYear, null, $optionsYear)
            ),
            $dateFormat
        );
        return $xhtml;
    }
}
