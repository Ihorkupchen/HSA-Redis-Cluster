<?php

class CacheArticleRepositoryDecorator implements ArticleRepository
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly Cache $cache,
        private readonly int $ttl = 3600
    ) {
    }

    public function find(int $articleId): ?Article
    {
        $key = "article:$articleId";

        return $this->cache->getOrSet($key, fn() => $this->articleRepository->find($articleId), $this->ttl);
    }

    public function save(Article $article): bool {
        $result = $this->articleRepository->save($article);

        if ($result) {
            $key = "article:{$article->id}";
            $this->cache->set($key, $article, $this->ttl);
        }

        return $result;
    }
}
