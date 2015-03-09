# Installation

```
composer require phpro/grumphp
```

# Configuration

`composer.json`:

```
{
    "require-dev": {
      "phpro/grumphp": "dev-master",
      "squizlabs/php_codesniffer": "~2.3"
    }
}
```

`grumphp.yml`:

```
phpcs:
    standard: "PSR2"

bin_dir: "./bin"
base_dir: "."
git_dir: "."
```
