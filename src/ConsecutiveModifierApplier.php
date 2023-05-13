<?php

namespace Echore\Stargazer;

class ConsecutiveModifierApplier extends ModifierApplier {

	public function apply(float|int $value, array $modifiers, ?int &$totalAbsolute = null, bool $absolute = true): float {
		$finalAbsolute = 0;
		foreach ($modifiers as $modifier) {
			$value *= $modifier->multiplier;
			$finalAbsolute += $modifier->absolute;
		}

		if ($absolute) {
			$value += $finalAbsolute;
		}

		$totalAbsolute = $finalAbsolute;

		return $value;
	}
}
