<?php
namespace EC_Manager;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use EC_Manager\Main;
class BankTimer extends PluginTask {
   public function __construct(Main $plugin){
      parent::__construct($plugin);
      $this->main = $plugin;
   }
   public function onRun($tick){
      $time = $this->getMain()->getBankTime();
      if($time["h"] < 0){
         $this->getMain()->setBankTime(array("h"=>$this->getMain()->getDefaultBankTime(),"m"=>0,"s"=>0));
         $time = $this->getMain()->getBankTime();
         $this->getMain()->addBankInterest();
      }
      if($time["s"] <= 0){
         $time["s"] = 59;
         if($time["m"] <= 0){
            $time["m"] = 59;
            $time["h"] = $time["h"] - 1;
         }else{
            $time["m"] = $time["m"] - 1;
         }
      }else{
         $time["s"] = $time["s"] - 1;
      }
      $this->getMain()->setBankTime($time);
   }
   public function getMain(){
      return $this->main;
   }
}