<?php

class TicketsModel extends Model {
  public function createTicket($data) {
    $ticket = $this->createEntity('Ticket');
    $user = $this->getLoggedInUser();

    $ticket->name       = $data->name       ?? 'New Ticket';
    $ticket->category   = $data->category   ?? 0;
    $ticket->priority   = $data->priority   ?? 0;
    $ticket->status     = $data->status     ?? 0;
    $ticket->open       = $data->open       ?? 1;
    $ticket->creator    = $data->creator    ?? $user->id;
    $ticket->assignee   = $data->assignee   ?? $ticket->creator;

    $ticket->setDescription($data->description ?? "");

    return $ticket->insert() ? $ticket->id : false;
  }

  public function updateTicket($data) {
    $ticket = $this->createEntity('Ticket')->findById($data->id);

    if (!$ticket) return [
      'status' => 'error',
      'code' => 404,
      'message' => "No ticekt with id $data->id",
    ];

    foreach($ticket->getColumns() as $column) {
      if (isset($data->$column)) $ticket->$column = $data->$column;
    }

    $result = $ticket->update();

    if ($result && isset($data->description)) $ticket->setDescription($data->description);

    return ['status' => $result ? 'success' : 'error'];
  }

  public function getTickets($data) {
    if (isset($data->id)) {
      $ticket = $this->createEntity('Ticket')->findById($data->id);
      return $ticket ? [$ticket] : [];
    }

    return $this->createEntity('Ticket')->findAll();
  }
}