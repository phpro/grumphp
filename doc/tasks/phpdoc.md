# Phpdoc

Phpdoc is the world standard auto-documentation tool for PHP. Written in PHP, phpdoc can be used directly from the command-line. 
It lives under the `phpdoc` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        phpdoc:
          config_file: ~
          target_folder: ~
          cache_folder: ~
          filename: ~
          directory: ~
          encoding: ~
          extensions: ~
          ignore: ~
          ignore_tags: ~
          ignore_symlinks: ~
          markers: ~
          title: ~
          force: ~
          visibility: ~
          default_package_name: ~
          source_code: ~
          progress_bar: ~
          template: ~
          quiet: ~
          ansi: ~
          no_ansi: ~
          no_interaction: ~
```

**config_file**

*Default: `null`*

Without config_file parameter phpdoc will search for a phpdoc.dist.xml config file. 
This file can be overload by phpdoc.xml.
If no file found, no config file will be used.


**target_folder**

*Default: `null`*

Without this parameter the doc will be generated in an `output/` folder.


**cache_folder**

*Default: `null`*

Without this parameter, cache will be placed in the `target_folder`.


**filename**

*Default: `null`*

Comma separated file list to documents.


**directory**

*Default: `null`*

Comma separated directory list to documents.


**encoding**

*Default: `null`*

Without this parameter, encoding will be `'UTF-8'`.


**extensions**

*Default: `null`*

Comma separated file extension list. Contains extension of file to parse.
Without this parameter, parsed file are :
* php
* php3
* phtml

**ignore**

*Default: `null`*

Comma separated list of paths to skip when parsing.

**ignore_tags**

*Default: `null`*

Comma separated list of tags to skip when parsing.


**ignore_symlinks**

*Default: `false`*

Tells the parser not to follow symlinks.


**markers**

*Default: `null`*

Provide a comma-separated list of markers to parse (TODO ...).


**title**

*Default: `null`*

Specify a title for the documentation.


**force**

*Default: `null`*

Ignore exceptions and continue parsing.


**visibility**

*Default: `null`*

Provide a comma-separated list of visibility scopes to parse.
This parameter may be used to tell phpDocumentor to only parse public properties and methods, or public and protected.


**default_package_name**

*Default: `null`*

Default package name


**source_code**

*Default: `null`*

When this parameter is provided the parser will add a compressed, base64-encoded version of the parsed file’s source as child element of the <file> element. 
This information can then be picked up by the transformer to generate a syntax highlighted view of the file’s source code and even have direct links to specific lines.


**progress_bar**

*Default: `null`*

Display progress bar during the process.


**template**

*Default: `null`*

Specify a template to use. Without this parameter the template named "clean" will be used.

**quiet**

*Default: `null`*

With this option, only errors will be displayed.


**ansi**

*Default: `null`*

Force ANSI output.


**no_ansi**

*Default: `null`*

Disable ANSI output.


**no_interaction**

*Default: `null`*

Do not ask any interactive question.