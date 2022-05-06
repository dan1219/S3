<?php
include "parse.php";
ini_set('max_execution_time', 2000);
ini_set('memory_limit', '-1');

$pagesCount = 1;
//$pagesCount = getPagesCount(); //разкомментировать для получения всех товаров 
 
$productsInfoArr = []; 

 $urls = [];
for ($i=1;$i<=$pagesCount;$i++){
	$urls[] = 'https://www.electronictoolbox.com/api/category/83118/new-products/?page=' . $i;
}

$from = 0;
$offset = 20; 
$fullCycle = false;
$wasLastIteration = false;
do{
	$urlArr = array_slice($urls,$from,$offset);
	if (!$wasLastIteration){
		if ($from + $offset >= count($urls))
			$wasLastIteration = true;
		$from += $offset;
		
	}else
		$fullCycle = true;

	if (!$fullCycle)
		makeRequest($urlArr,$productsInfoArr); 
	
}while(!$fullCycle);

$json = json_encode(array('products' => $productsInfoArr),JSON_UNESCAPED_SLASHES);

if (file_put_contents("feed.json", $json))
    echo "Файл создан";
else 
    echo "Ошибка создания файла";


function getPagesCount(){
	$url = "https://www.electronictoolbox.com/api/category/83118/new-products/?page=1";
	$handle = curl_init();

	$headers = array(
	   "x-requested-with: XMLHttpRequest",
	   "Content-type: application/json",
	);

	curl_setopt($handle, CURLOPT_URL, $url);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);

	$data = curl_exec($handle);
	curl_close($handle);

	$firstPage = json_decode($data,true);
	$pagesCount = $firstPage['pager']['pagesCount']; 

	return $pagesCount;
}

function makeRequest($urlArr,&$productsInfoArr){
	$headers = array(
	   "x-requested-with: XMLHttpRequest",
	);

	$multiCurl = [];
	$result = [];
	$mh = curl_multi_init();

	foreach ($urlArr as $i => $id) {
	  $fetch_url = $id;
	  $multiCurl[$i] = curl_init();
	  curl_setopt($multiCurl[$i], CURLOPT_URL,$fetch_url);
	  curl_setopt($multiCurl[$i], CURLOPT_HTTPHEADER, $headers);
	  curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER,1);
	  curl_multi_add_handle($mh, $multiCurl[$i]);
	}

	do {
	    $status = curl_multi_exec($mh, $active);
	    if ($active) {
	        curl_multi_select($mh);
	    }
	} while ($active && $status == CURLM_OK);

	foreach($multiCurl as $k => $ch) {
	  $result[$k] = curl_multi_getcontent($ch);
	  curl_multi_remove_handle($mh, $ch);
	}
	curl_multi_close($mh);

	foreach ($result as $page) {
		$productArr = json_decode($page,true);
		$products = $productArr['items']; 
		
		$productUrls = [];
		foreach ($products as $elem) {
			$productUrls[] = 'https://www.electronictoolbox.com'.$elem['url'];
		}

		getHtml($productUrls,$productsInfoArr);

	}
	
}

function getHtml($urlArr,&$productsInfoArr){
	$headers = array(
	   "x-requested-with: XMLHttpRequest",
	);

	$multiCurl = [];
	$result = [];
	$mh = curl_multi_init();

	foreach ($urlArr as $i => $id) {
	  $fetch_url = $id;
	  $multiCurl[$i] = curl_init();
	  curl_setopt($multiCurl[$i], CURLOPT_URL,$fetch_url);
	  curl_setopt($multiCurl[$i], CURLOPT_HTTPHEADER, $headers);
	  curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER,1);
	  curl_multi_add_handle($mh, $multiCurl[$i]);
	}

	do {
	    $status = curl_multi_exec($mh, $active);
	    if ($active) {
	        curl_multi_select($mh);
	    }
	} while ($active && $status == CURLM_OK);

	foreach($multiCurl as $k => $ch) {
	  $result[$k] = curl_multi_getcontent($ch);
	  curl_multi_remove_handle($mh, $ch);
	}
	curl_multi_close($mh);

	foreach ($result as $html) {
		$productsInfoArr[] = parsePage($html);
	}
}

?>








