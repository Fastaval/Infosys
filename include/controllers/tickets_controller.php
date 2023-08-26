<?php

class TicketsController extends Controller
{
  protected $prerun_hooks = array(
    array('method' => 'checkUser', 'exclusive' => true),
  );

  public function mainPage() {
    $this->page->ticket_id = $this->vars['ticket_id'] ?? 0;

    $scripts = glob(PUBLIC_PATH."js/tickets/*index*.js");
    if (!empty($scripts)) {
      $this->page->registerEarlyLoadJSModule('tickets/'.basename($scripts[0]));
    }

    $stylesheets = glob(PUBLIC_PATH."css/tickets/*.css");
    foreach($stylesheets as $sheet){ // iterate script files
      $this->page->includeCSS('tickets/'.basename($sheet));
    }
  }

  public function ajaxTickets() {
    if ($this->page->request->isPost()) {
      // Posting ticket
      $post = $this->page->request->post;

      if (isset($post->id)) {
        $this->updateTicket($post);
      } else {
        $this->createTicket($post);
      }
    } else {
      // GET Ticket(s)
      $get = $this->page->request->get;
      $this->getTickets($get);
    }
  }

  private function createTicket($post) {
    $id = $this->model->createTicket($post);
    if ($id !== false) {
      $this->jsonOutput(['status' => 'success', 'id' => $id]);
    } else {
      $this->jsonOutput(['status' => 'error'], 400);
    }
  }

  private function updateTicket($post) {
    $result = $this->model->updateTicket($post);

    if ($result['status'] === 'success') {
      $this->jsonOutput(['status' => 'success']);
    } else {
      $code = $result['code'] ?? 400;
      $this->jsonOutput($result, $code);
    }
  }

  private function getTickets($get) {
    $tickets = $this->model->getTickets($get);

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
      $entry['description'] = $description['message'];
      $entry['created']     = $description['posted'];
      $entry['last_edit']   = $description['last_edit'];


      $output[$ticket->id] = $entry;
    }

    $this->jsonOutput(['status' => 'success', 'tickets' => $output]);
  }
}