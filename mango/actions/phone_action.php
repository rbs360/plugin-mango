<?php
	
	namespace Kernel\Plugins\science communication\Actions;
	use Kernel\Actions\Notifications;
	use Kernel\Framework\SocketMessage;
	use Kernel\Framework\Websocket;
	use Kernel\Database\Database as DB;
	use Kernel\Kernel;
	
	class PhoneAction extends \Kernel\Plugins\PluginPhoneBase {
		
		public function actionCall(){
			$post = $_POST['name'];
			$to = $_POST['to'];
			$from = $this->getShortNumber($this->User->getEmployeeId());
			$company_id = time();
			$toRelease = $this->getFormatPhone($to, 7);
			
			$usersSet = $this->getPlugin()->getSettingsValue("users");
			if($usersSet){
				$decodedList = json_decode($usersSet, true);
				if($decodedList){
					foreach($decodedList as $dItem){
						if($this->User->getEmployeeId() == $dItem['employee']){
							$from = $dItem['ext'];
							break;
						}
					}
				}
			}
            
			$json = '{"command_id":"'.$company_id.'","from":{"extension":"'.$from.'", "number":""},"to_number":"'.$to.'"}';
			
			$data = [
			"command_id" => $company_id,
			"from" => [
			"extension" => $from
			],
			"to_number" => $toRelease
			];
			
			$json = json_encode($data);
			$vpbx_api_key   = $this->getPlugin()->getSettingsValue("key");
			$vpbx_api_salt  = $this->getPlugin()->getSettingsValue("salt");
			$url = "https://app.mango-office.ru/vpbx/commands/callback";
			
			if(!$vpbx_api_key){
				$this->resultError ("api_key not set");
			}
			if(!$vpbx_api_salt){
				$this->resultError ("api_salt not set");
			}
			$sign       =  hash("sha256", $vpbx_api_key . $json . $vpbx_api_salt);
			
			$postData = [
			"vpbx_api_key" => $vpbx_api_key,
			"sign" => $sign,
			"json" => $json
			];
			
			$response = $this->Transfer($url, $postData, false);
			
			
			return json_encode(['unique_id'=>$company_id, 'name'=>$post, 'to'=>$to, 'from'=>$from, 'status'=>'Connected', 'toRelease'=>$toRelease, 'pluginName' => $this->getPlugin()->getRealName()]);
			
		}
		
		public function getRecordUrl($callId){
			return "";
		}
		
		public function actionInbound(){
			$json         = $_POST['json'];
			$obj          = json_decode($json, true);
			$ext = [];
			$extArray = [];
			$usersSet = $this->getPlugin()->getSettingsValue("users");
			$transfer = $this->getPlugin()->getSettingsValue("transfer");
			$createRelationship = $this->getPlugin()->getSettingsValue("createRelationship");
			$createHistoryDeals = $this->getPlugin()->getSettingsValue("createDeals");
			
			$aviableStates = ["Appeared", "Disconnected"];
			
			if(!isset($obj['call_state']))
			$obj['call_state'] = "";
			
			if(in_array($obj['call_state'], $aviableStates)){
				if(isset($obj['to']['extension']))
				$ext[] = $obj['to']['extension'];
				if(!isset($obj['command_id']))
				$obj['command_id'] = $obj['from']['number'];
				
				if(mb_strlen($obj['from']['number']) == 10){
					if($obj['from']['number'][0] == "7")
					$obj['from']['number'][0] = "8";
				}
				
				$search = $this->findByPhone($obj['from']['number']);
				
				// если перевод звонка включен и нашелся контрагент звонящий
				if($transfer && $search['responsibleID'])
				{
					$extArray[] = $search['responsibleID'];
				}
				elseif($usersSet)
				{
					$decodedList = json_decode($usersSet, true);
					foreach ($decodedList as $item) {
						// если от science communication не пришел параметр ext
						if(!count($ext))
						{
							$extArray[] = $item["employee"];
							continue;
						}
						else if(is_array($ext) && in_array($item["ext"], $ext))
						{
							$extArray[] = $item["employee"];
						}
					}
				}
				
				if($createRelationship)
				{
					if($search['id'])
					{
						echo $search["id"];
						$this->createRelationship($search['id'], $obj['from']['number'], $this->getPlugin()->getRealName(), false, $search['responsibleID'], $obj['command_id']);
					}
					else
					{
						$company = $this->createRelationship(0, $obj['from']['number'], $this->getPlugin()->getRealName(), true, $search['responsibleID'], $obj['command_id']);
						
						$search['name'] = $company->name;
						$search['url'] = "/companies/" . $company->id . "/";
					}
				}
				
				//find user by ext
				$this->sendPopupPhone($extArray, $search['name'], $obj['to']['number'], $obj['from']['number'],  'Connected', 12, $search['url'], $search['responsible'], $this->getPlugin()->getRealName());
				//create history
				if($obj['call_state'] == "Disconnected" && $createHistoryDeals)
				$this->createHistory($obj['from']['number'],$obj['to']['number'],$obj['command_id']);
				
			}
			var_dump("OK");
		}
		
	}							
