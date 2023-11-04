<?php
class PaymentController extends Controller {

  protected $prerun_hooks = [
    ['method' => 'allowCrossSiteAccess', 'exclusive' => true, 'methodlist' => [
      'paymentCallBack',
    ]], 
  ];

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

  public function paymentCallBack() {
    fastcgi_finish_request(); // No need to keep the payment server waiting

    $post = $this->page->request->post;
    [$participant, $amount] = $this->model->completePayment($post);

    $mc = new MailController($this->route, $this->config, $this->dic);
    $mc->sendPaymentConfirmation($participant, $amount);
    exit;
  }
  
  
  public function createPaymentURL() {
    if (!$this->page->request->isPost()) {
      $this->jsonOutput([
        'status' => 'error',
        'message' => 'Not a POST request'
      ], 400);
    }
    $post = $this->page->request->post;

    if (!isset($post->id)) {
      $this->jsonOutput([
        'status' => 'error',
        'message' => 'No user ID'
      ], 401);
    }

    [$status, $msg] = $this->model->createPaymentURL($post);

    if ($status == 'error') {
      $this->jsonOutput([
        'status' => 'error',
        'message' => $msg
      ], 400);
    }

    $this->jsonOutput([
      'status' => 'success',
      'url' => $msg
    ]);
  }

  public function cancelPayment() {
    if (!$this->page->request->isPost()) {
      $this->jsonOutput([
        'status' => 'error',
        'message' => 'Not a POST request'
      ], 400);
    }
    $post = $this->page->request->post;

    if (!isset($post->token)) {
      $this->jsonOutput([
        'status' => 'error',
        'message' => 'No payment token'
      ], 401);
    }

    $this->model->cancelPayment($post->token);

    $this->jsonOutput([
      'status' => 'success',
    ]);
  }

  public function checkPayment() {
    if (!$this->page->request->isPost()) {
      $this->jsonOutput([
        'status' => 'error',
        'message' => 'Not a POST request'
      ], 400);
    }
    $post = $this->page->request->post;

    if (!isset($post->token)) {
      $this->jsonOutput([
        'status' => 'error',
        'message' => 'No payment token'
      ], 401);
    }

    [$status, $msg] = $this->model->getPaymentStatus($post->token);
    if ($status == 'error') {
      $this->jsonOutput([
        'status' => 'error',
        'message' => $msg
      ], 400);
    }

    $this->jsonOutput([
      'status' => 'success',
      'payment_status' => $status
    ]);
  }

  public function checkTotal() {
    if (!$this->page->request->isPost()) {
      $this->jsonOutput([
        'status' => 'error',
        'message' => 'Not a POST request'
      ], 400);
    }
    $post = $this->page->request->post;

    if (!isset($post->id)) {
      $this->jsonOutput([
        'status' => 'error',
        'message' => 'No user ID'
      ], 401);
    }

    if (!isset($post->pass)) {
      $this->jsonOutput([
        'status' => 'error',
        'message' => 'No password'
      ], 401);
    }
    
    [$status, $result] = $this->model->checkTotal($post);

    if ($status == 'error') {
      $code = $result == 'wrong credentials' ? 401 : 400;

      $this->jsonOutput([
        'status' => 'error',
        'message' => $result
      ], $code);
    }

    $this->jsonOutput([
      'status' => 'success',
      'totals' => $result
    ]);
  }
}