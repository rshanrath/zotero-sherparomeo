<?php
//set_include_path(get_include_path() . PATH_SEPARATOR . '/path/to/Zend/library');
require_once('Zend/Http/Client.php');
require_once('SherpaRomeo.php');

if ((! isset($argv[1])) || (! isset($argv[2]))){
  echo "Not enough parameters\n";
  usage();
  exit;
}

$userid = $argv[1];
$zotero_key = $argv[2];
$sherpa_romeo_key = $argv[3];

$url_base = 'https://api.zotero.org/users/'.$userid.'/';
$client = new Zend_HTTP_Client();
$client->setUri($url_base.'items/?key='.$zotero_key.'&itemType=journalArticle&content=json');
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
  $jtitle = $content_json->publicationTitle;
 
  if ($issn || $jtitle){
    $sr_key = str_replace(" ", "", $issn.$jtitle);
    if (!isset($sr_responses[$sr_key])){
      $issn_hits = 0;
      if ($issn){
        $sr_data = new SherpaRomeo($issn, 'issn', $sherpa_romeo_key);
        if ($sr_data->getNumHits() > 0){
          $issn_hits = $sr_data->getNumHits();
          $sr_responses[$sr_key] = $sr_data;
        }
      }
      if ($issn_hits == 0){
        if ($jtitle){
          $sr_data = new SherpaRomeo($jtitle, 'jtitle', $sherpa_romeo_key);
          $sr_responses[$sr_key] = $sr_data;
        }
      }
    }else{
      $sr_data = $sr_responses[$sr_key];
    }

    $pubs = array_values($sr_data->getPublishers());
    if (count($pubs) == 1){
      $pub = $pubs[0];
      $content_json->tags[] = array('tag' => 'SherpaRomeo Pre '.$pub->getPreArchiving(), 'type' => 1);
      $content_json->tags[] = array('tag' => 'SherpaRomeo Post '.$pub->getPreArchiving(), 'type' => 1); 
      $content_json->tags[] = array('tag' => 'SherpaRomeo PDF '.$pub->getPdfArchiving(), 'type' => 1); 

      $client->setUri($url_base.'items/'.$item_key.'?key='.$zotero_key);
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
      echo "Couldn't find single publisher for ".$item_key."\n";
    }
  }else{
    echo "No ISSN or publicationTitle for ".$item_key."\n";
  }
}

function usage(){
  echo "\n";
  echo "zotero_sherpa.pbp [zotero_user] [zotero_key] [sherpa_romeo_key]";
  echo "\n";
}

?>
