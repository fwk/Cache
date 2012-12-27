<?php
namespace Fwk\Cache\Serializers;

use Fwk\Cache\Serializer;

class Igbinary implements Serializer
{
    public function __construct()
    {
        if (!extension_loaded('igbinary')) {
            throw new \RuntimeException(
                "Extension 'igbinary' is required for this cache adapter"
            );
        }
    }
    
    public function serialize($value)
    {
        return igbinary_serialize($value);
    }
    
    public function unserialize($str)
    {
        return igbinary_unserialize($str);
    }
}