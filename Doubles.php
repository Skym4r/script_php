<?php 
/*
Скрипт,который анализируют новых клиентов и если есть определенный дубль, то в кастомное поле, карточки клиента, ставится чекбокс
Из-за большого колличесива клиентов было созданно 2 таблицы клиентов исходный базы, по ним и будут проверятся данные новых клиентов. 
Если клиент есть в бд, с индетичным телефоном и почтой, то ставим пометку дубль. 
Если был найден клиент только с телефоном или только с почтой, то записываем в БД и ставим пометку дубль
Если не было найдено дубля то просто добавляем в БД.
*/

$host = 'localhost'; // адрес сервера/
$db_name = 'nameDB'; // имя базы данных
$user = 'login'; // имя пользователя
$password = 'password'; // пароль
// создание подключения к базе   
$connection = mysqli_connect($host, $user, $password, $db_name);

$apiUrl = 'utl_ratilCRM';
$apiKey = 'API_TOKEN_ratilCRM';
$id=$_GET['id'];
$order = ordersGet($apiUrl, $apiKey, $id)['orders'][0];
$name=$order['firstName'];//имя клиента из заказа для добавления в БД
$site=$order['site'];//магазин 
$customer=$order['customer']['id'];//айди клиента
$customers = customersList($apiUrl, $apiKey, $customer)['customers'][0];//запрос по клиенту
$phones=$customers['phones'];
$email=$customers['email'];//почта клиента
$number=[];
$k=0;
foreach($phones as $num){
   $number[]=$num['number'];//номера телнфона клиента
   $k++;
}


   
for($i=0;$i<$k;$i++)
{
//ищем клиента по номеру телефона и почты
   $query1 = "SELECT * FROM `TABLE 1` WHERE  `COL 2`='".$number[$i]."' AND `COL 1`='$email'" ;      
   $result1 = mysqli_query($connection, $query1);
   while($row1 = $result1->fetch_assoc())
   {
      $n1=$row1['index'];  
   }
   // если получили нул то результат отрицательный
   if(empty($n1))
   {
      $k1_ob=0;
   }
   // иначе положительный результат на нахождение клиента в бд
   if(isset($n1))
   {
   $k1_ob=1;
   }

}



//тоже самое проворачиваем со второй таблицей базы данных
for($i=0;$i<$k;$i++)
{
   //ищем клиента по номеру телефона и почты во второй таблице
   $query2 = "SELECT * FROM `TABLE 2` WHERE `COL 2`='".$number[$i]."' AND `COL 1`='$email'"; 
   $result2 = mysqli_query($connection, $query2);
   while($row2 = $result2->fetch_assoc())
   {
      $n2=$row2['index'];  
   }

   if(empty($n2))
   {
      $k3_ob=0;
   }
   if(isset($n2))
   {
      $k3_ob=1;
   }

}


for($i=0;$i<$k;$i++)
{
   //ищем клиента по номеру телефона 
   $query3 = "SELECT * FROM `TABLE 1` WHERE `COL 2`='".$number[$i]."'";      
   $result3 = mysqli_query($connection, $query3);  
   while($row3 = $result3->fetch_assoc())
   {
      $n3=$row3['index'];  
   }
   if(empty($n3))
   {
   $k_n1=0;
   }
   if(isset($n3))
   {
   $k_n1=1;
   }
}


for($i=0;$i<$k;$i++)
{
    //ищем клиента по номеру телефона  во второй таблице
   $query4 = "SELECT * FROM `TABLE 2` WHERE `COL 2`='".$number[$i]."'";      
   $result4 = mysqli_query($connection, $query4);
   while($row4 = $result4->fetch_assoc())
   {
      $n4=$row4['index'];  
   }
   if(empty($n4))
   {
   $k_n4=0;
   }
   if(isset($n4))
   {
   $k_n4=1;
   }
}

for($i=0;$i<$k;$i++)
{
   //ищем клиента по почте во второй таблице
   $query5 = "SELECT * FROM `TABLE 1` WHERE `COL 1`='".$email[$i]."'"; 
   $result5 = mysqli_query($connection, $query4);
   while($row5 = $result5->fetch_assoc())
   {
      $n4=$row4['index'];  
   }

   if(empty($n5))
   {  
   $k_e1=0;
   }
   if(isset($n5))
   {
   $k_e1=1;
   }
}

for($i=0;$i<$k;$i++)
{
      //ищем клиента по почте во второй таблице
   $query6 = "SELECT * FROM `TABLE 2` WHERE `COL 1`='".$email[$i]."'"; 
   $result6 = mysqli_query($connection, $query4);
   while($row6 = $result6->fetch_assoc())
   {
      $n6=$row6['index'];  
   }

   if(empty($n6))
   {
      $k_e6=0;
      }
   if(isset($n6))
   {
      $k_e6=1;
   }
}


if(($k1_ob==1)and ($k3_ob==0))
{
   echo "есть в базе данных";
}


if(($k1_ob==0)and ($k3_ob==1))
{
   echo "есть в базе данных";
}



