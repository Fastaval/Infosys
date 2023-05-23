<?php

class TicketsController extends Controller
{
  public function mainPage() {

    $scripts = glob(PUBLIC_PATH."js/tickets/*");
    foreach($scripts as $script){ // iterate script files
      $this->page->registerEarlyLoadJS('tickets/'.basename($script));
    }

    $stylesheets = glob(PUBLIC_PATH."css/tickets/*");
    foreach($stylesheets as $sheet){ // iterate script files
      $this->page->includeCSS('tickets/'.basename($sheet));
    }

  }
}