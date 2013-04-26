<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Paginator_Adapter_DoctrineQuery implements Zend_Paginator_Adapter_Interface
{
    /**
     *
     * @var Doctrine_Query
     */
    private $_q;

    /**
     *
     * @var int
     */
    private $_count = null;
    
    /**
     *
     * @param 
     */
    private $_items = null;

    public function __construct(Doctrine_Query $q)
    {
        if ($this->_count == null) {
            $this->_q = $q;
            
            $saveCache = false;
            $hash = $this->_q->getResultCacheHash();
            if (strpos($hash, 'Pag_') !== false && $this->_q->getResultCacheDriver()) {
                $hash = $hash . '_count';
                $cacheDriver = $this->_q->getResultCacheDriver();
                if ($cacheDriver->fetch($hash) !== false) {
                    $this->_count = $cacheDriver->fetch($hash);
                } else {
                    $saveCache = true;
                }
            } 

            if ($this->_count == null) {
                $q->getSqlQuery();
                $stmt = $q->getConnection()->prepare($q->getCountSqlQuery());
                $stmt->execute($q->getInternalParams());
                $rows = $stmt->fetchAll();
                $this->_count = $rows[0]['num_results'];

                if ($saveCache) {
                    $cacheDriver->save($hash, $this->_count, $this->_q->getQueryCacheLifeSpan());
                }
            }   
        }    
    }

    public function getItems($offset, $itemCountPerPage)
    {
        if ($this->_items === null) {
            $this->_q->limit($itemCountPerPage)->offset($offset);
            
            $hash = $this->_q->getResultCacheHash();
            if (strpos($hash, 'Pag_') !== false &&  $this->_q->getResultCacheDriver()) {
                $hash = $hash . '_o' . $offset . '_i' . $itemCountPerPage;
                $this->_q->setResultCacheHash($hash);
            }
            
            $this->_items = $this->_q->execute();
        }    
        return $this->_items;
    }

    public function count()
    {
        return $this->_count;
    }
}