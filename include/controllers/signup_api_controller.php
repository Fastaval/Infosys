<?php
class SignupApiController extends Controller {

  const DATA_DIR = INCLUDE_PATH."signup-data/";

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
    $string = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
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

  public function getWear() {
    $wear = $this->model->getWear();
    $this->jsonOutput($wear);
  }

  public function submitSignup() {
    if (!$this->page->request->isPost()) {
      header('HTTP/1.1 400 Not a POST request');
      exit;
    }
    $data = $this->page->request->post->getRequestVarArray();

    $json = json_encode($data['signup'], JSON_PRETTY_PRINT);
    $hash = hash('md5', $json);
    file_put_contents(self::DATA_DIR."$hash.json", $json);

    $result = $this->model->submitSignup($data);
    $status = count($result['errors']) == 0 ? '200' : '400';

    $this->jsonOutput([
      'result' => $result,
      'hash' => $hash,
    ], $status);
  }

  public function confirmSignup() {
    if (!$this->page->request->isPost()) {
      header('HTTP/1.1 400 Not a POST request');
      exit;
    }
    $data = $this->page->request->post->getRequestVarArray();

    $signup_file = self::DATA_DIR."$data[hash].json";
    if(!is_file($signup_file)) die("Signup with Hash:$data[hash] not found.");

    $signup = json_decode(file_get_contents($signup_file));
    $data['signup'] = $signup;
    [$info,$result] = $this->model->confirmSignup($data);
    $status = count($result['errors']) == 0 ? '200' : '400';

    $this->jsonOutput([
      'result' => $result,
      'info' => $info,
    ], $status);
  }
}