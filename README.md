# Installation

```
composer require phpro/grumphp
```

# Configuration
```
{
    "require-dev": {
      "phpro/grumphp": "dev-master",
      "squizlabs/php_codesniffer": "~2.3"
    },
    "extra": {
      "grumphp": {
        "base_dir": ".",
        "git_dir": ".",
        "phpcs": {
          "standard": "PSR2"
        }
      }
    }
}
```
