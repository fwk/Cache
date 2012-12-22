<?php
namespace Fwk\Cache;


class Manager
{
    /**
     * @var Adapter
     */
    protected $adapter;
    
    /**
     *
     * @param Adapter $adapter Cache adapter to use for this instance
     * 
     * @return void
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }
    
    /**
     *
     * @return Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
    
    /**
     * Fetch a cache entry
     * 
     * @param string   $key    Cache key
     * @param mixed    $maxAge Max age for this entry
     * @param \Closure $save   Save closure
     * 
     * @return CacheEntry or null if not found
     */
    public function get($key, $maxAge = null, \Closure $save = null)
    {
    }
    
    public function has($key, $maxAge = null)
    {
    }
    
    public function put($key, $item, $maxAge = null)
    {
    }
    
    public function flush()
    {
    }
    
    /**
     *
     * @param string $wildcard 
     * 
     * @return array
     */
    public function find($wildcard)
    {
    }
}