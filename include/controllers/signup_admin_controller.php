<?php

class SignupAdminController extends Controller {
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
        'error' => "No page with id:$post->page_id",
      ], '404');
      exit;
    } 

    $page_file = file_get_contents($page_file_path);
    $page = json_decode($page_file);
    switch ($post->type) {
      case 'section': // Section
        if(!isset($page->sections)) $page->sections = [];
        $section = [
          'headline' => $post->headline
        ];
        if(isset($page->sections[$post->section_id])) {
          array_splice($page->sections, $post->section_id, 0, [$section]);  
          break;
        }
        $page->sections[$post->section_id] = $section;
        break;
      case 'text_input': // Text input
        if(!isset($page->sections[$post->section_id])){
          $this->jsonOutput([
            'error' => "No section with id:$post->section_id on page:$post->page_id",
          ], '400');
          exit;
        } 
        $section = $page->sections[$post->section_id];
        if(!isset($section->items)){
          $section->items = [];
        } 
        $item = [
          'type' => 'text_input',
          'text' => $post->text,
          'infosys_id' => $post->infosys_id,
        ];
        if(isset($section->items[$post->item_id])) {
          array_splice($section->items, $post->item_id, 0, [$item]);  
        } else {
          $section->items[$post->item_id] = $item;
        }
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

  public function editText() {
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

    if(!isset($post->lang)) {
      $this->jsonOutput([
        'error' => 'Language not set',
      ], '400');
      exit;
    }
  
    $page_file = file_get_contents($page_file_path);
    $page = json_decode($page_file);

    // Check IDs - Section
    if (in_array($post->type, ['infosys_id','item','headline'])){
      if(!isset($post->section_id)) {
        $this->jsonOutput([
          'error' => 'No Section ID',
        ], '400');
        exit;
      }
      if(!isset($page->sections[$post->section_id])) {
        $this->jsonOutput([
          'error' => "Page:$post->page_id Section:$post->section_id doesn't exist",
        ], '400');
        exit;
      }
    }

    // Check IDs - Item
    if (in_array($post->type, ['infosys_id','item'])){
      if(!isset($post->item_id)) {
        $this->jsonOutput([
          'error' => 'No Item ID',
        ], '400');
        exit;
      }
      if(!isset($page->sections[$post->section_id]->items[$post->item_id])) {
        $this->jsonOutput([
          'error' => "Page:$post->page_id Section:$post->section_id Item:$post->item_id doesn't exist",
        ], '400');
        exit;
      }
    }

    // Check Language
    if (in_array($post->type, ['item', 'headline', 'title'])){
      if(!isset($post->lang)) {
        $this->jsonOutput([
          'error' => 'No Language(lang) Value',
        ], '400');
        exit;
      }
      if(!in_array($post->lang, ['en', 'da'])) {
        $this->jsonOutput([
          'error' => "Unknown Langugae:$post->lang",
        ], '400');
        exit;
      }
    }

    switch ($post->type) {
      case 'title':
        $page->title->{$post->lang} = $post->text;
        break;
      case 'headline':
        $page->sections[$post->section_id]->headline->{$post->lang} = $post->text;
        break;
      case 'item':
        $page->sections[$post->section_id]->items[$post->item_id]->text->{$post->lang} = $post->text;
        break;
      case 'infosys_id':
        $page->sections[$post->section_id]->items[$post->item_id]->infosys_id = $post->text;
        break;

      default:
        $this->jsonOutput([
          'error' => 'Unknown element type',
        ], '400');
        exit;
    }
    file_put_contents($page_file_path, json_encode($page, JSON_PRETTY_PRINT));
    
    header('Status: 200');
    exit;
  }
}