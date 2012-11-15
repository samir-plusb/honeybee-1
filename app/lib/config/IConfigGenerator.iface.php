<?php

interface IConfigGenerator
{
    public function generate($name, array $affectedPaths);
}
