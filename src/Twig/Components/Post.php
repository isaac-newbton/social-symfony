<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use App\Entity\Post as PostComponent;

#[AsTwigComponent]
class Post
{
    public PostComponent $post;
}