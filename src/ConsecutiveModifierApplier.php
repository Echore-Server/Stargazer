<?php

namespace Echore\Stargazer;

class ConsecutiveModifierApplier extends ModifierApplier {

	public function apply(float|int $value, array $modifiers, array $applyModifiers = [], int|float|null &$totalAbsolute = null, bool $absolute = true): float {
		$totalAbsolute ??= 0;
		foreach ($modifiers as $modifier) {
			if (!$modifier->testFilter($value)) {
				continue;
			}

			$before = $value;

			$diff = $value * ($modifier->multiplier - 1.0);

			foreach ($applyModifiers as $applyModifier) {
				$diff = $this->apply($diff, [$applyModifier], absolute: $absolute);
			}

			$value += $diff;
			$totalAbsolute += $modifier->absolute;

			$modifier->onApplied($before, $value);
		}

		if ($absolute) {
			$value += $totalAbsolute;
		}

		return $value;
	}
}
