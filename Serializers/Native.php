<?php
namespace Fwk\Cache\Serializers;

use Fwk\Cache\Serializer;

class Native implements Serializer
{
    public function serialize($value)
    {
        return serialize($value);
    }
    
    public function unserialize($str)
    {
        return unserialize($str);
    }
}