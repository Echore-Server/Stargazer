<?php

namespace Echore\Stargazer;

class Modifier {

	public int|float $absolute;

	public int|float $multiplier;

	/**
	 * @param float|int $absolute
	 * @param float|int $multiplier
	 */
	public function __construct(float|int $absolute, float|int $multiplier) {
		$this->absolute = $absolute;
		$this->multiplier = $multiplier;
	}

	public static function absolute(float|int $v): self {
		return new self($v, 1.0);
	}

	public static function multiplier(float|int $v): self {
		return new self(0.0, $v);
	}
}
