<?php 
/*
По вебхуку отслеживаем все изменения в заказе, 
в случае если поле "Статус оплаты" перейдёт в "Оплачен" в дочернем документе счёта поставщику так же следует перевести доп. поле "Статус оплаты" в "Оплачен" 
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', E_ALL);
$postData = file_get_contents('php://input');
$data = json_decode($postData, true);//получаем запрос с вебхука на добавленние оплаты в заказ
$url=$data['events'][0]['meta']['href'];
$res=getInfo($url);
$oplata=$res['invoicesIn']['0']['meta']['href'];
$k=[];
foreach($res['attributes'] as  $attributes)
{
if(array_key_exists('value', $attributes))
{
    $k[]=$attributes['value']['name'];//смотрим занчение  доп поля по оплате
}
}

if(in_array('Оплачен', $k))
{
	$dop = array('meta' => array(
		'href' =>	  'https://online.moysklad.ru/api/remap/1.2/entity/invoicein/metadata/attributes/id_attribut',
		'type' =>	  'attributemetadata',
		'mediaType' => 'application/json'
	  ),
	  'id' => 'id_attribut',
	  'name' => 'Статус оплаты',
	  'type' => 'customentity',
	  'value' => array(
		'meta' => array(
		  'href' => 'https://online.moysklad.ru/api/remap/1.2/entity/customentity/id13/id12',
		  'metadataHref' => 'https://online.moysklad.ru/api/remap/1.2/context/companysettings/metadata/customEntities/id13',
		  'type' => 'customentity',
		  'mediaType' => 'application/json',
		  'uuidHref' => 'https://online.moysklad.ru/app/#custom_id13/edit?id=id12'
		),
		'name' => 'Оплачен'
	  ));
	  $ress[] = $dop;
	  $pizza = explode('/1.2/',$oplata);
	  $msid = ['attributes' => $ress];
	  $red = put($pizza[1], $msid);//передаем значение доп поля в дочерний документ
}

if(in_array('Нужна оплата', $k))
{
	$dop = array('meta' => array(
		'href' =>	  'https://online.moysklad.ru/api/remap/1.2/entity/invoicein/metadata/attributes/id_attribut',
		'type' =>	  'attributemetadata',
		'mediaType' => 'application/json'
	  ),
	  'id' => 'id_attribut',
	  'name' => 'Статус оплаты',
	  'type' => 'customentity',
	  'value' => array(
		'meta' => array(
		  'href' => 'https://online.moysklad.ru/api/remap/1.2/entity/customentity/id13/id12',
		  'metadataHref' => 'https://online.moysklad.ru/api/remap/1.2/context/companysettings/metadata/customEntities/id13',
		  'type' => 'customentity',
		  'mediaType' => 'application/json',
		  'uuidHref' => 'https://online.moysklad.ru/app/#custom_id13/edit?id=id12'
		),
		'name' => 'Нужна оплата'
	  ));	
	  $ress[] = $dop;
	  $pizza = explode('/1.2/',$oplata);
	  $msid = ['attributes' => $ress];
	  $red = put($pizza[1], $msid);//передаем значение доп поля в дочерний документ

}

if(in_array('Частично оплачен', $k))
{
	$dop = array('meta' => array(
		'href' =>	  'https://online.moysklad.ru/api/remap/1.2/entity/invoicein/metadata/attributes/id_attribut',
		'type' =>	  'attributemetadata',
		'mediaType' => 'application/json'
	  ),
	  'id' => 'id_attribut',
	  'name' => 'Статус оплаты',
	  'type' => 'customentity',
	  'value' => array(
		'meta' => array(
		  'href' => 'https://online.moysklad.ru/api/remap/1.2/entity/customentity/id13/id12',
		  'metadataHref' => 'https://online.moysklad.ru/api/remap/1.2/context/companysettings/metadata/customEntities/id13',
		  'type' => 'customentity',
		  'mediaType' => 'application/json',
		  'uuidHref' => 'https://online.moysklad.ru/app/#custom_id13/edit?id=id12'
		),
		'name' => 'Частично оплачен'
	  ));	
	  $ress[] = $dop;
	  $pizza = explode('/1.2/',$oplata);
	  $msid = ['attributes' => $ress];
	  $red = put($pizza[1], $msid);//передаем значение доп поля в дочерний документ

}

else
{
	exit();
}

function put($url, $data = array())
{
	$url = 'https://online.moysklad.ru/api/remap/1.2/'. rtrim($url, '/');	
	$data_json = json_encode($data);
	$ch = curl_init($url);	
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Authorization: Basic login:password',
		'Content-Length: ' . strlen($data_json)
	));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
	$response = curl_exec($ch);
	curl_close($ch);
	$data = json_decode($response, true);
	return $response;
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