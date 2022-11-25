#  TwigCs

Check Twig coding standard based on [FriendsOfTwig/TwigCs](https://github.com/FriendsOfTwig/TwigCs) .

***Composer***

```
composer require --dev "friendsoftwig/twigcs:>=4"
```

***Config***

The task lives under the `twigcs` namespace and has following configurable parameters:

```yaml
# grumphp.yml
grumphp:
    tasks:
        twigcs:
            path: '.'
            severity: 'warning'
            display: 'all'
            ruleset: 'FriendsOfTwig\Twigcs\Ruleset\Official'
            triggered_by: ['twig']
            exclude: []
```

**path**

*Default: null*

By default `.` (current folder) will be used.
On precommit the path will not be used, changed files will be passed as arguments instead.
You can specify an alternate location by changing this option. If the path doesn't exist or is not accessible an exception will be thrown.

**severity**

*Default: 'warning'*

Severity level of sniffing (possibles values are : 'IGNORE', 'INFO', 'WARNING', 'ERROR').

**display**

*Default: 'all'*

The violations to display (possibles values are : 'all', 'blocking').

**ruleset**

*Default: 'FriendsOfTwig\Twigcs\Ruleset\Official'*

Ruleset used, default ruleset is based on [official one from twig](https://twig.symfony.com/doc/2.x/coding_standards.html)

**triggered_by**

*Default: [twig]*

This option will specify which file extensions will trigger this task.

**exclude**

*Default: []*

This option will specify which relative subfolders or files will be excluded from this task.
