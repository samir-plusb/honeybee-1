# Templates

- [Templates](#templates)
  - [Output type renderer](#output-type-renderer)
  - [Template lookup paths](#template-lookup-paths)
  - [Master templates and Twig macros](#master-templates-and-twig-macros)
  - [Support for other template libraries](#support-for-other-template-libraries)
  - [TBD / Ideas / Misc](#tbd--ideas--misc)

## Output type renderer

There is a `Honeybee\Agavi\Renderer\ProxyRenderer` that defines a chain of
renderers that are used to render templates. By default the
```output_types.xml``` file defines two renderers to be tried:

1. ```Honeybee\Agavi\Renderer\TwigRenderer``` using `Twig` for templates and
1. ```Honeybee\Agavi\Renderer\PhpRenderer``` using PHP for templates

The default filename extensions are

- `.twig` for the `TwigRenderer` and
- `.php` for the `PhpRenderer`.

The `ProxyRenderer` tries the `TwigRenderer` first. If that renderer did not
succeed, the `PhpRenderer` will be tried. If both renderers did not succeed
(e.g. because of missing templates) an exception is thrown. This means, that
you can use PHP and Twig templates side by side. If there are both a PHP and a
twig template for a single iew the twig template is rendered.

## Template lookup paths

The basic template lookup path structure is:

1. ```app/project/templates/modules/<module_name>/<view_name>```
1. ```app/modules/<module_name>/templates/<view_name>```
1. ```app/modules/<module_name>/impl/<view_name>```

The lookup path is expanded using the current action's name, the current view's
name and the current renderers default extension.

In addition to that the current locale is taken into account when searching for
templates:

1. ```app/project/module_templates/<module_name>/<locale_identifier>/<action_name/<action_name><view_name><extension>```
1. ```app/project/module_templates/<module_name>/<locale_short_identifier>/<action_name/<action_name><view_name><extension>```
1. ```app/project/module_templates/<module_name>/<action_name/<action_name><view_name>.<locale_identifier><extension>```
1. ```app/project/module_templates/<module_name>/<action_name/<action_name><view_name>.<locale_short_identifier><extension>```
1. ```app/project/module_templates/<module_name>/<action_name/<action_name><view_name><extension>```
1. ```app/modules/<module_name>/templates/<locale_identifier>/<action_name/<action_name><view_name><extension>```
1. ```app/modules/<module_name>/templates/<locale_short_identifier>/<action_name/<action_name><view_name><extension>```
1. ```app/modules/<module_name>/templates/<action_name/<action_name><view_name>.<locale_identifier><extension>```
1. ```app/modules/<module_name>/templates/<action_name/<action_name><view_name>.<locale_short_identifier><extension>```
1. ```app/modules/<module_name>/templates/<action_name/<action_name><view_name><extension>```
1. ```app/modules/<module_name>/impl/<action_name/<action_name><view_name>.<locale_identifier><extension>```
1. ```app/modules/<module_name>/impl/<action_name/<action_name><view_name>.<locale_short_identifier><extension>```
1. ```app/modules/<module_name>/impl/<action_name/<action_name><view_name><extension>```

This means, for the Agavi module `User` with the action `Login` and the view
`Input` assuming a current locale of ```de_DE``` and using the `TwigRenderer`
the following paths are checked for templates before an exception is thrown:

```
app/project/module_templates/User/de_DE/Login/LoginInput.twig
app/project/module_templates/User/de/Login/LoginInput.twig
app/project/module_templates/User/Login/LoginInput.de_DE.twig
app/project/module_templates/User/Login/LoginInput.de.twig
app/project/module_templates/User/Login/LoginInput.twig

app/modules/User/templates/de_DE/Login/LoginInput.twig
app/modules/User/templates/de/Login/LoginInput.twig
app/modules/User/templates/Login/LoginInput.de_DE.twig
app/modules/User/templates/Login/LoginInput.de.twig
app/modules/User/templates/Login/LoginInput.twig

app/modules/User/impl/Login/LoginInput.de_DE.twig
app/modules/User/impl/Login/LoginInput.de.twig
app/modules/User/impl/Login/LoginInput.twig
```

## Master templates and Twig macros

The Agavi setting ```core.template_dir``` specifies the path to the master
templates of the project. There is a sub directory `macros` for Twig macros. The
`TwigRenderer` has the following lookup paths for Twig templates:

1. paths from the ```template_dirs``` parameter of the `TwigRenderer` (or the ```core.template_dir``` as a default; usually ```app/templates```)
1. path to the directory the current template is in (e.g. ```app/modules/impl/Login/```)
1. path to the module's template directory (via ```agavi.template.directory``` parameter from the modules's `module.xml` file; e.g. ```app/modules/impl```)

The default configuration in the ```output_types.xml``` file for the
```template_dirs``` parameter of the default `TwigRenderer` leads to the
following lookups:

1. ```<project_dir>/templates/macros```
1. ```<project_dir>/templates```
1. ```app/modules/<module_name>/templates/macros```
1. ```app/modules/<module_name>/templates```
1. ```app/templates/macros```
1. ```app/templates```

If there is a default twig macro in `app/templates/macros` you can put a macro
with the same name into one of the directories with higher precendence and thus
override the builtin macro with your own version.

## Support for other template libraries

TBD: This does not work as described, as the ```output_types.xml``` file does
not have working XIncludes. Thus the config handler has to be changed or the
`Pulq` approach with a sandbox file has to be adopted. See:
- https://github.com/berlinonline/pulq/blob/master/app/config/output_types.xml
- https://github.com/berlinonline/pulq/blob/master/app/config/ot_sandbox.xml
- https://github.com/berlinonline/familienportal/blob/master/applications/portal/project/app/config/output_types.xml

If you want to use other template libraries or languages you can do so by
overriding the default renderers specified in the ```output_types.xml``` file.
You can either use one of the Agavi provided ones (like the
```AgaviXsltRenderer``` or ```AgaviSmartyRenderer```) or create your own class.
Your own class should implement the ```AgaviIReusableRenderer``` interface and
may extend the base ```AgaviRenderer```. Make sure your class is autoloadable
and then specify it as a (default) renderer in the ```output_types.xml``` file:

```xml
<renderers default="custom">
    <renderer name="custom" class="Honeybee\Agavi\Renderer\ProxyRenderer">
        <ae:parameters>
            <ae:parameter name="some_path">%project.dir%</ae:parameter>
        </ae:parameters>
    </renderer>
</renderers>
```

## TBD / Ideas / Misc

- make introduction and overriding of output types work flawlessly
