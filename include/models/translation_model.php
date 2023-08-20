<?php

class TranslationModel extends Model {
  public function findTranslation($label) {
    $label = str_replace('*', '%', $label);

    $query = 
    "SELECT * FROM translations WHERE label LIKE ?";

    $result = $this->db->query($query, $label);

    return $this->formatTranslations($result);
  }

  private function formatTranslations($result) {
    $translations = [];


  }

  private function arrayinsert(array $keys, $value) {
    
  }
}