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
 * @category   Cache
 * @package    Fwk\Cache
 * @subpackage Adapters
 * @author     Julien Ballestracci <julien@nitronet.org>
 * @copyright  2013-2014 Julien Ballestracci <julien@nitronet.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpfwk.com
 */
namespace Fwk\Cache\Adapters;

use Fwk\Cache\Adapter;
use Fwk\Cache\Exceptions\AdapterError;

class Filesystem implements Adapter
{
    /**
     * @var string 
     */
    protected $cacheDirectory;
    
    /**
     * @var Serializer 
     */
    protected $serializer;
    
    /**
     *
     * @param string $cacheDirectory Target directory for cache files
     * 
     * @return void
     */
    public function __construct($cacheDirectory = null)
    {
        if (null === $cacheDirectory) {
            $cacheDirectory = sys_get_temp_dir();
        }
        
        if (!is_dir($cacheDirectory)) {
            throw new AdapterError(
                sprintf("Invalid cache directory: %s", $cacheDirectory)
            );
        }
        
        $this->cacheDirectory = rtrim($cacheDirectory, DIRECTORY_SEPARATOR);
    }
    
    /**
     *
     * @param string $fileName
     * @param string $contents 
     * 
     * @return int
     */
    public function write(\Fwk\Cache\CacheEntry $entry)
    {
        $fileInfos = implode(DIRECTORY_SEPARATOR, array(
            $this->cacheDirectory,
            md5('cacheKey:'. $entry->getKey()) . '.cache'
        ));
        
        $fileContents = implode(DIRECTORY_SEPARATOR, array(
            $this->cacheDirectory,
            md5('cacheKey:'. $entry->getKey())
        ));
        
        $entry->setSerializer($this->serializer);
        $serialized = $entry->getSerializedContents();
        
        file_put_contents($fileInfos, $this->serializer->serialize($entry));
        file_put_contents($fileContents, $serialized);
        
        return true;
    }
    
    /**
     *
     * @param string $fileName
     * 
     * @return boolean
     */
    public function exists($key)
    {
        $file = implode(DIRECTORY_SEPARATOR, array(
            $this->cacheDirectory,
            md5('cacheKey:'. $key) .'.cache'
        ));
        
        return is_file($file);
    }
    
    /**
     *
     * @param string $fileName
     * 
     * @return CacheEntry 
     */
    public function readEntry($key)
    {
        if (!$this->exists($key)) {
            return false;
        }
        
        $fileInfos = implode(DIRECTORY_SEPARATOR, array(
            $this->cacheDirectory,
            md5('cacheKey:'. $key) . '.cache'
        ));
        
        $contents = file_get_contents($fileInfos);
        $entry = $this->serializer->unserialize($contents);
        
        if (!$entry instanceof \Fwk\Cache\CacheEntry) {
            throw new \Fwk\Cache\Exceptions\ReadError("contents is not a valid CacheEntry");
        }
        
        $entry->setSerializer($this->serializer);
        
        return $entry;
    }
    
    /**
     *
     * @param string $fileName
     * 
     * @return string 
     */
    public function read($key)
    {
        if (!$this->exists($key)) {
            return false;
        }
        
        $fileContents = implode(DIRECTORY_SEPARATOR, array(
            $this->cacheDirectory,
            md5('cacheKey:'. $key)
        ));
        
        return file_get_contents($fileContents);
    }
    
    /**
     *
     * @param string $fileName
     * 
     * @return boolean 
     */
    public function delete($key)
    {
        if (!$this->exists($key)) {
            return true;
        }
        
        $fileInfos = implode(DIRECTORY_SEPARATOR, array(
            $this->cacheDirectory,
            md5('cacheKey:'. $key) . '.cache'
        ));
        
        $fileContents = implode(DIRECTORY_SEPARATOR, array(
            $this->cacheDirectory,
            md5('cacheKey:'. $key)
        ));
        
        $ret = (int)unlink($fileInfos) + (int)unlink($fileContents);
        
        return $ret == 2;
    }
    
    /**
     *
     * @return \Fwk\Cache\Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    public function setSerializer(\Fwk\Cache\Serializer $serializer)
    {
        $this->serializer = $serializer;
        
        return $this;
    }
}