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
        $adapter->setSerializer($serializer);
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
        $this->hits++;
        
        if ($this->has($key, ($maxAge instanceof \Closure ? null : $maxAge))) {
            $entry = $this->adapter->readEntry($key);
            if (!$entry instanceof CacheEntry) {
                throw new Exceptions\ReadError();
            }

            $entry->setSerializedContents($this->adapter->read($key));
            
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
        $entry = $this->adapter->readEntry($key);
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
    public function put($key, $item, $maxAge = null, array $tags = array())
    {
        $entry = new CacheEntry($item, $key);
        $entry->setMaxAge($maxAge);
        $entry->setTags($tags);
        
        $res = $this->adapter->write($entry);
        
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
        return round($this->cacheHits*100/$this->hits, 2);
    }
    
    /**
     *
     * @return integer 
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     *
     * @return integer 
     */
    public function getCacheHits()
    {
        return $this->cacheHits;
    }
}