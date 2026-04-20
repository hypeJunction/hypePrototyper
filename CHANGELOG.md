<a name="5.0.0"></a>
# 5.0.0 (2026-04-11)

### Breaking Changes

* **elgg:** raise minimum to Elgg 4.x (PHP 7.4+). Plugins on Elgg 3.x must stay on hypePrototyper 4.x.
* **hypeapps dependency removed:** `UploadField`, `IconField`, and `ImageUploadField` no longer call `hypeApps()->iconFactory` or `hypeApps()->uploader`. These classes now use native Elgg 4 APIs (`elgg()->uploads`, `ElggEntity::saveIconFromUploadedFile()`, `elgg_get_icon_sizes()`). The `hypeapps` plugin is no longer a runtime dependency.

### Migration (3.x → 4.x)

* **bootstrap:** deleted `start.php` and `manifest.xml`. Plugin metadata now lives in `composer.json` + `elgg-plugin.php`.
* **bootstrap class:** introduced `hypeJunction\Prototyper\Bootstrap` extending `Elgg\DefaultPluginBootstrap`; loads `autoloader.php` from `boot()` to preserve the `hypePrototyper()` DI container function.
* **declarative config:** vendor assets (`jquery.cropper.css`, `jquery.cropper.js`) registered via `views` key; CSS extensions registered via `view_extensions` key in `elgg-plugin.php`.
* **removed APIs:** `forward()`/`register_error()`/`system_message()` in `ActionController` replaced with `elgg_ok_response()`/`elgg_error_response()`; `get_installed_translations()` → `elgg()->translator->getInstalledTranslations()`; `IOException` renamed to `Elgg\Exceptions\FileSystem\IOException`; `elgg_get_entities()` `types`/`subtypes` keys → `type`/`subtype`.
* **plugin id:** lowercased from `hypePrototyper` to `hypeprototyper` in `elgg_get_plugin_from_id()` callsite in `Plugin::factory()`.
* **PHP 8:** fixed nested-ternary parse errors in metadata/annotation input views.
* **dead code:** removed `isElggVersionBelow('1.9.0')` branches.

### Dependency Updates

* `elgg/elgg ^4.0`, `composer/installers ^2.0`, PHP `>=7.4`, version bumped to `5.0.0`
* Removed `require-dev` on `hypejunction/hypeapps >5.0`
