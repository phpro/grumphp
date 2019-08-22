# Global installation

It is possible to install or update GrumPHP on your system with following commands:

```sh
composer global require phpro/grumphp
```

This will install the `grumphp` executable in the `~/.composer/vendor/bin` folder.
Make sure to add this folder to your system `$PATH` variable:

```sh
# .zshrc or .bashrc
export PATH="$HOME/.composer/vendor/bin:$PATH"
```

That's all! The `grumphp` command will be available on your CLI.
When your project also has a grumphp executable, this on will be used in favour of the one globally installed.
The same goes for other tools like e.g. `phpunit` you have installed both globally and locally.
