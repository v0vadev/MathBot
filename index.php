<?
require 'db.php';
define('TOKEN', '');
define('VERSION', 'v1.1');
define('NAME', 'MathBot (Calculator)');
define('IMAGE_URL', 'https://bot.vkrot.xyz/images/');
$data = json_decode(file_get_contents('php://input'));
if(isset($data->message)){
	$msg = $data->message->text;
	$id = $data->message->from->id;
	$cancelKey = [
	 'keyboard' => [
	  ['Отмена']
	 ],
	 'resize_keyboard' => true
	];
	$cancelKey = json_encode($cancelKey);
	$keyboard = [
   'keyboard' => [
    ['Действия', 'Квадратный корень'],
    ['Возведение в квадрат','Квадратное уравнение'],
    ['Помощь', 'Инфо']
   ],
   'one_time_keyboard' => true
  ];
 $keyboard = json_encode($keyboard);
	if($res = $mysqli->query("SELECT * FROM users WHERE telegram=".$id)){
	 if($res->num_rows == 0){
		 if(!$mysqli->query("INSERT INTO `users` (`telegram`) VALUES (".$id.")")){
			 botApi('sendMessage', [
			  'chat_id' => $id,
			  'text' => 'Mysql insert err: '.$mysqli->error
			 ]);
		 }
  	}
 } else{
  	botApi('sendMessage', [
	  'chat_id' => $id,
	  'text' => 'Mysql err: '.$mysqli->error
	 ]);
 }
 if($msg == '/cancel' || $msg == 'Отмена'){
 	 if($mysqli->query("UPDATE users SET state=0 WHERE telegram=".$id)){
 	 	 botApi('sendMessage', [
 	 	  'chat_id' => $id,
 	 	  'text' => 'Текущая операция отменена',
 	 	  'parse_mode' => 'Markdown',
 	 	  'reply_markup' => $keyboard
 	 	 ]);
 	 } else{
 	 	 botApi('sendMessage', [
 	 	  'chat_id' => $id,
 	 	  'text' => 'mysql error: '.$mysqli->error
 	 	 ]);
 	 	 return;
 	 }
 }
 if($res = $mysqli->query("SELECT * FROM `users` WHERE telegram=".$id)){
 	 while($row = $res->fetch_assoc()){
 	 	 if($row['state'] != 0){
 	 	 	 switch($row['state']){
 	 	 	 	 case 1:
 	 	 	 	  if(is_numeric($msg)){
 	 	 	 	  	 if($msg == 0){
 	 	 	 	  	 	 botApi('sendMessage', [
 	 	 	 	  	 	  'chat_id' => $id,
 	 	 	 	  	 	  'text' => 'Коэффициент a не может быть равным 0'
 	 	 	 	  	 	 ]);
 	 	 	 	  	 	 break;
 	 	 	 	  	 }
 	 	 	 	  	 if($mysqli->query("UPDATE users SET abc='".$msg."', state=2 WHERE telegram=".$id)){
 	 	 	 	  	 	 botApi('sendMessage', [
		       'chat_id' => $id,
		       'text' => 'Введите коэффициент b'
		      ]);
 	 	 	 	  	 } else{
 	 	 	 	  	 	 botApi('sendMessage', [
		       'chat_id' => $id,
		       'text' => 'mysql err: '.$mysqli->error
		      ]);
 	 	 	 	  	 }
 	 	 	 	  } else{
 	 	 	 	  	 botApi('sendMessage', [
		      'chat_id' => $id,
		      'text' => 'Введите число'
		     ]);
 	 	 	 	 }
 	 	 	 	 break;
 	 	 	 	case 2:
 	 	 	 	 if(is_numeric($msg)){
 	 	 	 	  	 if($mysqli->query("UPDATE users SET abc=CONCAT(abc, ',".$msg."'), state=3 WHERE telegram=".$id)){
 	 	 	 	  	 	 botApi('sendMessage', [
		       'chat_id' => $id,
		       'text' => 'Введите коэффициент c'
		      ]);
 	 	 	 	  	 } else{
 	 	 	 	  	 	 botApi('sendMessage', [
		       'chat_id' => $id,
		       'text' => 'mysql err: '.$mysqli->error
		      ]);
 	 	 	 	  	 }
 	 	 	 	  } else{
 	 	 	 	  	 botApi('sendMessage', [
		      'chat_id' => $id,
		      'text' => 'Введите число'
		     ]);
 	 	 	 	 }
 	 	 	 	 break;
 	 	 	 	case 3:
 	 	 	 	 if(is_numeric($msg)){
 	 	 	 	  	 if($mysqli->query("UPDATE users SET abc=CONCAT(abc, NULL), state=0 WHERE telegram=".$id)){
 	 	 	 	  	  $abc = explode(',', $row['abc']);
 	 	 	 	  	  $abc[] = $msg;
 	 	 	 	  	  $r = solveQe($abc[0], $abc[1], $abc[2]);
 	 	 	 	  	 	 botApi('sendMessage', [
		       'chat_id' => $id,
		       'text' => 'D = `'.$r[123].'`',
		       'parse_mode' => 'Markdown'
		      ]);
		      if(!isset($r[99])){
		      	$r = 'x¹ = `'.$r[0].'`
x² = `'.$r[1].'`';
		      } else{
		      	$r = $r[99];
		      }
		      botApi('sendMessage', [
		       'chat_id' => $id,
		       'text' => $r,
		       'parse_mode' => 'Markdown',
		       'reply_markup' => $keyboard
		      ]);
 	 	 	 	  	 } else{
 	 	 	 	  	 	 botApi('sendMessage', [
		       'chat_id' => $id,
		       'text' => 'mysql err: '.$mysqli->error
		      ]);
 	 	 	 	  	 }
 	 	 	 	  } else{
 	 	 	 	  	 botApi('sendMessage', [
		      'chat_id' => $id,
		      'text' => 'Введите число'
		     ]);
 	 	 	 	 }
 	 	 	 	 break;
 	 	 	 	case 4:
		   $param = explode(' ', $msg);
		   if(empty($param) || count($param) < 2 || !is_numeric($param[0]) || !is_numeric($param[1])){
		   	 $answer = 'Введите 2 числа';
		   	 botApi('sendMessage', [
		   	  'chat_id' => $id,
		   	  'text' => $answer,
		   	  'parse_mode' => 'Markdown'
		   	 ]);
		   	 break;
		   }
		   $n1 = $param[0];
		   $n2 = $param[1];
		   $sum = $n1+$n2;
		   $dif = $n1-$n2;
		   $sum = $n1.' + '.$n2.' = `'.$sum.'`';
		   $dif = $n1.' - '.$n2.' = `'.$dif.'`';
		   $prod = $n1.' × '.$n2.' = `'.$n1*$n2.'`';
		   if($n2 != 0) $quo = $n1.' ÷ '.$n2.' = `'.$n1/$n2.'`';
		   if($n2 == 0) $quo = $n1.' ÷ '.$n2.' = NaN (на ноль делить нельзя)';
		   $res = [
		    $sum, $dif, $prod, $quo
		   ];
		   for($i=0;$i<count($res);$i++){
		   	botApi('sendMessage', [
		   	 'chat_id' => $id,
		   	 'text' => $res[$i],
		   	 'parse_mode' => 'Markdown',
		   	 'reply_markup' => $keyboard
		   	]);
		   	if(!$mysqli->query("UPDATE users SET state=0 WHERE telegram=".$id)){
		   		botApi('sendMessage', [
		   		 'chat_id' => $id,
		   		 'text' => 'mysql error: '.$mysqli->error
		   		]);
		   	}
		   }
 	 	 	 	 break;
 	 	 	 	case 5:
		   if(!isset($msg) || !is_numeric($msg)){
		   	 $answer = 'Введите число';
		   	 botApi('sendMessage', [
		   	  'chat_id' => $id,
		   	  'text' => $answer,
		   	  'parse_mode' => 'Markdown'
		   	 ]);
		   	 break;
		   	}
		   	$answer = sqrt($msg);
		   $answer = '√'.$msg.' = `'.$answer.'`';
		   botApi('sendMessage', [
		    'chat_id' => $id,
		    'text' => $answer,
		    'parse_mode' => 'Markdown',
		    'reply_markup' => $keyboard
		   ]);
		   if(!$mysqli->query("UPDATE users SET state=0 WHERE telegram=".$id)){
		   		botApi('sendMessage', [
		   		 'chat_id' => $id,
		   		 'text' => 'mysql error: '.$mysqli->error
		   		]);
		   	}
		   	break;
		  case 6:
		   if(!isset($msg) || !is_numeric($msg)){
		   	 $answer = 'Введите число';
		   	 botApi('sendMessage', [
		   	  'chat_id' => $id,
		   	  'text' => $answer,
		   	  'parse_mode' => 'Markdown'
		   	 ]);
		   	 break;
		   	}
		   	$sqr = $msg*$msg;
		   	$answer = $msg.'² = `'.$sqr.'`';
		   	botApi('sendMessage', [
		   	 'chat_id' => $id,
		   	 'text' => $answer,
		   	 'parse_mode' => 'Markdown'
		   	]);
		   	if(!$mysqli->query("UPDATE users SET state=0 WHERE telegram=".$id)){
		   		botApi('sendMessage', [
		   		 'chat_id' => $id,
		   		 'text' => 'mysql error: '.$mysqli->error
		   		]);
		   	}
		   break;
 	 	 	 }
		   return;
 	 	 }
 	 }
 } else{
 	 botApi('sendMessage', [
		    'chat_id' => $id,
		    'text' => $mysqli->error
		   ]);
 }
	switch($msg){
		case '/start':
		 $answer = '*Добро пожаловать!*
Этот бот умеет выполнять математические действия, например, находить *сумму*, *разность*, *произведение* и *частное* чисел. Также бот имеет некоторые другие команды. Список команд - /help';
		 botApi('sendMessage', [
		  'chat_id' => $id,
		  'text' => $answer,
		  'parse_mode' => 'Markdown',
		  'reply_markup' => $keyboard
		 ]);
		 break;
		 case '/help':
		 case 'Помощь':
		  $answer = '*Помощь:*
/help — помощь
/math — выполнить математические действия
/sqrt — квадратный корень из числа
/sqr - возведение числа в квадрат
/qe - решение квадратного уравнения
/info - информация о боте
_Помимо этого, вы можете вызывать бота в других чатах (личных, групповых, каналах). Для этого наберите в поле ввода сообщения_ @MathGeniusBot_, а затем 2 числа_';
		  botApi('sendMessage', [
		   'chat_id' => $id,
		   'text' => $answer,
		   'parse_mode' => 'Markdown'
		  ]);
		  break;
		  case '/cancel':
		  case 'Отмена':
		   break;
		  case '/math':
		  case 'Действия':
		   if(!$mysqli->query("UPDATE users SET state=4 WHERE telegram=".$id)){
		    	botApi('sendMessage', [
		   	  'chat_id' => $id,
		   	  'text' => 'mysql error: '.$mysqli->error
		   	 ]);
		   	} else{
		   		botApi('sendMessage', [
		   	  'chat_id' => $id,
		   	  'text' => 'Отправьте мне 2 числа',
		   	  'reply_markup' => $cancelKey
		   	 ]);
		   	}
		   break;
		  case '/sqrt':
		  case 'Квадратный корень':
		   if($mysqli->query("UPDATE users SET state=5 WHERE telegram=".$id)){
		   	 botApi('sendMessage', [
		   	  'chat_id' => $id,
		   	  'text' => 'Пришлите число',
		   	  'reply_markup' => $cancelKey
		   	 ]);
		   } else{
		   	 botApi('sendMessage', [
		   	  'chat_id' => $id,
		   	  'text' => 'mysql error: '.$mysqli->error
		   	 ]);
		   }
		   break;
		  case '/info':
		  case 'Инфо':
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
    case '/sqr':
    case 'Возведение в квадрат':
     if($mysqli->query("UPDATE users SET state=6 WHERE telegram=".$id)){
     	 botApi('sendMessage', [
     	  'chat_id' => $id,
     	  'text' => 'Пришлите число',
     	  'reply_markup' => $cancelKey
     	 ]);
     } else{
     	 botApi('sendMessage', [
     	  'chat_id' => $id,
     	  'text' => 'mysql error: '.$mysqli->error
     	 ]);
     }
		   break;
		  case '/qe':
		  case 'Квадратное уравнение':
		   if($mysqli->query("UPDATE users SET state=1 WHERE telegram=".$id)){
		   	 $set = 'Введите коэффициент a';
		   } else{
		   	 botApi('sendMessage', [
		    'chat_id' => $id,
		    'text' => $mysqli->error
		   ]);
		   }
		   botApi('sendMessage', [
		    'chat_id' => $id,
		    'text' => $set,
		    'reply_markup' => $cancelKey
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

function solveQe($a,$b,$c){
	//a*x²+b*x+c=0
	$res = [];
	$D = ($b*$b)-(4*$a*$c);
	$res[123] = $D;
	if($D < 0){
		$res[99] = 'Уравнение не имеет решений';
	}
	if($D == 0){
		$res[0] = neg($b)/(2*$a);
		$res[1] = 'нет';
	}
	if($D > 0){
		$res[0] = (neg($b)+sqrt($D))/(2*$a);
		$res[1] = (neg($b)-sqrt($D))/(2*$a);
	}
	return $res;
}

function neg($num){
	return $num*-1;
}
?>