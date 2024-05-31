<?php

namespace Echore\Stargazer;

class Modifier {

	public int|float $absolute;

	public int|float $multiplier;

	/**
	 * @param float|int $absolute
	 * @param float|int $multiplier
	 * @param self::FILTER_* $filterType
	 */
	public function __construct(float|int $absolute, float|int $multiplier) {
		$this->absolute = $absolute;
		$this->multiplier = $multiplier;
	}

	public static function default(): self {
		return new self(0.0, 1.0);
	}

	public static function absolute(float|int $v): self {
		return new self($v, 1.0);
	}

	public static function multiplier(float|int $v): self {
		return new self(0.0, $v);
	}

	/**
	 * @return Modifier
	 *
	 * @see Modifier::multiplied
	 */
	public function divided(): Modifier {
		return new self($this->absolute, 1.0 - $this->multiplier);
	}

	/**
	 * fixme: もうちょっといいメソッド名ないかな？ (dividedも)
	 * @return Modifier
	 *
	 * @see Modifier::divided
	 */
	public function multiplied(): Modifier {
		return new self($this->absolute, 1.0 + $this->multiplier);
	}
}
