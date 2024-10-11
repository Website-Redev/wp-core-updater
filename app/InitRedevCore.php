<?php
namespace App;

final class InitRedevCore
{

	public static function getRedevAllServices()
	{
		// register all class
		return [
			Controllers\CoreUpdateController::class,
		];
	}

	public static function registerRedevServices()
	{
		foreach (self::getRedevAllServices() as $class) {
			$service = self::instantiate($class);
			if (method_exists($service, 'register')) {
				$service->register();
			}
		}
	}

	private static function instantiate($class)
	{
		$service = new $class();

		return $service;
	}

	public static function activate()
	{
		flush_rewrite_rules();
	}

	public static function deactivate()
	{
		flush_rewrite_rules();
	}
}
