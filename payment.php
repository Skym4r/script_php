<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', E_ALL);
/*
Скрипт по хуку срабатывает на каждый добавленный в МС(мой склад) платеж, платежи могут быть разных типов, поэтому данный скрипт работает с 4 хуками.
Данные платежи не всегда имееют прямую связь с заказом в МС (например связан с отгрузкой), поэтому мы просматриваем все варианты связи с закзом, берем идентификатор и ищем по фильтрации нужный нам заказ
в CRM системе.
Далее проверяем на наличии платежа в заказе в CRM системе, если платеж есть, то меняем статус, если нет, то добавляем его.
*/
$apiUrl = 'utl_ratilCRM';
$apiKey = 'API_TOKEN_ratilCRM';


$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

$url=$data['events'][0]['meta']['href'];
$result=getInfo($url);
$url_zakaz=$result['operations'][0]['meta']['href'];
if(strpos($url_zakaz, 'customerorder')!==false)
{
$result_zakaz=getInfo($url_zakaz);
$id_ms=substr($url_zakaz,62);
	if (array_key_exists('payments',$result_zakaz))
	{
		$ordersList=ordersList($apiUrl, $apiKey, $id_ms)['orders'];
		if(!$ordersList)
		{
				exit();
		}
		if($ordersList)
		{
			$ordersList=$ordersList[0];
			$id_retail=$ordersList['id'];
			if ($ordersList['payments'])
			{
				foreach($ordersList['payments'] as $pay)
				{
			
					if($pay['status']=='paid')
					{
						exit();
					}
					if($pay['status']!='paid')
					{
						$pay_status=yespayments($apiUrl, $apiKey, $pay['id'], $totalSumm, $ordersList['site'])
					}
				}
			}
		}
	}
	if (array_key_exists('invoicesOut',$result_zakaz))
	{
	$url_chiot=$result_zakaz['invoicesOut'][0]['meta']['href'];
	$result_chiot=getInfo($url_chiot);
	
		if (array_key_exists('demands',$result_chiot))
		{

		sleep(10);
		$ordersList=ordersList($apiUrl, $apiKey, $id_ms)['orders'];
			if(!$ordersList)
			{
				exit();
			}
			if($ordersList)
			{
				$ordersList=$ordersList[0];
				$id_retail=$ordersList['id'];
				if ($ordersList['payments'])
				{
					foreach($ordersList['payments'] as $pay)
					{
						
						if($pay['status']=='paid')
						{
							exit();
						}
						if($pay['status']!='paid')
						{
							$pay_status=yespayments($apiUrl, $apiKey, $pay['id'], $totalSumm, $ordersList['site'])
						}
					}
					
				}
				if (!$ordersList['payments'])
					{
						$pay=notpayments($apiUrl, $apiKey, $id_retail, $ordersList['totalSumm'], $ordersList['site']);	
					}
			}
		}
	}
}

if(strpos($url_zakaz, 'demand')!==false)
{
	$result_chiot=getInfo($url_zakaz);
	if (array_key_exists('customerOrder',$result_chiot))
	{
		$zakaz=$result_chiot['customerOrder']['meta']['href'];
		$id_ms=substr($zakaz,62);
		var_dump($id_ms);
		sleep(10);
		$ordersList=ordersList($apiUrl, $apiKey, $id_ms)['orders'];
		var_dump($ordersList);
		if(!$ordersList)
		{
			exit();
		}
		if($ordersList)
			{
				$ordersList=$ordersList[0];
				$id_retail=$ordersList['id'];
				if ($ordersList['payments'])
				{
					foreach($ordersList['payments'] as $pay)
					{
						if($pay['status']=='paid')
						{
							exit();
						}
						if($pay['status']!='paid')
						{
							$pay_status=yespayments($apiUrl, $apiKey, $pay['id'], $totalSumm, $ordersList['site'])
						}
					}
					
				}
				if (!$ordersList['payments'])
					{
						$pay=notpayments($apiUrl, $apiKey, $id_retail, $ordersList['totalSumm'], $ordersList['site']);	
					}
			}
	}
	if (array_key_exists('invoicesOut',$result_chiot))
	{
	$url_chiot=$result_chiot['invoicesOut'][0]['meta']['href'];
	$res=getInfo($url_chiot);
		if (array_key_exists('customerOrder',$res))
		{
			$zakaz=$res['customerOrder']['meta']['href'];
			$id_ms=substr($zakaz,62);
			var_dump($id_ms);
			sleep(10);
			$ordersList=ordersList($apiUrl, $apiKey, $id_ms)['orders'];
			if(!$ordersList)
			{
				exit();
			}
			if($ordersList)
			{
				$ordersList=$ordersList[0];
				$id_retail=$ordersList['id'];
				if ($ordersList['payments'])
				{
					foreach($ordersList['payments'] as $pay)
					{
						if($pay['status']=='paid')
						{
							exit();
						}
						if($pay['status']!='paid')
						{
							$pay_status=yespayments($apiUrl, $apiKey, $pay['id'], $totalSumm, $ordersList['site'])
							
						}
					}
					
				}
				if (!$ordersList['payments'])
					{
						$pay=notpayments($apiUrl, $apiKey, $id_retail, $ordersList['totalSumm'], $ordersList['site']);				
					}
			}
		}
	
	}

}

function yespayments($apiUrl, $apiKey, $payid, $totalSumm, $site)
{
	$url = "$apiUrl/api/v5/orders/payments/".$payid."/edit?apiKey=$apiKey";
	$request = http_build_query([
	'site' => $site,
	'payment' => json_encode([
	'externalId'=>$payid,
	'status'=>'paid'
	])]);
	$curl = curl_init();
	curl_setopt_array($curl, [
	CURLOPT_URL => $url,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_CUSTOMREQUEST => 'POST',
	CURLOPT_POSTFIELDS => $request,
	]);
	$response = curl_exec($curl);			
	curl_close($curl);
							
}

function notpayments($apiUrl, $apiKey, $id, $totalSumm, $site)
{	
	$url = "$apiUrl/api/v5/orders/payments/create?apiKey=$apiKey";
	$request = http_build_query([
	'site' => $site,
	'payment' => json_encode([
	   'amount'=>$totalSumm,
	'paidAt'=>date("Y-m-d H:i:s"),
	'type'=>'banktransfer',
	'status'=>'paid',
	'order'=>['id'=>$id]      
		])]);
	var_dump($request);
	$curl = curl_init();
	curl_setopt_array($curl, [
	CURLOPT_URL => $url,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_CUSTOMREQUEST => 'POST',
	CURLOPT_POSTFIELDS => $request,
	]);
	$response = curl_exec($curl);
	curl_close($curl);
	
}


function ordersList($apiUrl, $apiKey, $id)
{
$url = "$apiUrl/api/v5/orders?apiKey=$apiKey";
$request = http_build_query([
'customFields'=>['moyskladexternalid'=>$id]
]);
 $curl = curl_init();
curl_setopt_array($curl, [
CURLOPT_URL => $url,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_CUSTOMREQUEST => 'GET',
CURLOPT_POSTFIELDS => $request,
]);
$response = curl_exec($curl);
curl_close($curl);
return json_decode($response, true);
}

function getInfo($link) {
$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_URL, $link);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_USERPWD, "login:password");
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
$out = curl_exec($curl);
curl_close($curl);
$result = json_decode($out,TRUE);
return $result;
}
?>