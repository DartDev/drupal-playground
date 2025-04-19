# DP - Entity Types

## Introduction

This module provides an example implementation of a custom content entity type.

It is similar to the Node content entity type from the Drupal Core,
albeit in a more barebones state - warranting further improvements.

For example, you may find that `Duck` inherits from the `RevisionableContentEntityBase`,
supporting revisions, while `Node` inherits from the `EditorialContentEntityBase`,
supporting both revisions and published/unpublished status control of the entity.

(Publication status may be important depending on the use case, as there are
potential interactions with it in the Drupal ecosystem, e.g. via
the Content Moderation module.)

In case of the Duck entity, the status is controlled by a simple checkbox, which
indicates whether a specific Duck is enabled.

Find out more: https://www.drupal.org/docs/drupal-apis/entity-api

## Sub-Modules

### DP - Entity Types - Preconfigured Types

Provides a collection of example entity types for this module.
