<?php

class TranlationController extends Controller
{
  public function findTranslation() {
    $label = $this->vars['label'] ?? '';

    $translations = $this->model->findTranslation($label);

    if (empty($translations)) {
      header('HTTP/1.1 404 No matching translation found');
      exit;
    }

    $this->jsonOutput($translations);
  }
}