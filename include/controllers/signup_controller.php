<?php

class SignupController extends Controller {
  /**
   * outputs json data and sets headers accordingly
   *
   * @param string $data        Data to output
   * @param string $http_status HTTP status code
   *
   * @access protected
   * @return void
   */
  protected function jsonOutput($data, $http_status = '200', $content_type = 'text/plain')
  {
    $string = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    header('Status: ' . $http_status);
    header('Content-Type: ' . $content_type . '; charset=UTF-8');
    header('Content-Length: ' . strlen($string));
    echo $string;
    exit;
  }

  
  /**
   * Show page for editing signup pages
   */
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
    $this->page->includeCSS('signup.css');
    $this->page->includeCss('fontello-ebe72605/css/idtemplate.css');
    $this->page->registerEarlyLoadJS('signup/page_admin.js');
  }

  /**
   * Add element to signup page
   */
  public function addPageElement() {
    if (!$this->page->request->isPost()) {
      header('HTTP/1.1 400 Not a POST request');
      exit;
    }
    $post = $this->page->request->post;

    $page_file_path = INCLUDE_PATH."signup-pages/$post->page_id.json";
    if(!is_file($page_file_path)) {
      $this->jsonOutput([
        'error' => 'No page with that id',
      ], '404');
      exit;
    } 

    $page_file = file_get_contents($page_file_path);
    $page = json_decode($page_file);
    switch ($post->type) {
      case 'section':
        if(!isset($page->sections)) $page->sections = [];
        if(isset($page->sections[$post->section_id])) {
          $this->jsonOutput([
            'error' => 'Section ID already exist',
          ], '409');
          exit;
        }
        $section = [
          'headline' => $post->headline
        ];
        $page->sections[$post->section_id] = $section;
        break;

      default:
        $this->jsonOutput([
          'error' => 'Unknown element type',
        ], '404');
        exit;
    }
    file_put_contents($page_file_path, json_encode($page, JSON_PRETTY_PRINT));
    
    header('Status: 200');
    exit;
  }
}