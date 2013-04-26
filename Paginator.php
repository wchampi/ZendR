<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Paginator extends Zend_Paginator
{
    private static $_rowsPerPage = 20;

    private static $_numberPages = 5;

    public static function factory($data, $page, $rowsPerPage = null, $numberPages = null)
    {
        if ($rowsPerPage == null){
            $rowsPerPage = self::$_rowsPerPage;
        }
        if ($numberPages == null){
            $numberPages = self::$_numberPages;
        }

        if (is_array($data)) {
            $pager = parent::factory($data);
        } elseif (is_integer($data)) {
            $pager = parent::factory($data);
        } else {
            switch (get_class($data)) {
                case 'Doctrine_Collection':
                    $pager = parent::factory(
                        $data,
                        'DoctrineCollection',
                        array('ZendR_Paginator_Adapter' => 'ZendR/Paginator/Adapter')
                    );
                    break;
                case 'Doctrine_Query':
                    $pager = parent::factory(
                        $data,
                        'DoctrineQuery',
                        array('ZendR_Paginator_Adapter' => 'ZendR/Paginator/Adapter')
                    );
                    break;
                default:
                    $pager = parent::factory($data);
                    break;
            }
        }

        $ykPager = new ZendR_Paginator($pager->getAdapter());
        $ykPager->setCurrentPageNumber($page);
        $ykPager->setPageRange($numberPages);
        $ykPager->setItemCountPerPage($rowsPerPage);
        return $ykPager;
    }

    public function render()
    {
        $resourceLayout = Zend_Controller_Front::getInstance()
            ->getParam('bootstrap')
            ->getResource('layout');
        $view = $resourceLayout->getView();
        $view->addBasePath(dirname(__FILE__) . '/Paginator');
        
        $view->pager = $this;
        return $view->render('pagination-control.phtml');
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