<?php
class SignupApiController extends Controller {

  const DATA_FOLDER = SIGNUP_FOLDER."data/";

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

  public function getConfig() {
    $module = $this->vars['module'];
    $config = $this->model->getConfig($module);
    $this->jsonOutput($config);
  }

  public function getPageList() {
    $response = [];

    $pages = $this->model->getAllPages();
    foreach($pages as $name => $page){
      $response[$name] = [];
      $response[$name]['slug'] = $page->slug;
      $response[$name]['order'] = $page->order;
    }

    $this->jsonOutput($response);
  }

  public function getPage() {
    $page_id = $this->vars['page_id'];
    $page = $this->model->getPage($page_id);
    $this->jsonOutput($page);
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
    $hash = date('Y-m-d-').hash('md5', $json);
    file_put_contents(self::DATA_FOLDER."$hash.json", $json);

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

    $signup_file = self::DATA_FOLDER."$data[hash].json";
    if(!is_file($signup_file)) die("Signup with Hash:$data[hash] not found.");

    $signup = json_decode(file_get_contents($signup_file), true);
    $data['signup'] = $signup;
    [$info,$result] = $this->model->confirmSignup($data);
    $status = count($result['errors']) == 0 ? '200' : '400';

    // TODO send mail

    $this->jsonOutput([
      'result' => $result,
      'info' => $info,
    ], $status);
  }

  public function loadSignup() {
    if (!$this->page->request->isPost()) {
      header('HTTP/1.1 400 Not a POST request');
      exit;
    }
    $data = $this->page->request->post->getRequestVarArray();

    $result = $this->model->loadSignup($data['id'], $data['pass']);

    $status = count($result['errors']) == 0 ? '200' : '400';
    $this->jsonOutput($result, $status);
  }
}