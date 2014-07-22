<?php

namespace BanHammer;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\Player;

class Main extends PluginBase implements Listener, CommandExecutor{
    public function onEnable(){
    	$this->saveDefaultConfig();
        $this->getResource("config.yml");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("BanHammer Loaded!");
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
	switch($cmd->getName()){
	    case "banhammer":
		if(!isset($args[0])){
		    $sender->sendMessage("Usage: /banhammer <get|edit> [item|banip|ban|kick]");
		    return true;
		}else{
		if($args[0] == "edit"){
		    if(!isset($args[1])){
			    $sender->sendMessage("Usage: /banhammer edit <type|item>");
			    return true;
			}else{
		    if($sender->hasPermission("banhammer.edit")){
			if($args[1] == "type"){
			    if(!isset($args[2])){
				    $sender->sendMessage("Usage: /banhammer edit type <banip|ban|kick>");
				    return true;
				}else{
			    if($args[2] == "banip"){
				$current = $this->getConfig()->get("BanType");
				if($current == "banip"){
				    $sender->sendMessage("[BanHammer] The Ban Type is already set to BanIP!");
				    return true;
				}else{
				    $this->getConfig()->set("BanType", "banip");
				    $this->getConfig()->save();
				    $sender->sendMessage("[BanHammer] The Ban Type has been set to BanIP!");
				    return true;
			        }
			    }elseif($args[2] == "ban"){
				$current = $this->getConfig()->get("BanType");
				if($current == "ban"){
				    $sender->sendMessage("[BanHammer] The Ban Type is already set to Ban!");
				    return true;
				}else{
				    $this->getConfig()->set("BanType", "ban");
				    $this->getConfig()->save();
				    $sender->sendMessage("[BanHammer] The Ban Type has been set to Ban!");
				    return true;
			        }
			    }elseif($args[2] == "kick"){
				$current = $this->getConfig()->get("BanType");
				if($current == "kick"){
				    $sender->sendMessage("[BanHammer] The Ban Type is already set to Kick!");
				    return true;
			        }else{
				    $this->getConfig()->set("BanType", "kick");
				    $this->getConfig()->save();
				    $sender->sendMessage("[BanHammer] The Ban Type has been set to Kick!");
				    return true;
			        }
			    }else{
				$sender->sendMessage("Usage: /banhammer edit <type|item>");
			        return true;
			    }
		  	  }
			}elseif($args[1] == "item"){
			    if(!isset($args[2])){
			    	$sender->sendMessage("Usage: /banhammer edit item <ID>");
			    }else{
			    	if($this->getConfig()->get("BanHammer") == $args[2]){
			    	    $sender->sendMessage("[BanHammer] That item is already in use!");
			    	}else{
			    	    $this->getConfig()->set("BanHammer", $args[2]);
			    	    $this->getConfig()->save();
			    	}
			    }
			}
		    }else{
			$sender->sendMessage("[BanHammer] You do not have permission to do that!");
		        return true;
		    }
			}
		}elseif($args[0] == "get"){
		    if(!$sender instanceof Player){
			$sender->sendMessage("[BanHammer] You can only use this command in-game!");
			return true;
		    }else{
			if($sender->hasPermission("banhammer.use")){
				$id = Item::fromString($this->getConfig()->get("BanHammer"));
				$item = $id->setCount(1);
				$sender->getInventory()->addItem(clone $item);
				$sender->sendMessage("[BanHammer] The BanHammer has been added to your inventory!");
			        return true;
			}else{
			    $sender->sendMessage("[BanHammer] You do not have permission to do that!");
			    return true;
		        }
		    }
		}else{
		    $sender->sendMessage("Usage: /banhammer <get|edit> [banip|ban|kick]");
		    return true;
	        }
	    break;
        }
      }
    }
	
    public function onAttack(EntityDamageByEntityEvent $event){
    	if(($event->getDamager()) instanceof Player){
    	    $player = $event->getDamager();
            if(($event->getEntity()) instanceof Player){
                $target = $event->getEntity();
    	        if($player->getInventory->getItemInHand()->getID() == $this->getConfig()->get("BanHammer")){
    	            if($player->hasPermission("banhammer.use")){
    	                if($target->hasPermission("banhammer.use") or $target->hasPermission("banhammer.exempt")){
    	                    $sender->sendMessage("[BanHammer] You do not have permission to " . $this->getConfig()->get("BanType") . " that player!");
    	                    $event->setCancelled();
    	                }else{
    	                    if($this->getConfig()->get("BanType") == "banip"){
    	                        $ip = $target->getAddress();
    	                        $player->getServer()->getIPBans()->addBan($ip, "The BanHammer has spoken!", null, $player->getName());
    	                        foreach($sender->getServer()->getOnlinePlayers() as $t){
		    	            if($t->getAddress() === $ip){
	    	                        $t->kick("The BanHammer has Spoken!");
			            }
			        }
    	                    }elseif($this->getConfig()->get("BanType") == "ban"){
    	                        $player->getServer()->getNameBans()->addBan($target->getName(), "The BanHammer has spoken!", null, $player->getName());
			        $target->getName()->kick("The BanHammer has spoken!");
    	                    }elseif($this->getConfig()->get("BanType") == "kick"){
    	                        $target->getName()->kick("The BanHammer has spoken!");
    	                    }
    	                }
    	            }
    	        }
            }
    	}
    }
    
    public function onDisable(){
        $this->getLogger()->info("BanHammer Unloaded!");
    }
}
?>
