<?php

namespace Echore\Stargazer;

class ModifiableValue {

	protected int|float $original;

	protected int|float $value;

	protected ModifierSet $modifiers;


	public function __construct(int|float $original, int $mode) {
		$this->value = $original;
		$this->original = $original;
		$this->modifiers = new ModifierSet($mode);
	}

	public function getFinalFloored(): int {
		return (int) floor($this->getFinal());
	}

	public function getFinal(): float {
		return $this->modifiers->getResult()->multiplier * $this->value;
	}

	/**
	 * @param float|int $original
	 */
	public function setOriginal(float|int $original): void {
		$this->value = $original;
		$this->original = $original;
	}

	/**
	 * @return ModifierSet
	 */
	public function getModifiers(): ModifierSet {
		return $this->modifiers;
	}

	/**
	 * @return float|int
	 */
	public function getOriginal(): float|int {
		return $this->original;
	}

	/**
	 * @return float|int
	 */
	public function getValue(): float|int {
		return $this->value;
	}

	/**
	 * @param float|int $value
	 */
	public function setValue(float|int $value): void {
		$this->value = $value;

		$this->modifiers->onChanged();
	}

	public function __clone(): void {
		$this->modifiers = clone $this->modifiers;
	}

}
