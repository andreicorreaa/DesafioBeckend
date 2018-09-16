<?php

class Tools
{

	public static function Count($array)
	{
		$count = 0;
		if(!is_null($array) && !empty($array) && count($array) > 0)
		{
			foreach($array->result() as $rows)
			{
				$count++;
			}
		}

		return $count;
	}

	public static function IsValid($obj)
	{
		$count = 0;

		if(!is_null($obj) && !empty($obj))
		{
			$count++;
		}

		return $count;
	}
	
	public static function IsValidNumber($num)
	{
		$count = 0;

		if(!empty($num) && is_numeric($num) && $num > 0)
		{
			$count++;
		}

		return $count;
	}

	public static function IsNullOrEmpty($obj)
	{
		$count = 0;

		if(is_null($obj) || empty($obj))
		{
			$count++;
		}

		return $count;
	}

	public static function RemoveSpecialCaracter($text, $notRemoveSpace=FALSE)
	{
		if(!$notRemoveSpace)
		{
			$text = str_replace(" ", "_", $text);
		}
		
		$text = str_replace("ç", "c", $text);
		$text = str_replace("Ç", "C", $text);

		$text = str_replace("â", "a", $text);
		$text = str_replace("ã", "a", $text);
		$text = str_replace("á", "a", $text);
		$text = str_replace("à", "a", $text);
		$text = str_replace("Â", "A", $text);
		$text = str_replace("Ã", "A", $text);
		$text = str_replace("Á", "A", $text);
		$text = str_replace("À", "A", $text);

		$text = str_replace("é", "e", $text);
		$text = str_replace("ê", "e", $text);
		$text = str_replace("É", "E", $text);
		$text = str_replace("Ê", "E", $text);

		$text = str_replace("í", "i", $text);
		$text = str_replace("î", "i", $text);
		$text = str_replace("Í", "I", $text);
		$text = str_replace("Î", "I", $text);

		$text = str_replace("ó", "o", $text);
		$text = str_replace("õ", "o", $text);
		$text = str_replace("ô", "o", $text);
		$text = str_replace("Ó", "O", $text);
		$text = str_replace("Õ", "O", $text);
		$text = str_replace("Ô", "O", $text);

		$text = str_replace("ú", "u", $text);
		$text = str_replace("Ú", "U", $text);

		//
		return $text;
	}

    public static function GetCurrentDate()
    {		
        $timestamp = mktime(date("H") - (-(SITE_TIMEFIX)), date("i"), date("s"), date("m"), date("d"), date("Y"));
        return gmdate("Y-m-d H:i:s", $timestamp);
    }

	/**
	* @static
	* @method FormatDate
	* @param string
	* @return string
	* @desc Formata uma string no padrao que deve ser gravado no banco de dados
	*/
	public static function FormatDate($date)
	{
		$result = null;

		if(isset($date) && !empty($date))
		{
			$result = implode("-",array_reverse(explode('/', $date)));
		}

		return $result;
	}

	/**
	 * @static
	 * @method IsValidEmail
	 * @param string
	 * @return boolean
	 * @desc Verifica se o email é válido
	*/
	public static function IsValidEmail($email)
	{
		$count		= "^[a-zA-Z0-9\._-]+@";
		$domain		= "[a-zA-Z0-9\._-]+.";
		$extension	= "([a-zA-Z]{2,4})$^";

		$pattern = $count.$domain.$extension;

		if (preg_match($pattern, $email))
		{
			$arrayEmail = explode('@', $email);
			$arrayKeyName = array('teste');

			foreach($arrayKeyName as $value)
			{
				if ($arrayEmail[0] == $value)
				{
					return false;
				}
			}

			return true;
		}
		else
		{
			return false;
		}

	}

	public static function Token($text){
		$text = (string)$text;
		$text = trim($text);
		$text = self::ToLower($text);
		$text = self::RemoveSpecialCaracter($text, true);
		$words = explode(" ", $text);
		return $words;
    }

    public static function DiffBetweenDates($initialDate, $finalDate, $round = 0)
	{
		$difference = 0;

		$initialDate = strtotime($initialDate);
		$finalDate = strtotime($finalDate);

		$difference = ($finalDate - $initialDate) / 86400;

		if ($round != 0)
			return floor($difference);
		else
			return $difference;
	}

	public static function ToLower($text)
	{
		$text = strtolower($text);
		$text = str_replace("Ç", "ç", $text);
		$text = str_replace("Â", "â", $text);
		$text = str_replace("Ã", "ã", $text);
		$text = str_replace("Á", "á", $text);
		$text = str_replace("À", "à", $text);
		$text = str_replace("É", "é", $text);
		$text = str_replace("Ê", "ê", $text);
		$text = str_replace("Í", "í", $text);
		$text = str_replace("Ó", "ó", $text);
		$text = str_replace("Õ", "õ", $text);
		$text = str_replace("Ô", "ô", $text);
		$text = str_replace("Ú", "ú", $text);

		return $text;
	}

	public static function catchWordSubject($interaction){
		
		$countHigh 		= 0;
		$countNormal 	= 0;

		$HighPriorityWords = array(
			'reclam',
			'entreg',
			'pagam',
			'confirm',
			'troc',
			'cancel',
			'cadast',
			'pedid',
			'taman',
			'diferen'
		);

		$NormalPriorityWords = array(
			'sem',
			'assun',
			'duvid',
			'elog',
			'suges',
			'infor',
			'acompa',
		);

		foreach ($interaction->Subject as $word) {

			foreach ($HighPriorityWords as $wordList) {
				
				if (strpos($word, $wordList) !== false) {
				    $countHigh++;
				}
			}

			foreach ($NormalPriorityWords as $wordList) {
				
				if (strpos($word, $wordList) !== false) {
				    $countNormal++;
				}
			}
		}

		if($countHigh > $countNormal){
			return 'A';
		}else{
			return 'N';
		}
	}

	public static function catchWordMessage($interaction){

		$countHigh 		= 0;

		$HighPriorityWords = array(
			'?',
			'!',
			'procon',
			'reclam',
			'porem',
			'entretan',
			'soluc',
			'errado',
			'providenc',
			'cabiv',
			'pedid',
			'pagam',
			'mas',
			'nao',
			'tentativa',
			'contato',
			'resolver',
			'cancel',
			'quero',
			'corrig',
			'email',
			'senha',
			'incorret',
			'endere',
			'problem',
			'acompa'

		);

		foreach ($interaction->Message as $word) {

			foreach ($HighPriorityWords as $wordList) {
				
				if (strpos($word, $wordList) !== false) {
				    $countHigh++;
				}
			}
		}

		if($countHigh > 0){
			return 'A';
		}else{
			return 'N';
		}

	}

}
?>
