hypePrototyper
==============
![Elgg 1.8](https://img.shields.io/badge/Elgg-1.8.x-orange.svg?style=flat-square)
![Elgg 1.9](https://img.shields.io/badge/Elgg-1.9.x-orange.svg?style=flat-square)
![Elgg 1.10](https://img.shields.io/badge/Elgg-1.10.x-orange.svg?style=flat-square)
![Elgg 1.11](https://img.shields.io/badge/Elgg-1.11.x-orange.svg?style=flat-square)

A set of developer and administrator tools for prototyping and handling
entity forms.
This plugin attempts to create an easy way to build forms, validate user input,
and display entity profiles.


## Developer Notes

### Prototypes

Prototypes have 3 different facades:
* *Form* displays a set of inputs that are used to modify entity information
* *Action* validates and handles user input
* *Profile* outputs entity information

Prototypes consist of fields and are meant to be tied to registered Elgg actions.
Prototype fields can be populated using ```"prototype",$action``` plugin hook.

The idea here is that entities of the same type and subtype can be modified by multiple actions.
For example, a user entity can be created and modified by ```register``` and ```profile/edit```
actions.

Each prototype is tied to an instance of ```ElggObject```, ```ElggUser``` or ```ElggGroup```.
If the entity does not exist yet, the prototype creates a new instance (but only saves
it when the form has been submitted and validated).

### Form

```php

$user = elgg_get_logged_in_user_entity();
echo hypePrototyper()->form->with($user, 'profile/edit')->view();

```

```php

$form = hypePrototyper()->form->with(array('type' => 'user'), 'register')->viewBody();

echo elgg_view('input/form', array(
	'action' => 'register',
	'body' => $body,
	'enctype' => 'multipart/form-data', // if we have file inputs
));
```


### Action

```php

$guid = get_input('guid');
$container_guid = get_input('container_guid');

if (!$guid) {
	$entity = array(
		'type' => 'object',
		'subtype' => 'blog',
		'container_guid' => $container_guid,
	);
} else {
	$entity = get_entity($guid);
}

if (!$entity) {
	// something is wrong
    forward();
}

hypePrototyper()->action->with($entity, 'blog/edit')->handle();
```

```php

$entity = array(
	'type' => 'object',
	'subtype' => 'file',
	'access_id' => ACCESS_LOGGED_IN,
);

// In case we want to do more stuff with the new entity
try {
	$controller = hypePrototyper()->action->with($entity, 'file/upload');
	if ($controller->validate()) {
		$entity = $controller->update();
	}
	if ($entity) {
		// do more stuff
	}
} catch (Exception $ex) {
	// do something with the error
}
```

The above will validate the form and add all values to the entity based. If the form validation fails, the user
will be forwarded back to the form (forms are made sticky) and failing validation
rules will be explained.

### Profile

```php

echo hypePrototyper()->profile->with($group, 'groups/edit')
	->filter(function($field) {
		return in_array($field->getShortname(), array('title', 'description', 'tags'));
	})
	->view($vars);

```


### Fields

```php

elgg_register_plugin_hook_handler('prototype', 'profile/edit', 'prepare_profile_edit_form');

function prepare_profile_edit_form($hook, $type, $return, $params) {

	if (!is_array($return)) {
		$return = array();
	}

	$entity = elgg_extract('entity', $params);

	$fields = array(
		'name' => array(
			'type' => 'text',
			'validation_rules' => array(
				'max_length' => 50
			)
		),
		'briefdescription' => 'longtext',
		'interests' => array(
			'type' => 'tags',
			'required' => true,
			'show_access' => ACCESS_PRIVATE,
		),
		'favorite_foods' => array(
			'type' => 'text',
			'multiple' => true,
			'show_access' => true,
		),
		'eye_color' => array(
			'type' => 'dropdown',
			'label' => elgg_echo('eye_color'),
			'options_values' => array(
				'blue' => elgg_echo('blue'),
				'brown' => elgg_echo('brown'),
			),
		),
		'looking_for' => array(
			'type' => 'checkboxes',
			'label' => false,
			'help' => false,
			'options' => profile_get_looking_for_options(),
		),
		'height' => array(
			'type' => 'text',
			'value_type' => 'number',
			'multiple' => false,
			'show_access' => false,
			'required' => true,
			'validation_rules' => array(
				'min' => 25,
				'max' => 50,
				'minlength' => 2,
				'maxlength' => 4,
			),
		),
		'empathy' => array(
			'type' => 'stars',
			'data_type' => 'annotation',
			'min' => 0,
			'max' => 10,
		),
		'spouse' => array(
			'type' => 'autocomplete',
			'data_type' => 'relationship',
			'value_type' => 'entity',
			'inverse_relationship' => false,
			'bilateral' => true,
			'match_on' => 'friends',
		),
		'icon' => array(
			'data_type' => 'icon',
		),
	);

	return array_merge($return, $fields);
}

```

Fields are defined as ```$shortname => $value``` pairs, where the ```$shortname``` is a
name of an attribute, metadata, annotation etc. and ```$value``` is
a string that describes the input type (e.g. text, dropdown etc) or an array
with the following properties:

* ```type``` - type of an input, used as elgg_view("input/$type") (default ```text```)
* ```data_type``` - a model used to store and retrieve values (default ```metadata```)
	> ```attribute``` - an entity attribute, e.g. guid
	> ```metadata``` - an entity metadata
	> ```annotation``` - an entity annotation
	> ```relationship``` - an entity relationship
	> ```icon``` - an entity icon
	> ```category``` - entity categories (hypeCategories)
* ```class_name``` - PHP class used to instantiate a Field with a custom data type
* ```value_type``` - type of value if different from ```type```, e.g when a text input expects an integer
						The value type is automatically added to 'type' validation rules,
						thus setting 'value_type' => 'integer' is also equivalent to 'validation_rules' => ['type' => 'integer']
* ```input_view``` - view used to dipslay an input, if different from "input/$type"
* ```output_view``` - view used to dipslay an output, if different from "output/$type"
* ```required``` - whether or not a user input is requried (default ```false```)
* ```admin_only``` - whether or not the field is only visible to admins (default ```false```)
* ```hide_on_profile``` - whether or not the field should be hidden on automatically generated profile (default ```false```)
* ```priority``` - order of the field (default ```500```)
* ```show_access``` - whether or not to display an access input (default ```false```)
	This allows users to specify an access level for the metadata, annotation or attachment created
* ```label``` - what label to display with the input field (default ```true```)
	> ```true``` - set to ```elgg_echo("label:$type:$subtype:$shortname")```;
	> ```false``` - do not display a label
	> any other custom string
* ```help``` - what help text to display with the input field (default ```true```)
	> ```true``` - set to ```elgg_echo("help:$type:$subtype:$shortname")```;
	> ```false``` - do not display help text
	> any other custom string
* ```multiple``` - whether or not a user can clone the field and add multiple values (default ```false```)
* ```validation_rules``` - an array of rule => expecation pairs
	You can define custom validation rules and use ```'validate:$rule','prototyper'``` to validate the values
	See hypePrototyperValidators for a full list of available validators
* ```options``` and ```options_values``` - array of options to pass to the input
* ```flags``` - comma-separated list of flags that can be used for input/output filtering
* all other options will be passed to the input view, so you can add ```class``` for example

The following options are available for ```relationship``` data type:
* ```inverse_relationship``` - store as inverse relationship
* ```bilateral``` - make it a bilateral relationship (two relationships will be added)


### Custom Fields

To define a new field type, register it as so:

```php

hypePrototyper()->config->registerType('icon', \hypeJunction\Prototyper\Elements\IconField::CLASSNAME, array(
			'accept' => 'image/*',
			'value_type' => 'image',
			'multiple' => false,
			'show_access' => false,
			'input_view' => 'input/file',
			'output_view' => false,
			'ui_sections' => array(
				'access' => false,
				'multiple' => false,
			)
		));

```

The above registers a new input type 'icon' with a handler class IconField that extends abstract Field.
The third parameter contains default key - value pairs (which can later be overridden in a hook).
'ui_sections' parameter specifies which sections should be disabled in the admin interface provided by
hypePrototyperUI.


