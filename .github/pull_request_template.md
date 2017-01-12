| Q             | A
| ------------- | ---
| Branch        | master for features and deprecations
| Bug fix?      | yes/no
| New feature?  | yes/no
| BC breaks?    | yes/no
| Deprecations? | yes/no
| Documented?   | yes/no
| Fixed tickets | comma-separated list of tickets fixed by the PR, if any

<!-- Please add an advanced description on what this PR is doing to GrumPHP. -->


<!-- Are you creating a new task? Make sure to complete this checklist: -->

# New Task Checklist:

- [ ] Is the README.md file updated?
- [ ] Are the dependencies added to the composer.json suggestions?
- [ ] Is the doc/tasks.md file updated?
- [ ] Are the task parameters documented?
- [ ] Is the task registered in the tasks.yml file?
- [ ] Does the task contains phpspec tests?
- [ ] Is the configuration having logical allowed types?
- [ ] Does the task run in the correct context?
- [ ] Is the `run()` method readable?
- [ ] Is the `run()` method using the configuration correctly?
- [ ] Are all CI services returning green?
