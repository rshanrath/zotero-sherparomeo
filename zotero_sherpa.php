<?php
#set_include_path(get_include_path() . PATH_SEPARATOR . '/path/to/Zend/library');
require_once('Zend/Http/Client.php');
require_once('SherpaRomeo.php');

if ((! isset($argv[1])) || (! isset($argv[2]))){
  echo "Not enough parameters\n";
  usage();
  exit;
}

$userid = $argv[1];
$key = $argv[2];

$url_base = 'https://api.zotero.org/users/'.$userid.'/';
$client = new Zend_HTTP_Client();
$client->setUri($url_base.'items/?key='.$key.'&itemType=journalArticle&content=json');
$response = $client->request();

if (200 != $response->getStatus()){
  echo "Failed to get items (".$response->getStatus().")\n";
  exit;
}

$items = $response->getBody();
$items_xml = new DOMDocument();
$items_xml->loadXML($items);
$item_list = $items_xml->getElementsByTagName('entry');
$sr_responses = array();

foreach ($item_list as $item){

  $content = $item->getElementsByTagName('content')->item(0);
  $content_json = json_decode($content->nodeValue);

  $etag = $content->getAttribute('zapi:etag');

  $item_key = $item->getElementsByTagNameNS('http://zotero.org/ns/api', 'key')->item(0)->nodeValue;
  $issn = $content_json->ISSN;
 
  if ($issn){
    if (!isset($sr_responses[$issn])){
      $sr_data = new SherpaRomeo($issn);
      $sr_responses[$issn] = $sr_data;
    }else{
      $sr_data = $sr_responses[$issn];
    }

    $content_json->tags[] = array('tag' => 'SherpaRomeo Pre '.$sr_data->getPreArchiving(), 'type' => 1);
    $content_json->tags[] = array('tag' => 'SherpaRomeo Post '.$sr_data->getPreArchiving(), 'type' => 1); 
    $content_json->tags[] = array('tag' => 'SherpaRomeo PDF '.$sr_data->getPdfArchiving(), 'type' => 1); 

    $client->setUri($url_base.'items/'.$item_key.'?key='.$key);
    $client->setHeaders('Content-Type', 'application/json');
    $client->setHeaders('If-Match', $etag);
    $client->setRawData(json_encode($content_json));
    $update_response = $client->request('PUT');

    if (200 == $update_response->getStatus()){
      echo "Update successful for ".$item_key."\n";
    }else{
      echo "Update failed (".$update_response->getStatus().") for ".$item_key."\n";
    }
  }else{
    echo "No ISSN for ".$item_key."\n"; 
  }
}

function usage(){
  echo "\n";
  echo "zotero_sherpa.pbp [zotero_user] [zotero_key]";
  echo "\n";
}

?>
