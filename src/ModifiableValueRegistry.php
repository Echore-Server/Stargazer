<?php

namespace Echore\Stargazer;

class ModifiableValueRegistry {

	/**
	 * @var array<string, ModifiableValue>
	 */
	protected array $values;

	public function __construct() {
		$this->values = [];
	}

	public function dispose(): void{
		foreach($this->values as $value){
			$value->dispose();
		}
	}

	public function exists(string $name): bool{
		return isset($this->values[$name]);
	}

	public function register(string $name, ModifiableValue $value): void {
		if (isset($this->values[$name])) {
			return;
		}

		$this->set($name, $value);
	}

	public function __clone(): void {
		$values = [];
		foreach($this->values as $name => $v){
			$values[$name] = clone $v;
		}

		$this->values = $values;
	}

	/**
	 * @return array<string, ModifiableValue>
	 */
	public function getAll(): array{
		return $this->values;
	}

	/**
	 * @param string[] $names
	 * @param ModifiableValue $value
	 * @return void
	 */
	public function registerAll(array $names, ModifiableValue $value): void{
		foreach($names as $name){
			$this->register($name, clone $value);
		}
	}

	protected function set(string $name, ModifiableValue $value): void {
		$this->values[$name] = $value;
	}

	public function get(string $name): ?ModifiableValue {
		return $this->values[$name] ?? null;
	}
}
