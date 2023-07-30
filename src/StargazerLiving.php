<?php

namespace Echore\Stargazer;

use pocketmine\entity\Living;

/**
 * @extends BaseStargazer<Living>
 */
class StargazerLiving extends BaseStargazer {
	public function __construct(mixed $entity) {
		parent::__construct($entity);
	}

}
