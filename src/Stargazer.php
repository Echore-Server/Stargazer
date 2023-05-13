<?php

namespace Echore\Stargazer;

use Closure;
use pocketmine\entity\Living;
use pocketmine\utils\ObjectSet;
use WeakMap;

class Stargazer {

	const SOURCE_MODIFIER_ABSOLUTE = 259;
	const SOURCE_MODIFIER_MULTIPLIER = 260;

	/**
	 * @var WeakMap<Living, Stargazer>
	 */
	private static WeakMap $map;

	protected Living $entity;

	protected ModifiableValue $maxHealth;

	protected ModifiableValue $health;

	protected ModifiableValue $movementSpeed;

	protected ModifierApplier $modifierApplier;

	/**
	 * @var ObjectSet<Modifier>
	 */
	protected ObjectSet $takeDamageModifiers;

	/**
	 * @var ObjectSet<Modifier>
	 */
	protected ObjectSet $inflictDamageModifiers;

	public function __construct(Living $entity) {
		$this->entity = $entity;
		$this->modifierApplier = ModifierApplierTypes::default();

		/**
		 * @param ModifiableValue $value
		 * @param Closure(ModifiableValue $value, Living $entity): void $hook
		 * @return ModifiableValue
		 */
		$hook = function(ModifiableValue $value, Closure $hook) use ($entity): ModifiableValue {
			$value->getDirtyHooks()->add(function() use ($entity, $hook, $value): void {
				($hook)($value, $entity);
			});

			return $value;
		};

		$this->maxHealth = $hook(new ModifiableValue(20), function(ModifiableValue $value, Living $entity): void {
			$entity->setMaxHealth($value->getFinal($this->modifierApplier));

			if ($entity->getHealth() > $entity->getMaxHealth()) {
				$entity->setHealth($entity->getMaxHealth());
			}
		});

		$this->health = $hook(new ModifiableValue(20), function(ModifiableValue $value, Living $entity): void {
			$final = $value->getFinal($this->modifierApplier);
			$entity->setHealth($final);

			if ($entity->getHealth() > $entity->getMaxHealth()) {
				$entity->setHealth($entity->getMaxHealth());
			}
		});

		$this->movementSpeed = $hook(new ModifiableValue(0.10), function(ModifiableValue $value, Living $entity): void {
			$final = $value->getFinal($this->modifierApplier);

			$entity->setMovementSpeed($final, true);
		});

		$this->movementSpeed->setValue($entity->getMovementSpeed());

		$this->takeDamageModifiers = new ObjectSet();
		$this->inflictDamageModifiers = new ObjectSet();
	}

	/**
	 * @return ModifiableValue
	 */
	public function getHealth(): ModifiableValue {
		return $this->health;
	}

	/**
	 * @return ModifiableValue
	 */
	public function getMaxHealth(): ModifiableValue {
		return $this->maxHealth;
	}

	public static function get(Living $entity): Stargazer {
		self::$map ??= new WeakMap();

		return self::$map[$entity] ??= self::load($entity);
	}

	private static function load(Living $entity): Stargazer {
		return new Stargazer($entity);
	}

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
