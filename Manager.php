<?php
/**
 * Fwk
 *
 * Copyright (c) 2011-2012, Julien Ballestracci <julien@nitronet.org>.
 * All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * PHP Version 5.3
 *
 * @category  Cache
 * @package   Fwk\Cache
 * @author    Julien Ballestracci <julien@nitronet.org>
 * @copyright 2013-2014 Julien Ballestracci <julien@nitronet.org>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link      http://www.phpfwk.com
 */
namespace Fwk\Cache;

use Fwk\Cache\Serializers\Native;

class Manager
{
    /**
     * Cache Adapter for this Manager
     * 
     * @var Adapter
     */
    protected $adapter;
    
    /**
     * Total hits 
     * 
     * @var integer 
     */
    protected $hits = 0;
    
    /**
     * Successfull cache hits
     * 
     * @var integer 
     */
    protected $cacheHits = 0;
    
    /**
     * Constructor
     * 
     * If no Serializer is given, the Native PHP serializer will be
     * used {@see \Fwk\Cache\Serializers\Native}
     * 
     * @param Adapter    $adapter    Cache Adapter
     * @param Serializer $serializer Optional Serializer to use
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
     * Returns the Cache Adapter for this Manager
     * 
     * @return Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
    
    /**
     * Fetch a cache entry.
     * 
     * Lifetime of an Entry ($maxAge) could be:
     * - An integer (seconds)
     * - Null (infinite lifetime)
     * - Relative (eg: 1day, 2hours, 4years, 45secs)
     * - A Closure that must return a boolean value telling if the entry 
     *   is expired or not (eg: md5 hash comparision).
     * 
     * If $save is a \Closure and no entry was found in the cache, the function
     * will be called and must return the item we wish to store. It'll be saved
     * with the given $maxAge lifetime.
     * 
     * @param string   $key    Cache key name
     * @param mixed    $maxAge Lifetime for this entry
     * @param \Closure $save   Save closure
     * 
     * @return CacheEntry or null if not found
     * @throws Exceptions\ReadError if an error occurs
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
     * Tells if a Cache entry ($key) is stored in the cache and not expired.
     * 
     * Lifetime ($maxAge) could be:
     * - An integer (seconds)
     * - Null (infinite lifetime)
     * - Relative (eg: 1day, 2hours, 4years, 45secs)
     * - A Closure that must return a boolean value telling if the entry 
     *   is expired or not (eg: md5 hash comparision).
     * 
     * @param string $key    Cache key name
     * @param mixed  $maxAge Override the entry's maxAge for defining if its
     * expired.
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
     * Stores an item ($item) in the Cache at index $key.
     * 
     * Lifetime ($maxAge) could be:
     * - An integer (seconds)
     * - Null (infinite lifetime)
     * - Relative (eg: 1day, 2hours, 4years, 45secs)
     * - A Closure that must return a boolean value telling if the entry 
     *   is expired or not (eg: md5 hash comparision). 
     * 
     * @param string $key    Cache key name
     * @param mixed  $item   The item we want to store
     * @param mixed  $maxAge The lifetime of the item
     * @param array  $tags   Optional tags for later retrieving
     * 
     * @return CacheEntry
     * @throws Exceptions\WriteError if an error occurs
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
     * Deletes an entry from the Cache.
     *  
     * @param string $key Cache key name or CacheEntry
     * 
     * @return Manager 
     * @throws Exceptions\WriteError if an error occurs
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
    
    /**
     * Returns the computed cache hit ratio (rounded 2)
     * 
     * @return float
     */
    public function hitRatio()
    {
        return round($this->cacheHits*100/$this->hits, 2);
    }
    
    /**
     * Returns total hits
     * 
     * @return integer 
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * Returns only successfull cache hits
     * 
     * @return integer 
     */
    public function getCacheHits()
    {
        return $this->cacheHits;
    }
}