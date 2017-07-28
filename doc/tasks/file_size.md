# File size

The file size task ensures a maximum size for a file to be added to git.

```yaml
# grumphp.yml
parameters:
    tasks:
        file_size:
            max_size: 10M
```

**max_size**

*Default: 10M*

Defines the maximum size. The target value may use magnitudes of kilobytes (k, ki), megabytes (m, mi), or gigabytes (g, gi). Those suffixed with an i use the appropriate 2**n version in accordance with the IEC standard.
