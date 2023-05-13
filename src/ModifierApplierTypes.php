<?php

namespace Echore\Stargazer;

class ModifierApplierTypes {

	private static ConsecutiveModifierApplier $consecutive;

	private static ModifierApplier $default;

	private static bool $initialized = false;

	public static function default(): ModifierApplier {
		if (!self::$initialized) {
			self::init();
		}

		return self::$default;
	}

	public static function init(): void {
		if (!self::$initialized) {
			self::$consecutive = new ConsecutiveModifierApplier();

			self::$default = self::$consecutive; // todo:
		}
	}

	public static function consecutive(): ConsecutiveModifierApplier {
		if (!self::$initialized) {
			self::init();
		}

		return self::$consecutive;
	}

}
