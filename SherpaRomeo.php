<?php
require_once('Zend/Http/Client.php');

class SherpaRomeo {
  private $pre;
  private $post;
  private $pdf;

  function __construct($query, $request_type = 'issn'){
    $query_string = 'versions=all&'.$request_type.'='.$query;
    $client = new Zend_HTTP_Client();
    $client->setUri('http://www.sherpa.ac.uk/romeo/api29.php?'.$query_string);
    $response = $client->request();

    if ($response->getStatus() == 200){
      $sr = $response->getBody();
      $sr_xml = new DOMDocument();
      $sr_xml->loadXML($sr);
      $this->pre = $sr_xml->getElementsByTagName('prearchiving')->item(0)->nodeValue;
      $this->post = $sr_xml->getElementsByTagName('postarchiving')->item(0)->nodeValue;
      $this->pdf = $sr_xml->getElementsByTagName('pdfarchiving')->item(0)->nodeValue;
    }
  }

  public function getPreArchiving(){
    return $this->pre;
  }

  public function getPostArchiving(){
    return $this->post;
  }

  public function getPdfArchiving(){
    return $this->pdf;
  }
}

?>
