<?php
/**
*
* @Name : WebCrawler/parse.php
* @Version : 1.0
* @Programmer : Max
* @Date : 2019-07-02
* @Released under : https://github.com/BaseMax/WebCrawler/blob/master/LICENSE
* @Repository : https://github.com/BaseMax/WebCrawler
*
**/
// Based on https://github.com/BaseMax/NetPHP
$debug=false;
// $debug=true;
$debug_details=true;
$debug_details=false;
function useragent() {
	return "Mozilla/5.0 (Windows NT 6.1; r…) Gecko/20100101 Firefox/60.0";
	return "Mozilla/5.0(Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36(KHTML,like Gecko) curlrome/68.0.3440.106 Mobile Safari/537.36";
}
function get_headers_from_curl_response($headerContent) {
	$headers = array();
	$arrRequests = explode("\r\n\r\n",$headerContent);
	for($index = 0; $index < count($arrRequests) -1; $index++) {
		foreach(explode("\r\n",$arrRequests[$index]) as $i => $line) {
			if($i === 0)
				$headers[$index]['http_code'] = $line;
			else {
				list($key,$value) = explode(': ',$line);
				$headers[$index][$key] = $value;
			}
		}
	}
	return $headers;
}
function post($url,$values,$headers=[],$reffer="",$auto_redirect=true) {
	global $debug,$debug_details;
	if($debug) {
		print "@Request[POST]----------------------------------------------\n";
		print "----------@link ".$url."\n";
		if($debug_details) {
			if($reffer!="") {
				print "----------@Reffer\n";
				print $reffer;
				print "\n";
			}
			if(count($values)!=0) {
				print "----------@Values\n";
				print_r($values);
			}
			if(count($headers)!=0) {
				print "----------@Headers\n";
				print_r($headers);
			}
		}
	}
	$curl = curl_init($url);
	if(is_array($headers)) {
		curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
	}
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,true);
	curl_setopt($curl,CURLOPT_FOLLOWLOCATION,$auto_redirect);
	curl_setopt($curl,CURLOPT_HEADER,true);
	curl_setopt($curl,CURLOPT_VERBOSE,false);
	curl_setopt($curl,CURLOPT_POST,true);
	curl_setopt($curl,CURLOPT_POSTFIELDS,$values);
	curl_setopt($curl,CURLOPT_USERAGENT,useragent());
	curl_setopt($curl,CURLOPT_COOKIEJAR,"_cookies.txt");
	curl_setopt($curl,CURLOPT_COOKIEFILE,"_cookies.txt");
	if($reffer != "")
		curl_setopt($curl,CURLOPT_REFERER,$curl);
	$response = curl_exec($curl);
	$header_size = curl_getinfo($curl,CURLINFO_HEADER_SIZE);
	$header = substr($response,0,$header_size);
	$body = substr($response,$header_size);
	curl_close($curl);
	if($debug && $debug_details) {
		print "----------@Response Headers\n";
		print_r($header);
		print "----------@Response Body\n";
		print_r($body);
		print "\n";
	}
	return [$body,$header];
}
function get($url,$headers=[],$reffer="",$auto_redirect=true)
{
	global $debug,$debug_details;
	if($debug) {
		print "@Request[GET]----------------------------------------------\n";
		print "----------@link ".$url."\n";
		if($debug_details) {
			if($reffer!="") {
				print "----------@Reffer\n";
				print $reffer;
				print "\n";
			}
			if(count($headers)!=0) {
				print "----------@Headers\n";
				print_r($headers);
			}
		}
	}
	$curl = curl_init($url);
	if(is_array($headers)) {
		curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
	}
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,true);
	curl_setopt($curl,CURLOPT_FOLLOWLOCATION,$auto_redirect);
	curl_setopt($curl,CURLOPT_HEADER,true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl,CURLOPT_VERBOSE,false);
	curl_setopt($curl,CURLOPT_POST,false);
	curl_setopt($curl,CURLOPT_USERAGENT,useragent());
	curl_setopt($curl,CURLOPT_COOKIEJAR,"_cookies.txt");
	curl_setopt($curl,CURLOPT_COOKIEFILE,"_cookies.txt");
	if($reffer != "")
		curl_setopt($curl,CURLOPT_REFERER,$curl);
	$response = curl_exec($curl);
	$header_size = curl_getinfo($curl,CURLINFO_HEADER_SIZE);
	$header = substr($response,0,$header_size);
	$body = substr($response,$header_size);
	curl_close($curl);
	if($debug && $debug_details) {
		print "----------@Response Headers\n";
		print_r($header);
		print "----------@Response Body\n";
		print_r($body);
		print "\n";
	}
	return [$body,$header];
}
////////////////////////////////////////////////////////
function random() {
	// like Math.random() in JS
	return(float)rand()/(float)getrandmax();
}
function store_token($response) {
	preg_match_all("/name=\"\_token\" value=\"(?<token>[^\"]+)\"/i",$response,$tokens);
	if(!isset($tokens["token"][0])) {
		exit("No Token value in login page!\n");
	}
	return $tokens["token"][0];
}
function get_parse($content,$name) {
	//name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value=""
	preg_match('/name="'.$name.'" id="'.$name.'" value="(?<value>[^\"]+)"/i',$content,$values);
	// print_r($values);
	$value=$values["value"];
	$value=str_replace("-","+",$value);
	$value=str_replace("_","/",$value);
	return $value;
}
$from="https://global-stock-exchange.com/ReportList.aspx?search&LetterType=6&Isic=441118&AuditorRef=-1&PageNumber=1&Audited&NotAudited&IsNotAudited=false&Childs&Mains&Publisher=false&CompanyState=0&Category=-1&CompanyType=1&Consolidatable&NotConsolidatable&Symbol=";
$url="https://search.global-stock-exchange.com/api/search/v2/q?&Audited=true&AuditorRef=-1&Category=-1&Childs=true&CompanyState=0&CompanyType=1&Consolidatable=true&IsNotAudited=false&Isic=441118&Length=-1&LetterType=6&Mains=true&NotAudited=true&NotConsolidatable=true&PageNumber=1&Publisher=false&TracingNo=-1&search=true&Symbol=";
$_items=[];
$namesList="List of the symbols...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
...
[More...]";
$names=explode("\n", $namesList);
// print_r($names);
$index=1;
$headers=[
	// "Cache-Control"=>"no-cache",
	// "Connection"=>"keep-alive",
	// "Content-Encoding"=>"gzip",
	// "Content-Length"=>"2425",
	// "Content-Type"=>"application/xml; charset=utf-8",
	// "Expires"=>"-1",
	// "Pragma"=>"no-cache",
	// "Vary"=>"Accept-Encoding",
	"Host"=>"search.global-stock-exchange.com",
	"Upgrade-Insecure-Requests"=>1,
];
function check($address) {
	$response=get($address);
	$content=$response[0];

	// preg_match('/ctl00_lblListedCapital" class="label" style="color:#C04000;">(?<value>[^\<]+)<\/span>/i', $response[0], $value);
	// print_r($value["value"]);

	preg_match('/ctl00_lblYearEndToDate" class="label" style="color:#C04000;"><bdo dir="ltr">(?<value>[^\<]+)<\/bdo>/i', $response[0], $value);
	// print_r($value["value"]);
	$date=$value["value"];

	// preg_match('/ctl00_cphBody_ucSFinancialPosition_grdSFinancialPosition_ctl21_txbLiabilityYear0" class="Hidden" onkeypress="return ForceNumber\(event, &#39;int&#39;\);" style="width:80%;">\s*<span class="spanFormattedValue">(?<value>[^\<]+)<\/span>/i', $response[0], $value);
	// // print_r($value["value"]);
	// $sarmaye1=$value["value"];


	// preg_match('/ctl00_cphBody_ucSFinancialPosition_grdSFinancialPosition_ctl21_txbLiabilityYear1" class="Hidden" onkeypress="return ForceNumber\(event, &#39;int&#39;\);" style="width:80%;">\s*<span class="spanFormattedValue">(?<value>[^\<]+)<\/span>/i', $response[0], $value);
	// // print_r($value["value"]);
	// $sarmaye2=$value["value"];


	// preg_match('/ctl00_cphBody_ucSFinancialPosition_grdSFinancialPosition_ctl08_txbLiabilityYear0" class="Hidden" onkeypress="return ForceNumber\(event, &#39;int&#39;\);" style="width:80%;">\s*<span class="spanFormattedValue">(?<value>[^\<]+)<\/span>/i', $response[0], $value);
	// // print_r($value["value"]);
	// $tashilat1=$value["value"];


	// preg_match('/ctl00_cphBody_ucSFinancialPosition_grdSFinancialPosition_ctl08_txbLiabilityYear1" class="Hidden" onkeypress="return ForceNumber\(event, &#39;int&#39;\);" style="width:80%;">\s*<span class="spanFormattedValue">(?<value>[^\<]+)<\/span>/i', $response[0], $value);
	// // print_r($value["value"]);
	// $tashilat2=$value["value"];


	// preg_match('/ctl00_cphBody_ucSFinancialPosition_grdSFinancialPosition_ctl17_txbAssetYear0" class="Hidden" onkeypress="return ForceNumber\(event, &#39;int&#39;\);" style="width:80%;">\s*<span class="spanFormattedValue">(?<value>[^\<]+)<\/span>/i', $response[0], $value);
	// // print_r($value["value"]);
	// $darayysabetmash1=$value["value"];


	// // preg_match('/ctl00_cphBody_ucSFinancialPosition_grdSFinancialPosition_ctl16_txbAssetYear1" class="Hidden" onkeypress="return ForceNumber\(event, &#39;int&#39;\);" style="width:80%;">\s*<span class="spanFormattedValue">(?<value>[^\<]+)<\/span>/i', $response[0], $value);
	// // print_r($value["value"]);


	// preg_match('/ctl00_cphBody_ucSFinancialPosition_grdSFinancialPosition_ctl31_txbLiabilityYear0" class="Hidden" onkeypress="return ForceNumber\(event, &#39;int&#39;\);" style="width:80%;">\s*<span class="spanFormattedValue">(?<value>[^\<]+)<\/span>/i', $response[0], $value);
	// // print_r($value["value"]);
	// $sodeziananbash1=$value["value"];


	// preg_match('/ctl00_cphBody_ucSFinancialPosition_grdSFinancialPosition_ctl31_txbLiabilityYear1" class="Hidden" onkeypress="return ForceNumber\(event, &#39;int&#39;\);" style="width:80%;">\s*<span class="spanFormattedValue">(?<value>[^\<]+)<\/span>/i', $response[0], $value);
	// // print_r($value["value"]);
	// $sodeziananbash2=$value["value"];



	// preg_match('/ctl00_cphBody_ucSFinancialPosition_grdSFinancialPosition_ctl32_txbLiabilityYear0" class="Hidden" onkeypress="return ForceNumber\(event, &#39;int&#39;\);" style="width:80%;">\s*<span class="spanFormattedValue">(?<value>[^\<]+)<\/span>/i', $response[0], $value);
	// // print_r($value["value"]);
	// $jamhoghoogh1=$value["value"];


	// preg_match('/ctl00_cphBody_ucSFinancialPosition_grdSFinancialPosition_ctl32_txbLiabilityYear1" class="Hidden" onkeypress="return ForceNumber\(event, &#39;int&#39;\);" style="width:80%;">\s*<span class="spanFormattedValue">(?<value>[^\<]+)<\/span>/i', $response[0], $value);
	// // print_r($value["value"]);
	// $jamhoghoogh2=$value["value"];

	preg_match('/<option\s*(?<selected>selected\=\"selected\"|)\s*value\=\"(?<id>[0-9]+)\"\>ترازنامه(\s*|)\n/i', $content, $sheets);
	// print_r($sheets);
	if(isset($sheets["selected"]) and $sheets["selected"] == "" && isset($sheets["id"])) {
		$address.="&sheetid=". $sheets["id"];
		$response=get($address);
		$content=$response[0];
	}

	print $address;
	print "\n";

	print "date: " . $date . "\n";

	$sarmaye1=detect($content, ["سرمایه"], 1);
	$sarmaye2=detect($content, ["سرمایه"], 2);
	print "sarmaye1: " . $sarmaye1 . "\n";
	print "sarmaye2: " . $sarmaye2 . "\n";

	$tashilat1=detect($content, ["تسهیلات مالی", "تسهیلات مالی دریافتی"], 1);
	$tashilat2=detect($content, ["تسهیلات مالی", "تسهیلات مالی دریافتی"], 2);
	print "tashilat1: " . $tashilat1 . "\n";
	print "tashilat2: " . $tashilat2 . "\n";

	$darayysabetmash1=detect($content, ["دارایی‌های ثابت مشهود", "داراییهای ثابت مشهود"], 1);
	print "darayysabetmash1: " . $darayysabetmash1 . "\n";

	$sodeziananbash1=detect($content, ["سود (زیان) انباشته"], 1);
	$sodeziananbash2=detect($content, ["سود (زیان) انباشته"], 2);
	print "sodeziananbash1: " . $sodeziananbash1 . "\n";
	print "sodeziananbash2: " . $sodeziananbash2 . "\n";

	$jamhoghoogh1=detect($content, ["جمع حقوق صاحبان سهام"], 1);
	$jamhoghoogh2=detect($content, ["جمع حقوق صاحبان سهام"], 2);
	print "jamhoghoogh1: " . $jamhoghoogh1 . "\n";
	print "jamhoghoogh2: " . $jamhoghoogh2 . "\n";

	print "---------------------------------------\n";
}
function key_check($name) {
	$name=str_replace("(", "\\(", $name);
	$name=str_replace(")", "\\)", $name);
	return $name;
}
// Maybe useful...
function fa_en_numeric($string) {
	return strtr($string, array('۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4', '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9', '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4', '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9'));
}
function number_check($value) {
	if($value == null || $value == "") {
		print("Error!\n");
		return "None";
	}
	$value=fa_en_numeric($value);
	// print "###" . $value[0] . ", " . $value[mb_strlen($value)-1] . "\n";
	if($value[0] == "(" && $value[mb_strlen($value)-1] == ")") {
		// print "\n====>".$value."\n---->";
		$value="-".mb_substr($value, 1, -1);
		// print $value."\n";
	}
	$value=str_replace(",", "", $value);
	return $value;
}
function detect($content, $keys, $year=1) {
	$name="(";
	if(is_array($keys)) {
		$length=count($keys);
		if($length == 1) {
			$name=key_check($keys[0]);
		}
		else {
			foreach($keys as $index=>$key) {
				$name.=key_check($key)."|";
				// $name.=$key;
				// if($index+1 != $length) {
				// 	$name.="|";
				// }
			}
			$name=rtrim($name, "|");
			$name.=")";
		}
	}
	else {
		$name=key_check($keys);
	}
	// print $name."\n";
	// exit();
	$regex1='>\s*'.$name.'\s*<\/span>\s*<\/td><td[^\>]+>\s*<([^\>]+)>\s*<span class="spanFormattedValue">(?<value>[^\<]+)<\/span>';
	if($year == 2) {
		$regex1.='\s*<\/td><td[^\>]+>\s*<([^\>]+)>\s*<span class="spanFormattedValue">(?<value2>[^\<]+)<\/span>';
		// print $regex1."\n";
		preg_match('/'.$regex1.'/i', $content, $value);
		// print_r($value);
		if(!isset($value["value2"]) || $value["value2"] == null || $value["value2"] == "") {
			$value["value2"]=null;
		}
		return number_check($value["value2"]);
	}
	else {
		preg_match('/'.$regex1.'/i', $content, $value);
		// print $regex1."\n";
		// print_r($value);
		if(!isset($value["value"]) || $value["value"] == null || $value["value"] == "") {
			$value["value"]=null;
		}
		return number_check($value["value"]);
	}
}
foreach($names as $name) {
	get("https://global-stock-exchange.com/ReportList.aspx?search&LetterType=-1&AuditorRef=-1&PageNumber=1&Audited&NotAudited&IsNotAudited=false&Childs&Mains&Publisher=false&CompanyState=-1&Category=-1&CompanyType=-1&Consolidatable&NotConsolidatable");
	sleep(2);
	// print $url.urlencode($name)."\n";
	$response=get($url.urlencode($name), $headers, $from.urlencode($name));
	$json=json_decode($response[0], true);
	// print_r($json);
	if(isset($json["Letters"])) {
		$items=$json["Letters"];
		// print_r($items[0]);
		print $name ."\n";
		if(isset($items[0])) {
			$address="https://global-stock-exchange.com". $items[0]["Url"];
			// print $address;
			// print "\n";
			check($address);
		}
		else {
			print "Error!\n";
		}
	}
	// print_r($response);
	// file_put_contents("test-".$index.".html", $response[0]);
	sleep(10);
	// break;
	// exit();
	$index++;
}
