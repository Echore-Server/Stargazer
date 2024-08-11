<?php

declare(strict_types=1);

namespace Echore\Stargazer;

use pocketmine\utils\ObjectSet;
use RuntimeException;

class ModifierSet {

	const MODE_ADDITION = 0;
	const MODE_MULTIPLICATION = 1;

	/**
	 * @var array<string, Modifier>
	 */
	protected array $set;

	protected array $namespaceIds;

	protected Modifier $result;

	private ObjectSet $changeHooks;

	/**
	 * @var array<string, float>
	 */
	private array $appliedMultipliers = [];

	/**
	 * @var self::MODE_*
	 */
	private int $mode;


	public function __construct(int $mode) {
		$this->mode = $mode;
		$this->set = [];
		$this->namespaceIds = [];
		$this->changeHooks = new ObjectSet();
		$this->result = Modifier::multiplier(1.0);
	}

	/**
	 * @return Modifier
	 */
	public function getResult(): Modifier {
		return $this->result;
	}

	public function isEmpty(): bool {
		return count($this->set) === 0;
	}

	/**
	 * @return ObjectSet
	 */
	public function getChangeHooks(): ObjectSet {
		return $this->changeHooks;
	}

	public function add(string $namespace, Modifier $modifier): string {
		if (!isset($this->namespaceIds[$namespace])) {
			$this->registerNamespace($namespace);
		}

		$this->put($id = $this->getNextIdByNamespace($namespace), $modifier);

		return $id;
	}

	protected function registerNamespace(string $namespace): void {
		$this->namespaceIds[$namespace] = 0;
	}

	public function put(string $id, Modifier $modifier): void {
		if (isset($this->set[$id])) {
			throw new RuntimeException("Modifier id \"$id\" already set");
		}

		$this->internalPut($id, $modifier);
		$this->onChanged();
	}

	/**
	 * @internal
	 */
	public function internalPut(string $id, Modifier $modifier): void {
		$this->set[$id] = $modifier;
		$this->appliedMultipliers[$id] = $modifier->multiplier;
		$this->internalProcessAdd($modifier->multiplier, $this->result);
	}

	public function internalProcessAdd(float $multiplier, Modifier $origin): void {
		if ($this->mode === self::MODE_ADDITION) {
			$origin->multiplier += ($multiplier - 1.0);
		} elseif ($this->mode === self::MODE_MULTIPLICATION) {
			$origin->multiplier *= $multiplier;
		}
	}

	public function onChanged(): void {
		foreach ($this->changeHooks as $hook) {
			($hook)();
		}
	}

	protected function getNextIdByNamespace(string $namespace): string {
		$id = $namespace . ":" . $this->namespaceIds[$namespace] ?? throw new RuntimeException("Namespace \"$namespace\" not registered");
		$this->namespaceIds[$namespace]++;

		return $id;
	}

	public function recalculate(string $targetId): void {
		if (isset($this->set[$targetId]) && isset($this->appliedMultipliers[$targetId])) {
			$modifier = $this->set[$targetId];
			$appliedMultiplier = $this->appliedMultipliers[$targetId];

			$this->internalProcessRemove($appliedMultiplier, $this->result);
			$this->appliedMultipliers[$targetId] = $modifier->multiplier;
			$this->internalProcessAdd($modifier->multiplier, $this->result);

			$this->onChanged();
		}
	}

	public function internalProcessRemove(float $multiplier, Modifier $origin): void {
		if ($this->mode === self::MODE_ADDITION) {
			$origin->multiplier -= ($multiplier - 1.0);
		} elseif ($this->mode === self::MODE_MULTIPLICATION) {
			if (abs($multiplier) <= 0.0000001) {
				$this->recalculateAll();
			} else {
				$origin->multiplier /= $multiplier;
			}
		}
	}

	public function recalculateAll(): void {
		$this->appliedMultipliers = [];
		$this->result->multiplier = 1.0;

		foreach ($this->set as $id => $modifier) {
			$this->appliedMultipliers[$id] = $modifier->multiplier;
			$this->internalProcessAdd($modifier->multiplier, $this->result);
		}

		$this->onChanged();
	}

	public function __clone(): void {
		$clonedSet = [];

		foreach ($this->set as $id => $modifier) {
			$clonedSet[$id] = clone $modifier;
		}

		$this->set = $clonedSet;
		$this->result = Modifier::multiplier($this->result->multiplier);
		$this->changeHooks = clone $this->changeHooks;
	}

	public function putAll(ModifierSet $set): void {
		foreach ($set->getAll() as $id => $modifier) {
			$this->put($id, $modifier);
		}
	}

	/**
	 * @return array<string, Modifier>
	 */
	public function getAll(): array {
		return $this->set;
	}

	public function addAll(string $namespace, ModifierSet $set): array {
		if (!isset($this->namespaceIds[$namespace])) {
			$this->registerNamespace($namespace);
		}

		$resultIds = [];
		foreach ($set->getAll() as $id => $modifier) {
			$this->put($resultIds[] = $namespace . ":" . $id, $modifier);
		}

		return $resultIds;
	}

	public function remove(string $id): void {
		if ($this->internalRemove($id)){
			$this->onChanged();
		}
	}

	public function internalRemove(string $id): bool {
		if (isset($this->set[$id])) {
			$modifier = $this->set[$id];
			unset($this->set[$id]);

			unset($this->appliedMultipliers[$id]);
			$this->internalProcessRemove($modifier->multiplier, $this->result);
			return true;
		}

		return false;
	}

	public function clear(): void {
		$changed = !empty($this->set);
		$this->internalClear();
		if ($changed) $this->onChanged();
	}

	public function internalClear(): void {
		$this->set = [];
		$this->appliedMultipliers = [];
		$this->result->multiplier = 1.0;
	}

	public function get(string $id): ?Modifier {
		return $this->set[$id] ?? null;
	}

	public function has(string $id): bool {
		return isset($this->set[$id]);
	}

	/**
	 * @return array<string, Modifier>
	 */
	public function toArray(): array {
		return $this->getAll();
	}

}
