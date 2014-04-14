<?
	class Oxygen_Communication_Broadcast {
		private $url;
		private $channel = false;

		public function __construct($url, $channel = false){
			$this->url = $url;
			if($channel !== false){
				$this->channel = $channel;
			}
		}

		public function publish($channel, $data = false){
			if($data === false){
				if($this->channel === false){
					throw new Exception("No channel has been set for broadcast server ".$this->url);
				}else{
					$data = $channel;
				}
			}else{
				$this->channel = $channel;
			}

			$broadcastMessage = array(
                "channel" => $this->channel,
                "data" => $data
            );
			
			$broadcastMessage = json_encode($broadcastMessage);

			$curl = curl_init();
			$headers = array(
				"Content-Type: application/json",
				"Content-length: ".strlen($broadcastMessage)
			);
            curl_setopt($curl, CURLOPT_URL, $this->url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $broadcastMessage);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            $result = curl_exec($curl);
            curl_close($curl);
		}
	}