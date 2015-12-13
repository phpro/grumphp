# GrumPHP does not work with submodules.

When you use a submodule, GrumPHP will throw diff errors.
This is because the plugin uses Gitlib which does not support submodules.

If you do not need to update your submodule, you can just remove all references to it.
If the changed .gitmodules is not commited nothing will change in your repo.

Ref.: https://github.com/gitonomy/gitlib/issues/12
