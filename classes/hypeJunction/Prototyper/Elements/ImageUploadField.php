<?php

namespace hypeJunction\Prototyper\Elements;

class ImageUploadField extends UploadField {

	const CLASSNAME = __CLASS__;

	/**
	 * {@inheritdoc}
	 */
	public function handle(\ElggEntity $entity) {

		$result = parent::handle($entity);

		if (!$result) {
			return $result;
		}

		// make sure we do not duplicate icon creation
		elgg_register_plugin_hook_handler('entity:icon:sizes', 'object', array($this, 'getIconSizes'), 999);

		$shortname = $this->getShortname();

		$icon_sizes = (array) $this->input_vars->{"data-icon-sizes"};
		if (empty($icon_sizes)) {
			return $result;
		}

		$upload = $this->getValues($entity);
		
		$image_upload_crop_coords = (array) get_input('image_upload_crop_coords', array());
		$ratio_coords = (array) elgg_extract($shortname, $image_upload_crop_coords, array());
		foreach ($icon_sizes as $icon_size) {
			$ratio = (int) $icon_size['w'] / (int) $icon_size['h'];
			$coords = $ratio_coords[(string) $ratio];
			
			$x1 = (int) elgg_extract('x1', $coords);
			$x2 = (int) elgg_extract('x2', $coords);
			$y1 = (int) elgg_extract('y1', $coords);
			$y2 = (int) elgg_extract('y2', $coords);

			if ($x2 <= $x1 || $y2 <= $y1) {
				continue;
			}

			list($master_width, $master_height) = getimagesize($_FILES[$shortname]['tmp_name']);

			$options = array(
				'icon_sizes' => array(
					$icon_size['name'] => $icon_size,
				),
				'coords' => array(
					'x1' => $x1,
					'x2' => $x2,
					'y1' => $y1,
					'y2' => $y2,
					'master_width' => $master_width,
					'master_height' => $master_height,
				)
			);

			foreach (array('x1', 'x2', 'y1', 'y2') as $c) {
				$entity->{"_coord_{$ratio}_{$coord}"} = elgg_extract($c, $coords);
			}
			
			hypeApps()->iconFactory->create($upload, $_FILES[$shortname]['tmp_name'], $options);
		}
		
		elgg_unregister_plugin_hook_handler('entity:icon:sizes', 'object', array($this, 'getIconSizes'));

		return $result;
	}

	/**
	 * Callback for icon size hook
	 * We do not want to regenerate default icons
	 *
	 * @param string $hook   "entity:icon:sizes"
	 * @param string $type   "object"
	 * @param array  $return Sizes
	 * @return array
	 */
	public function getIconSizes($hook, $type, $return) {
		foreach ($return as $key => $val) {
			if (in_array($key, array_keys((array) elgg_get_config('icon_sizes')))) {
				unset($return[$key]);
			}
		}
		return $return;
	}

}
