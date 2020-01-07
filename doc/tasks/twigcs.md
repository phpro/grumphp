#  TwigCs

Check Twig coding standard based on [FriendsOfTwig/TwigCs](https://github.com/FriendsOfTwig/TwigCs) .

***Composer***

```
composer require --dev friendsoftwig/twigcs
```

***Config***

The task lives under the `twigcs` namespace and has following configurable parameters:

```yaml
# grumphp.yml
parameters:
    tasks:
        twigcs:
            path: '.'
            exclude: []
            severity: 'warning'
            ruleset: 'FriendsOfTwig\Twigcs\Ruleset\Official'
            triggered_by: ['twig']
```

**path**

*Default: null*

By default `.` (current folder) will be used.
You can specify an alternate location by changing this option.

**exclude**

*Default: []*

List of directories to exclude.

**severity**

*Default: 'warning'*

Severity level of sniffing (possibles values are : 'IGNORE', 'INFO', 'WARNING', 'ERROR').

**ruleset**

*Default: 'FriendsOfTwig\Twigcs\Ruleset\Official'*

Ruleset used, default ruleset is based on [official one from twig](https://twig.symfony.com/doc/2.x/coding_standards.html)

**triggered_by**

*Default: [twig]*

This option will specify which file extensions will trigger this task.
