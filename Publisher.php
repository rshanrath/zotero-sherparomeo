<?php

/**
 * Holds Publisher Info
 *
 * @author scotthanrath
 */
class Publisher {
  private $id;
  private $name;
  
  private $prearchving;
  private $postarchiving;
  private $pdfarchiving;

  private $prerestrictions = array();
  private $postrestrictions = array();
  private $pdfrestrictions = array();

  private $conditions = array();
  
  function __construct($id, $name) {
    $this->id = $id;
    $this->name = $name;
  }

  public function getId() {
    return $this->id;
  }

  public function setId($id) {
    $this->id = $id;
  }

  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
  }

  public function getPrearchiving() {
    return $this->prearchiving;
  }

  public function setPrearchiving($prearchiving) {
    $this->prearchiving = $prearchiving;
  }

  public function getPostarchiving() {
    return $this->postarchiving;
  }

  public function setPostarchiving($postarchiving) {
    $this->postarchiving = $postarchiving;
  }

  public function getPdfarchiving() {
    return $this->pdfarchiving;
  }

  public function setPdfarchiving($pdfarchiving) {
    $this->pdfarchiving = $pdfarchiving;
  }

  public function getPrerestrictions() {
    return $this->prerestrictions;
  }

  public function setPrerestrictions($prerestrictions) {
    $this->prerestrictions = $prerestrictions;
  }

  public function getPostrestrictions() {
    return $this->postrestrictions;
  }

  public function setPostrestrictions($postrestrictions) {
    $this->postrestrictions = $postrestrictions;
  }

  public function getPdfrestrictions() {
    return $this->pdfrestrictions;
  }

  public function setPdfrestrictions($pdfrestrictions) {
    $this->pdfrestrictions = $pdfrestrictions;
  }

  public function getConditions() {
    return $this->conditions;
  }

  public function setConditions($conditions) {
    $this->conditions = $conditions;
  }


}
?>
