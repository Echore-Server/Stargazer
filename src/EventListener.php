<?php

namespace Echore\Stargazer;

use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;

class EventListener implements Listener {


	/**
	 * @param EntityDamageEvent $event
	 * @return void
	 * @priority LOW
	 */
	public function onDamage(EntityDamageEvent $event): void {
		$entity = $event->getEntity();

		$final = $event->getFinalDamage() - $event->getModifier(Stargazer::SOURCE_MODIFIER_ABSOLUTE);


		if ($event instanceof EntityDamageByEntityEvent) {
			$damager = $event->getDamager();

			if ($damager instanceof Living) {
				$damagerStargazer = Stargazer::get($damager);

				$applied = $damagerStargazer->getModifierApplier()->apply($final, $damagerStargazer->getInflictDamageModifiers()->toArray(), [], $totalAbsolute, false);
				$sourceModifier = $applied - $final;

				$event->setModifier(
					$event->getModifier(Stargazer::SOURCE_MODIFIER_MULTIPLIER) + $sourceModifier,
					Stargazer::SOURCE_MODIFIER_MULTIPLIER
				);

				$event->setModifier(
					$event->getModifier(Stargazer::SOURCE_MODIFIER_ABSOLUTE) + $totalAbsolute,
					Stargazer::SOURCE_MODIFIER_ABSOLUTE
				);
			}
		}

		if ($entity instanceof Living) {
			$entityStargazer = Stargazer::get($entity);

			$applied = $entityStargazer->getModifierApplier()->apply(
				$final, $entityStargazer->getTakeDamageModifiers()->toArray(),
				[], $totalAbsolute, false); // TODO: stargazer modifiedValueModifier support
			$sourceModifier = $applied - $final;

			$event->setModifier(
				$event->getModifier(Stargazer::SOURCE_MODIFIER_MULTIPLIER) + $sourceModifier,
				Stargazer::SOURCE_MODIFIER_MULTIPLIER
			);

			$event->setModifier(
				$event->getModifier(Stargazer::SOURCE_MODIFIER_ABSOLUTE) + $totalAbsolute,
				Stargazer::SOURCE_MODIFIER_ABSOLUTE
			);
		}
	}
}
