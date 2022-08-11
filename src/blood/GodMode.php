<?php

declare(strict_types=1);

namespace blood;

use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class GodMode extends PluginBase implements Listener {
	
	private Config $config;
	private array $immortals = [];
	
	public function onEnable() : void {
        	@mkdir($this->getDataFolder());
       		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->config = $this->getConfig();
		$this->getLogger()->info("§aGodMode has been enabled!");
	}
	
	public function onDisable() : void {
		$this->getLogger()->info("§cGodMode has been disabled!");
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch($command->getName()){
			case "god":
				if(!isset($args[0])){
					if(!$sender instanceof Player){
						$sender->sendMessage($this->config->get("invalid-permission"));
						return false;
					}
					if(in_array($sender->getName(), $this->immortals)){
						unset($this->immortals[array_search($sender->getName(), $this->immortals)]);
						$sender->sendMessage($this->config->get("disabled-message"));
					}else{
						$this->immortals[] = $sender->getName();
						$sender->sendMessage($this->config->get("enabled-message"));
					}
				}else{
					$player = $this->getServer()->getPlayerByPrefix($args[0]);
					if($player === null){
						$sender->sendMessage($this->config->get("invalid-player"));
						return false;
					}
					if(in_array($player->getName(), $this->immortals)){
						unset($this->immortals[array_search($player->getName(), $this->immortals)]);
						$sender->sendMessage($this->config->get("disabled-message"));
						if($this->config->get("alert-player")){
							$player->sendMessage(str_replace("{player}", $sender->getName(), $this->config->get("alert-disabled-message")));
						}
					}else{
						$this->immortals[] = $player->getName();
						$sender->sendMessage($this->config->get("enabled-message"));
						if($this->config->get("alert-player")){
							$player->sendMessage(str_replace("{player}", $sender->getName(), $this->config->get("alert-enabled-message")));
						}
					}
				}
			default:
				return false;
		}
	}
	
	public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        if($entity instanceof Player){
            if(in_array($entity->getName(), $this->immortals)){
                $event->cancel();
            }
        }
    }
}
