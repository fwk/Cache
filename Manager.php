<?php
namespace Fwk\Cache;

use Fwk\Cache\Serializers\Native;

class Manager
{
    /**
     * @var Adapter
     */
    protected $adapter;
    
    /**
     * @var Serializer
     */
    protected $serializer;
    
    /**
     * @var integer 
     */
    protected $hits = 0;
    
    /**
     * @var integer 
     */
    protected $cacheHits = 0;
    
    /**
     *
     * @param Adapter $adapter Cache adapter to use with this instance
     * 
     * @return void
     */
    public function __construct(Adapter $adapter, Serializer $serializer = null)
    {
        if (null === $serializer) {
            $serializer = new Native();
        }
        
        $this->adapter      = $adapter;
        $this->serializer   = $serializer;
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
     *
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }
    
    /**
     *
     * @param Serializer $serializer 
     * 
     * @return Manager
     */
    public function setSerializer(Serializer $serializer)
    {
        $this->serializer   = $serializer;
        
        return $this;
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
        $this->hits++;
        
        if ($this->has($key, $maxAge)) {
            $entry = $this->serializer->unserialize($this->adapter->read($key));
            
            if (!$entry instanceof CacheEntry) {
                throw new Exceptions\ReadError();
            }
            
            $this->cacheHits++;
            
            return $entry;
        }
        
        if (is_callable($save)) {
            $item = call_user_func($save);
            if ($item !== null) {
                return $this->put($key, $item, $maxAge);
            }
        }
        
        return null;
    }
    
    /**
     *
     * @param string    $key
     * @param mixed     $maxAge 
     * 
     * @return boolean
     */
    public function has($key, $maxAge = null)
    {
        if (!$this->adapter->exists($key)) {
            return false;
        }
        
        $entry = $this->serializer->unserialize($this->adapter->read($key));
        if (!$entry instanceof CacheEntry) {
            return false;
        }
        
        if ($maxAge !== null) {
            $entry->setMaxAge($maxAge);
        }
        
        return !$entry->isExpired();
    }
    
    /**
     *
     * @param string $key
     * @param mixed  $item
     * @param mixed  $maxAge 
     * 
     * @return CacheEntry
     */
    public function put($key, $item, $maxAge = null)
    {
        $entry = new CacheEntry($item, $key);
        $entry->setMaxAge($maxAge);
        
        $res = $this->adapter->write(
            $key, 
            $entry->getMaxAge(), 
            $this->serializer->serialize($entry)
        );
        
        if (!$res) {
            throw new Exceptions\WriteError();
        }
        
        return $entry;
    }
    
    /**
     *
     * @param string $key key name or CacheEntry
     * 
     * @return Manager 
     */
    public function erase($keyOrEntry)
    {
        if ($key instanceof CacheEntry) {
            $key = $key->getKey();
        }
        
        $res = $this->adapter->delete($key);
        if (!$res) {
            throw new Exceptions\WriteError();
        }
        
        return $this;
    }
    
    public function flush()
    {
    }
    
    /**
     *
     * @return float
     */
    public function hitRatio()
    {
        return round($this->cacheHits*100/$this->hits);
    }
}