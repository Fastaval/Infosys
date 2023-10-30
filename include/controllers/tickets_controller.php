<?php

class TicketsController extends Controller
{
  protected $prerun_hooks = array(
    array('method' => 'checkUser', 'exclusive' => true),
  );

  //--------------------------------------------------------------------------------------------------------------------
  // Pages
  //--------------------------------------------------------------------------------------------------------------------
  public function mainPage() {
    $this->page->ticket_id = $this->vars['ticket_id'] ?? 0;
    $this->page->user_id = $this->model->getLoggedinUser()->id;

    $scripts = glob(PUBLIC_PATH."js/tickets/*index*.js");
    if (!empty($scripts)) {
      $this->page->registerEarlyLoadJSModule('tickets/'.basename($scripts[0]));
      //$this->page->registerEarlyLoadJS('tickets/'.basename($scripts[0]));
    }

    $stylesheets = glob(PUBLIC_PATH."css/tickets/*.css");
    foreach($stylesheets as $sheet){ // iterate script files
      $this->page->includeCSS('tickets/'.basename($sheet));
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  // Ticket AJAX functions
  //--------------------------------------------------------------------------------------------------------------------
  public function ajaxTickets() {
    if ($this->page->request->isPost()) {
      // Posting ticket
      $data = $this->getPostData();

      if (isset($data->id)) {
        $this->updateTicket($data);
      } else {
        $this->createTicket($data);
      }
    } else {
      // GET Ticket(s)
      $get = $this->page->request->get;
      $this->getTickets($get);
    }
  }

  private function createTicket($data) {
    $id = $this->model->createTicket($data);
    if ($id !== false) {
      $this->jsonOutput([
        'status' => 'success',
        'message' => "Ticket with ID:$id created",
        'id' => $id,
        ]);
    } else {
      $this->jsonOutput(['status' => 'error'], 400);
    }
  }

  private function updateTicket($data) {
    $result = $this->model->updateTicket($data);
    $this->sendResult($result, "Ticket updated");
  }

  private function getTickets($get) {
    $tickets = $this->model->getTickets($get);

    if (empty($tickets)) {
      $this->jsonOutput(['status' => 'no results'], 404);
    }
    
    $user_id = $this->model->getLoggedInUser()->id;
    $output = [];
    foreach($tickets as $ticket) {
      $entry = [];
      foreach($ticket->getColumns() as $column) {
        $entry[$column] = $ticket->$column;
      }

      $description = $ticket->getDescription();
      $entry['description'] = $description['message'];
      $entry['created']     = $description['posted'];
      $entry['last_edit']   = $description['last_edit'];
      
      $entry['subscribed']  = $ticket->isSubscribed($user_id);

      $output[$ticket->id] = $entry;
    }

    $this->jsonOutput(['status' => 'success', 'tickets' => $output]);
  }

  //--------------------------------------------------------------------------------------------------------------------
  // Ticket messages AJAX funtions
  //--------------------------------------------------------------------------------------------------------------------
  public function ajaxMessages() {
    $ticket_id = $this->vars['ticket_id'];
    if(empty($ticket_id) && !is_int($ticket_id)) {
      header('HTTP/1.1 400 Missing or malformed ticket ID');
      exit;
    }

    if ($this->page->request->isPost()) {
      // Posting ticket
      $data = $this->getPostData();

      if (isset($data->id)) {
        $this->updateMessage($data, $ticket_id);
      } else {
        $this->createMessage($data, $ticket_id);
      }
    } else {
      // GET Ticket(s)
      $get = $this->page->request->get;
      $this->getMessages($get, $ticket_id);
    }
  }

  private function createMessage($data, int $ticket_id) {
    $id = $this->model->createMessage($data, $ticket_id);
    if ($id !== false) {
      $this->jsonOutput([
        'status' => 'success',
        'message' => "Message with ID:$id created",
        'id' => $id,
        ]);
    } else {
      $this->jsonOutput(['status' => 'error'], 400);
    }
  }

  private function updateMessage($data, int $ticket_id) {
    $result = $this->model->updateMessage($data, $ticket_id);
    $this->sendResult($result, "Message updated");
  }

  private function getMessages($get, $ticket_id) {
    $messages = $this->model->getMessages($get, $ticket_id);
    $this->jsonOutput(['status' => 'success', 'messages' => $messages]);
  }

  //--------------------------------------------------------------------------------------------------------------------
  // Ticket subscriptions AJAX funtions
  //--------------------------------------------------------------------------------------------------------------------
  public function ajaxSubscription() {
    $ticket_id = $this->vars['ticket_id'];
    if(empty($ticket_id) && !is_int($ticket_id)) {
      header('HTTP/1.1 400 Missing or malformed ticket ID');
      exit;
    }

    if ($this->page->request->isPost()) {
      // Posting subscription
      $data = $this->getPostData();
      $this->setSubscription($data, $ticket_id);
    } else {
      // GET Ticket(s)
      $get = $this->page->request->get;
      $this->getSubscription($get, $ticket_id);
    }
  }

  private function setSubscription($data, $ticket_id) {
    $result = $this->model->setSubscription($data, $ticket_id);
    $response = [
      'status' => $result == 'success' ? 'success' : 'error',
    ];
    if ($result == 'no change') {
      $response['message'] = "Desired subscription status is same as current";
    }

    $this->sendResult($response, "Subscription updated");
  }

  private function getSubscription($data, $ticket_id) {
    $user_id = $data->user ?? $this->model->getLoggedInUser()->id;

    $tickets = $this->model->getTickets(['id' => $ticket_id]);
    if (count($tickets) == 0) {
      $this->jsonOutput([
        'status' => 'error',
        'message' => "no ticket with ID:$ticket_id"
      ], 404);
    }

    $this->jsonOutput([
      'status' => 'success',
      'subscribed' => $tickets[0]->isSubscribed($user_id)
    ], 400);
  }

  //--------------------------------------------------------------------------------------------------------------------
  // Utility functions
  //--------------------------------------------------------------------------------------------------------------------
  private function getPostData() {
    // Checking for JSON input
    if(isset($_SERVER["CONTENT_TYPE"]) &&  str_contains($_SERVER["CONTENT_TYPE"], 'json')) {
      // Retrieve JSON
      if (!($input = file_get_contents('php://input'))) {
        $this->jsonOutput([
          'status' => 'error',
          'message' => "Could not read POST data",
        ], 400);
      }
      // Parse JSON
      if (!($data = json_decode($input))) {
        $this->jsonOutput([
          'status' => 'error',
          'message' => "Could not parse JSON file",
        ], 400);
      }
      return $data;
    } else {
      // Just normal form data
      return $this->page->request->post;
    }
  }

  private function sendResult($result, $message) {
    if ($result['status'] === 'success') {
      $this->jsonOutput([
        'status' => 'success',
        'message' => $message,
        ]);
    } else {
      $code = $result['code'] ?? 400;
      $this->jsonOutput($result, $code);
    }
  }
}