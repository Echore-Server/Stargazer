<?php

declare(strict_types=1);

namespace Echore\Stargazer;

use pocketmine\utils\ObjectSet;

class ModifierGroup {

	/**
	 * @var array<int, ModifierSet>
	 */
	protected array $modifiers;

	protected bool $dirty;

	protected ObjectSet $dirtyHooks;

	protected array $cacheSortedModifiers;

	public function __construct() {
		$this->modifiers = [];
		$this->dirty = false;
		$this->dirtyHooks = new ObjectSet();
		$this->cacheSortedModifiers = [];
	}

	/**
	 * @return ObjectSet
	 */
	public function getDirtyHooks(): ObjectSet {
		return $this->dirtyHooks;
	}

	/**
	 * @return array<int, ModifierSet>
	 */
	public function getAllRaw(): array {
		return $this->modifiers;
	}

	public function getModifiers(int $priority): ModifierSet {
		if (!isset($this->modifiers[$priority])) {
			// とりあえず加算で
			$this->modifiers[$priority] = $set = new ModifierSet(ModifierSet::MODE_ADDITION);
			$set->getChangeHooks()->add(function(): void {
				if (!$this->dirty) {
					foreach ($this->dirtyHooks as $hook) {
						($hook)();
					}
				}
				$this->dirty = true;
			});
		}

		return $this->modifiers[$priority];
	}

	public function clear(): void {
		$this->modifiers = [];
		$this->dirty = true;
	}

	/**
	 * @return float[]
	 */
	public function getSortedModifiers(): array {
		if ($this->dirty) {
			$arr = $this->modifiers;

			$this->cacheSortedModifiers = [];

			foreach ($arr as $priority => $modifierSet) {
				$this->cacheSortedModifiers[] = $modifierSet->getResult();
			}
			$this->dirty = false;
		}

		return $this->cacheSortedModifiers;
	}

	/**
	 * @return bool
	 */
	public function isDirty(): bool {
		return $this->dirty;
	}
}
