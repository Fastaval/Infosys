<?php

class MailModel extends Model {

  /**
   * Get relevant participants for a certain mail type
   */
  public function getRecipients($type, $filter_recent = 1) {

    // Find participants by list of ids
    if (is_array($type)) {
      $recipients = [];
      foreach($type as $id) {
        $recipients[] = $this->createEntity('Deltagere')->findById($id);
      }
      return $recipients;
    }

    // Find participant by single id
    if (intval($type) !== 0) {
      return [$this->createEntity('Deltagere')->findById(intval($type))];
    }

    // Get all participants
    $participants = $this->createEntity('Deltagere')->findAll();

    // Filter out canceled participants
    foreach ($participants as $id => $participant) {
      if ($participant->annulled == 'ja') {
        unset($participants[$id]);
      }
    }

    // Filter out anyone who recently recieved a mail of the same type (if needed)
    if ($filter_recent) {
      $this->filterOutRecentMails($participants, $type, $filter_recent);
    }

    // Filter out participants based on type
    switch ($type) {
      case 'setup':
        foreach ($participants as $id => $participant) {
          if ($participant->ready_mandag !== 'ja' && $participant->ready_tirsdag !== 'ja') {
            unset($participants[$id]);
          }
        }
        break;

      case 'fixpass':
        foreach ($participants as $id => $participant) {
          if (preg_match("/\d{6}/", $participant->password)) {
            unset($participants[$id]);
          }
        }
        break;
    }

    return $participants;
  }

  private function filterOutRecentMails(array &$participants, $type, $days_ago = 1) {
    $select = $this->createEntity('LogItem')->getSelect();
    $select->setWhere('message', 'LIKE', "%System sent $type mail to participant%")
        ->setRawWhere('DATE(created) > ADDDATE(NOW(), INTERVAL -'.intval($days_ago).' day)', 'AND');

    $filter_ids = [];
    foreach ($this->createEntity('LogItem')->findBySelectMany($select) as $log_item) {
      if (preg_match('/\(ID: (\d+) \)/', $log_item->message, $matches)) {
        $filter_ids[$matches[1]] = true;
      }
    }

    foreach ($participants as $index => $participant) {
      if (isset($filter_ids[$participant->id])) unset($participants[$index]);
    }
  }

}