| Q               | A
| --------------- | ---
| Version         | `vendor/bin/grumphp -V`
| Bug?            | yes/no
| New feature?    | yes/no
| Question?       | yes/no
| Documentation?  | yes/no
| Related tickets | comma-separated list of related tickets

<!-- Please add an advanced description on what this ISSUE is doing to GrumPHP. -->

<!-- In case of a bug, please fill in following information:-->
**My configuration**
```yaml
# grumphp.yml
# Please add a copy of your grumphp.yml file.
```

**Steps to reproduce:**
```sh
# Generate empty folder
mkdir tmp
cd tmp
git init
echo "vendor" > .gitignore
pbpaste > grumphp.yml
composer require --dev phpro/grumphp

# Your actions
# Please add the steps on how to reproduce the issue here.

# Run GrumPHP:
git add -A && git commit -m"Test"
# or
./vendor/bin/grumphp run
```

**Result:**
```
# Please add the result of the run or git commit actions here.
```
