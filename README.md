# Codestarter

**ALPHA STAGE**

A framework-agnostic configurable and extendable code file bootstrapper.

## Introduction

The framework make commands are tied to a default file structure. 
And current development practices like a modular monolith and domain driven design dictate another file structure.

That is why this tool is created.

## Quick guide

```
composer require xwero/codestarter
```
Add Xwero\Codestarter\Dev namespace to the psr-4 section of composer.json. Add the directory where the Codestarter files should be stored.

Copy the Flows directory in vendor/xwero/codestarter/assets to that same directory.

Open de config.php in Flows/Php. Add the root_namespace value and for the directories key a nested array of the directories the project works with.<br>
The nested array has one root key, other keys will be ignored.<br>
The name of the directory may have a {NAME} pattern, during directory creation question will be asked to define the pattern.

Copy vendor/xwero/codestarter/bin to the root of the project.

Run `bin/codestarter setup --create-directories` to create the wanted file structure.

Run `bin/codestarter setup --create-cache` to generate autocomplete suggestions for the class questions.

Run `bin/codestarter` To start generating a file.

## Documentation

[CLI](docs/cli.md)<br>
[Configuration](docs/confuration.md)<br>
[Extendability](docs/extendability.md)

## TODO

[] More tests<br>
[] Code cleanup<br>
[] Easier installation<br>
[] More elaborate template generation
[] More documentation

