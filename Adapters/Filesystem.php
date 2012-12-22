<?php
namespace Fwk\Cache\Adapters;

use Fwk\Cache\AbstractAdapter;
use Fwk\Cache\Exceptions\AdapterError;

class Filesystem extends AbstractAdapter
{
    /**
     * @var string 
     */
    protected $cacheDirectory;
    
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
    public function write($key, $contents)
    {
        $file = implode(DIRECTORY_SEPARATOR, array(
            $this->cacheDirectory,
            md5('cacheKey:'. $key)
        ));
        
        return file_put_contents($file, $contents);
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
            md5('cacheKey:'. $key)
        ));
        
        return is_file($file);
    }
    
    /**
     *
     * @param string $fileName
     * 
     * @return string 
     */
    public function read($key)
    {
        $file = implode(DIRECTORY_SEPARATOR, array(
            $this->cacheDirectory,
            md5('cacheKey:'. $key)
        ));
        
        if (!is_file($file)) {
            return false;
        }
        
        return file_get_contents($file);
    }
    
    /**
     *
     * @param string $fileName
     * 
     * @return boolean 
     */
    public function delete($key)
    {
        $file = implode(DIRECTORY_SEPARATOR, array(
            $this->cacheDirectory,
            md5('cacheKey:'. $key)
        ));
        
        if (!is_file($file)) {
            return true;
        }
        
        return unlink($file);
    }
}