# XmlLint

The XmlLint task will lint all your XML files.
It lives under the `xmllint` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        xmllint:
            load_from_net: false
            x_include: false
            dtd_validation: false
            scheme_validation: false
```

**load_from_net**

*Default: false*

This option can be used to tell the linter if external files can be loaded from the net.
When enabled all online DTD and XSD resources will be loaded and validated if required.
You can speed up the validation a lot by disabling this option.

**x_include**

*Default: false*

By enabling this option, the xincluded resources you specified in the XMl are fetched. 
After fetching the resources, all additional validations are run on the complete XML resource.


**dtd_validation**

*Default: false*

It is possible to validate XML against the specified DTD. 
Both internal, external as online resources are fetched and used for validation.


**scheme_validation**

*Default: false*

It is possible to validate XML against the specified XSD schemes. 
Both internal, external as online resources are fetched and used for validation.