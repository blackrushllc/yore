# User module settings

# FIX: THIS DIR STRU SHOULD BE modules/[MOD NAME]/settings

This folder contains settings which will override the default settings in a module.

A settings file in this folder with the same name as a module 
will augment the settings file used by the module.

Deleting or renaming the settings file will restore the default settings used by
the module.

This way, a user can customise the settings used by a module without
globally editing the settings of the module or having his settings overwritten
if the module gets updated.
