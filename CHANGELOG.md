# Changelog

All notable changes to `laravel-models-generator` will be documented in this file.

## v1.1.1 - 2025-07-25

### Features

- Fix: add $connection property to model, if config param is set to true

## v1.1.0 - 2025-07-09

### Features

- Added `query_builders` config param to use new Laravel 12.19 attribute `UseEloquentBuilder`

## v1.0.0 - 2025-06-04

### Features

- First production-ready release.

## v0.4.5 - 2025-05-24

### Features

- Added `observer` config param, to add observers to models.

## v0.4.4 - 2025-05-16

### Features

- Added `table_traits` config param to use traits in specific models

## v0.4.3 - 2025-05-15

### Features

- Added generate_children_classes config param, to avoid the generation of children classes when you generate base classes

## v0.4.2 - 2025-04-29

### Features

- Added `ulids` config value to handle ulids fields

## v0.4.1 - 2025-04-27

### Features

- Added the `uuids` config value to manage uuids columns

## v0.4.0 - 2025-04-25

### Features

- Complete refactor
- Fixed a bug that copied base class properties to child classes as well

## v0.3.6 - 2025-04-20

### Features

- Added SQLServer compatibility

## v0.3.5 - 2025-04-06

### Features

- Add Laravel 9 and 12 compatibility
- Manage correctly null primary keys

## v0.3.4 - 2025-04-06

### Features

- Fix `morph` relationship
- Add `exclude_relationships` config value to avoid the creation of relationship

## v0.3.3 - 2025-03-29

### Features

- Add `exclude_columns` config value to exclude columns from fillable array

## v0.3.2 - 2025-03-29

### Features

- Add config value to customize timestamps fields

## v0.3.1 - 2025-03-27

### Features

- Add comments to PHPDocs column property (Ex. @property int $id (comment))

## v0.3.0 - 2025-03-27

### Features

- Add PostgreSQL compatibility

## v0.2.4 - 2025-03-16

### Features

- added compatibility with Laravel 10

## v0.2.3 - 2025-03-13

### Features

- add `prefix_table` config param to remove table prefix value from generated models

## v0.2.2 - 2025-01-24

### Features

- add `relationships_name_case_type` config param to define the way relationships name are created

## v0.2.1 - 2025-01-21

### Features

- fix PHPStan errors

## v0.2.0 - 2025-01-21

### Features

- add `generate_views` config param to create views
- fix #4

## v0.1.9 - 2025-01-08

### Features

- Use `integer` instead of `int` for column casting

## v0.1.8 - 2025-01-01

### Features

- Fixes belongsTo relationship bug
- Add `base_files` config parameter to generate base model classes

## v0.1.7 - 2024-12-16

### Features

- Fixed PHPDocs for datetime fields
- Added `enums_casting` parameter to config file

## v0.1.6 - 2024-12-16

### Features

- Fix relationships names
- Add PHPDocs for relationships

## v0.1.5 - 2024-12-15

### Features

- Fix belongsTo relationships name
- add `clean_models_directory_before_generation` parameter to config file

## v0.1.4 - 2024-12-15

### Features

- Fix belongsTo relationships name
- add `strict_types` parameter to the config file
- add PHPDocs to be PHPStan level 9 compliant

## v0.1.2 - 2024-10-27

### Features

- Add SQLite support

## v0.1.1 - 2024-10-20

### Features

- Add model properties

## v0.1.0 - 2024-10-20

### Features

- Mysql support
- Polymorphic relationships support
- Add interfaces to all models
- Add tratis to all models
