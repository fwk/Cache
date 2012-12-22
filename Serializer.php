<?php
namespace Fwk\Cache;


interface Serializer
{
    public function serialize($value);
    
    public function unserialize($str);
}