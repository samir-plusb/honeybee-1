<?php

interface IComparable
{
    /**
     * Return -1 if smaller, 0 if equal and 1 if bigger.
     *
     * @return int
     */
    public function compareTo($other);
}

?>
