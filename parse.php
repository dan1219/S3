<?php

function parsePage($html){
	$dom = new DOMDocument();
	libxml_use_internal_errors(true);
	$dom->loadHTML($html);
	libxml_clear_errors();

	$productObj = (object)null;

	createProductObj(getProductName($dom),'productName',$productObj);
	createProductObj(getSKU($dom),'sku',$productObj);
	createProductObj(getDescription($dom),'description',$productObj);
	createProductObj(getBrand($dom),'brand',$productObj);
	createProductObj(getProductAvailability($dom),'stock',$productObj);
	createProductObj(getCurrentPrice($dom),'currentPrice',$productObj);
	createProductObj(getOldPrice($dom),'oldPrice',$productObj);
	createProductObj(getWeight($dom),'weight',$productObj);
	createProductObj(getDimensions($dom),'dimensions',$productObj);
	createProductObj(getImagesUrls($html),'images',$productObj);

	return $productObj;

}

	function getProductName($dom){
		$className="product-title";
		$nodes = getElementByClassName($dom,$className);
		$item = $nodes->item(0);
		$headerArr = $item->getElementsByTagName('h1'); 
		$result = $headerArr->item(0)->nodeValue;
		return $result;
		
	}
	function getSKU($dom){
		$className="sku";
		$nodes = getElementByClassName($dom,$className);
		$item = $nodes->item(0);
		$spans = $item->getElementsByTagName('span'); 
		$sku = $spans->item(1);
		$result = $sku->nodeValue;
		return $result;
	}

	function getDescription($dom){
		$className = "description-product-content";
		$nodes = getElementByClassName($dom,$className);
		$item = $nodes->item(0);
		$result = $item->ownerDocument->saveXML($item);
		return $result;
	}

	function getBrand($dom){
		$className = "options";
		$nodes = getElementByClassName($dom,$className);
		$descriptionTab = $nodes->item(0);

		$content = $descriptionTab->lastElementChild;
		$option = $content->firstElementChild;
		$title = $option->firstElementChild->nodeValue;

		if ($title != 'Brand'){
			return;
		}

		$brand = $content->firstElementChild->firstElementChild->nextElementSibling->firstElementChild->firstElementChild->nodeValue;
		return $brand;
	}

	function getProductAvailability($dom){
		$className = "product-label-icon__out-of-stock";
		$nodes = getElementByClassName($dom,$className);
		if ($nodes->length > 0)
			return "Out of stock";
		return "In stock";
	}

	function getCurrentPrice($dom){
		$className =  "product-quantity-one-price";
		$nodes = getElementByClassName($dom,$className);
		$price = $nodes->item(0)->firstElementChild->nodeValue;
		return $price;
	}

	function getOldPrice($dom){
		$className = "product-quantity-old-price";
		$nodes = getElementByClassName($dom,$className);
		if ($nodes->length == 0)
			return;

		$oldPrice = $nodes->item(0)->firstElementChild->nodeValue;
		return $oldPrice;

	}

	function getWeight($dom){
		$className = "options";
		$nodes = getElementByClassName($dom,$className);

		if ($nodes->length < 2)
			return;

		$specsTab = $nodes->item(1);
		$content = $specsTab->lastElementChild;
		if ($content->childElementCount == 0)
			return;
		$option = $content->firstElementChild;
		$title = $option->firstElementChild->nodeValue;

		$title = mb_strtolower($title);
		if (!strpos($title, 'weight')){
			return;
		}
		$weight = $content->firstElementChild->firstElementChild->nextElementSibling->firstElementChild->nodeValue;
		return $weight;
	}

	function getDimensions($dom){
		$className = "options";
		$nodes = getElementByClassName($dom,$className);
		
		if ($nodes->length < 2)
			return;
		$specsTab = $nodes->item(1);

		$content = $specsTab->lastElementChild;
		if ($content->childElementCount < 2)
			return;
		$option = $content->firstElementChild->nextElementSibling;
		$title = $option->firstElementChild->nodeValue;

		$title = mb_strtolower($title);
		if (!strpos($title, 'dimensions')){
			return;
		}
		$dimensions = $content->firstElementChild->nextElementSibling->firstElementChild->nextElementSibling->firstElementChild->nodeValue;
		return $dimensions;
	}

	function getImagesUrls($html){
		$pattern = '/https:\/\/i\d.s3stores.com\/images\/\w+\/preview_\w+.(jpeg|png)/';
		if(preg_match_all($pattern, $html, $matches, PREG_PATTERN_ORDER)) {
			$resultArr = $matches[0];
			$resultArr =  array_unique($resultArr);
			return $resultArr;
		}else{
			echo "no";
		}
	}

	function getElementByClassName($dom,$className){
		$finder = new DomXPath($dom);
		$result = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");
		return $result;
	}

	function createProductObj($fieldValue,$fieldName,&$productObj){
		if ($fieldValue)
			$productObj -> {$fieldName} = $fieldValue;
	}

?>