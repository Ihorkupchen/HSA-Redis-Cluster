<?php

class Cache
{
    public function __construct(private Redis $redis)
    {
    }

    public function getOrSet(string $key, callable $fallback, int $ttl): mixed
    {
        $cachedData = $this->redis->get($key);

        if ($cachedData !== false) {
            echo "Retrieved from cache\n"; //log

            return unserialize($cachedData);
        }

        //Avoid Cache Stampede due to locking
        if (!$this->redis->set("lock:$key", "1", ["nx", "ex" => 5])) {
            echo "Another process updates the cache\n"; //log

            sleep(1);

            return $this->getOrSet($key, $fallback, $ttl);
        }

        $data = $fallback();
        $this->set($key, $data, $ttl); //update the cache with new data

        $this->redis->del("lock:$key"); // remove the lock

        echo "Retrieved from fallback\n"; //log

        return $data;
    }

    public function set(string $key, mixed $data, int $ttl): void
    {
        $this->redis->setex($key, $ttl, serialize($data));
    }

}