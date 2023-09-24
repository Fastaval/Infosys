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

    // TODO check rights to modify ticket

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

  public function createMessage($post, int $ticket_id) {
    $user = $this->getLoggedInUser();

    $query = "INSERT INTO tickets_messages (user, ticket, posted, message) VALUES (?, ?, ?, ?)";

    $values = [
      $user->id,
      $ticket_id,
      time(),
      $post->message ?? "",
    ];

    return $this->db->exec($query, $values);
  }

  public function getMessages($get, int $ticket_id) {
    $args = [$ticket_id];
    
    $user_condition = "user IS NOT NULL";
    if (isset($get->user)) {
      $user_condition = "user = ?";
      $args[] = $get->user;
    }

    $query = "SELECT * FROM tickets_messages WHERE ticket = ? AND $user_condition";
    return $this->db->query($query, $args, 'assoc');
  }

  public function updateMessage($post, int $ticket_id) {
    $user = $this->getLoggedInUser();

    // Check input
    $query = "SELECT user, ticket FROM tickets_messages WHERE id = ?";
    $result = $this->db->query($query, $post->id);

    // Check message id
    if (empty($result)) {
      return [
        'status' => 'error',
        'code' => '404',
        'message' => "No message with ID:$post->id found"
      ];
    }

    $message = $result[0];

    // Check ticket id
    if ($message['ticket'] != $ticket_id) {
      return [
        'status' => 'error',
        'code' => '400',
        'message' => "Message with $post->id does not belong to ticket $ticket_id"
      ];
    }

    //Check user
    if ($message['user'] != $user->id) {
      return [
        'status' => 'error',
        'code' => '403',
        'message' => "Message $post->id does not belong to user $user->id"
      ];
    }

    $query = "UPDATE tickets_messages SET message = ?, last_edit = ? WHERE id = ?";
    $args = [
      $post->message,
      time(),
      $post->id,
    ];

    $result = $this->db->exec($query, $args);
    return ['status' => $result ? 'success' : 'error'];
  }
}