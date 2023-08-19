<?php

class TicketsController extends Controller
{
  protected $prerun_hooks = array(
    array('method' => 'checkUser', 'exclusive' => true),
  );

  public function mainPage() {
    $this->page->ticket_id = $this->vars['ticket_id'] ?? 0;

    $scripts = glob(PUBLIC_PATH."js/tickets/*");
    foreach($scripts as $script){ // iterate script files
      $this->page->registerEarlyLoadJS('tickets/'.basename($script));
    }

    $stylesheets = glob(PUBLIC_PATH."css/tickets/*");
    foreach($stylesheets as $sheet){ // iterate script files
      $this->page->includeCSS('tickets/'.basename($sheet));
    }
  }

  public function createTicket() {
    if (!$this->page->request->isPost()) {
      header('HTTP/1.1 400 Not a POST request');
      exit;
    }
    $post = $this->page->request->post;

    $id = $this->model->createTicket($post);
    if ($id !== false) {
      $this->jsonOutput(['status' => 'success', 'id' => $id]);
    } else {
      $this->jsonOutput(['status' => 'error'], 400);
    }
  }

  public function updateTicket() {
    if (!$this->page->request->isPost()) {
      header('HTTP/1.1 400 Not a POST request');
      exit;
    }
    $post = $this->page->request->post;

    if (!isset($post->id)) {
      header('HTTP/1.1 400 No ID');
      exit;
    }

    $result = $this->model->updateTicket($post);
    if ($result['status'] === 'success') {
      $this->jsonOutput(['status' => 'success']);
    } else {
      $code = $result['code'] ?? 400;
      $this->jsonOutput($result, $code);
    }
  }

  public function getTickets() {
    $tickets = $this->model->getTickets($this->page->request->get);

    if (empty($tickets)) {
      $this->jsonOutput(['status' => 'no results'], 404);
    }
    
    $output = [];
    foreach($tickets as $ticket) {
      $entry = [];
      foreach($ticket->getColumns() as $column) {
        $entry[$column] = $ticket->$column;
      }

      $description = $ticket->getDescription();
      $this->fileLog(print_r($description, true));

      $entry['description'] = $description['message'];
      $entry['created']     = $description['posted'];
      $entry['last_edit']   = $description['last_edit'];


      $output[$ticket->id] = $entry;
    }

    $this->jsonOutput(['status' => 'success', 'tickets' => $output]);
  }
}