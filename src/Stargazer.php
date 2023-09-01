<?php

namespace Echore\Stargazer;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use pocketmine\utils\ObjectSet;
use WeakMap;

abstract class Stargazer {

	const SOURCE_MODIFIER_ABSOLUTE = 259;
	const SOURCE_MODIFIER_MULTIPLIER = 260;

	/**
	 * @var WeakMap<Living, Stargazer>
	 */
	private static WeakMap $map;

	protected ModifierApplier $modifierApplier;

	/**
	 * @var ObjectSet<Modifier>
	 */
	protected ObjectSet $takeDamageModifiers;

	/**
	 * @var ObjectSet<Modifier>
	 */
	protected ObjectSet $inflictDamageModifiers;

	public function __construct() {
		$this->modifierApplier = ModifierApplierTypes::default();

		$this->takeDamageModifiers = new ObjectSet();
		$this->inflictDamageModifiers = new ObjectSet();
	}

	public static function initFor(Living $entity): void {
		self::get($entity);
	}

	public static function get(Living $entity): Stargazer {
		self::$map ??= new WeakMap();

		return self::$map[$entity] ??= self::load($entity);
	}

	private static function load(Living $entity): Stargazer {
		if ($entity instanceof Player) {
			return new StargazerPlayer($entity);
		}

		return new StargazerLiving($entity);


	}

	/**
	 * @return ModifiableValue
	 */
	abstract public function getMaxHealth(): ModifiableValue;

	/**
	 * @return ModifiableValue
	 */
	abstract public function getMovementSpeed(): ModifiableValue;

	/**
	 * @return ModifiableValue
	 */
	abstract public function getAttackDamage(): ModifiableValue;

	/**
	 * @return ModifierApplier
	 */
	public function getModifierApplier(): ModifierApplier {
		return $this->modifierApplier;
	}


	/**
	 * @return ObjectSet<Modifier>
	 */
	public function getInflictDamageModifiers(): ObjectSet {
		return $this->inflictDamageModifiers;
	}

	/**
	 * @return ObjectSet<Modifier>
	 */
	public function getTakeDamageModifiers(): ObjectSet {
		return $this->takeDamageModifiers;
	}

}
