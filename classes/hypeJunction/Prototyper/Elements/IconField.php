<?php

namespace hypeJunction\Prototyper\Elements;

class IconField extends UploadField {

	const CLASSNAME = __CLASS__;

	/**
	 * {@inheritdoc}
	 */
	public function getValues(\ElggEntity $entity) {
		return ($entity->icontime);
	}

	/**
	 * {@inheritdoc}
	 */

	/**
	 * {@inheritdoc}
	 */
	public function handle(\ElggEntity $entity) {

		$shortname = $this->getShortname();

		$icon_sizes = elgg_get_icon_sizes($entity->getType(), $entity->getSubtype());
		$custom_icon_sizes = (array) $this->input_vars->{"icon_sizes"};
		$icon_sizes = array_merge($icon_sizes, $custom_icon_sizes);

		if (empty($icon_sizes)) {
			return $entity;
		}

		if (empty($_FILES[$shortname]['tmp_name']) || !is_uploaded_file($_FILES[$shortname]['tmp_name'])) {
			return $entity;
		}

		$image_upload_crop_coords = (array) get_input('image_upload_crop_coords', array());
		$ratio_coords = (array) elgg_extract($shortname, $image_upload_crop_coords, array());

		list($master_width, $master_height) = getimagesize($_FILES[$shortname]['tmp_name']);

		foreach ($icon_sizes as $icon_name => $icon_size) {
			$ratio = (int) $icon_size['w'] / (int) $icon_size['h'];
			$coords = (array) elgg_extract("$ratio", $ratio_coords, array());

			$x1 = (int) elgg_extract('x1', $coords);
			$x2 = (int) elgg_extract('x2', $coords);
			$y1 = (int) elgg_extract('y1', $coords);
			$y2 = (int) elgg_extract('y2', $coords);

			$crop_coords = [];
			if ($x2 > $x1 && $y2 > $y1) {
				$crop_coords = [
					'x1' => $x1,
					'y1' => $y1,
					'x2' => $x2,
					'y2' => $y2,
					'master_width' => $master_width,
					'master_height' => $master_height,
				];
			}

			if ($entity->saveIconFromUploadedFile($shortname, 'icon', $crop_coords)) {
				foreach (array('x1', 'x2', 'y1', 'y2') as $c) {
					$entity->{"_coord_{$ratio}_{$c}"} = elgg_extract($c, $coords, 0);
					if ($ratio === 1) {
						$entity->$c = elgg_extract($c, $coords, 0);
					}
				}
			}
		}

		return $entity;
	}

}
