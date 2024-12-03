<?php

namespace App\Application\DTO;

class OrderDTO
{
    public int $userId;
    /** @var ArticleDTO[] */
    public array $articles;
}
