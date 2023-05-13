<?php

declare(strict_types=1);

namespace Echore\Stargazer;

use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

	protected function onEnable(): void {
		$eventListener = new EventListener();

		$this->getServer()->getPluginManager()->registerEvents($eventListener, $this);
	}

}
