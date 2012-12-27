# Fwk Cache

Caching utility for PHP 5.3+

## Installation

Via [Composer](http://getcomposer.org):

```
{
    "require": {
        "fwk/cache": "dev-master",
    }
}
```

If you don't use Composer, you can still [download](https://github.com/fwk/Cache/zipball/master) this repository and add it
to your ```include_path``` [PSR-0 compatible](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)

## Documentation

```php
use Fwk\Cache\Manager;

$cache = new Manager(
    new \Fwk\Cache\Adapters\Filesystem(__DIR__ .'/tmp'),
    new \Fwk\Cache\Serializers\Native()
); 

// fetch or store (using a closure) our item if older than 1 day
$item = $cache->get('cached.item', '1day', function() {
    $obj = new \stdClass();
    $obj->value = "item value";

    // long and resource-heavy code used to generate our item
    sleep(5); 
    
    return $obj; 
})->getContents();

```

More documentation on the road... 

## Legal 

Fwk is licensed under the 3-clauses BSD license. Please read CREDITS and LICENSE for full details.

```
Copyright (c) 2012-2013, Julien Ballestracci <julien@nitronet.org>.
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:

 * Redistributions of source code must retain the above copyright
   notice, this list of conditions and the following disclaimer.

 * Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in
   the documentation and/or other materials provided with the
   distribution.

 * Neither the name of Julien Ballestracci nor the names of his
   contributors may be used to endorse or promote products derived
   from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.
```