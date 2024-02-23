<?php

declare(strict_types=1);

namespace Echore\Stargazer;

use pocketmine\utils\ObjectSet;
use RuntimeException;

class ModifierSet {

	/**
	 * @var array<string, Modifier>
	 */
	private array $set;

	private array $namespaceIds;

	private ObjectSet $changeHooks;

	public function __construct() {
		$this->set = [];
		$this->namespaceIds = [];
		$this->changeHooks = new ObjectSet();
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

		$this->set[$id] = $modifier;
		$this->onChanged();
	}

	protected function onChanged(): void {
		foreach ($this->changeHooks as $hook) {
			($hook)();
		}
	}

	protected function getNextIdByNamespace(string $namespace): string {
		$id = $namespace . ":" . $this->namespaceIds[$namespace] ?? throw new RuntimeException("Namespace \"$namespace\" not registered");
		$this->namespaceIds[$namespace]++;

		return $id;
	}

	public function __clone(): void {
		$clonedSet = [];

		foreach ($this->set as $id => $modifier) {
			$clonedSet[$id] = clone $modifier;
		}

		$this->set = $clonedSet;
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
		unset($this->set[$id]);
		$this->onChanged();
	}

	public function clear(): void {
		$this->set = [];
		$this->onChanged();
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
