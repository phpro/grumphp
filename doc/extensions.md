# Extensions

You might have [a custom tasks](tasks.md#creating-a-custom-task)
 or [event listeners](runner.md#events) that is not included in the default GrumPHP project.
It is possible to group this additional GrumPHP configuration in an extension. 
This way you can centralize this custom logic in your own extension package and load it wherever you need it.

The configuration looks like this:

```yaml
# grumphp.yml
grumphp:
    extensions:
        - My\Project\GrumPHPExtension
```

The configured extension class needs to implement `GrumPHP\Extension\ExtensionInterface`.
Since GrumPHP is using the [symfony/dependency-injection](https://symfony.com/doc/current/service_container.html) internally to configure all resources,
a GrumPHP extension can append multiple configuration files to the container configuration.

We support following loaders: YAML, XML, INI, GLOB, DIR.
*Note:* We don't support the PHP or CLOSURE loaders to make sure your extension is compatible with our grumphp-shim PHAR distribution.
All dependencies get scoped with a random prefix in the PHAR, making these loaders not usable in there.

Example extension:

```php
<?php
namespace My\Project;

use GrumPHP\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MyAwesomeGrumPHPExtension implements ExtensionInterface
{
    public function imports(): iterable
    {
        $configDir = dirname(__DIR).'/config';
    
        yield $configDir.'/my-extension.yaml';
        yield $configDir.'/my-extension.xml';
        yield $configDir.'/my-extension.ini';
        yield $configDir.'/my-extension/*';
    }
}
```

Example config file in which you enable a custom task:

```yaml
# my-extension.yaml
services:
  My\CustomTask:
    arguments: []
    tags:
      - {name: grumphp.task, task: myCustomTask}
```

# Third Party Extensions

This page lists third party extensions implementing useful GrumPHP tasks.

- [pluswerk/grumphp-bom-task](https://github.com/pluswerk/grumphp-bom-task) Forces files to have no BOM (Byte Order Mark).
- [pluswerk/grumphp-xliff-task](https://github.com/pluswerk/grumphp-xliff-task) Validates XLIFF files.
- [wearejust/grumphp-extra-tasks](https://github.com/wearejust/grumphp-extra-tasks) Extra GrumPHP tasks like a PhpCsAutoFixer.
- [nlubisch/grumphp-easycodingstandard](https://github.com/nlubisch/grumphp-easycodingstandard) GrumPHP task for running EasyCodingStandard.
- [jonmldr/grumphp-doctrine-task](https://github.com/jonmldr/grumphp-doctrine-task) GrumPHP task for Doctrine's schema validation in Symfony projects.

Did you write your own extension? Feel free to add it to this list!
