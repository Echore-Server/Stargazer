<?php

namespace Echore\Stargazer;

use pocketmine\entity\Living;
use pocketmine\player\Player;
use WeakMap;

abstract class Stargazer {

	const SOURCE_MODIFIER_ABSOLUTE = 259;
	const SOURCE_MODIFIER_MULTIPLIER = 260;

	/**
	 * @var WeakMap<Living, Stargazer>
	 */
	private static WeakMap $map;

	protected ModifierGroup $takeDamageModifiers;

	protected ModifierGroup $inflictDamageModifiers;

	protected ModifierGroup $takeHealModifiers;

	protected ModifierGroup $giveHealModifiers;

	public function __construct() {
		$this->takeDamageModifiers = new ModifierGroup();
		$this->inflictDamageModifiers = new ModifierGroup();
		$this->takeHealModifiers = new ModifierGroup();
		$this->giveHealModifiers = new ModifierGroup();
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
	 * @return ModifierGroup
	 */
	public function getGiveHealModifiers(): ModifierGroup {
		return $this->giveHealModifiers;
	}

	/**
	 * @return ModifierGroup
	 */
	public function getTakeHealModifiers(): ModifierGroup {
		return $this->takeHealModifiers;
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
	 * @return ModifierGroup
	 */
	public function getInflictDamageModifiers(): ModifierGroup {
		return $this->inflictDamageModifiers;
	}

	/**
	 * @return ModifierGroup
	 */
	public function getTakeDamageModifiers(): ModifierGroup {
		return $this->takeDamageModifiers;
	}
}
