<?php
namespace Fwk\Cache;


class CacheEntry
{
    protected $createdOn;
    
    protected $maxAge;
    
    protected $contents;
    
    protected $key;
    
    protected $new = true;
    
    public function __construct($contents = null, $key = null)
    {
        $this->contents     = $contents;
        $this->createdOn    = time(null);
        $this->key          = $key;
    }
    
    public function isExpired()
    {
        if (null === $this->maxAge) {
            return false;
        }
        
        $ts = $this->createdOn + $this->maxAge;
        
        return ($ts <= time(null));
    }
    
    public function isNew()
    {
        return $this->new;
    }
    
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    public function setCreatedOn($timestamp)
    {
        $this->createdOn = $timestamp;
        
        return $this;
    }

    public function getMaxAge()
    {
        return $this->maxAge;
    }

    public function setMaxAge($maxAge)
    {
        if (is_string($maxAge)) {
            if (preg_match_all('/([0-9]+)\s?([s|sec|second|seconds|d|day|days|m|min|minute|minutes|h|hour|hours|w|week|weeks|month|months|y|year|years])/i', $maxAge, $matches)) {
                $secs = 0;
                foreach($matches[0] as $idx => $inf) {
                    $num = (int)$matches[1][$idx];
                    $multiple = $matches[2][$idx];
                    
                    switch($multiple) {
                        case 's':
                            $secs += $num;
                            break;
                        
                        case 'm':
                            $secs += ($num*60);
                            break;
                        
                        case 'h':
                            $secs += ($num*60*60);
                            break;
                        
                        case 'd':
                            $secs += ($num*60*60*24);
                            break;
                        
                        case 'w':
                            $secs += ($num*60*60*24*7);
                            break;
                        
                        case 'month':
                        case 'months':
                            $secs += ($num*60*60*24*7*30);
                            break;
                        
                        case 'y':
                            $secs += ($num*60*60*24*7*51);
                            break;
                    }
                }
                
                $maxAge = $secs;
            }
        }
        
        $this->maxAge = $maxAge;
        
        return $this;
    }

    public function getContents()
    {
        return $this->contents;
    }

    public function setContents($contents)
    {
        $this->contents = $contents;
        
        return $this;
    }
    
    public function getKey()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        $this->key = $key;
        
        return $this;
    }
    
    public function getCacheFile()
    {
        return $this->cacheFile;
    }

    public function setCacheFile($cacheFile)
    {
        $this->cacheFile = $cacheFile;
        
        return $this;
    }
    
    public function __wakeup() {
        $this->new = false;
    }
}