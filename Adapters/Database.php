<?php
namespace Fwk\Cache\Adapters;

use Fwk\Cache\Adapter;
use Fwk\Cache\Exceptions\AdapterError;
use \PDO;

/**
 * Database Adapter
 * 
 * Uses a PDO instance to write cache data into two tables: cache_infos & 
 * cache_entries (see options)
 * 
 * SQL Schema:
 * <code>
 * DROP TABLE IF EXISTS `cache_entries`;
 * CREATE TABLE IF NOT EXISTS `cache_entries` (
 *   `key` varchar(255) NOT NULL,
 *   `contents` blob,
 *   PRIMARY KEY (`key`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 * 
 * DROP TABLE IF EXISTS `cache_infos`;
 * CREATE TABLE IF NOT EXISTS `cache_infos` (
 *   `key` varchar(255) NOT NULL,
 *   `created_on` int(11) NOT NULL,
 *   `max_age` text,
 *   `tags` text,
 *   PRIMARY KEY (`key`),
 *   KEY `created_on` (`created_on`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 * </code>
 * 
 */
class Database implements Adapter
{
    const TAGS_SEPARATOR = '#-#';
    
    /**
     * @var PDO 
     */
    protected $pdo;
    
    /**
     * @var Serializer 
     */
    protected $serializer;
    
    /**
     * @var array
     */
    protected $options = array();
    
    /**
     *
     * @param PDO $pdoInstance 
     * 
     * @return void
     */
    public function __construct(PDO $pdoInstance, array $options = array())
    {
        $this->pdo = $pdoInstance;
        
        $this->options = array_merge(array(
            'table.infos'   => 'cache_infos',
            'table.entries' => 'cache_entries'
        ), $this->options);
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
        if ($this->exists($entry->getKey())) {
            $this->delete($entry->getKey());
        }
        
        $maxAge = $entry->getMaxAge();
        if (is_object($maxAge)) {
            $maxAge = $this->serializer->serialize($maxAge);
        }
        
        $data = array(
            'key'           => $entry->getKey(),
            'created_on'    => $entry->getCreatedOn(),
            'max_age'       => $maxAge,
            'tags'          => implode(self::TAGS_SEPARATOR, $entry->getTags())
        );
        
        $query = "INSERT INTO %s VALUES (%s,%s,%s,%s)";
        $stmt = $this->pdo->exec(
            sprintf(
                $query, 
                $this->options['table.infos'],
                $this->pdo->quote($entry->getKey()),
                $this->pdo->quote($entry->getCreatedOn()),
                $this->pdo->quote($maxAge),
                $this->pdo->quote(implode(self::TAGS_SEPARATOR, $entry->getTags()))
            )
        );
        
        $entry->setSerializer($this->serializer);
        $data2 = array(
            'key'       => $entry->getKey(),
            'contents'  => $entry->getSerializedContents()
        );
        
        $query = "INSERT INTO %s VALUES (%s, %s)";
        $stmt = $this->pdo->exec(
            sprintf(
                $query, 
                $this->options['table.entries'],
                $this->pdo->quote($entry->getKey()),
                $this->pdo->quote($entry->getSerializedContents())
            )
        );
        
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
        $query = "SELECT COUNT(*) AS count FROM %s WHERE `key` = :key";
        $stmt = $this->pdo->prepare(
            sprintf(
                $query, 
                $this->options['table.infos']
            )
        );
        
        $stmt->execute(array('key' => $key));
        $res = $stmt->fetch();
        
        return $res['count'];
    }
    
    /**
     *
     * @param string $fileName
     * 
     * @return CacheEntry 
     */
    public function readEntry($key)
    {
        $query = "SELECT * FROM %s WHERE `key` = ? LIMIT 1";
        $stmt = $this->pdo->prepare(sprintf($query, $this->options['table.infos']));
        $stmt->execute(array($key));
        $res = $stmt->fetch();
        
        if (!$res) {
            throw new \Fwk\Cache\Exceptions\ReadError("invalid CacheEntry key: $key");
        }
        
        $entry = new \Fwk\Cache\CacheEntry(null, $key);
        $entry->setCreatedOn($res['created_on']);
        $entry->setKey($res['key']);
        
        if (strlen($res['max_age']) > 50) {
            $maxAge = $this->serializer->unserialize($res['max_age']);
        } else {
            $maxAge = $res['max_age'];
        }
        
        $entry->setMaxAge($maxAge);
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
        $query = "SELECT * FROM %s WHERE `key` = :key LIMIT 1";
        $stmt = $this->pdo->prepare(sprintf($query, $this->options['table.entries']));
        $stmt->execute(array('key' => $key));
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$res) {
            throw new \Fwk\Cache\Exceptions\ReadError("invalid CacheEntry key: $key");
        }
        
        return $res['contents'];
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
        
        $success = 0;
        $query = "DELETE FROM %s WHERE `key` =%s LIMIT 1";
        
        $aff = $this->pdo->exec(sprintf($query, $this->options['table.entries'], $this->pdo->quote($key)));
        $aff += $this->pdo->exec(sprintf($query, $this->options['table.infos'], $this->pdo->quote($key)));
        
        return $aff == 2;
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