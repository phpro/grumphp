# Deptrac

Follow the [installation instructions](https://github.com/sensiolabs-de/deptrac#installation) to add deptrac to your 
project.

The Deptrac task will check for dependencies between the software layers of your project. It lives under the `deptrac` 
namespace and has following configurable parameters:


```yaml
# grumphp.yml
grumphp:
    tasks:
        deptrac:
            depfile: ~
            formatter: ~
            graphviz_display: ~
            graphviz_dump_image: ~
            graphviz_dump_dot: ~
            graphviz_dump_html: ~
            junit_dump_xml: ~
            xml_dump: ~
            baseline_dump: ~
```

**depfile**

*Default: null*

Set path to deptrac configuration file. Example: `/var/www/src/depfile.yml`

**formatter**

*Default: []*

Enable (multiple) formatters with this option, e.g. `console`, `github-actions`, `graphviz`, `table`, `junit`, `xml`, `baseline`.

**graphviz_display**

*Default: true*

Open the generated graphviz image. Set to `true` to activate.

**graphviz_dump_image**

*Default: null*

Set path to a dumped png file.

**graphviz_dump_dot**

*Default: null*

Set path to a dumped dot file.

**graphviz_dump_html**

*Default: null*

Set path to a dumped html file.

**junit_dump_xml**

*Default: null*

Set path to a dumped JUnit xml file.

**xml_dump**

*Default: null*

Set path to a dumped xml file.

**baseline_dump**

*Default: null*

Set path to a dumped baseline file.
