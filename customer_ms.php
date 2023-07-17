<?php
$apiUrl='https://maksimk1979.retailcrm.ru/';
$apiKey='HX8ujqPntEQZUOw8R9YLhyKNs571sij5';
$id=$_GET['id'];//получаем гет параметр с запроса
$response = customersList($apiUrl, $apiKey, $id)['customers']['0'];//получаем клиента
$firstName=$response['firstName'];
$site=$response['site'];

$phones=$response['phones']['0']['number'];//получаем номер телефона клиента
//Делаем массив для создания нового клиента в МС
$dataToMs = [
    'name' => $firstName,
    'legalLastName'=>$firstName,
    'phone' => $phones,
    'companyType'=>'individual',
    'inn'=> '-',
    'kpp'=> '-',
    'ogrn'=> '-',
    'okpo'=> '-',
    ];

$resMove = $ms->request('entity/counterparty', $dataToMs);
$msid=$resMove['id'];
$response = customersEdit($apiUrl, $apiKey, $msid,  $id,  $site);//Добавляем в доп поля идентификатор МС'а 

function customersList($apiUrl, $apiKey, $id)
{
$url = "$apiUrl/api/v5/customers?apiKey=$apiKey";
$request = http_build_query([
'ids' => [$id]
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

function customersEdit($apiUrl, $apiKey, $order,  $id,  $site)
{
$url = "$apiUrl/api/v5/customers/$id/edit?apiKey=$apiKey";
$request = http_build_query([
'site' => $site,
'customFields'=>['moyskladexternalid'=>$order], 
'id'=>$id

]);
 $curl = curl_init();
curl_setopt_array($curl, [
CURLOPT_URL => $url,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_CUSTOMREQUEST => 'POST',
CURLOPT_POSTFIELDS => $request,
]);
$response = curl_exec($curl);
curl_close($curl);
return json_decode($response, true);
}

function request($url, $data = array())
{
$url = 'https://online.moysklad.ru/api/remap/1.1/'. rtrim($url, '/');
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Basic login:password',
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
if (!empty($data)) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_POST, true);
}
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
return $data;
}

?>