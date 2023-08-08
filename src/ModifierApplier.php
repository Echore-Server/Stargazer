<?php

namespace Echore\Stargazer;

abstract class ModifierApplier {

	/**
	 * @param int|float $value
	 * @param Modifier[] $modifiers
	 * @param Modifier[] $applyModifiers
	 * @param int|null $totalAbsolute
	 * @param bool $absolute
	 * @return float
	 */
	abstract public function apply(int|float $value, array $modifiers, array $applyModifiers = [], ?int &$totalAbsolute = null, bool $absolute = true): float;

}
