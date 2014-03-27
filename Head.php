<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Head
{
    private $_titles;
    private $_order;
    private $_by;
    private $_defaultOrder;
    private $_defaultBy;

    /**
	 *
	 * @return ZendR_Head
	 */
    public static function factory($titles, $order, $by, $defaultOrder = null, $defaultBy = null)
    {
        $head = new ZendR_Head($titles);
        $head->setDefaultOrder($defaultOrder);
        $head->setDefaultBy($defaultBy);
        $head->setOrder($order);
        $head->setBy($by);
        return $head;
    }

    public function  __construct($titles) {
        if (!is_array($titles)) {
            throw new ErrorException('Los titulos debe ser un array', 0, 1, '', '');
        }

        if (count($titles) == 0) {
            throw new ErrorException('El # de elementos debe ser mayor a 0', 0, 1, '', 1);
        }

        $this->_titles         = $titles;
        $this->_defaultOrder    = null;
        $this->_defaultBy       = null;
        $this->_order           = null;
        $this->_by              = null;
    }

    public function setOrder($order)
    {
        $newOrder = null;
        foreach ($this->_titles as $orderTitles => $val) {
            if ($order == $orderTitles) {
                $newOrder = $orderTitles;
                break;
            }
        }

        if ($newOrder === null) {
            if ($this->_defaultOrder === null) {
                reset($this->_titles);
                $newOrder = key($this->_titles);
                if (!is_array($newOrder)) {
                    $newOrder = array(
                        'col' => $newOrder
                    );
                }
                $newOrder = $newOrder['col'];
            } else {
                $newOrder = $this->_defaultOrder;
            }
        }

        $this->_order = $newOrder;
    }

    public function setBy($by)
    {
        $newBy = null;
        $by = trim($by);
        if ($by == 'asc' || $by == 'desc') {
            $newBy = $by;
        }

        if ($newBy === null) {
            if ($this->_defaultBy === null) {
                $newBy = 'asc';
            } else {
                $newBy = $this->_defaultBy;
            }
        }

        $this->_by = $newBy;
    }

    public function setDefaultOrder($order)
    {
        $this->_defaultOrder = $order;
    }

    public function setDefaultBy($by)
    {
        $this->_defaultBy = $by;
    }

    public function formatTitles()
    {
        $titles = $this->_titles;
        foreach ($titles as $title => $name) {
            $titles[$title] = array();
            $title = trim($title);
            if (substr($title, 0, 1) == '_') {
                $titles[$title]['name'] = ZendR_String::parseString($name)
                                            ->subStr(1);
                $titles[$title]['byCurrent'] = '';
            } else {
                if (($title) == $this->_order) {
                   $aditional  = $this->_by;
                   $by         = $this->_by == 'asc'?'desc':'asc';
                } else {
                   $aditional  = '';
                   $by         = 'asc';
                }
                $titles[$title]['byCurrent']   = $aditional;
                $titles[$title]['order']      = $title;
                $titles[$title]['by']         = $by;
                $titles[$title]['name']     = ZendR_String::parseString($name);
            }
        }

        return array('titles' => $titles,
                    'mediaUrl' => self::$_mediaUrl,
                    'mediaUrlExt' => self::$_mediaUrlExt);
    }

    public function orderBy()
    {
        if ($this->_order == $this->_defaultOrder) {
            return $this->_order . ' ' . $this->_by;
        }
        
        if (is_array($this->_titles[$this->_order])) {
            $col = $this->_titles[$this->_order]['col'];
        } else {
            $col = $this->_titles[$this->_order];
        }        
        
        return  $col . ' ' . $this->_by;
    }

    public function formatTitleDb()
    {
        return implode(',', ZendR_Head::parseTitlesForDB($this->_titles));
    }
    
    public static function parseTitlesForDB($titles, $aditional = array())
    {
        $newTitles = array();
        foreach ( $titles as $strUI => $strDB) {
            $key = ZendR_String::parseString($strUI)
                ->toStringSearch()
                ->replace(array(' ', '.', '(', ')'), array('_', '_', '_', '_'))
                ->__toString();
            
            if (!is_array($strDB)) {
                $strDB = array(
                    'col' => $strDB
                );
            }
            
            $newTitles[$key] = $strDB['col'];
        }


        if (!is_array($aditional)) {
            $string         = $aditional;
            $aditional      = array();
            $aditional[]    = $string;
        }

        foreach ( $aditional as $key => $val) {
            if (is_numeric($key)) {
                $newTitles[$val] = $val;
            } else {
                $newTitles[$key] = $val;
            }
        }

        return $newTitles;
    }

    public static function parseTitlesForUI($titles)
    {
        $newTitles = array();
        foreach ( $titles as $strUI => $strDB) {
            $key = ZendR_String::parseString($strUI)
                ->toStringSearch()
                ->replace(array(' ', '.', '(', ')'), array('_', '_', '_', '_'))
                ->__toString();
            $newTitles[$key] = $strUI;
        }
        return $newTitles;
    }

    public function render($basePath = '')
    {
        $view = new Zend_View();
        
        if (file_exists($basePath)) {
            $view->addBasePath($basePath);
        } else {
            $view->addBasePath(dirname(__FILE__) . '/Head');
        }
        
        $view->titles   = $this->_titles;
        $view->order    = $this->_order;
        $view->by       = $this->_by;
        return $view->render('titles.phtml');
    }

    public function  __toString()
    {
        try {
            return $this->render();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}