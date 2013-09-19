<?php

spl_autoload_register(function($class_name)
{
    require $class_name . '.php';
});

class MyProjectBaseAction extends ProjectBaseAction
{
    public function __construct()
    {
        echo "Would the real slim shady pls stand up!" . PHP_EOL;
    }
}

class_alias('MyProjectBaseAction', 'ProjectBaseAction');

// this is a framework action, that extends the framework's default ProjectBaseAction.
// by using the class_alias we gain the ability to inject a custom implementation into the class hierarchy.
class Action extends ProjectBaseAction
{

}

$a = new Action();

