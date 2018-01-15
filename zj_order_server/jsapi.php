<?php 
	ini_set('date.timezone','Asia/Shanghai');
	require_once "cache.php";

	header('content-type:application:json;charset=utf8');  
	header('Access-Control-Allow-Origin:*');  
	header('Access-Control-Allow-Methods:POST');  
	header('Access-Control-Allow-Headers:x-requested-with,content-type');  

	$order_type = $_POST['type'];
	$order_name = $_POST['name'];
	$order_content = $_POST['order'];

	function get_curr_time_section()  
	{  
		$checkDayStr = date('Y-m-d ',time());  
		$timeBegin1 = strtotime($checkDayStr."9:00".":00");  
		$timeEnd1 = strtotime($checkDayStr."11:15".":00");
		 
		$timeBegin2 = strtotime($checkDayStr."16:00".":00");  
		$timeEnd2 = strtotime($checkDayStr."17:30".":00");  
		 
		$curr_time = time();  
		 
		if(($curr_time >= $timeBegin1 && $curr_time <= $timeEnd1) || ($curr_time >= $timeBegin2 && $curr_time <= $timeEnd2))
		{  
			return 0;  
		}  
		  
		return -1;  
	}  
	if($order_type != 2)
	{
		//查询列表任何时间都可以
		$result = get_curr_time_section(); 
		if($result == -1)
		{
			echo "4".'|'."false";  
			return;
		}
	}
    
    
	
	setPayInfos($order_type,$order_name,$order_content);



	function setPayInfos($type,$name,$content)
	{	
		$response = "";
		//$response = $name.'|'.$bumen.'|'.$content;
		$cache = new Cache();  
		if($type == 1 || $type == 3){
			//提交
			$cacheData = $cache->get('orderList');
			$response = $name.'|'.$content;
			if($cacheData === FALSE){
				$cache->set('orderList', $response);
				$response = $type.'|'."false";
			}
			else
			{
				if(stripos($cacheData,$name)!== false)
				{
					if($type == 1)
					{
						$orders = explode('#',$cacheData);
						$newOrderData = "";
						for($i = 0;$i <count($orders); $i++)
						{ 
							$str = $orders[$i];
							if(stripos($str,$name)!== false)
							{
								$str = $response;
							}
							if($newOrderData == "")
							{
								$newOrderData = $str;
							}else{
								$newOrderData = $newOrderData.'#'.$str;
							}
						} 
						$cache->set('orderList', $newOrderData);
					}
					$response = $type.'|'."true";
				}
				else
				{
					$cache->set('orderList', $cacheData.'#'.$response);
					$response = $type.'|'."false";
				}
			}
			
		}
		else if($type == 2){
			//请求数据
			$response = $cache->get('orderList');
		}

		echo $response;
	}
?>

