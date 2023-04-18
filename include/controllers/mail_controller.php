<?php

class MailController extends Controller {

  static protected $enabled_types = [
    //'setup' => true,
    //'fixpass' => true,
    //'evaluation' => true,
  ];

  /**
   * Send mail for participants joining during the setup days
   */
  public function sendSetupMail() {
    $recipients = $this->model->getRecipients('setup');
    //$recipients = $this->model->getRecipients(1);

    $year = $this->getConYear();
    $title = [
      'da' => "Opsætning af Fastaval $year",
      'en' => "Fastaval Set-up $year"
    ];

    $this->sendBatchMail('setup', $title, $recipients);
  }

  /**
   * Send all participants a link to the evaluation
   */
  public function sendEvaluationMail() {
    $recipients = $this->model->getRecipients('evaluation');
    //$recipients = $this->model->getRecipients(3);

    $year = $this->getConYear();
    $title = [
      'da' => "Evaluering af Fastaval $year",
      'en' => "Evaluation of Fastaval $year"
    ];

    $this->sendBatchMail('evaluation', $title, $recipients, ['work_area']);
  }

  /**
   * Used for fixing passwords and notifying participants who had their passwords changed
   */
  public function fixPasswords() {
    $recipients = $this->model->getRecipients('fixpass', 0);

    echo "Updating participant passwords for ".count($recipients)." participants<br>";
    die('Password safety');

    foreach ($recipients as $participant) {
      $participant->createPass();
      $participant->update();
      echo $participant->id ."<br>";
    }

    $year = $this->getConYear();
    $title = [
      'da' => "Ny kode til Fastaval $year",
      'en' => "New passcode for Fastaval $year"
    ];

    $this->sendBatchMail('fixpass', $title, $recipients, ['password']);
  }

  private function sendBatchMail($type, $title, $recipients, $info = []) {
    $total = count($recipients);
    echo "Sending $type mail to $total recipients<br>\n";

    if (!(isset(self::$enabled_types[$type]) && self::$enabled_types[$type] == true)) {
      die ("Mail type '$type' is not enabled");
    }
    die ('Double safe');

    // Finish response before sending mails, to avoid timeout
    session_write_close();
    fastcgi_finish_request();
    set_time_limit(0); // setting this to avoid ending execution when sending a lot of mails

    $count = 0;
    foreach ($recipients as $recipient) {
      if (!$recipient->email) continue;

      $lang = $recipient->speaksDanish() ? 'da' : 'en';
      //$lang = 'en';
      $this->page->setTemplate("mail/{$type}_mail_$lang");

      // Get personalized info for the mail
      $this->page->lang = $lang;
      $this->page->name = $recipient->getName();
      foreach ($info as $field) {
        if (is_string($field)) {
          $page_info[$field] = $recipient->$field;
        }
      }
      $this->page->info = $page_info;

      $mail = new Mail($this->config);
      $mail->setFrom($this->config->get('app.email_address'), $this->config->get('app.email_alias'))
          ->setRecipient($recipient->email)
          ->setSubject($title[$lang])
          ->setBodyFromPage($this->page);
      $mail->send();

      $this->log("System sent $type mail to participant (ID: $recipient->id )", 'Mail', null);
      $count++;
    }

    $this->log("Finished sending $type mail to $count participants", 'Mail', null);
    exit;
  }
}