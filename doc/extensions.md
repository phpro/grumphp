# Extensions

You will probably have some custom tasks or event listeners that are not included in the default GrumPHP project.
It is possible to group this additional GrumPHP configuration in an extension. 
This way you can easily create your own extension package and load it whenever you need it.

The configuration looks like this:

```yaml
# grumphp.yml
parameters:
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
