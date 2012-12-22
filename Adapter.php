<?php
namespace Fwk\Cache;


interface Adapter
{
    public function read($key);
    
    public function exists($key);
    
    public function write($key, $maxAge, $contents);
    
    public function delete($key);
}