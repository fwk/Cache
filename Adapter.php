<?php
namespace Fwk\Cache;

use Fwk\Cache\CacheEntry;

interface Adapter
{
    /**
     * @return CacheEntry
     */
    public function readEntry($key);
    
    public function read($key);
    
    public function exists($key);
    
    public function write(CacheEntry $entry);
    
    public function delete($key);
    
    /**
     *
     * @return \Fwk\Cache\Serializer
     */
    public function getSerializer();

    /**
     * 
     * @param Serializer $serializer
     * 
     * @return Adapter
     */
    public function setSerializer(\Fwk\Cache\Serializer $serializer);
}