# Deptrac

Follow the [installation instructions](https://github.com/sensiolabs-de/deptrac#installation) to add deptrac to your 
project.

The Deptrac task will check for dependencies between the software layers of your project. It lives under the `deptrac` 
namespace and has following configurable parameters:


```yaml
# grumphp.yml
parameters:
    tasks:
        deptrac:
            formatter_graphviz: ~
            formatter_graphviz_display: ~
            formatter_graphviz_dump_image: ~
            formatter_graphviz_dump_dot: ~
            formatter_graphviz_dump_html: ~
```


**formatter_graphviz**

*Default: 0*

Set to `1` to enable the graphviz formatter.

**formatter_graphviz_display**

*Default: false*

Open the generated graphviz image. Set to `true` to activate.

**formatter_graphviz_dump_image**

*Default: null*

Set path to a dumped png file.

**formatter_graphviz_dump_dot**

*Default: null*

Set path to a dumped dot file.

**formatter_graphviz_dump_html**

*Default: null*

Set path to a dumped html file.
