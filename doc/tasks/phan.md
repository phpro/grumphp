# Phan

The Phan task will run your automated PHP tasks.

***Composer***

```
composer require --dev phan/phan
```

***Config***

The task lives under the `phan` namespace and has following configurable parameters.

```yaml
# grumphp.yml
parameters:
    tasks:
        phan:
            config_file: .phan/config.php
            output_mode: text
            output: null
            triggered_by: [php]
```

**config_file**

*Default: .phan/config.php*

If your config.php file is located at an exotic location, you can specify your custom build file location with this option.
This option is set to `.phan/config.php` by default.
This means that `.phan/config.php` is automatically loaded if the file exists in the current directory.


**output_mode**

*Default: text*

This option sets the output mode. Valid outpot modes are 'text', 'json', 'csv', 'codeclimate', 'checkstyle', or 'pylint'.
This option is set to `text` by default.

**output**

*Default: null*

It's possible to save the output to a file, you can specify the file name with this option.
This option is set to `null` by default.

**triggered_by**

*Default: [php]*

This option will specify which file extensions will trigger the phan task.
By default Phan will be triggered by altering a PHP file.
You can overwrite this option to whatever file you want to use!
