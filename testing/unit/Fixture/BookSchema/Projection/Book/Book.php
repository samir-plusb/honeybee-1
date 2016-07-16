<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Book;

use Honeybee\Projection\Resource\Resource;

class Book extends Resource
{
    public function getTitle()
    {
        return $this->get('title');
    }

    public function getDescription()
    {
        return $this->get('description');
    }
}
