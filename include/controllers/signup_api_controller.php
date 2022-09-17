<?php
class SignupApiController extends Controller {

  protected $prerun_hooks = array(
    ['method' => 'allowCrossSiteAccess', 'exclusive' => true, 'methodlist' => []], 
  );

  /**
   * sets the proper header to allow cross site
   * access to the api
   *
   * @access public
   * @return void
   */
  public function allowCrossSiteAccess()
  {
    header('Access-Control-Allow-Origin: *');
  }

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

  public function getConfig() {
    $config = $this->model->getConfig();
    $this->jsonOutput($config);
  }

  public function getPageList() {
    $response = (object) [];

    $page_files = glob(INCLUDE_PATH."signup-pages/*");
    foreach($page_files as $page_file){ // iterate files
      if(!is_file($page_file)) continue;
      $name = basename($page_file, ".json");
      $page = json_decode(file_get_contents($page_file));
      $response->$name = (object) [];
      $response->$name->slug = $page->slug;
      $response->$name->order = $page->order;
    }

    $this->jsonOutput($response);
  }

  public function getPage() {
    $page_id = $this->vars['page_id'];
    $page_file = INCLUDE_PATH."signup-pages/$page_id.json";
    if(!is_file($page_file)) die("Signup page not found");

    $page = file_get_contents($page_file);
    header('Status: 200');
    header('Content-Type: text/plain; charset=UTF-8');
    header('Content-Length: ' . strlen($page));
    echo $page;
    exit;
  }

  public function getFood() {
    $food = $this->model->getFood();
    $this->jsonOutput($food);
  }

  public function getActivities() {
    $activities = $this->model->getActivities();
    $this->jsonOutput($activities);
  }
}