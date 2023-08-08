<?php

namespace Echore\Stargazer;

class ConsecutiveModifierApplier extends ModifierApplier {

	public function apply(float|int $value, array $modifiers, array $applyModifiers = [], ?int &$totalAbsolute = null, bool $absolute = true): float {
		$finalAbsolute = 0;
		foreach ($modifiers as $modifier) {
			if (!$modifier->testFilter($value)) {
				continue;
			}

			$diff = $value * ($modifier->multiplier - 1.0);

			foreach ($applyModifiers as $applyModifier) {
				$diff = $this->apply($diff, [$applyModifier], absolute: $absolute);
			}

			$value += $diff;
			$finalAbsolute += $modifier->absolute;
		}

		if ($absolute) {
			$value += $finalAbsolute;
		}

		$totalAbsolute = $finalAbsolute;

		return $value;
	}
}
