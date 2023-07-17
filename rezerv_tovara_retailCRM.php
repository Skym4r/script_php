<?php 
/*Скрипт нужен для того чтобы сделать бронь и резервирование товаров, 
если остатков данного товара нет на складе, который выбран в заказе, 
то товар нужен бронировать в тот склад где больше всего остатков 
*/
$apiUrl = 'utl_ratilCRM';
$apiKey = 'API_TOKEN_ratilCRM';
$id=$_GET['id'];//получаем гет парметр с запроса 

$order=ordersGet($apiUrl, $apiKey, $id)['orders'][0];#Получаем информацию о заказе
$shipmentStore = $order['shipmentStore'];#склад отгрузки
$items=$order['items'];#массив товаров

foreach($items as $item) 
{
    $id=$item['id'];#id товара
    $xmlid=$item['offer']['xmlId'];#xmlId товара
    $externalId=$item['offer']['externalId'];#externalId товара
    $price=$item['prices']['0']['price'];#цена товара
    $quantity=$item['prices']['0']['quantity'];#колличество товара в зказе товара
    $store=stores($apiUrl, $apiKey, $xmlid, $id, $shipmentStore, $externalId, $price, $quantity);
}

function stores($apiUrl, $apiKey, $xmlid, $id, $shipmentStore, $externalId, $price, $quantity)
{
    $stores = storeInventories($apiUrl, $apiKey, $xmlid, $externalId);#ищем по определенному фильтру товар на складах
    $stores=$stores['offers'];
    foreach ($stores as $elementOffer) 
    {
        foreach ($elementOffer['stores'] as $store)
        {
            if ($store['store'] == $shipmentStore)//смотрим колличесво остатков на складе из заказа 
                {
                $s=$store['quantity'];
                }
            if ($store['store'] == 'Cклад1')//смотрим колличесво остатков на складе1
                {
                $s1=$store['quantity'];
                }
            if ($store['store'] == 'Cклад2')//смотрим колличесво остатков на складе2
                {
                $s2=$store['quantity'];
                }
            if ($store['store'] == 'Cклад3')//смотрим колличесво остатков на складе3
                {
                $s3=$store['quantity'];
                }
            if ($store['store'] == 'Cклад4')//смотрим колличесво остатков на складе4  
                {
                $s4=$store['quantity'];
                }
            if ($store['store'] == 'Cклад5')//смотрим колличесво остатков на складе5
                {
                $s5=$store['quantity'];
                }
            if ($store['store'] == 'Cклад6')//мотрим колличесво остатков на складе6 
                {
                $s6=$store['quantity'];
                }
        }       
    }
    //Сравниваем остатки на складах и выбираем самый наибольший склад
    if (($s >= $quantity)and($s>=$s1)and($s>=$s2)and($s>=$s3)and($s>=$s4)and($s>=$s5)and($s>=$s6))
    {
        $pack=packs($apiUrl, $apiKey, $id, $shipmentStore, $price, $quantity);
    }
    if (($s1 >= $quantity)and($s1>=$s)and($s1>=$s2)and($s1>=$s3)and($s1>=$s4)and($s1>=$s5)and($s1>=$s6))
    {
       $pack=packs($apiUrl, $apiKey, $id, 'Cклад1', $price, $quantity);
    }
    if (($s2 >= $quantity)and($s2>=$s)and($s2>=$s1)and($s2>=$s3)and($s2>=$s4)and($s2>=$s5)and($s2>=$s6))
    {
        $pack=packs($apiUrl, $apiKey, $id, 'Cклад2', $price, $quantity);
    }
    if (($s3 >= $quantity)and($s3>=$s)and($s3>=$s1)and($s3>=$s2)and($s3>=$s4)and($s3>=$s5)and($s3>=$s6))
    {
        $pack=packs($apiUrl, $apiKey, $id, 'Cклад3', $price, $quantity);
    }
    if (($s4 >= $quantity)and($s4>=$s)and($s4>=$s1)and($s4>=$s2)and($s4>=$s3)and($s4>=$s5)and($s4>=$s6))
    {
        $pack=packs($apiUrl, $apiKey, $id, 'Cклад4', $price, $quantity);
    }
    if (($s5 >= $quantity)and($s5>=$s)and($s5>=$s1)and($s5>=$s2)and($s5>=$s3)and($s5>=$s4)and($s5>=$s6))
    {
        $pack=packs($apiUrl, $apiKey, $id, 'Cклад5', $price, $quantity);
    }
    if (($s6 >= $quantity)and($s6>=$s)and($s6>=$s1)and($s6>=$s2)and($s6>=$s3)and($s6>=$s4)and($s6>=$s5))
    {
        $pack=packs($apiUrl, $apiKey, $id, 'Cклад6', $price, $quantity);
    }
}


function storeInventories($apiUrl, $apiKey, $xmlId, $externalId)
{
$url = "$apiUrl/api/v5/store/inventories?apiKey=$apiKey";
$request = http_build_query([
'offerXmlId' => $xmlId,
'offerExternalId' => $externalId,
'details' => 1
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



function ordersGet($apiUrl, $apiKey, $id)
{
$url = "$apiUrl/api/v5/orders?apiKey=$apiKey";
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



function packs($apiUrl, $apiKey, $id, $store, $price, $quantity)
{
$url = "$apiUrl/api/v5/orders/packs/create?apiKey=$apiKey";
$request = http_build_query([
'pack'=>json_encode([
    'quantity' => $quantity,
    'store' => $store,
    'purchasePrice'=>$initialPrice,
    'shipmentDate' => date('Y-m-d'),
    'itemId'=>$id])
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
          
?>
