<?php

namespace Echore\Stargazer;

use Closure;
use pocketmine\utils\ObjectSet;

class ModifiableValue {

	protected int|float $original;

	protected int|float $value;


	/**
	 * @var ObjectSet<Modifier>
	 */
	protected ObjectSet $modifiers;

	protected ObjectSet $applicable;

	protected int|float $finalValue;

	protected int $totalAbsolute;

	protected bool $dirty;

	/**
	 * @var ObjectSet<Closure>
	 */
	protected ObjectSet $dirtyHooks;

	/**
	 * @var ObjectSet<Modifier>
	 */
	protected ObjectSet $modifiedValueModifiers;

	public function __construct(int|float $original) {
		$this->value = $original;
		$this->original = $original;
		$this->modifiers = new ObjectSet();
		$this->dirty = false;
		$this->totalAbsolute = 0;
		$this->applicable = new ObjectSet();
		$this->dirtyHooks = new ObjectSet();
		$this->modifiedValueModifiers = new ObjectSet();
		$this->finalValue = $original;
	}


	public function __clone(): void {
		$this->dirtyHooks = clone $this->dirtyHooks;
	}

	/**
	 * @return ObjectSet<Closure>
	 */
	public function getDirtyHooks(): ObjectSet {
		return $this->dirtyHooks;
	}

	/**
	 * @return bool
	 */
	public function isDirty(): bool {
		return $this->dirty;
	}

	public function removeAll(): void {
		$this->modifiers->clear();
		$this->dirty();
	}

	protected function dirty(): void {
		$this->dirty = true;

		foreach ($this->dirtyHooks as $hook) {
			($hook)();
		}
	}

	public function getFinalFloored(?ModifierApplier $applier = null): int {
		return (int) floor($this->getFinal($applier));
	}

	public function getFinal(?ModifierApplier $applier = null): float {
		$applier ??= ModifierApplierTypes::default();
		if ($this->dirty) {
			$this->finalValue = $applier->apply($this->value, $this->applicable->toArray(), $this->modifiedValueModifiers->toArray(), absolute: false) + $this->totalAbsolute;
			$this->dirty = false;
		}

		return $this->finalValue;
	}

	public function apply(Modifier $modifier): void {

		$this->totalAbsolute += $modifier->absolute;

		if ($modifier->multiplier != 1.0) {
			$this->applicable->add($modifier);
		}

		$this->modifiers->add($modifier);
		$this->dirty();
	}

	/**
	 * @return ObjectSet<Modifier>
	 */
	public function getApplicable(): ObjectSet {
		return $this->applicable;
	}

	public function applyModifiedValue(Modifier $modifier): void {
		$this->modifiedValueModifiers->add($modifier);
		$this->dirty();
	}

	public function removeModifiedValue(Modifier $modifier): void {
		$this->modifiedValueModifiers->remove($modifier);
		$this->dirty();
	}

	public function remove(Modifier $modifier): void {
		$this->modifiers->remove($modifier);
		$this->applicable->remove($modifier);

		$this->totalAbsolute -= $modifier->absolute;
		$this->dirty();
	}

	public function removeAllOfModifierValue(): void {
		$this->modifiedValueModifiers->clear();
		$this->dirty();
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
		$this->dirty();
	}

}
