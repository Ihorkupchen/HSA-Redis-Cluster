<?php

class MysqlArticleRepository implements ArticleRepository
{
    public function find(int $articleId): ?Article
    {
        //simulation of a long database query for test
        sleep(5);
        $article = ['id' => $articleId, 'title' => 'Title', 'content' => 'Content'];

        return new Article($article['id'], $article['title'], $article['content']);
    }

    public function save(Article $article): bool
    {
        //save to DB

        return true;
    }
}