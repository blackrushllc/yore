# User module views

This folder contains views which will override the default views in a module.

A view in this folder under a directory with the same name as a module 
will supercede the view file used by the module.

Deleting or renaming the view file will restore the default view used by
the module.

This way, a user can copy and then extend the view used by a module without
globally editing the appearance of the module or having his changes overwritten
if the module gets updated.
