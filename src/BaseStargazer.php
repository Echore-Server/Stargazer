<?php

namespace Echore\Stargazer;

use Closure;
use pocketmine\entity\Attribute;
use pocketmine\entity\Living;
use pocketmine\player\Player;

/**
 * @template T of Living
 */
abstract class BaseStargazer extends Stargazer {

	/**
	 * @var T&Living
	 */
	protected mixed $entity;

	protected ModifiableValue $maxHealth;

	protected ModifiableValue $movementSpeed;

	protected ModifiableValue $attackDamage;

	protected ModifierApplier $modifierApplier;

	/**
	 * @param T&Living $entity
	 */
	public function __construct(mixed $entity) {
		parent::__construct();
		$this->entity = $entity;

		$this->maxHealth = $this->createHook(new ModifiableValue(20), function(ModifiableValue $value, mixed $entity): void {
			$entity->setMaxHealth($value->getFinalFloored($this->modifierApplier));

			if ($entity->getHealth() > $entity->getMaxHealth()) {
				$entity->setHealth($entity->getMaxHealth());
			}
		});

		$this->movementSpeed = $this->createHook(new ModifiableValue(0.10), function(ModifiableValue $value, mixed $entity): void {
			$final = $value->getFinal($this->modifierApplier);

			if ($entity instanceof Player) {
				$final *= $entity->isSprinting() ? 1.3 : 1.0;
			}
			$entity->setMovementSpeed($final, true);
		});

		$this->movementSpeed->setValue($entity->getMovementSpeed());


		$this->attackDamage = $this->createHook(new ModifiableValue(1.0), function(ModifiableValue $value, mixed $entity): void {
			$final = $value->getFinal($this->modifierApplier);

			$entity->getAttributeMap()->get(Attribute::ATTACK_DAMAGE)->setValue($final, true);
		});
	}

	/**
	 * @param ModifiableValue $value
	 * @param Closure(ModifiableValue $value, T&Living $entity): void $hook
	 * @return ModifiableValue
	 */
	protected function createHook(ModifiableValue $value, Closure $hook): ModifiableValue {
		$value->getDirtyHooks()->add(function() use ($hook, $value): void {
			($hook)($value, $this->entity);
		});

		return $value;
	}

	/**
	 * @return ModifiableValue
	 */
	public function getMaxHealth(): ModifiableValue {
		return $this->maxHealth;
	}

	/**
	 * @return ModifiableValue
	 */
	public function getMovementSpeed(): ModifiableValue {
		return $this->movementSpeed;
	}

	public function getAttackDamage(): ModifiableValue {
		return $this->attackDamage;
	}
}
