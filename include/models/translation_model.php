<?php

class TranslationModel extends Model {
  public function findTranslations($label, $lang = null) {
    $args = [];
    $args[] = str_replace('*', '%', $label);

    $lang_condition = "";
    if ($lang !== null) {
      $lang_condition =  "AND lang = ?";
      $args[] = $lang;
    }

    $query = 
    "SELECT * FROM translations WHERE label LIKE ? $lang_condition";

    $result = $this->db->query($query, $args);

    return $this->formatTranslations($result);
  }

  private function formatTranslations($result) {
    $translations = [];

    foreach($result as $entry) {
      $keys = explode(".", $entry['label']);
      $keys[] = $entry['lang'];

      self::arrayInsert($keys, $entry['string'], $translations);
    }

    return $translations;
  }

  private static function arrayInsert(array $keys, $value, array &$subject) {
    $key = array_shift($keys);

    if (empty($keys)) {
      $subject[$key] = $value;
      return;
    }

    $subject[$key] ??= [];
    $child = &$subject[$key];

    self::arrayInsert($keys, $value, $child);
  }
}