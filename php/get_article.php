<?php

require 'RedisConnection.php';
require 'Cache.php';
require 'article/Article.php';
require 'article/ArticleRepository.php';
require 'article/MysqlArticleRepository.php';
require 'article/CacheArticleRepositoryDecorator.php';

$sentinels = [
    ['host' => 'redis-sentinel1', 'port' => 26379],
    ['host' => 'redis-sentinel2', 'port' => 26379],
    ['host' => 'redis-sentinel3', 'port' => 26379]
];

$masterName = 'mymaster';

try {

    $redis = (new RedisConnection())->connect($sentinels, $masterName);

    $cache = new Cache($redis);

    $articleRepository = new MysqlArticleRepository();
    $articleRepository = new CacheArticleRepositoryDecorator($articleRepository, $cache, 10);

    $retrievedArticle = $articleRepository->find(1);

    var_dump($retrievedArticle);
} catch (Exception $e) {
    echo "Помилка: {$e->getMessage()}\n";
}

