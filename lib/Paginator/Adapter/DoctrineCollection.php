<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Paginator_Adapter_DoctrineCollection implements Zend_Paginator_Adapter_Interface
{
    private $_items;

    public function __construct(Doctrine_Collection $items)
    {
        $this->_items = $items;
    }

    public function getItems($offset, $itemCountPerPage)
    {
        $inicio = $offset;
        $fin    = $offset + $itemCountPerPage;
        $items = array();
        for ($i = $offset; $i < $fin; $i++) {
            if (isset($this->_items[$i])) {
                $items[] = $this->_items[$i];
            }
        }
        return $items;
    }

    public function count()
    {
        return count($this->_items);
    }
}