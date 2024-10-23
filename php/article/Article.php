<?php

class Article {
    public function __construct(
            public int $id,
            public string $title,
            public string $content
    ) {
    }
}