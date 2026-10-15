<?php

namespace App\Controllers;

use GingerTek\Routy;
use App\Data\Locations;
use App\Data\Categories;

class SettingsController
{
  public static function viewSettings(Routy $app)
	{
		$app->render('settings', [
			'locations' => Locations::list(),
			'categories' => Categories::list()
		]);
	}

	public static function postSaveSettings(Routy $app)
	{
		$user_id = $app->getCtx('current_user')->id;
		$body = $app->getBody();
		$locations = Locations::list();
		foreach ($locations as $location) {
			if (isset($body->locations[$location->id]))
				Locations::update(
					$location->id,
					[
						'name' => $body->locations[$location->id]['name'],
						'updated_by' => $user_id,
					]
				);
			else
				Locations::delete($location->id);
		}
		if (!empty($body->location)) {
			Locations::create([
				'name' => $body->location,
				'created_by' => $user_id,
			]);
		}
		$categories = Categories::list();
		foreach ($categories as $category) {
			if (isset($body->categories[$category->id]))
				Categories::update(
					$category->id,
					[
						'name' => $body->categories[$category->id]['name'],
						'updated_by' => $user_id,
					]
				);
			else
				Categories::delete($category->id);
		}
		if (!empty($body->category)) {
			Categories::create([
				'name' => $body->category,
				'created_by' => $user_id,
			]);
		}
		$app->redirect('/settings');
	}
}