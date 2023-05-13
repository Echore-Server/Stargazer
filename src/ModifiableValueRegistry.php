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

	public function register(string $name, ModifiableValue $value): void {
		if (isset($this->values[$name])) {
			return;
		}

		$this->set($name, $value);
	}

	protected function set(string $name, ModifiableValue $value): void {
		$this->values[$name] = $value;
	}

	public function get(string $name): ?ModifiableValue {
		return $this->values[$name] ?? null;
	}
}
