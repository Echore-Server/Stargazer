<?php

namespace Echore\Stargazer;

class ModifiableValue {

	protected int|float $original;

	protected int|float $value;

	protected ModifierSet $preModifiers;

	protected ModifierSet $modifiers;

	/**
	 * @var ModifierSet[]
	 */
	protected array $attachedModifiers;

	protected int $mode;


	public function __construct(int|float $original, int $mode) {
		$this->value = $original;
		$this->original = $original;
		$this->mode = $mode;
		$this->preModifiers = new ModifierSet($mode);
		$this->modifiers = new ModifierSet($mode);
		$this->attachedModifiers = [];
	}

	/**
	 * @return ModifierSet
	 */
	public function getPreModifiers(): ModifierSet {
		return $this->preModifiers;
	}

	public function attachModifiers(ModifierSet $modifier): void{
		$this->attachedModifiers[spl_object_hash($modifier)] = $modifier;
	}

	public function detachModifiers(ModifierSet $modifier): void{
		unset($this->attachedModifiers[spl_object_hash($modifier)]);
	}

	public function getFinalFloored(): int {
		return (int) floor($this->getFinal());
	}

	public function getFinal(): float {
		if (empty($this->attachedModifiers)) {
			return $this->modifiers->getResult() * $this->preModifiers->getResult() * $this->value;
		}

		$f = new ModifierSet($this->mode);
		foreach($this->attachedModifiers as $modifier){
			$f->add("temp", $modifier->getResult());
		}
		$f->add("temp", $this->modifiers->getResult());
		return $f->getResult() * $this->preModifiers->getResult() * $this->value;
	}
	public function dispose(): void{
		$this->modifiers->getChangeHooks()->clear();
		$this->preModifiers->getChangeHooks()->clear();
		$this->attachedModifiers = [];
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
	 * @param float|int $original
	 */
	public function setOriginal(float|int $original): void {
		$this->value = $original;
		$this->original = $original;
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

		$this->preModifiers->onChanged();
		$this->modifiers->onChanged();
	}

	public function addValue(float|int $value): void{
		$this->setValue($this->value + $value);
	}

	public function __clone(): void {
		$this->modifiers = clone $this->modifiers;
		$this->preModifiers = clone $this->preModifiers;
	}

}
