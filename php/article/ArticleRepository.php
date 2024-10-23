<?php

interface ArticleRepository
{
    public function find(int $articleId): ?Article;

    public function save(Article $article): bool;
}