<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('America/Sao_Paulo');

class Algoritmo extends CI_Controller {

	public function index()
	{

		$this->load->helper('tools');
		$this->load->helper('date');

		$file = file_get_contents(FCPATH."tickets.json");
		if($file != null){

			$return = $this->Reading(json_decode($file));

			if(Tools::IsValid($return)){
				$name = 'tickets.json';
				$fp = fopen($name, 'w');
				fwrite($fp, json_encode($return));
				fclose($fp);
			}
		}else{
			echo "Arquivo não encontrado!";
		}
	}

	public function Reading($tickets){
		
		$return = new stdClass();
		$return->data = array();

		if(Tools::IsValid($tickets)){
			
			foreach ($tickets as $ticket) {
				$data 					= new stdClass();

				$data->TicketID 		= $ticket->TicketID;
    	 		$data->CategoryID 		= $ticket->CategoryID;
    	 		$data->CustomerID 		= $ticket->CustomerID;
    	 		$data->CustomerName 	= $ticket->CustomerName;
    	 		$data->CustomerEmail 	= $ticket->CustomerEmail;
    	 		$data->DateCreate 		= $ticket->DateCreate;
    	 		$data->DateUpdate 		= $ticket->DateUpdate;

    	 		//separando dados da interação para futuro processamento
    	 		$data->Interactions 	= $this->_SplitInteractions($ticket->Interactions);

    	 		//1ª prioridade: Assunto
    	 		$data->Interactions 	= $this->_SubjectPriority($data->Interactions);

    	 		//2ª prioridade: Quem enviou
    	 		$data->sender_priority 	= $this->_SenderPriority($data->Interactions);

    	 		//3ª prioridade: Mensagem
    	 		$data->Interactions 	= $this->_MessagePriority($data->Interactions);

    	 		//4ª prioridade: Data de criação/Alteração
    	 		$data->date_priority 	= $this->_DatePriority($data->DateCreate, $data->DateUpdate);

    	 		// Prioridade e sua pontuação
    	 		$data					= $this->_CalculatePriority($data);

	   	 		$ticket->priority 		= $data->priority;
	   	 		$ticket->score 			= $data->score;

    	 		array_push($return->data, $data);
			}

		}

		return $tickets;
	}

	private function _DatePriority($DateCreate, $DateUpdate){

		$data = new stdClass();

		$now = new DateTime(date("Y-m-d H:i:s"));
		$DateCreate = new DateTime($DateCreate);
		$DateUpdate = new DateTime($DateUpdate);

		$daysOpen 	= $now->diff($DateCreate);
		$daysUpdate = $now->diff($DateUpdate);

		//regra criada 7 dias de resposta
		if((int)$daysUpdate->days >= 7){

			$data->date_priority = 'A';
		}else if((int)$daysOpened->days >= 7){

			if((int)$daysUpdate->days >= 7){

				$data->date_priority = 'A';
			}else{

				$data->date_priority = 'M';
			}
		}else{

			$data->date_priority = 'M';
		}

		$data->daysOpened = $daysOpen->days;
		$data->daysUpdate = $daysUpdate->days;

		return $data;
	}

	private function _SplitInteractions($Interactions){

		$data = array();

		if(Tools::IsValid($Interactions)){

			foreach ($Interactions as $interaction) {
				
				$obj = new stdClass();

				$obj->Subject 		= Tools::Token($interaction->Subject);
				$obj->Message 		= Tools::Token($interaction->Message);
				$obj->DateCreate 	= $interaction->DateCreate;
				$obj->Sender 		= $interaction->Sender;

				array_push($data, $obj);
			}
		}

		return $data;
	}

	private function _SubjectPriority($Interactions){

		if(Tools::IsValid($Interactions)){

			foreach ($Interactions as $interaction) {

				$interaction->subject_priority = Tools::catchWordSubject($interaction);
			}

		}

		return $Interactions;
	}

	private function _SenderPriority($Interactions){

		$retorno = null;

		if(Tools::IsValid($Interactions)){

			foreach ($Interactions as $interaction) {

				if(strcasecmp($interaction->Sender, 'Expert') == 0){

					$retorno = 'N';
				}else{

					$retorno = 'A';
				}
			}

		}

		return $retorno;
	}

	private function _MessagePriority($Interactions){

		if(Tools::IsValid($Interactions)){

			foreach ($Interactions as $interaction) {

				$interaction->message_priority = Tools::catchWordMessage($interaction);
			}
		}

		return $Interactions;
	}

