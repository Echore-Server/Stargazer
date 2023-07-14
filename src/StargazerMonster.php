<?php

namespace Echore\Stargazer;

use Lyrica0954\SmartEntity\entity\LivingBase;

/**
 * @extends BaseStargazer<LivingBase>
 */
class StargazerMonster extends BaseStargazer {
	public function __construct(mixed $entity) {
		parent::__construct($entity);
	}

}
