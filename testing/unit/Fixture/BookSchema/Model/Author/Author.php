<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Author;

use Honeybee\Model\Aggregate\AggregateRoot;

class Author extends AggregateRoot
{
    public function getFirstname()
    {
        return $this->get('firstname');
    }

    public function getLastname()
    {
        return $this->get('lastname');
    }
}
