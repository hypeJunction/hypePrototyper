<?php

namespace hypeJunction\Prototyper;

/**
 * Plugin DI wrapper
 *
 * @property-read \ElggPlugin                               $plugin
 * @property-read \hypeJunction\Prototyper\Config           $config
 * @property-read \hypeJunction\Prototyper\HookHandlers     $hooks
 * @property-read \hypeJunction\Prototyper\UI               $ui
 * @property-read \hypeJunction\Prototyper\EntityFactory    $entityFactory
 * @property-read \hypeJunction\Prototyper\FieldFactory     $fieldFactory
 * @property-read \hypeJunction\Prototyper\Prototype        $prototype
 * @property-read \hypeJunction\Prototyper\Form             $form
 * @property-read \hypeJunction\Prototyper\ActionController $action
 * @property-read \hypeJunction\Prototyper\Profile          $profile
 */
final class Plugin extends \hypeJunction\Plugin {

	/**
	 * Instance
	 * @var self
	 */
	public static $instance;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(\ElggPlugin $plugin) {

		$this->setValue('plugin', $plugin);

		$this->setFactory('config', function (Plugin $p) {
			return new \hypeJunction\Prototyper\Config($p->plugin);
		});
		$this->setFactory('hooks', function (Plugin $p) {
			return new \hypeJunction\Prototyper\HookHandlers($p->config);
		});
		$this->setFactory('ui', function(Plugin $p) {
			return new \hypeJunction\Prototyper\UI($p->config);
		});

		$this->setClassName('entityFactory', '\hypeJunction\Prototyper\EntityFactory');

		$this->setFactory('fieldFactory', function(Plugin $p) {
			return new \hypeJunction\Prototyper\FieldFactory($p->config, $p->entityFactory);
		});

		$this->setFactory('prototype', function(Plugin $p) {
			return new \hypeJunction\Prototyper\Prototype($p->config, $p->entityFactory, $p->fieldFactory);
		});

		$this->setFactory('form', function(Plugin $p) {
			return new \hypeJunction\Prototyper\Form($p->config, $p->prototype, $p->entityFactory);
		});

		$this->setFactory('action', function(Plugin $p) {
			return new \hypeJunction\Prototyper\ActionController($p->config, $p->prototype, $p->entityFactory);
		});

		$this->setFactory('profile', function(Plugin $p) {
			return new \hypeJunction\Prototyper\Profile($p->config, $p->prototype, $p->entityFactory);
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public static function factory() {
		if (self::$instance === null) {
			$plugin = elgg_get_plugin_from_id('hypeprototyper');
			self::$instance = new self($plugin);
		}

		return self::$instance;
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {
		\elgg_register_event_handler('init', 'system', [$this, 'init']);
	}

	/**
	 * Init callback
	 *
	 * @return void
	 */
	public function init() {

		// View extensions and asset definitions are declared in elgg-plugin.php for 4.x.
		// Field type registrations remain here because they populate the plugin's
		// internal Config DI state rather than Elgg core registries.

		hypePrototyper()->config->registerType('title', Elements\AttributeField::CLASSNAME, [
			'shortname' => 'title',
			'input_view' => 'input/text',
			'output_view' => 'output/text',
			'value_type' => 'text',
			'show_access' => false,
			'multiple' => false,
			'required' => true,
			'ui_sections' => [
				'multiple' => false,
				'access' => false,
			]
		]);
		hypePrototyper()->config->registerType('name', Elements\AttributeField::CLASSNAME, [
			'shortname' => 'name',
			'input_view' => 'input/text',
			'output_view' => 'output/text',
			'value_type' => 'text',
			'show_access' => false,
			'multiple' => false,
			'required' => true,
			'ui_sections' => [
				'multiple' => false,
				'access' => false,
			]
		]);

		hypePrototyper()->config->registerType('description', Elements\AttributeField::CLASSNAME, [
			'shortname' => 'description',
			'input_view' => 'input/longtext',
			'output_view' => 'output/longtext',
			'value_type' => 'text',
			'show_access' => false,
			'multiple' => false,
			'ui_sections' => [
				'multiple' => false,
				'access' => false,
			]
		]);

		hypePrototyper()->config->registerType('access', Elements\AttributeField::CLASSNAME, [
			'shortname' => 'access_id',
			'value' => elgg_get_config('default_access') ?? ACCESS_PUBLIC,
			'input_view' => 'input/access',
			'output_view' => 'output/access',
			'value_type' => 'int',
			'show_access' => false,
			'multiple' => false,
			'required' => true,
			'ui_sections' => [
				'multiple' => false,
				'access' => false,
			]
		]);

		hypePrototyper()->config->registerType('text', Elements\MetadataField::CLASSNAME);
		hypePrototyper()->config->registerType('text', Elements\AnnotationField::CLASSNAME);

		hypePrototyper()->config->registerType('plaintext', Elements\MetadataField::CLASSNAME, [
			'value_type' => 'text',
		]);
		hypePrototyper()->config->registerType('longtext', Elements\MetadataField::CLASSNAME, [
			'value_type' => 'text',
		]);
		hypePrototyper()->config->registerType('hidden', Elements\MetadataField::CLASSNAME, [
			'multiple' => false,
			'required' => false,
			'show_access' => false,
			'label' => false,
			'help' => false,
			'ui_sections' => [
				'required' => false,
				'access' => false,
				'multiple' => false,
				'label' => false,
				'help' => false,
			]]);

		hypePrototyper()->config->registerType('select', Elements\MetadataField::CLASSNAME, [
			'ui_sections' => [
				'optionsvalues' => true,
			]
		]);

		hypePrototyper()->config->registerType('access', Elements\MetadataField::CLASSNAME, [
			'ui_sections' => [
				'optionsvalues' => false,
			]
		]);

		hypePrototyper()->config->registerType('checkboxes', Elements\MetadataField::CLASSNAME, [
			'ui_sections' => [
				'multiple' => false,
				'optionsvalues' => true,
			]
		]);

		hypePrototyper()->config->registerType('radio', Elements\MetadataField::CLASSNAME, [
			'ui_sections' => [
				'multiple' => false,
				'optionsvalues' => true,
			]]);

		hypePrototyper()->config->registerType('tags', Elements\MetadataField::CLASSNAME, [
			'ui_sections' => [
				'multiple' => false,
			]]);

		hypePrototyper()->config->registerType('date', Elements\MetadataField::CLASSNAME, [
			'timestamp' => false,
		]);

		hypePrototyper()->config->registerType('time', Elements\MetadataField::CLASSNAME, [
			'input_view' => 'input/prototyper/time',
			'format' => 'g:ia',
			'interval' => 900, // 15min
		]);

		hypePrototyper()->config->registerType('email', Elements\MetadataField::CLASSNAME);

		hypePrototyper()->config->registerType('url', Elements\MetadataField::CLASSNAME);

		hypePrototyper()->config->registerType('stars', Elements\MetadataField::CLASSNAME, [
			'value_type' => 'number',
			'ui_sections' => [
				'validation' => false,
			]
		]);
		hypePrototyper()->config->registerType('stars', Elements\AnnotationField::CLASSNAME, [
			'value_type' => 'number',
			'ui_sections' => [
				'validation' => false,
			]
		]);

		hypePrototyper()->config->registerType('userpicker', Elements\RelationshipField::CLASSNAME, [
			'value_type' => 'guid',
			'inverse_relationship' => false,
			'bilateral' => false,
			'multiple' => false,
			'show_access' => false,
			'ui_sections' => [
				'access' => false,
				'multiple' => false,
				'relationship' => true,
			]
		]);

		hypePrototyper()->config->registerType('friendspicker', Elements\RelationshipField::CLASSNAME, [
			'value_type' => 'guid',
			'inverse_relationship' => false,
			'bilateral' => false,
			'multiple' => false,
			'show_access' => false,
			'ui_sections' => [
				'access' => false,
				'multiple' => false,
				'relationship' => true,
			]
		]);

		if (elgg_is_active_plugin('hypeCategories')) {
			hypePrototyper()->config->registerType('category', Elements\CategoryField::CLASSNAME, [
				'value_type' => 'guid',
				'inverse_relationship' => false,
				'bilateral' => false,
				'multiple' => true,
				'show_access' => false,
				'ui_sections' => [
					'access' => false,
					'multiple' => true,
					'relationship' => false,
				]
			]);
		}

		hypePrototyper()->config->registerType('icon', Elements\IconField::CLASSNAME, [
			'accept' => 'image/*',
			'value_type' => 'image',
			'multiple' => false,
			'show_access' => false,
			'input_view' => 'input/file',
			'output_view' => 'icon/default',
			'ui_sections' => [
				'value' => false,
				'access' => false,
				'multiple' => false,
			]
		]);

		hypePrototyper()->config->registerType('upload', Elements\UploadField::CLASSNAME, [
			'multiple' => false,
			'show_access' => false,
			'input_view' => 'input/file',
			'ui_sections' => [
				'value' => true,
				'access' => false,
				'multiple' => false,
				'validation' => true
			]
		]);

		hypePrototyper()->config->registerType('image_upload', Elements\ImageUploadField::CLASSNAME, [
			'multiple' => false,
			'accept' => 'image/*',
			'value_type' => 'image',
			'show_access' => false,
			'input_view' => 'input/file',
			'validation_rules' => [
				'type' => 'image',
			],
			'ui_sections' => [
				'value' => true,
				'access' => false,
				'multiple' => false,
				'validation' => true
			]
		]);
	}
}
