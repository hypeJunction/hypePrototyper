# hypePrototyper — Architecture (Elgg 4.x)

## Summary

hypePrototyper is a form-prototyping framework for Elgg. It lets developers
declaratively register entity field schemas (text, file, icon, image upload,
metadata, attribute fields) and renders Elgg forms from those schemas, while
also handling validation and persistence on submit.

## Plugin metadata

- Composer name: `hypejunction/hypeprototyper`
- Plugin id (lowercase dir): `hypeprototyper`
- Target: Elgg 4.x, PHP >= 7.4
- Bootstrap: `\hypeJunction\Prototyper\Bootstrap`
- No runtime dependency on hypeApps (removed in this migration step)

## Directory layout

```
classes/hypeJunction/Prototyper/
  Bootstrap.php          Plugin bootstrap (init, ready hooks)
  Plugin.php             Service container / facade
  Config.php             Plugin config
  Prototype.php          High-level field orchestration
  EntityFactory.php      Hydrates ElggEntity from input
  FieldFactory.php       Builds Field objects from config
  Form.php               Form rendering
  UI.php                 View helpers
  ActionController.php   Shared action controller
  Profile.php            Profile field shim

  Elements/              Field type implementations
    Field.php            Base field
    UploadField.php      File upload (uses elgg()->uploads + ElggFile)
    IconField.php        Entity icon (uses saveIconFromUploadedFile)
    ImageUploadField.php File upload + entity icon on uploaded ElggFile
    MetadataField.php    Metadata-backed field
    AttributeField.php   Entity-attribute-backed field
    ...

  Structs/               Value objects (InputVars, ValidationStatus, ...)
  UI/                    UI widgets / templates
```

## Registered hooks/events

Defined in `Bootstrap::init()` and `Bootstrap::ready()` (see source for the
authoritative list). Key registrations:

- `forms/prototyper/*` view registrations
- `prototyper/*` form action handlers
- `view_vars`/`view` filter chains for cropper UI

## Views and view extensions

- Provides cropper CSS/JS via `vendors/jquery.cropper`
- Extends `elgg.css`, `admin.css` with `css/framework/prototyper/stylesheet`
- Extends `prototyper/input/before` with `prototyper/elements/js`
- Extends `input/file` with `prototyper/ui/cropper`

## Field upload pipeline (post-hypeApps refactor)

`UploadField::handle()` now uses native Elgg 4 APIs:

1. `elgg()->uploads->getFiles($shortname)` enumerates Symfony UploadedFile
   instances from the request.
2. For each valid uploaded file, a new `ElggFile` is created with
   `container_guid`, `access_id`, `origin = 'prototyper'`, and
   `prototyper_field = $shortname`. Subtype defaults to `file`.
3. `acceptUploadedFile($uploadedFile)` writes bytes; mime/simpletype are
   derived via `Elgg\Filesystem\MimeTypeDetector`.
4. The previous file (if any) is deleted before save.

`IconField::handle()` and `ImageUploadField::handle()` now call
`$entity->saveIconFromUploadedFile($shortname, 'icon', $crop_coords)`
directly. Custom icon size lookups use `elgg_get_icon_sizes($type, $subtype)`.
The legacy `entity:icon:sizes` register/unregister dance is gone — Elgg 4
generates only the master icon and lazily renders other sizes on request.

Friendly upload error messages come from `elgg_get_friendly_upload_error()`
(replaces `hypeApps()->uploader->getFriendlyUploadError()`).

## Dependencies (composer)

- `php >= 7.4`
- `composer/installers ~1.0`
- No runtime hypeApps dependency

## Migration notes (3.x → 4.x)

- Plugin directory renamed `hypePrototyper` → `hypeprototyper` (lowercase) to
  match `composer.json` `name`.
- `start.php` and `manifest.xml` removed; metadata sourced from
  `composer.json` and `elgg-plugin.php`.
- All `hypeApps()->iconFactory` (6 calls in 2 files) and
  `hypeApps()->uploader` (2 calls) replaced with native Elgg 4 APIs.
- `getIconSizes()` callback hooks removed from IconField/ImageUploadField —
  Elgg 4's icon service is single-master + lazy.
- Pre-existing PHPUnit baseline: 85 tests, 554 assertions, 15 errors (see
  beads `elgg-migrate-lpbf`). All 15 errors are unrelated to this refactor —
  they stem from MetadataField/Field hook handler signatures still using the
  3.x callable shape and from a removed `create_metadata()` helper.
