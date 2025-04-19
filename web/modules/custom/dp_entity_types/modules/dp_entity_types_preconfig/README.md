# DP - Entity Types - Preconfigured Types

## Introduction

This module provides a collection of example duck types.

The duck types added by this module are restricted from manual deletion,
as the module handles it automatically during uninstallation.

It is not a mandatory measure when handling preconfigured entity types in
general, but serves as an example of clean approach for controlling the
objects added by the custom code, in order to restrict the user from
mishandling them.

### Note

You may notice that when the custom entity types are programmatically
deleted - the related content remains in the database.

This is a default behavior which corresponds to the single purpose of
the action of entity type deletion - to delete the entity type itself.

However, it is possible to extend the `hook_uninstall()` of this module
to handle the content deletion first, and then remove the unwanted bundles.
