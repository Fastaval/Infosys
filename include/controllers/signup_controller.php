<?php

class SignupController extends Controller {
  
  public function signupPages() {
    $pages = [];

    $page_files = glob(INCLUDE_PATH."signup-pages/*");
    foreach($page_files as $page_file){ // iterate files
      if(!is_file($page_file)) continue;
      $page = json_decode(file_get_contents($page_file));
      $page->file = basename($page_file, ".json");
      $pages[$page->order] = $page;
    }
    ksort($pages);
    $this->page->signup_pages = $pages;
    $this->page->includeCss('fontello-ebe72605/css/idtemplate.css');
    $this->page->registerEarlyLoadJS('signup/page_admin.js');
  }    
}