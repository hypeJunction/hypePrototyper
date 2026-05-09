# hypePrototyper — Architecture (Elgg 5.x)

## Summary

hypePrototyper is a form-prototyping framework for Elgg. It lets developers
declaratively register entity field schemas and renders forms from those schemas,
handling validation and persistence on submit.

## Plugin metadata

- Composer name: `hypejunction/hypeprototyper`
- Plugin id: `hypeprototyper`
- Target: Elgg 5.x, PHP >= 8.2
- Bootstrap: `\hypeJunction\Prototyper\Bootstrap`
- Dependencies: `hypeapps`, `hypelists`

## Directory layout

```
classes/hypeJunction/Prototyper/
  Bootstrap.php          Loads autoloader, calls Plugin::boot()
  Plugin.php             Service container (config, prototype, form, action, profile)
  Config.php             Field type registry
  Prototype.php          Field orchestration (dispatches prototype events)
  EntityFactory.php      Hydrates ElggEntity from input
  FieldFactory.php       Builds Field objects from config
  Form.php               Form rendering
  ActionController.php   Shared action controller

  Elements/              Field type implementations
    Field.php            Base field (input_vars / validate:<rule> events)
    AnnotationField.php  Annotation-backed (handle:annotation:before/after events)
    MetadataField.php    Metadata-backed (handle:metadata:before/after events)
    RelationshipField.php Relationship-backed
    UploadField.php      File upload (handle:upload:before/after events)
    AttributeField.php   Entity-attribute field
    CategoryField.php    Category field (requires hypeCategories)
    IconField.php        Entity icon
    ImageUploadField.php File upload + entity icon
    ValidationStatus.php Validation result value object
    FieldCollection.php  Collection of fields
```

## Registered Events (Elgg 5.x)

| Event | Type | Handler |
|-------|------|---------|
| `init` | `system` | `\hypeJunction\Prototyper\Plugin->init` |

Events dispatched for extension by other plugins:

| Event | Type | Purpose |
|-------|------|---------|
| `prototype` | `<action>` | Gather field definitions for an entity type/action |
| `input_vars` | `prototyper` | Filter input variables for a field |
| `validate:<rule>` | `prototyper` | Validate a field value by rule |
| `handle:annotation:before/after` | `prototyper` | Pre/post annotation save |
| `handle:metadata:before/after` | `prototyper` | Pre/post metadata save |
| `handle:relationship:before/after` | `prototyper` | Pre/post relationship save |
| `handle:upload:before/after` | `prototyper` | Pre/post file upload |

## Dependencies (composer)

- `php >= 8.2`, `elgg/elgg ^5.0`, `composer/installers ^2.0`

## Migration notes (4.x → 5.x)

- `elgg_trigger_plugin_hook()` → `elgg_trigger_event_results()` (12 call sites in 5 files)
- `elgg_register_plugin_hook_handler()` → `elgg_register_event_handler()` (tests)
- `\Elgg\Hook` → `\Elgg\Event` in test closures and mock builders
- `add_translation('en', $array)` → `return $array;` (Elgg 5.x drops procedural add_translation)
- `get_default_access()` → `elgg_get_config('default_access') ?? ACCESS_PUBLIC` (3 call sites)
- Docker: PHP 7.4 → PHP 8.2, MySQL 5.7 → MySQL 8.0, project `*-elgg5`
- PHPUnit: 85 tests, 589 assertions

## Seeding

No seeder required. This plugin owns no entity types, subtypes, or persistent relationship schemas — it is a pure UI/utility/admin plugin with no persisted entity surface of its own.
