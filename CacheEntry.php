<?php
namespace Fwk\Cache;


class CacheEntry
{
    protected $addedOn;
    
    protected $maxAge;
    
    protected $contents;
    
    protected $manager;
    
    public function __construct($contents = null)
    {
        $this->contents = $contents;
    }
    
    public function isExpired()
    {
    }
    
    public function update()
    {
    }
    
    public function delete()
    {
    }
    
    public function getAddedOn()
    {
        return $this->addedOn;
    }

    public function setAddedOn($addedOn)
    {
        $this->addedOn = $addedOn;
        
        return $this;
    }

    public function getMaxAge()
    {
        return $this->maxAge;
    }

    public function setMaxAge($maxAge)
    {
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

    public function getManager()
    {
        return $this->manager;
    }

    public function setManager(Manager $manager)
    {
        $this->manager = $manager;
        
        return $this;
    }
}