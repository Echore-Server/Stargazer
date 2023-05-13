<?php

namespace Echore\Stargazer;

abstract class ModifierApplier {

	/**
	 * @param int|float $value
	 * @param Modifier[] $modifiers
	 * @param int|null $totalAbsolute
	 * @param bool $absolute
	 * @return float
	 */
	abstract public function apply(int|float $value, array $modifiers, ?int &$totalAbsolute = null, bool $absolute = true): float;

}
