<?php

ini_set('display_errors', true);

$_baseUrl = '../../../';
require('../../../libs/includes/ajax.utils.inc.php');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://djerem.com/admin/plugins/galeries/ajax/fbsynchro.ajax.php?json=true&master=true');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 300);

$contents = curl_exec($ch);
curl_close($ch);

echo $contents;
$todo = json_decode($contents, true);
print_r($todo);
$i = 0;
foreach($todo as $todoinst) {
	
	$continue = false;	
	foreach($todoinst as $todoel) {
		
		if(!empty($todoel['create'])) {
			if($todoel['create'])
				$continue = true;
			else
				$continue = false;
		}
		
		if($continue)
			continue;
		
		$i++;
		if($i > 10) {
			echo 'BREAK';
			return;
		}
		
		$todoel['master'] = true;
		$queryStr = http_build_str($todoel);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://djerem.com/admin/plugins/galeries/ajax/fbsynchroelement.ajax.php?' . $queryStr);
		echo 'http://djerem.com/admin/plugins/galeries/ajax/fbsynchroelement.ajax.php?' . $queryStr;
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		
		$contents = curl_exec($ch);
		curl_close($ch);		
	}

}


?>