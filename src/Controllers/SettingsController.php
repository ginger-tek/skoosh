<?php

namespace App\Controllers;

use GingerTek\Routy;
use App\Data\Locations;
use App\Data\Categories;

class SettingsController
{
	public static function getSettings(Routy $app)
	{
		$app->sendJson([
			'locations' => Locations::list(),
			'categories' => Categories::list()
		]);
	}

	public static function putSaveSettings(Routy $app)
	{
		$user_id = $app->getCtx('current_user')->id;
		$body = $app->getBody();
		$locations = Locations::list();
		$body_locations_ids = array_column($body->locations, 'id');
		foreach ($locations as $location) {
			if (($idx = array_search($location->id, $body_locations_ids)) !== false)
				Locations::update(
					$location->id,
					[
						'name' => $body->locations[$idx]->name,
						'updated_by' => $user_id,
					]
				);
			else
				Locations::delete($location->id);
		}
		foreach ($body->locations as $id => $location) {
			if (!isset($locations[$id])) {
				Locations::create([
					'name' => $location->name,
					'created_by' => $user_id,
				]);
			}
		}
		$categories = Categories::list();
		$body_categories_ids = array_column($body->categories, 'id');
		foreach ($categories as $category) {
			if (($idx = array_search($category->id, $body_categories_ids)) !== false)
				Categories::update(
					$category->id,
					[
						'name' => $body->categories[$idx]->name,
						'updated_by' => $user_id,
					]
				);
			else
				Categories::delete($category->id);
		}
		foreach ($body->categories as $id => $category) {
			if (!isset($categories[$id])) {
				Categories::create([
					'name' => $category->name,
					'created_by' => $user_id,
				]);
			}
		}
		self::getSettings($app);
	}
}