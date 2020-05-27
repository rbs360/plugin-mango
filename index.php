<?php
	namespace Kernel\Plugins\Mango;
	
	
	
	class Index extends \Kernel\Plugins\PluginBase {
		
		/** get system name */
		public function getName()
		{
			return "mango";
		}
		
		
		/**
			* Render description install
		*/
		public function actionIndex() {
			$this->display("description");
		}
		
		
		
		/**
			* Render settings form
		*/
		public function actionSettings() {
			$this->beforeDisplay();
			$this->page->set($this->settings);
			
			$transfer = "";
			$transferName = $this->getRealName("transfer");
			$createRelationship = "";
			$createRelationshipName = $this->getRealName("createRelationship");
			$urlName = $this->getRealName("url");
			if($this->getSettingsValue("transfer"))
			{
				$transfer = "checked='checked'";
			}
			if($this->getSettingsValue("createRelationship"))
			{
				$createRelationship = "checked='checked'";
			}
			$createdDeals = "";
			$createdDealsName = $this->getRealName("createDeals");
			if($this->getSettingsValue("createDeals"))
            $createdDeals = "checked='checked'";
			
			$this->page->set([
            "api_key" => $this->getRealName("api_key"),
            "api_salt" => $this->getRealName("api_salt"),
            "transfer" => $transfer,
            "transferName" => $transferName,
            "createRelationship" => $createRelationship,
            "createRelationshipName" => $createRelationshipName,
            "createDeals" => $createdDeals,
            "createDealsName" => $createdDealsName,
			"urlName" => $urlName
			]);
			
			$this->printCallbackLink(\Kernel\Plugins\PluginManager::PLUGIN_TYPE_PHONE);
			$this->printUsers();
			$this->display("settings");
		}
		
	}