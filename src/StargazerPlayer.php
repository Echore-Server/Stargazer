<?php

namespace Echore\Stargazer;

use pocketmine\player\Player;

/**
 * @extends BaseStargazer<Player>
 */
class StargazerPlayer extends BaseStargazer {


	public function __construct(mixed $entity) {
		parent::__construct($entity);
	}
}
