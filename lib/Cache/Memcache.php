<?php
/**
 *
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Cache_Memcache extends Doctrine_Cache_Memcache
{
    /**
     * Save a cache record directly. This method is implemented by the cache
     * drivers and used in Doctrine_Cache_Driver::save()
     *
     * @param string $id        cache id
     * @param string $data      data to cache
     * @param int $lifeTime     if != false, set a specific lifetime for this cache record (null => infinite lifeTime)
     * @return boolean true if no problem
     */
    protected function _doSave($id, $data, $lifeTime = false)
    {
        if (isset($this->_options['compression'])) {
            $flag = MEMCACHE_COMPRESSED;
        } else {
            $flag = 0;
        }

        return $this->_memcache->set($id, $data, $flag, $lifeTime);
    }
    
    /**
     * Fetch an array of all keys stored in cache
     *
     * @return array Returns the array of cache keys
     */
    protected function _getCacheKeys()
    {
        $keys = array();
        $allSlabs = $this->_memcache->getExtendedStats('slabs');

        foreach ($allSlabs as $server => $slabs) {
            if (is_array($slabs)) {
                foreach (array_keys($slabs) as $slabId) {
                    if ((int) $slabId > 0) {
                        $dump = $this->_memcache->getExtendedStats('cachedump', (int) $slabId);
                        foreach ($dump as $entries) {
                            if ($entries) {
                                $keys = array_merge($keys, array_keys($entries));
                            }
                        }
                    }    
                }
            }    
        }
        return $keys;
    }
}