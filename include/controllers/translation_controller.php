<?php

class TranslationController extends Controller
{
  public function ajaxTranslations () {
    $label = $this->vars['label'] ?? '';

    if ($this->page->request->isPost()) {
      // Posting translation
      $this->checkUser();

      // TODO
      $post = $this->page->request->post;

    } else {
      // GET Ticket(s)
      $get = $this->page->request->get;
      $this->findTranslations($label, $get->lang);
    }
  }
  
  public function findTranslations($label, $lang) {
    $translations = $this->model->findTranslations($label, $lang);

    if (empty($translations)) {
      $this->jsonOutput(
        [
          'status' => 'error',
          'code' => '404',
          'message' => "No translations matching \"$label\""
        ],
        404
      );
    }

    $this->jsonOutput(
      [
        'status' => 'success',
        'translations' => $translations,
      ]);
  }
}