if(($k1_ob==0)and ($k3_ob==0)and($k_n1==1)and($k_n3==0)and($k_e1==1)and($k_e3==0))
{
   echo "добавляем в базу данных и ставим дубль";
   for($i=0;$i<$k;$i++)
   {
      $sql = "INSERT INTO `TABLE 2` (`COL 1`, `COL 2`, `COL 3`) VALUES ( '".$email."','".$number[$i]."','".$name."')";
      if(mysqli_query($connection, $sql))
      {
         echo "Данные успешно добавлены";
      } 
      else
      {
      echo "Ошибка: " . mysqli_error($conn);
      }
   }
   $request = customersEdit($apiUrl, $apiKey, $order,  $id,  $site);
}



if(($k1_ob==0)and ($k3_ob==0)and($k_n1==1)and($k_n3==0)and($k_e1==0)and($k_e3==1))
{
   echo "добавляем в базу данных и ставим дубль";
   for($i=0;$i<$k;$i++)
   {
      $sql = "INSERT INTO `TABLE 2` (`COL 1`, `COL 2`, `COL 3`) VALUES ( '".$email."','".$number[$i]."','".$name."')";
      if(mysqli_query($connection, $sql))
      {
         echo "Данные успешно добавлены";
      } 
      else
      {
         echo "Ошибка: " . mysqli_error($conn);
      }
   }
$request = customersEdit($apiUrl, $apiKey, $order,  $id,  $site);
}


if(($k1_ob==0)and ($k3_ob==0)and($k_n1==1)and($k_n3==0)and($k_e1==0)and($k_e3==0))
{
      echo "добавляем в базу данных и ставим дубль";
      for($i=0;$i<$k;$i++)
      {
         $sql = "INSERT INTO `TABLE 2` (`COL 1`, `COL 2`, `COL 3`) VALUES ( '".$email."','".$number[$i]."','".$name."')";
         if(mysqli_query($connection, $sql))
         {
            echo "Данные успешно добавлены";
         } 
         else
         {
            echo "Ошибка: " . mysqli_error($conn);
         }
      }
$request = customersEdit($apiUrl, $apiKey, $order,  $id,  $site);
}



if(($k1_ob==0)and ($k3_ob==0)and($k_n1==0)and($k_n3==1)and($k_e1==1)and($k_e3==0))
{
   echo "добавляем в базу данных и ставим дубль";
   for($i=0;$i<$k;$i++)
   {
      $sql = "INSERT INTO `TABLE 2` (`COL 1`, `COL 2`, `COL 3`) VALUES ( '".$email."','".$number[$i]."','".$name."')";
      if(mysqli_query($connection, $sql))
      {
         echo "Данные успешно добавлены";
      } 
      else
      {
         echo "Ошибка: " . mysqli_error($conn);
      }
   }
$request = customersEdit($apiUrl, $apiKey, $order,  $id,  $site);
}




if(($k1_ob==0)and ($k3_ob==0)and($k_n1==0)and($k_n3==1)and($k_e1==0)and($k_e3==0))
{
   echo "добавляем в базу данных и ставим дубль";
   for($i=0;$i<$k;$i++)
   {
      $sql = "INSERT INTO `TABLE 2` (`COL 1`, `COL 2`, `COL 3`) VALUES ( '".$email."','".$number[$i]."','".$name."')";
      if(mysqli_query($connection, $sql))
      {
         echo "Данные успешно добавлены";
      } 
      else
      {
         echo "Ошибка: " . mysqli_error($conn);
      }
   }  
$request = customersEdit($apiUrl, $apiKey, $order,  $id,  $site);
}




if(($k1_ob==0)and ($k3_ob==0)and($k_n1==0)and($k_n3==1)and($k_e1==0)and($k_e3==1))
{
   echo "добавляем в базу данных и ставим дубль";
   for($i=0;$i<$k;$i++)
   {
      $sql = "INSERT INTO `TABLE 2` (`COL 1`, `COL 2`, `COL 3`) VALUES ( '".$email."','".$number[$i]."','".$name."')";
      if(mysqli_query($connection, $sql))
      {
      echo "Данные успешно добавлены";
      } 
      else
      {
      echo "Ошибка: " . mysqli_error($conn);
      }
}
$request = customersEdit($apiUrl, $apiKey, $order,  $id,  $site);
}






if(($k1_ob==0)and ($k3_ob==0)and($k_n1==0)and($k_n3==0)and($k_e1==0)and($k_e3==0))
{
   echo "добавляем в базу данных, новый клиент";
   for($i=0;$i<$k;$i++)
   {
      $sql = "INSERT INTO `TABLE 2` (`COL 1`, `COL 2`, `COL 3`) VALUES ( '".$email."','".$number[$i]."','".$name."')";
      if(mysqli_query($connection, $sql))
      {
         echo "Данные успешно добавлены";
      } 
      else
      {
      echo "Ошибка: " . mysqli_error($conn);
      }
   }
}


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

function customersEdit($apiUrl, $apiKey,  $id,  $site)
{
$url = "$apiUrl/api/v5/customers/$id/edit?apiKey=$apiKey";
$request = http_build_query([
'site' => $site,
'customFields'=>['est_dubl'=>true], 
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

?>