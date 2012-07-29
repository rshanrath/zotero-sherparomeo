<?php
require_once('Zend/Http/Client.php');
require_once('Publisher.php');


class SherpaRomeo {
  private $issn;
  private $jtitle;
  private $publishers = array();
  private $num_hits = 0;

  function __construct($query, $request_type = 'issn', $sr_api_key = ''){
    $query_string = 'versions=all&'.$request_type.'='.urlencode($query);
    if ($sr_api_key){
      $query_string .= '&ak='.$sr_api_key;
    }
    $client = new Zend_HTTP_Client();
    $client->setUri('http://www.sherpa.ac.uk/romeo/api29.php?'.$query_string);
    $response = $client->request();

    if ($response->getStatus() == 200){
      $sr = $response->getBody();
      $sr_xml = new DOMDocument();
      $sr_xml->loadXML($sr);
      $this->num_hits = $sr_xml->getElementsByTagName('numhits')->item(0)->nodeValue;
      $this->issn = $sr_xml->getElementsByTagName('issn')->item(0)->nodeValue;
      $this->jtitle = $sr_xml->getElementsByTagName('jtitle')->item(0)->nodeValue;

      $pubs = $sr_xml->getElementsByTagName('publisher');
      for ($i = 0; $i < $pubs->length; $i++){
        $id = $pubs->item($i)->getAttribute('id');
        $name = $pubs->item($i)->getElementsByTagName('name')->item(0)->nodeValue;

        $p = new Publisher($id, $name);
        $p->setPrearchiving($pubs->item($i)->getElementsByTagName('prearchiving')->item(0)->nodeValue);
        $p->setPostarchiving($pubs->item($i)->getElementsByTagName('postarchiving')->item(0)->nodeValue);
        $p->setPdfarchiving($pubs->item($i)->getElementsByTagName('pdfarchiving')->item(0)->nodeValue);

        $prerestrictions_xml = $pubs->item($i)->getElementsByTagName('prerestriction');
        $prerestrictions = array();
        for ($j = 0; $j < $prerestrictions_xml->length; $j++){
          $prerestrictions[] = strip_tags(trim($prerestrictions_xml->item($j)->nodeValue));
        }
        $p->setPrerestrictions($prerestrictions);

        $postrestrictions_xml = $pubs->item($i)->getElementsByTagName('postrestriction');
        $postrestrictions = array();
        for ($j = 0; $j < $postrestrictions_xml->length; $j++){
          $postrestrictions[] = strip_tags(trim($postrestrictions_xml->item($j)->textContent));
        }
        $p->setPostrestrictions($postrestrictions);

        $pdfrestrictions_xml = $pubs->item($i)->getElementsByTagName('pdfrestriction');
        $pdfrestrictions = array();
        for ($j = 0; $j < $pdfrestrictions_xml->length; $j++){
          $pdfrestrictions[] = strip_tags(trim($pdfrestrictions_xml->item($j)->nodeValue));
        }
        $p->setPdfrestrictions($pdfrestrictions);

        $conditions_xml = $pubs->item($i)->getElementsByTagName('condition');
        $conditions = array();
        for ($j = 0; $j < $conditions_xml->length; $j++){
          $conditions[] = $conditions_xml->item($j)->nodeValue;
        }
        $p->setConditions($conditions);

        $this->publishers[$id] = $p;
      }
    }
  }
  
  public function getPublishers() {
    return $this->publishers;
  }

  public function setPublishers($publishers) {
    $this->publishers = $publishers;
  }

  public function getIssn(){
    return $this->issn;
  }

  public function getNumHits(){
    return $this->num_hits;
  }
}

?>
