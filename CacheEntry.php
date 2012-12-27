<?php
namespace Fwk\Cache;

use SuperClosure\SuperClosure;

class CacheEntry
{
    protected $createdOn;
    
    protected $maxAge;
    
    protected $contents;
    
    protected $serializedContents;
    
    protected $key;
    
    protected $new = true;
    
    protected $tags = array();
    
    protected $serializer = null;
    
    public function __construct($contents = null, $key = null, 
        array $tags = array())
    {
        $this->contents     = $contents;
        $this->createdOn    = time(null);
        $this->key          = $key;
        $this->tags         = $tags;
    }
    
    /**
     *
     * @return boolean 
     */
    public function isExpired()
    {
        if (null === $this->maxAge) {
            return false;
        } elseif ($this->maxAge instanceof SuperClosure) {
            $closure = $this->maxAge->getClosure();
            return $closure();
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
        } elseif($maxAge instanceof \Closure) {
            $maxAge = new SuperClosure($maxAge);
        }
        
        $this->maxAge = $maxAge;
        
        return $this;
    }

    public function getContents()
    {
        if(!isset($this->contents)) {
            $this->contents = $this->serializer
                                    ->unserialize($this->serializedContents);
        }
        
        return $this->contents;
    }

    public function setContents($contents)
    {
        $this->contents = $contents;
        
        return $this;
    }
    
    public function getSerializedContents()
    {
        if (!isset($this->serializedContents)) {
            $this->serializedContents = $this->serializer
                                            ->serialize($this->contents);
        }
        
        return $this->serializedContents;
    }

    public function setSerializedContents($serializedContents)
    {
        $this->serializedContents = $serializedContents;
        
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
    
    /**
     *
     * @param string $tag
     * 
     * @return CacheEntry 
     */
    public function addTag($tag) 
    {
        if (!$this->hasTag($tag)) {
            array_push($this->tags, $tag);
        }
        
        return $this;
    }
    
    /**
     *
     * @param string $tag
     * 
     * @return boolean 
     */
    public function hasTag($tag)
    {
        return in_array($tag, $this->tags);
    }
    
    /**
     *
     * @param string $tag
     * 
     * @return CacheEntry 
     */
    public function removeTag($tag)
    {
        $final = array();
        foreach ($this->tags as $tag1) {
            if ($tag1 != $tag) {
                array_push($final, $tag1);
            }
        }
        
        $this->tags = $final;
        
        return $this;
    }
    
    /**
     *
     * @param array $tags
     * 
     * @return CacheEntry 
     */
    public function removeTags(array $tags)
    {
        foreach ($tags as $tag) {
            $this->removeTag($tag);
        }
        
        return $this;
    }
    
    /**
     *
     * @param array $tags
     * 
     * @return CacheEntry 
     */
    public function addTags(array $tags)
    {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
        
        return $this;
    }
    
    /**
     *
     * @return Serializer 
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;
        
        return $this;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setTags(array $tags)
    {
        $this->tags = $tags;
        
        return $this;
    }

    public function __sleep()
    {
        return array('createdOn', 'key', 'maxAge', 'tags');
    }
    
    public function __wakeup()
    {
        $this->new = false;
    }
}