#Extensions

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
use Symfony\Component\DependencyInjection\ContainerInterface;

class GrumPHPExtension implements ExtensionInterface
{
    public function load(ContainerInterface $container)
    {
        // Register your own stuff to the container!
    }
}
```
