<?php
namespace EC_Manager;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use EC_Manager\BankTimer;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
class Main extends PluginBase implements Listener {
   const NO_MONEY = "\x30";
   const SUCCESS = "\x31";
   const ERROR = "\x32";
   const ERROR_NO_DATA = "\x33";
   const ERROR_NEGATIVE = "\x34";
   public $deposit = array("h"=>0,"m"=>0,"s"=>0);
   public $data = array();
   public $bank = array();
   public $config;
   public function onEnable(){
      $this->getServer()->getPluginManager()->registerEvents($this,$this);
      $this->saveDefaultConfig();
      $this->config = $this->getConfig()->getAll();
      @mkdir($this->getDataFolder());
      @mkdir($this->getDataFolder()."bank/");
      if(!file_exists($this->getDataFolder()."bank/bankTime.date")){
         touch($this->getDataFolder()."bank/bankTime.date");
         file_put_contents($this->getDataFolder()."bank/bankTime.date", gzdeflate(json_encode(array("h"=>$this->config["bank-interest-time"],"m"=>0,"s"=>0))));
      }
      $this->deposit = json_decode(gzinflate(file_get_contents($this->getDataFolder()."bank/bankTime.date")), true);
      if(!file_exists($this->getDataFolder()."bank/bank.all")){
         touch($this->getDataFolder()."bank/bank.all");
         file_put_contents($this->getDataFolder()."bank/bank.all", gzdeflate("[]"));
      }
      $this->bank = json_decode(gzinflate(file_get_contents($this->getDataFolder()."bank/bank.all")), true);
      @mkdir($this->getDataFolder()."data/");
      if(!file_exists($this->getDataFolder()."data/allData.eco")){
         touch($this->getDataFolder()."data/allData.eco");
         file_put_contents($this->getDataFolder()."data/allData.eco", gzdeflate("[]"));
      }
      $this->data = json_decode(gzinflate(file_get_contents($this->getDataFolder()."data/allData.eco")), true);
      $this->getServer()->getScheduler()->scheduleRepeatingTask(new BankTimer($this), 20);
   }
   public function onCommand(CommandSender $s, Command $cmd, $txt, array $args){
      switch($cmd->getName()){
         case "cash":
         if(isset($args[0])){
            if($args[0] !== "" || $args[0] !== null){
               $p = $this->getServer()->getPlayer($args[0]);
               if($p !== null){
                  $s->sendMessage("§6* §ePixelPals §b|§f ".$p->getName()."'s cash: ".$this->getPlayerMoney($p->getName()).$this->getRoundNumber($this->getPlayerMoney($p->getName()))." ".$this->config["monetary-unit"]);
               }else{
                  $s->sendMessage("§6* §ePixelPals §c|§f That player is offline!");
               }
            }else{
               $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /cash <player>");
            }
         }else{
            $s->sendMessage("§6* §ePixelPals §b|§f Your cash: ".$this->getPlayerMoney($s->getName()).$this->getRoundNumber($this->getPlayerMoney($s->getName()))." ".$this->config["monetary-unit"]);
         }
         break;
         case "send":
         if(isset($args[0]) && isset($args[1])){
            if(is_numeric($args[0]) && $args[0] >= 0){
               if($this->getPlayerMoney($s->getName()) >= $args[0]){
                  $p = $this->getServer()->getPlayer($args[1]);
                  if($p !== null && $p !== $s){
                     $this->removePlayerMoney($s->getName(), $args[0]);
                     $s->sendMessage("§6* §ePixelPals §a|§f You have successfully sent ".$args[0].$this->getRoundNumber($args[0])." ".$this->config["monetary-unit"]." to ".$p->getName());
                     $this->addPlayerMoney($p->getName(), $args[0]);
                     $p->sendMessage("§6* §ePixelPals §a|§f You have received ".$args[0].$this->getRoundNumber($args[0])." ".$this->config["monetary-unit"]." from ".$s->getName());
                     $this->sendDc("💳 ".$s->getName()." sent ".$args[0].$this->config["monetary-unit"]." to ".$p->getName());
                  }else{
                     $s->sendMessage("§6* §ePixelPals §c|§f That player is offline!");
                  }
               }else{
                  $s->sendMessage("§6* §ePixelPals §c|§f You don't have enough coins!");
               }
            }else{
               $s->sendMessage("§6* §ePixelPals §c|§f Invalid number! please use valid number.");
            }
         }else{
            $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /send <amount> <player>");
         }
         break;
         case "bank":
         if(isset($args[0])){
            switch($args[0]){
               case "cash":
               $s->sendMessage("§4#+*§c--§6==[§e Pixel Bank §6]==§c--§4*+#");
               $s->sendMessage("§4*§c-§6» §eYour bank cash: §f".$this->getPlayerBankMoney($s->getName()).$this->getRoundNumber($this->getPlayerBankMoney($s->getName()))." ".$this->config["monetary-unit"]);
               $s->sendMessage("§4*§c-§6» §eCurrent interest: §f".$this->config["bank-interest"]);
               $s->sendMessage("§4*§c-§6» §eNext increase: §f".$this->getBankTime(true));
               break;
               case "deposit":
               if(isset($args[1])){
                  if(is_numeric($args[1]) && $args[1] >= 0){
                     if($args[1] <= $this->getPlayerMoney($s->getName())){
                        $this->removePlayerMoney($s->getName(), $args[1]);
                        $this->addPlayerBankMoney($s->getName(), $args[1]);
                        $s->sendMessage("§6* §ePixe Bank §a|§f You have successfully deposited ".$args[1].$this->getRoundNumber($args[1])." ".$this->config["monetary-unit"]." to the bank.");
                     }else{
                        $s->sendMessage("§6* §ePixel Bank §c|§f You don't have enough coins!");
                     }
                  }else{
                     $s->sendMessage("§6* §ePixel Bank §c|§f Invalid number! Please use correct number!");
                  }
               }else{
                  $s->sendMessage("§6* §ePixel Bank §c|§f Invalid syntax! Use: /bank help");
               }
               break;
               case "take":
               if(isset($args[1])){
                  if(is_numeric($args[1]) && $args[1] >= 0){
                     if($args[1] <= $this->getPlayerBankMoney($s->getName())){
                        $this->removePlayerBankMoney($s->getName(), $args[1]);
                        $this->addPlayerMoney($s->getName(), $args[1]);
                        $s->sendMessage("§6* §ePixel Bank §a|§f You have successfully took ".$args[1].$this->getRoundNumber($args[1])." ".$this->config["monetary-unit"]." coins from the bank.");
                     }else{
                        $s->sendMessage("§6* §ePixel Bank §c|§f You don't have enough coins!");
                     }
                  }else{
                     $s->sendMessage("§6* §ePixel Bank §c|§f Invalid number! Please use correct number!");
                  }
               }else{
                  $s->sendMessage("§6* §ePixel Bank §c|§f Invalid syntax! Use: /bank help");
               }
               break;
               case "gamble":
               if(isset($args[1])){
                  if(is_numeric($args[1]) && $args[1] >= 0){ 
                     if($args[1] <= $this->getPlayerBankMoney($s->getName())){
                        $chance = rand(0, 2);
                        $win = $args[1] * 2.1;
                        switch($chance){
                           case 0:
                           $s->sendMessage("§6* §ePixel Bank §8|§f It's tie! You didn't get or lost anything..");
                           break;
                           case 1:
                           $this->removePlayerBankMoney($s->getName(), $args[1]);
                           $this->addPlayerBankMoney($s->getName(), $win);
                           $s->sendMessage("§6* §ePixel Bank §a|§f You won! You got ".$win.$this->getRoundNumber($win)." ".$this->config["monetary-unit"]." coins!");
                           break;
                           case 2:
                           $this->removePlayerBankMoney($s->getName(), $args[1]);
                           $s->sendMessage("§6* §ePixel Bank §c|§f You lost! All the money is gone..");
                           break;
                        }
                     }else{
                        $s->sendMessage("§6* §ePixel Bank §c|§f You don't have enough coins!");
                     }
                  }else{
                     $s->sendMessage("§6* §ePixel Bank §c|§f Invalid number! Please use correct number!");
                  }
               }else{
                  $s->sendMessage("§6* §ePixel Bank §c|§f Invalid syntax! Use: /bank help");
               }
               break;
               case "help":
               $s->sendMessage("§4#+*§c--§6==[§e Bank Help §6]==§c--§4*+#");
               $s->sendMessage(" ");
               $s->sendMessage("§6- §e/bank cash");
               $s->sendMessage("§6- §e/bank deposit <amount>");
               $s->sendMessage("§6- §e/bank take <amount>");
               $s->sendMessage("§6- §e/bank gamble <amount>");
               $s->sendMessage("§6- §e/bank help");
               break;
               default:
               $s->sendMessage("§6* §ePixel Bank §c|§f Invalid syntax! Use: /bank help");
               break;
            }
         }else{
            $s->sendMessage("§6* §ePixel Bank §c|§f Invalid syntax! Use: /bank help");
         }
         break;
         case "top":
         $s->sendMessage("§4*§c-§6-==[§e Top cash §6]==-§c-§4*");
         foreach($this->getTopTen() as $player => $money){
            $s->sendMessage("§6* §e".$player."§f: §e".$money.$this->getRoundNumber($money)." ".$this->config["monetary-unit"]);
         }
         break;
         case "eco":
         if(isset($args[0])){
            switch($args[0]){
               case "help":
               $s->sendMessage("§6--==[§ePixelPals§6]==--");
               $s->sendMessage(" ");
               $s->sendMessage("§6- §e/eco setcash <amount> <player>");
               $s->sendMessage("§6- §e/eco setbcash <amount> <player>");
               $s->sendMessage("§6- §e/eco addcash <amount> <player>");
               $s->sendMessage("§6- §e/eco addbcash <amount> <player>");
               $s->sendMessage("§6- §e/eco removecash <amount> <player>");
               $s->sendMessage("§6- §e/eco removebcash <amount> <player>");
               $s->sendMessage("§6- §e/eco setitime <h> <m> <s>");
               $s->sendMessage("§6- §e/eco help");
               break;
               case "setcash":
               if(isset($args[1])){
                  if(is_numeric($args[1]) && $args[1] >= 0){
                     if(isset($args[2])){
                        $p = $this->getServer()->getPlayer($args[2]);
                        if($p !== null){
                           $this->setPlayerMoney($p->getName(), $args[1]);
                           $s->sendMessage("§6* §ePixelPals §a|§f ".$p->getName()."'s cash set to ".$args[1].$this->config["monetary-unit"]);
                        }else{
                           $s->sendMessage("§6* §ePixelPals §c|§f That player is offline!");
                        }
                     }else{
                        $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
                     }
                  }else{
                     $s->sendMessage("§6* §ePixelPals §c|§f Invalid number! Please use correct number!");
                  }
               }else{
                  $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
               }
               break;
               case "setbcash":
               if(isset($args[1])){
                  if(is_numeric($args[1]) && $args[1] >= 0){
                     if(isset($args[2])){
                        $p = $this->getServer()->getPlayer($args[2]);
                        if($p !== null){
                           $this->setPlayerBankMoney($p->getName(), $args[1]);
                           $s->sendMessage("§6* §ePixelPals §a|§f ".$p->getName()."'s bank cash set to ".$args[1].$this->config["monetary-unit"]);
                        }else{
                           $s->sendMessage("§6* §ePixelPals §c|§f That player is offline!");
                        }
                     }else{
                        $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
                     }
                  }else{
                     $s->sendMessage("§6* §ePixelPals §c|§f Invalid number! Please use correct number!");
                  }
               }else{
                  $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
               }
               break;
               case "addcash":
               if(isset($args[1])){
                  if(is_numeric($args[1]) && $args[1] >= 0){
                     if(isset($args[2])){
                        $p = $this->getServer()->getPlayer($args[2]);
                        if($p !== null){
                           $this->addPlayerMoney($p->getName(), $args[1]);
                           $s->sendMessage("§6* §ePixelPals §a|§f ".$args[1].$this->config["monetary-unit"]." coins added to ".$p->getName()."'s cash.");
                        }else{
                           $s->sendMessage("§6* §ePixelPals §c|§f That player is offline!");
                        }
                     }else{
                        $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
                     }
                  }else{
                     $s->sendMessage("§6* §ePixelPals §c|§f Invalid number! Please use correct number!");
                  }
               }else{
                  $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
               }
               break;
               case "addbcash":
               if(isset($args[1])){
                  if(is_numeric($args[1]) && $args[1] >= 0){
                     if(isset($args[2])){
                        $p = $this->getServer()->getPlayer($args[2]);
                        if($p !== null){
                           $this->addPlayerBankMoney($p->getName(), $args[1]);
                           $s->sendMessage("§6* §ePixelPals §a|§f ".$args[1].$this->config["monetary-unit"]." coins added to ".$p->getName()."'s bank cash.");
                        }else{
                           $s->sendMessage("§6* §ePixelPals §c|§f That player is offline!");
                        }
                     }else{
                        $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
                     }
                  }else{
                     $s->sendMessage("§6* §ePixelPals §c|§f Invalid number! Please use correct number!");
                  }
               }else{
                  $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
               }
               break;
               case "removecash":
               if(isset($args[1])){
                  if(is_numeric($args[1]) && $args[1] >= 0){
                     if(isset($args[2])){
                        $p = $this->getServer()->getPlayer($args[2]);
                        if($p !== null){
                           if($this->getPlayerMoney($p->getName()) >= $args[1]){
                              $this->removePlayerMoney($p->getName(), $args[1]);
                              $s->sendMessage("§6* §ePixelPals §a|§f Removed ".$args[1].$this->config["monetary-unit"]." coins from ".$p->getName()."'s cash!");
                           }else{
                              $s->sendMessage("§6* §ePixelPals §c|§f This player doesn't have that much cash!");
                           }
                        }else{
                           $s->sendMessage("§6* §ePixelPals §c|§f That player is offline!");
                        }
                     }else{
                        $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
                     }
                  }else{
                     $s->sendMessage("§6* §ePixelPals §c|§f Invalid number! Please use correct number!");
                  }
               }else{
                  $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
               }
               break;
               case "removebcash":
               if(isset($args[1])){
                  if(is_numeric($args[1]) && $args[1] >= 0){
                     if(isset($args[2])){
                        $p = $this->getServer()->getPlayer($args[2]);
                        if($p !== null){
                           if($this->getPlayerMoney($p->getName()) >= $args[1]){
                              $this->removePlayerBankMoney($p->getName(), $args[1]);
                              $s->sendMessage("§6* §ePixelPals §a|§f Removed ".$args[0].$this->config["monetary-unit"]." coins from ".$p->getName()."'s bank cash!");
                           }else{
                              $s->sendMessage("§6* §ePixelPals §c|§f This player doesn't have that much cash in their bank account!");
                           }
                        }else{
                           $s->sendMessage("§6* §ePixelPals §c|§f That player is offline!");
                        }
                     }else{
                        $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
                     }
                  }else{
                     $s->sendMessage("§6* §ePixelPals §c|§f Invalid number! Please use correct number!");
                  }
               }else{
                  $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
               }
               break;
               case "setitime":
               if(isset($args[1]) && isset($args[2]) && isset($args[3])){
                  if(is_numeric($args[1]) && $args[1] >= 0 && is_numeric($args[2]) && $args[2] >= 0 && is_numeric($args[3]) && $args[3] >= 0){
                     if($args[2] < 60 && $args[3] < 60){
                        $this->setBankTime(array("h"=>$args[1],"m"=>$args[2],"s"=>$args[3]));
                        $s->sendMessage("§6* §ePixelPals §a|§f Bank interest renewal time has been set to: ".$this->getBankTime(true));
                     }else{
                        $s->sendMessage("§6* §ePixelPals §c|§f The time must be maximum 60!");
                     }
                  }else{
                     $s->sendMessage("§6* §ePixelPals §c|§f Invalid number! Please enter correct time input!");
                  }
               }else{
                  $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
               }
               break;
               default:
               $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
               break;
            }
         }else{
            $s->sendMessage("§6* §ePixelPals §c|§f Invalid syntax! Use: /eco help");
         }
         break;
      }
   }
   public function onJoin(PlayerJoinEvent $e){
      $p = $e->getPlayer();
      if(!isset($this->data[$p->getName()])){
         $this->data[$p->getName()]["m"] = $this->config["default-cash"];
         $this->bank[$p->getName()]["b"] = $this->config["default-bank-cash"];
         $this->saveBank();$this->saveMoneyData();
      }
   }
   public function onQuit(PlayerQuitEvent $e){
      $this->saveMoneyData();
      $this->saveBank();
   }
   public function getPlayerMoney($player){
      if(!isset($this->data[$player])){
         return self::ERROR;
      }else{
         return $this->data[$player]["m"];
      }
   }
   public function getMoneyData(){
      return $this->data;
   }
   public function saveMoneyData(){
      if(count($this->data) > 0){
         file_put_contents($this->getDataFolder()."data/allData.eco", gzdeflate(json_encode($this->getMoneyData())));
         return true;
      }
      return false;
   }
   public function addPlayerMoney($player, $money){
      if($this->getPlayerMoney($player) !== self::ERROR){
         if($money >= 0){
            $this->data[$player]["m"] = $this->getPlayerMoney($player) + $money;
            $this->saveMoneyData();
            return self::SUCCESS;
         }else{
            return self::ERROR_NEGATIVE;
         }
      }else{
         return self::ERROR_NEGATIVE;
      }
   }
   public function removePlayerMoney($player, $money){
      if($this->getPlayerMoney($player) !== self::ERROR){
         if($money >= 0){
            if($money <= $this->getPlayerMoney($player)){
               $this->data[$player]["m"] = $this->getPlayerMoney($player) - $money;
               $this->saveMoneyData();
               return self::SUCCESS;
            }else{
               return self::ERROR_NEGATIVE;
            }
         }else{
            return self::ERROR_NEGATIVE;
         }
      }else{
         return self::ERROR_NO_DATA;
      }
   }
   public function setPlayerMoney($player, $money){
      
      if($this->getPlayerMoney($player) !== self::ERROR){
         if($money >= 0){
            $this->data[$player]["m"] = $money;
            $this->saveMoneyData();
            return self::SUCCESS;
         }else{
            return self::ERROR_NEGATIVE;
         }
      }else{
         return self::ERROR_NO_DATA;
      }
   }
   public function getBank(){
      return $this->bank;
   }
   public function addBankInterest(){
      $interest = str_replace("%", '', $this->config["bank-interest"]);
      foreach($this->bank as &$player){
         if($player["b"] > 0){
            $player["b"] += round(($player["b"] / 100) * $interest, 0);
         }
      }
      $this->sendDc("💵 Interest added! next: `".$this->getBankTime(true)."`");
      $this->saveBank();
   }
   public function saveBank(){
      file_put_contents($this->getDataFolder()."bank/bank.all", gzdeflate(json_encode($this->getBank())));
   }
   public function getPlayerBankMoney($player){
      if(!isset($this->bank[$player])){
         return self::ERROR_NO_DATA;
      }else{
         return $this->bank[$player]["b"];
      }
   }
   public function addPlayerBankMoney($player, $money){
      if($this->getPlayerBankMoney($player) !== self::ERROR_NO_DATA){
         if($money >= 0){
            $this->bank[$player]["b"] = $this->getPlayerBankMoney($player) + $money;
            $this->saveBank();
            return self::SUCCESS;
         }else{
            return self::ERROR_NEGATIVE;
         }
      }else{
         return self::ERROR_NEGATIVE;
      }
   }
   public function removePlayerBankMoney($player, $money){
      if($this->getPlayerBankMoney($player) !== self::ERROR_NO_DATA){
         if($money >= 0){
            $this->bank[$player]["b"] = $this->getPlayerBankMoney($player) - $money;
            $this->saveBank();
            return self::SUCCESS;
         }else{
            return self::ERROR_NEGATIVE;
         }
      }else{
         return self::ERROR_NEGATIVE;
      }
   }
   public function setPlayerBankMoney($player, $money){
      if($this->getPlayerBankMoney($player) !== self::ERROR_NO_DATA){
         if($money >= 0){
            $this->bank[$player]["b"] = $money;
            $this->saveBank();
            return self::SUCCESS;
         }else{
            return self::ERROR_NEGATIVE;
         }
      }else{
         return self::ERROR_NEGATIVE;
      }
   }
   public function getDefaultBankTime(){
      return $this->config["bank-interest-time"];
   }
   public function getBankTime($string = false){
      if($string){
         $time = $this->deposit["h"]."h ".$this->deposit["m"]."m ".$this->deposit["s"]."s";
         return $time;
      }else{
         return $this->deposit;
      }
   }
   public function setBankTime($time){
      $this->deposit = $time;
      file_put_contents($this->getDataFolder()."bank/bankTime.date", gzdeflate(json_encode($this->getBankTime())));
      return true;
   }
   public function getTopTen(): array {
      $data = $this->data;
      uasort($data, function($a, $b){return $b["m"] - $a["m"];});
      $tt = array_slice($data, 0, 10);
      $final = array();
      foreach($tt as $p => $m){
         $final[$p] = $m["m"];
      }
      return $final;
   }
   public function getRoundNumber($num){
      if($num >= 1000 && $num < 1000000){
         return "k";
      }elseif($num >= 1000000){
         return "m";
      }else{
         return "";
      }
   }
   public function sendDc($msg){
      return; // Disable Discord webhook since it may not be needed or could be misused. If you want to enable it, remove this line and add your webhook URL below.
      $url = "webhook url here";
      $curl = curl_init();
      $data = array(
      "content" => $msg,
      "username" => "Pixel Bank"
      );
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_exec($curl);
   }
}







