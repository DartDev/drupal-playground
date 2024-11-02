# Import Solutions

## Introduction

This module serves as a Back-End code showcase of custom data import solutions.

It is a good practice to make use of the existing code and implementations that work,
but it is paramount to understand what you are using and why - make sure to perform
your own research first, and choose the more fitting approach for the task at hand.

There is more than likely a better option for some of the presented cases, for example
the Migrate API and its ecosystem of contributed modules, which provide an extra set
of convenient features, such as the ability to perform a rollback.

Find out more: https://www.drupal.org/docs/drupal-apis/migrate-api

## How To Use

Visit one of the import forms shipped with the module and submit it.

## Structure

Unlike older guides and tutorials on the subject of batch jobs which can be
discovered out there - we are going to use a slightly different approach,
based on the static Batch classes.

A basic example is included with the module: `src/Batch/DefaultBatch.php`
