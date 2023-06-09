<?php

namespace Echore\Stargazer;

use Closure;
use pocketmine\utils\ObjectSet;

class ModifiableValue {

	protected int|float $original;

	protected int|float $value;


	/**
	 * @var Modifier[]
	 */
	protected array $modifiers;

	protected int|float $finalValue;

	protected bool $dirty;

	/**
	 * @var ObjectSet<Closure>
	 */
	protected ObjectSet $dirtyHooks;

	public function __construct(int|float $original) {
		$this->value = $original;
		$this->original = $original;
		$this->dirtyHooks = new ObjectSet();
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

	public function remove(Modifier $modifier): void {
		unset($this->modifiers[spl_object_hash($modifier)]);
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
			$this->finalValue = $applier->apply($this->value, $this->modifiers);
			$this->dirty = false;
		}

		return $this->finalValue;
	}

	public function apply(Modifier $modifier): void {
		$this->modifiers[spl_object_hash($modifier)] = $modifier;
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
