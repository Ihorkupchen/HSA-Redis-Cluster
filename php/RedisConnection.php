<?php

class RedisConnection
{
    public function connect(array $sentinels, string $masterName): Redis
    {
        [$masterHost, $masterPort] = $this->getMasterFromSentinel($sentinels, $masterName);

        $redis = new Redis();
        $redis->connect($masterHost, $masterPort);

        return $redis;
    }

    private function getMasterFromSentinel($sentinels, $masterName): array
    {
        foreach ($sentinels as $sentinelInfo) {
            try {
                $sentinel = new Redis();
                $sentinel->connect($sentinelInfo['host'], $sentinelInfo['port']);

                return $sentinel->rawCommand('SENTINEL', 'get-master-addr-by-name', $masterName);
            } catch (Exception $e) {
                echo "Failed to connect to Sentinel {$sentinelInfo['host']}:{$sentinelInfo['port']}, error: {$e->getMessage()}\n";
            }
        }

        throw new Exception('Failed to get master address');
    }
}