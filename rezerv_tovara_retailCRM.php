<?php 
$apiUrl = 'url_retailCRM';
$apiKey = 'API_retailCRM';

$id=$id_GET['id'];
$order=ordersGet($apiUrl, $apiKey, $id)['orders'][0];
$site=$order['site'];
$shipmentStore = $order['shipmentStore'];
$n=0;
$items=$order['items'];
foreach($items as $item) {
    var_dump($item['prices']);
    var_dump($item['priceType']['code']);
    $stores = storeInventories($apiUrl, $apiKey, $item['offer']['xmlId'],$item['offer']['externalId']);
    $stores=$stores['offers'];
    foreach ($stores as $elementOffer) 
    {
        foreach ($elementOffer['stores'] as $store)
        {
            if ($store['store'] == $shipmentStore)
                {
                $offersQuantity[$item['offer']['xmlId']] = $store['quantity'];
                $s+=$store['quantity'];
                }
        }       
    }
    if (($s > 0)) 
        {
            $newOrder[$n] = ['id' => $item['id'],'status' => 'in-reserve', 'price'=>$item['prices'][0]['price'], 'priceType'=>['code'=>$item['priceType']['code']]];
        }
 
    if ($s == 0) 
    {
        foreach ($stores as $elementOffer) 
            {
                foreach ($elementOffer['stores'] as $store)
                    {
                        if ($store['store'] == 'SklP')
                            {
                                $offersQuantity[$item['offer']['xmlId']] = $store['quantity'];
                                $s1+=$store['quantity'];
                            }
                    }       
            }

        if($s1>0)
            {
               $newOrder[$n] = ['id' => $item['id'],'status' => 'est-na-sklade-a', 'price'=>$item['prices'][0]['price'], 'priceType'=>['code'=>$item['priceType']['code']]];
            }
            
        if($s1==0)
            {
            foreach ($stores as $elementOffer) 
                {
                    foreach ($elementOffer['stores'] as $store)
                        {
                        if ($store['store'] == 'gag')
                            {
                                $offersQuantity[$item['offer']['xmlId']] = $store['quantity'];
                                $s2+=$store['quantity'];
                            }
                        }       
                }
                //var_dump($s2);
                if($s2>0)
                {
                    $newOrder[$n] = ['id' => $item['id'],'status' => 'est_na_skalde_gagar', 'price'=>$item['prices'][$n]['price'], 'priceType'=>['code'=>$item['priceType']['code']]];      
                    
                }
                if($s2==0)
                {
                    foreach ($stores as $elementOffer) 
                        {
                            foreach ($elementOffer['stores'] as $store)
                            {
                                if ($store['store'] == 'len')
                                    {
                                    $offersQuantity[$item['offer']['xmlId']] = $store['quantity'];
                                    $s3+=$store['quantity'];
                                    }
                            }       
                        }
                    if($s3>0)
                    {
                        $newOrder[$n] = ['id' => $item['id'],'status' => 'lenina', 'price'=>$item['prices'][$n]['price'], 'priceType'=>['code'=>$item['priceType']['code']]]; 
                        }
                    if($s3==0)
                    {
                        foreach ($stores as $elementOffer) 
                        {
                                foreach ($elementOffer['stores'] as $store)
                                {
                                
                                    if ($store['store'] == 'bek')
                                    {
                                    $offersQuantity[$item['offer']['xmlId']] = $store['quantity'];
                                    $s4+=$store['quantity'];
                                    }
                                }       
                        }
                        if($s4>0)
                        {                          
                           $newOrder[$n] = ['id' => $item['id'],'status' => 'bek', 'price'=>$item['prices'][$n]['price'], 'priceType'=>['code'=>$item['priceType']['code']]]; 
                        }
                        if($s4==0)
                        {
                            foreach ($elementOffer['stores'] as $store)
                                {
                                if ($store['store'] == 'dze')
                                    {
                                    $offersQuantity[$item['offer']['xmlId']] = $store['quantity'];
                                    $s5+=$store['quantity'];
                                    }
                                }
                            if($s5>0)
                                {                
                                    $newOrder[$n] = ['id' => $item['id'],'status' => 'dzerzh', 'price'=>$item['prices'][$n]['price'], 'priceType'=>['code'=>$item['priceType']['code']]]; 
                                } 
                            if($s5==0)
                            {
                                    foreach ($elementOffer['stores'] as $store)
                                        {
                                        if ($store['store'] == 'dru')
                                            {
                                                $offersQuantity[$item['offer']['xmlId']] = $store['quantity'];
                                                $s6+=$store['quantity'];
                                            }
                                        }
                                    if($s6>0)
                                        {
                                            $newOrder[$n] = ['id' => $item['id'],'status' => 'druzh', 'price'=>$item['prices'][$n]['price'], 'priceType'=>['code'=>$item['priceType']['code']]]; 
                                        } 
                               
                                
                            }    
                        }
                    }
                }
            }

            
    }
    $n++;       
    unset($s);
    unset($s1);
    unset($s2);
    unset($s3);
    unset($s4);
    unset($s5);
    unset($s6);
    

}
$ord=['items'=>$newOrder,'id'=>$id];
$response=ordersEdit( $apiUrl, $apiKey, $ord, $id, $site);



function ordersGet(string $apiUrl, string $apiKey, int $id): array
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


function storeInventories(string $apiUrl, string $apiKey, string $xmlId, string $externalId): array
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


function ordersEdit(string $apiUrl, string $apiKey, array $order, int $id,  string $site)
{
$url = "$apiUrl/api/v5/orders/$id/edit?apiKey=$apiKey";
$request = http_build_query([
'site' => $site,
'order' =>  json_encode($order),
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