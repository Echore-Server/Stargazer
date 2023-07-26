<?php

namespace Echore\Stargazer;

use Echore\NaturalEntity\INaturalEntity;

/**
 * @extends BaseStargazer<INaturalEntity>
 */
class StargazerMonster extends BaseStargazer {
	public function __construct(mixed $entity) {
		parent::__construct($entity);
	}

}
