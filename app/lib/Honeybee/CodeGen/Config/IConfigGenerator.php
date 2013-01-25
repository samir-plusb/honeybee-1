<?php

namespace Honeybee\CodeGen\Config;

interface IConfigGenerator
{
    public function generate($name, array $affectedPaths);
}
