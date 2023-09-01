<?php

namespace Echore\Stargazer;

use pocketmine\utils\ObjectSet;

class Modifier {

	const FILTER_NONE = 0;
	const FILTER_NEGATIVE = 1;
	const FILTER_POSITIVE = 2;
	const FILTER_ZERO = 3;

	public int|float $absolute;

	public int|float $multiplier;

	public readonly int $filterType;

	protected ObjectSet $applyListeners;

	/**
	 * @param float|int $absolute
	 * @param float|int $multiplier
	 * @param self::FILTER_* $filterType
	 */
	public function __construct(float|int $absolute, float|int $multiplier, int $filterType = self::FILTER_NONE) {
		$this->absolute = $absolute;
		$this->multiplier = $multiplier;
		$this->filterType = $filterType;
		$this->applyListeners = new ObjectSet();
	}

	public static function default(): self {
		return new self(0.0, 1.0);
	}

	public static function absolute(float|int $v, int $filterType = self::FILTER_NONE): self {
		return new self($v, 1.0, $filterType);
	}

	public static function multiplier(float|int $v, int $filterType = self::FILTER_NONE): self {
		return new self(0.0, $v, $filterType);
	}

	public function onApplied(float $before, float $after): void {
		foreach ($this->applyListeners as $listener) {
			($listener)();
		}
	}

	public function testFilter(float|int $value): bool {
		if ($this->filterType === Modifier::FILTER_NEGATIVE && $value > 0) {
			return false;
		} elseif ($this->filterType === Modifier::FILTER_POSITIVE && $value < 0) {
			return false;
		} elseif ($this->filterType === Modifier::FILTER_ZERO && ($value != 0)) {
			return false;
		}

		return true;
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

	/**
	 * @return self::FILTER_*
	 */
	public function getFilterType(): int {
		return $this->filterType;
	}

	public function merge(Modifier $modifier): self {
		return new self($this->absolute + $modifier->absolute, $this->multiplier * $modifier->multiplier);
	}
}
