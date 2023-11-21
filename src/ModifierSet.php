<?php

declare(strict_types=1);

namespace Echore\Stargazer;

use RuntimeException;

class ModifierSet {

	/**
	 * @var array<string, Modifier>
	 */
	private array $set;

	private array $namespaceIds;

	public function __construct() {
		$this->set = [];
		$this->namespaceIds = [];
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
	 * @return array<string, Modifier>
	 */
	public function getAll(): array {
		return $this->set;
	}

	public function remove(string $id): void {
		unset($this->set[$id]);
	}

	public function clear(): void {
		$this->set = [];
	}

	public function get(string $id): ?Modifier {
		return $this->set[$id] ?? null;
	}

	/**
	 * @return array<string, Modifier>
	 */
	public function toArray(): array {
		return $this->getAll();
	}

}
