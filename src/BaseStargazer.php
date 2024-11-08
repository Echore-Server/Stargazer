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

	/**
	 * @param T&Living $entity
	 */
	public function __construct(mixed $entity) {
		parent::__construct();
		$this->entity = $entity;

		$this->maxHealth = $this->createHook(new ModifiableValue(20, ModifierSet::MODE_ADDITION), function(ModifiableValue $value, mixed $entity): void {
			$final = max(1, $value->getFinalFloored());
			$entity->setMaxHealth($final);

			if ($entity->getHealth() > $entity->getMaxHealth()) {
				$entity->setHealth($entity->getMaxHealth());
			}
		});

		$this->movementSpeed = $this->createHook(new ModifiableValue(0.10, ModifierSet::MODE_ADDITION), function(ModifiableValue $value, mixed $entity): void {
			$final = max(0, $value->getFinal());

			if ($entity instanceof Player) {
				$final *= $entity->isSprinting() ? 1.3 : 1.0;
			}
			$entity->setMovementSpeed($final, true);
		});

		$this->movementSpeed->setValue($entity->getMovementSpeed());


		$this->attackDamage = $this->createHook(new ModifiableValue(1.0, ModifierSet::MODE_ADDITION), function(ModifiableValue $value, mixed $entity): void {
			$final = max(0, $value->getFinal());

			$entity->getAttributeMap()->get(Attribute::ATTACK_DAMAGE)->setValue($final, true);
		});
	}

	/**
	 * @param ModifiableValue $value
	 * @param Closure(ModifiableValue $value, T&Living $entity): void $hook
	 * @return ModifiableValue
	 */
	protected function createHook(ModifiableValue $value, Closure $hook): ModifiableValue {
		$value->getModifiers()->getChangeHooks()->add(function() use ($hook, $value): void {
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
