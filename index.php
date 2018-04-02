<?
define('TOKEN', '');
define('VERSION', 'v1.0');
define('NAME', 'MathBot (Calculator)');
define('IMAGE_URL', 'https://bot.vkrot.xyz/images/');
$data = json_decode(file_get_contents('php://input'));
if(isset($data->message)){
	$msg = $data->message->text;
	$id = $data->message->from->id;
	switch($msg){
		case '/start':
		 $answer = '*Добро пожаловать!*
Этот бот умеет выполнять математические действия, например, находить *сумму*, *разность*, *произведение* и *частное* чисел. Также бот имеет некоторые другие команды. Список команд - /help';
		 botApi('sendMessage', [
		  'chat_id' => $id,
		  'text' => $answer,
		  'parse_mode' => 'Markdown'
		 ]);
		 break;
		 case '/help':
		  $answer = '*Помощь:*
/help — помощь
/math n1 n2 — выполнить математические действия, где n1 и n2 - числа
/sqrt n — квадратный корень из числа n
/sqr n - n²
/info - информация о боте
_Помимо этого, вы можете вызывать бота в других чатах (личных, групповых, каналах). Для этого наберите в поле ввода сообщения_ @MathGeniusBot_, а затем 2 числа_';
		  botApi('sendMessage', [
		   'chat_id' => $id,
		   'text' => $answer,
		   'parse_mode' => 'Markdown'
		  ]);
		  break;
		  case contains($msg,'/math'):
		   $param = str_replace('/math', '', $msg);
		   $param = explode(' ', $param);
		   if(empty($param) || count($param) < 3 || !is_numeric($param[1]) || !is_numeric($param[2])){
		   	 $answer = 'Введите 2 числа';
		   	 botApi('sendMessage', [
		   	  'chat_id' => $id,
		   	  'text' => $answer,
		   	  'parse_mode' => 'Markdown'
		   	 ]);
		   	 break;
		   }
		   $n1 = $param[1];
		   $n2 = $param[2];
		   $sum = $n1+$n2;
		   $dif = $n1-$n2;
		   $sum = $n1.' + '.$n2.' = '.$sum;
		   $dif = $n1.' - '.$n2.' = '.$dif;
		   $prod = $n1.' × '.$n2.' = '.$n1*$n2;
		   if($n2 != 0) $quo = $n1.' ÷ '.$n2.' = '.$n1/$n2;
		   if($n2 == 0) $quo = $n1.' ÷ '.$n2.' = NaN (на ноль делить нельзя)';
		   $res = [
		    $sum, $dif, $prod, $quo
		   ];
		   for($i=0;$i<count($res);$i++){
		   	botApi('sendMessage', [
		   	 'chat_id' => $id,
		   	 'text' => $res[$i],
		   	 'parse_mode' => 'Markdown'
		   	]);
		   }
		   break;
		  case contains($msg, '/sqrt'):
		   $param = str_replace('/sqrt', '', $msg);
		   $param = explode(' ', $param)[1];
		   if(!isset($param) || !is_numeric($param)){
		   	 $answer = 'Введите число';
		   	 botApi('sendMessage', [
		   	  'chat_id' => $id,
		   	  'text' => $answer,
		   	  'parse_mode' => 'Markdown'
		   	 ]);
		   	 break;
		   }
		   $answer = sqrt($param);
		   $answer = '√'.$param.' = '.$answer;
		   botApi('sendMessage', [
		    'chat_id' => $id,
		    'text' => $answer,
		    'parse_mode' => 'Markdown'
		   ]);
		   break;
		  case '/info':
		   $answer = '*Информация*
'.NAME.' - '.VERSION.'
*Создатель:* [Владимир Аксенов](https://vk.com/aks03vova)
*GitHub:* [MathBot](https://github.com/v0vadev/MathBot)';
     botApi('sendMessage', [
      'chat_id' => $id,
      'text' => $answer,
      'parse_mode' => 'Markdown'
     ]);
     break;
    case contains($msg, '/sqr'):
     $param = str_replace('/sqr', '', $msg);
		   $n = explode(' ', $param)[1];
		   if(!isset($n) || !is_numeric($n)){
		   	 $answer = 'Введите число';
		   	 botApi('sendMessage', [
		   	  'chat_id' => $id,
		   	  'text' => $answer,
		   	  'parse_mode' => 'Markdown'
		   	 ]);
		   	 break;
		   	}
		   	$sqr = $n*$n;
		   	$answer = $n.'² = '.$sqr;
		   	botApi('sendMessage', [
		   	 'chat_id' => $id,
		   	 'text' => $answer,
		   	 'parse_mode' => 'Markdown'
		   	]);
		   	break;
		  default:
		   $answer = 'Я не знаю такой команды. Список - /help';
		   botApi('sendMessage', [
		   'chat_id' => $id,
		   'text' => $answer,
		   'parse_mode' => 'Markdown'
		  ]);
		  break;
	}
}

if(isset($data->inline_query)){
	$id = $data->inline_query->id;
	$uid = $data->inline_query->from->id;
	$query = $data->inline_query->query;
	$res = [];
	$param = explode(' ', $query);
	if($query == '' || count($param) < 2 || !is_numeric($param[0]) || !is_numeric($param[1])){
		$res[] = [
		 'type' => 'article',
		 'id' => '0',
		 'title' => 'Напишите 2 числа',
		 'description' => 'В ответ вы получите сумму, разность, произведение, частное, а также квадратный корень из каждого числа',
		 'message_text' => ''
		];
	} else{
		$n1 = $param[0];
		$n2 = $param[1];
		$sum = $n1+$n2;
		$dif = $n1-$n2;
		$prod = $n1*$n2;
		if($n2 != 0) $quo = $n1/$n2;
		if($n2 == 0) $quo = 'NaN (на ноль делить нельзя)';
		if($n2 != 0) $quoImg = IMAGE_URL.'division.jpg';
		if($n2 == 0) $quoImg = IMAGE_URL.'error.jpg';
		$sqr1 = sqrt($n1);
		$sqr2 = sqrt($n2);
		$sqr11 = $n1*$n1;
		$sqr22 = $n2*$n2;
		$res[] = [
		 'type' => 'article',
		 'id' => '1',
		 'title' => 'Сумма — '.$sum,
		 'description' => 'Сумма чисел '.$n1.' и '.$n2.' — '.$sum,
		 'message_text' => $n1.' + '.$n2.' = '.$sum,
		 'thumb_url' => IMAGE_URL.'plus.jpg'
		];
		$res[] = [
		 'type' => 'article',
		 'id' => '2',
		 'title' => 'Разность — '.$dif,
		 'description' => 'Разность чисел '.$n1.' и '.$n2.' — '.$dif,
		 'message_text' => $n1.' - '.$n2.' = '.$dif,
		 'thumb_url' => IMAGE_URL.'minus.png'
		];
		$res[] = [
		 'type' => 'article',
		 'id' => '3',
		 'title' => 'Произведение — '.$prod,
		 'description' => 'Произведение чисел '.$n1.' и '.$n2.' — '.$prod,
		 'message_text' => $n1.' × '.$n2.' = '.$prod,
		 'thumb_url' => IMAGE_URL.'multiplication.jpg'
		];
		$res[] = [
		 'type' => 'article',
		 'id' => '4',
		 'title' => 'Частное — '.$quo,
		 'description' => 'Частное чисел '.$n1.' и '.$n2.' — '.$quo,
		 'message_text' => $n1.' ÷ '.$n2.' = '.$quo,
		 'thumb_url' => $quoImg
		];
		$res[] = [
		 'type' => 'article',
		 'id' => '5',
		 'title' => 'Квадратный корень — '.$sqr1.', '.$sqr2,
		 'description' => 'Квадратный корень из числа '.$n1.' — '.$sqr1.', числа '.$n2.' — '.$sqr2,
		 'message_text' => '√'.$n1.' = '.$sqr1.', √'.$n2.' = '.$sqr2,
		 'thumb_url' => IMAGE_URL.'sqrt.jpg'
		];
		$res[] = [
		 'type' => 'article',
		 'id' => '6',
		 'title' => 'Квадрат — '.$sqr11.', '.$sqr22,
		 'description' => 'Квадрат числа '.$n1.' — '.$sqr11.', числа '.$n2.' — '.$sqr22,
		 'message_text' => $n1.'² = '.$sqr1.', '.$n2.'² = '.$sqr2,
		 'thumb_url' => IMAGE_URL.'sqr.jpg'
		];
	}
	botApi('answerInlineQuery', [
	 'inline_query_id' => $id,
	 'results' => json_encode($res)
	]);
}

function botApi($method, $params){
	$params = http_build_query($params);
	return json_decode(file_get_contents('https://api.telegram.org/bot'.TOKEN.'/'.$method.'?'.$params));
}
function contains($str,$search){
	if(strpos($str,$search) !== false){
		return true;
	} else{
		return false;
	}
}
?>