# Extensions

You will probably have some custom tasks or event listeners that are not included in the default GrumPHP project.
It is possible to group this additional GrumPHP configuration in an extension. 
This way you can easily create your own extension package and load it whenever you need it.

The configuration looks like this:

```yaml
# grumphp.yml
grumphp:
    extensions:
        - My\Project\GrumPHPExtension
```

The configured extension class needs to implement `ExtensionInterface`. 
Now you can register the tasks and events from your own package in the service container of GrumPHP.
For example:

```php
<?php
namespace My\Project;

use GrumPHP\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GrumPHPExtension implements ExtensionInterface
{
    public function load(ContainerBuilder $container)
    {
        // Register your own stuff to the container!
    }
}
```

# Third Party Extensions

This page lists third party extensions implementing useful GrumPHP tasks.

- [pluswerk/grumphp-bom-task](https://github.com/pluswerk/grumphp-bom-task) Forces files to have no BOM (Byte Order Mark).
- [pluswerk/grumphp-xliff-task](https://github.com/pluswerk/grumphp-xliff-task) Validates XLIFF files.
- [wearejust/grumphp-extra-tasks](https://github.com/wearejust/grumphp-extra-tasks) Extra GrumPHP tasks like a PhpCsAutoFixer.
- [nlubisch/grumphp-easycodingstandard](https://github.com/nlubisch/grumphp-easycodingstandard) GrumPHP task for running EasyCodingStandard.

Did you write your own extension? Feel free to add it to this list!
