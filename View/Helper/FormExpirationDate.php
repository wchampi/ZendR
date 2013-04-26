<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_View_Helper_FormExpirationDate extends Zend_View_Helper_FormElement
{

    public function formExpirationDate($name, $value = null, $attribs = null,
        $options = null)
    {
        $valueMonth = '';
        $valueYear  = '';

        if ($value != null) {
            list($valueMonth, $valueYear) = explode('/', $value);
        }        
        
        $translate = Zend_Registry::get('Zend_Translate');

        $view = $this->view;

        $optionsMonth     = array(
            '' => $translate->_('Month'),
            '1' => '01',
            '2' => '02',
            '3' => '03',
            '4' => '04',
            '5' => '05',
            '6' => '06',
            '7' => '07',
            '8' => '08',
            '9' => '09',
            '10' => '10',
            '11' => '11',
            '12' => '12'
        );

        $yearAct = date('Y');
        $optionsYear[''] = $translate->_('Year');
        for ($year = $yearAct; $year <= $yearAct + 15; $year++) {
            $optionsYear[$year] = $year;
        }
        
        return $view->formSelect($name . '[month]', $valueMonth, null, $optionsMonth)
            .  '&nbsp;' . $view->formSelect($name . '[year]', $valueYear, null, $optionsYear);
    }
}
