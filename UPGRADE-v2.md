# Upgrade from v1 to v2

This document is for GrumPHP extension or task developers.
Regular users of GrumPHP are not impacted by this version change: the upgrade should go smoothly.

There are 2 big breaking changes:

* The introduction of [AMPHP v3](https://amphp.org/) forced us to rewrite the internal API of GrumPHP in a breaking way.
  * As a user, you now benifit of a more stable, fiber based parallel task execution system.
* The [grumphp-shim PHAR](https://github.com/phpro/grumphp-shim) distribution had some issues with scoped dependencies.
  * We reworked the `TaskInterface` to make sure that there are no external dependencies in the interface's API anymore. 
  * We reworked the `ExtensionInterface` to make sure that there are no external dependencies in the interface's API anymore. 

## Task interface change

See https://github.com/phpro/grumphp/pull/1090


The GrumPHP `TaskInterface` now only has a dependency to the `GrumPHP\\` namespace.
The Symfony OptionsResolver will not be a part of the task interface anymore.

New task interface:


```php
namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\Context\ContextInterface;

interface TaskInterface
{
    public static function getConfigurableOptions(): ConfigOptionsResolver;

    public function canRunInContext(ContextInterface $context): bool;

    public function run(ContextInterface $context): TaskResultInterface;

    public function getConfig(): TaskConfigInterface;

    public function withConfig(TaskConfigInterface $config): TaskInterface;
}

```


This makes it possible for extensions to provide tasks that are compatible with the `grumphp-shim` release.
In order to make the task compatible, you'll need to wrap the symfony options-resolver with GrumPHP's own options-resolver:

```php
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();

        // ..... your config

        return ConfigOptionsResolver::fromClosure(
            static fn (array $options): array => $resolver->resolve($options)
        );
    }
```


❗ In extensions, you do not want to use `\GrumPHP\Task\Config\ConfigOptionsResolver::fromOptionsResolver()`
- since Symfony's options resolver will be scoped (prefixed) in `grumphp-shim`'s phar distrubition.



## Extension changes

See https://github.com/phpro/grumphp/pull/1091

In v2, you will find a new GrumPHP extension system.
Since the new implementation does not leak the dependency with `symfony/dependency-injection` to the PHP API of an extension,
we can make any extension work in grumphp-shim (PHAR distribution) with scoped dependencies as well.

A well calculated downside of this, is that an extension maintainer is now forced to configure the service declarations in a separate YAML (or xml, ...) file where previously this could be done directly from PHP.

Breaking change:


```diff
class MyExtension implements ExtensionInterface
{

-     public function load(ContainerBuilder $container): void { /* ... */ }

+    public function imports(): iterable {
+        yield '/path/to/my/config.yaml';
+    }
}
```

And make sure all services are registered through a `symfony/dependency-injection` compatible configuration file.
(YAML, XML, INI, ...)  - PHP is not supported because of the scoped vendor in the PHAR.

```yaml
# /path/to/my/config.yaml

services:
  xxxxxx: xxxx
```
