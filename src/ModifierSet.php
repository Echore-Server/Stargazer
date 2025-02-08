<?php

declare(strict_types=1);

namespace Echore\Stargazer;

use pocketmine\utils\ObjectSet;
use RuntimeException;

class ModifierSet {

	const MODE_ADDITION = 0;
	const MODE_MULTIPLICATION = 1;

	/**
	 * @var array<string, float>
	 */
	protected array $set;

	protected array $namespaceIds;

	protected float $result;

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
		$this->result = 1.0;
	}

	/**
	 * @return int
	 */
	public function getMode(): int {
		return $this->mode;
	}

	/**
	 * @param int $mode
	 */
	public function changeMode(int $mode): void {
		$this->mode = $mode;
		$this->recalculateAll();
	}

	public function recalculateAll(): void {
		$this->appliedMultipliers = [];
		$this->result = 1.0;

		foreach ($this->set as $id => $modifier) {
			$this->appliedMultipliers[$id] = $modifier;
			$this->result = $this->internalProcessAdd($modifier, $this->result);
		}

		$this->onChanged();
	}

	public function internalProcessAdd(float $multiplier, float $origin): float {
		if ($this->mode === self::MODE_ADDITION) {
			$origin += ($multiplier - 1.0);
		} elseif ($this->mode === self::MODE_MULTIPLICATION) {
			$origin *= $multiplier;
		}

		return $origin;
	}

	public function onChanged(): void {
		foreach ($this->changeHooks as $hook) {
			($hook)();
		}
	}

	/**
	 * @return array
	 */
	public function getNamespaceIds(): array {
		return $this->namespaceIds;
	}

	/**
	 * @param array $namespaceIds
	 */
	public function setNamespaceIds(array $namespaceIds): void {
		$this->namespaceIds = $namespaceIds;
	}

	public function getResult(): float {
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

	public function add(string $namespace, float $modifier): string {
		if (!isset($this->namespaceIds[$namespace])) {
			$this->registerNamespace($namespace);
		}

		$this->put($id = $this->getNextIdByNamespace($namespace), $modifier);

		return $id;
	}

	protected function registerNamespace(string $namespace): void {
		$this->namespaceIds[$namespace] = 0;
	}

	public function put(string $id, float $modifier): void {
		if (isset($this->set[$id])) {
			throw new RuntimeException("Modifier id \"$id\" already set");
		}

		$this->internalPut($id, $modifier);
		$this->onChanged();
	}

	/**
	 * @internal
	 */
	public function internalPut(string $id, float $modifier): void {
		$this->set[$id] = $modifier;
		$this->appliedMultipliers[$id] = $modifier;
		$this->result = $this->internalProcessAdd($modifier, $this->result);
	}

	protected function getNextIdByNamespace(string $namespace): string {
		$id = $namespace . ":" . $this->namespaceIds[$namespace] ?? throw new RuntimeException("Namespace \"$namespace\" not registered");
		$this->namespaceIds[$namespace]++;

		return $id;
	}

	public function putAll(ModifierSet $set): void {
		foreach ($set->getAll() as $id => $modifier) {
			$this->put($id, $modifier);
		}
	}

	/**
	 * @return array<string, float>
	 */
	public function getAll(): array {
		return $this->set;
	}

	public function update(string $targetId, float $newModifier): void {
		if (isset($this->set[$targetId]) && isset($this->appliedMultipliers[$targetId])) {
			$appliedMultiplier = $this->appliedMultipliers[$targetId];

			$this->result = $this->internalProcessRemove($appliedMultiplier, $this->result);
			$this->appliedMultipliers[$targetId] = $newModifier;
			$this->set[$targetId] = $newModifier;
			$this->result = $this->internalProcessAdd($newModifier, $this->result);

			$this->onChanged();
		}
	}

	public function internalProcessRemove(float $multiplier, float $origin): float {
		if ($this->mode === self::MODE_ADDITION) {
			$origin -= ($multiplier - 1.0);
		} elseif ($this->mode === self::MODE_MULTIPLICATION) {
			if (abs($multiplier) <= 0.0000001) {
				$this->recalculateAll();
			} else {
				$origin /= $multiplier;
			}
		}

		return $origin;
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
		if ($this->internalRemove($id)) {
			$this->onChanged();
		}
	}

	public function internalRemove(string $id): bool {
		if (isset($this->set[$id])) {
			$modifier = $this->set[$id];
			unset($this->set[$id]);

			unset($this->appliedMultipliers[$id]);
			$this->result = $this->internalProcessRemove($modifier, $this->result);

			return true;
		}

		return false;
	}

	public function clear(): void {
		$changed = !empty($this->set);
		$this->internalClear();
		if ($changed)
			$this->onChanged();
	}

	public function internalClear(): void {
		$this->set = [];
		$this->appliedMultipliers = [];
		$this->result = 1.0;
	}

	public function get(string $id): ?float {
		return $this->set[$id] ?? null;
	}

	public function has(string $id): bool {
		return isset($this->set[$id]);
	}

	/**
	 * @return array<string, float>
	 */
	public function toArray(): array {
		return $this->getAll();
	}

}