	private function _CalculatePriority($data){

		$countDate 		= 0;
		$countSender 	= 0;
		$countSubject 	= 0;
		$countMessage 	= 0;
		$countPriority 	= 0;

		if(Tools::IsValid($data)){

			//date
			($data->date_priority == 'A') ? $countDate = 1 : $countDate = 0.5;

			//Sender
			($data->sender_priority == 'A') ? $countSender = 1 : $countSender = 0.5;
			
			//subject and message
			foreach ($data->Interactions as $interaction) {

				//subject
				($interaction->subject_priority == 'A') ? $countSubject = 1 : $$countSubject = 0.5;

				//message
				if($interaction->message_priority == 'A'){

					//caso a ultima mensagem enviada seja do cliente, terá seu peso aumentado.
					if($data->sender_priority == 'A'){

						$countMessage += 1;
					}else{

						$countMessage += 0.5;
					}
				}else{

					$countMessage += 0.5;
				}
			}
			$countMessage = $countMessage / count($data->Interactions);

			//distribuindo pesos:
			$countPriority = ($countDate + $countSender + $countSubject + $countMessage) / 4;

			//70% de assertividade
			if($countPriority >= 0.70){
				$prioridade = 'Alta';
			}else{
				$prioridade = 'Normal';
			}

			//anexando resultados
			$data->priority = $prioridade;
			$data->score 	= $countPriority;

		}
		return $data;
	}

	public function API(){

		$this->load->helper('tools');

		$data     	= null;
		$auxiliar	= array();

		$filters = array(
            "pagination"  	=> $this->input->post('pagination'),
            "perpage" 		=> $this->input->post('perpage'),
            "order"   		=> $this->input->post('order'	),
            "dateinitial"   => $this->input->post('dateinitial'),
            "datefinal" 	=> $this->input->post('datefinal'),
            "priority" 		=> $this->input->post('priority')
        );

        $file = file_get_contents(FCPATH."tickets.json");

        if($file != null){

        	$data = json_decode($file);

        	// filtro prioridade
        	if(Tools::IsValid($filters['priority'])){

        		($filters['priority'] == 'A') ? $filters['priority'] = 'Alta' : $filters['priority'] = 'Normal';

        		foreach ($data as $key => $ticket) {

        			if(strcasecmp(trim($ticket->priority), trim($filters['priority'])) == 0){

        				array_push($auxiliar, $ticket);
        			}
        		}
        		$data = $auxiliar;
        	}

        	// filtro datecreated
        	if(Tools::IsValid($filters['dateinitial']) || Tools::IsValid($filters['datefinal'])){

        		$DateInitial = null;
        		$Datefinal 	= null;

        		if(Tools::IsValid($filters['dateinitial'])){
        			$DateInitial = new DateTime($filters['dateinitial']);
        		}

        		if(Tools::IsValid($filters['datefinal'])){
        			$Datefinal = new DateTime($filters['datefinal']);
        		}

        		$auxiliar = array();

        		foreach ($data as $key => $ticket) {

        			$dateCreate = new DateTime($ticket->DateCreate);

        			if( $DateInitial != null && $Datefinal != null){

        				if(($dateCreate > $DateInitial) && ($dateCreate < $Datefinal)){

        					array_push($auxiliar, $ticket);
        				}
        			}else if($DateInitial != null){

        				if($dateCreate > $DateInitial){

        					array_push($auxiliar, $ticket);
        				}
        			}else{
        				if($dateCreate < $Datefinal){

        					array_push($auxiliar, $ticket);
        				}
        			}
        		}
        		$data = $auxiliar;
        	}

        	//ordenacao
        	if(Tools::IsValid($filters['order'])){
        		$order = $filters['order'];

        		$auxiliar = array();

    			foreach ($data as $key => $ticket) {
    				array_push($auxiliar, $ticket);
    			}

        		if($order == '1'){
        			
        			$auxiliar = $this->sortObjectsByByField($auxiliar, 'DateCreate');
	        	
	        	}else if($order == '2'){

	        		$auxiliar = $this->sortObjectsByByField($auxiliar, 'DateUpdate');

	        	}else{

	        		$auxiliar = $this->sortObjectsByByField($auxiliar, 'priority');

	        	}

	        	$data = $auxiliar;
        	}

        	if(Tools::IsValid($filters['perpage'])){
        		$perpage 	= $filters['perpage'];
        		$page 		= $filters['pagination'];
        		
        		$MaxallowedItem = ($page * $perpage);

        		$allowedItem = ($page * $perpage) - $perpage;

        		$auxiliar 	= array();
        		$aux = 0;

        		foreach ($data as $key => $ticket) {
        			if(($aux < $perpage) && (($key < $MaxallowedItem) && ($key >= $allowedItem))){
        				array_push($auxiliar, $ticket);
        				$aux++;
        			}
        		}

        		$data = $auxiliar;
        	}
        	var_dump($data);
        	//echo json_encode($data);
        }
	}

	public function sortObjectsByByField($objects, $fieldName, $sortOrder = SORT_ASC, $sortFlag = SORT_REGULAR)
	{
	    $sortFields = array();
	    foreach ($objects as $key => $row) {
	        $sortFields[$key] = $row->{$fieldName};
	    }
	    array_multisort($sortFields, $sortOrder, $sortFlag, $objects);
	    return $objects;
	}
}

?>