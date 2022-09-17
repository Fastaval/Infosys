<?php

class SignupApiModel extends Model {

  public function getConfig() {
    $config = (object)[];
    $config->con_start = $this->config->get('con.start');
    return $config;
  }

  public function getFood() {
    $result = (object)[];
    
    // TODO make this a setting
    $category = [
      5 => "breakfast",
      12 => "dinner",
      18 => "dinner",
    ];
    $result->categories = [
      'breakfast' => [
        'en' => 'breakfast',
        'da' => 'morgenmad',
      ],
      'dinner' => [
        'en' => 'dinner',
        'da' => 'aftensmad',
      ]
      ];

    $food =  $this->createEntity('Madtider')->findAll();
    usort($food, function($a, $b) {
      return strtotime($a->dato) - strtotime($b->dato);
    });
    
    foreach($food as $fooditem) {
      $resultitem = (object) [];
      $resultitem->id = $fooditem->id;
      $resultitem->text = [
        'da' => $fooditem->description_da,
        'en' => $fooditem->description_en,
      ];
      $cat = $category[$fooditem->mad_id];
      $day = date('N', strtotime($fooditem->dato));
      $result->days[$day][$cat][] = $resultitem;
    }
    return $result;
  }

  public function getActivities() {
    $result = (object)[];

    // TODO make this a setting
    $result->categories = [
      'all' => [
        'da' => 'Alle Aktiviteter',
        'en' => 'All Activities',
      ],
      'rolle' => [
        'da' => 'Rollespil',
        'en' => 'Roleplaying',
        'color' => 'lightgreen',
        'include' => ['live'],
      ],
      'live' => [
        'da' => 'Live Rollespil',
        'en' => 'Live Roleplaying',
        'color' => 'mediumpurple',
        'nobutton' => true,
      ],
      'braet' => [
        'da' => 'Brætspil',
        'en' => 'Board games',
        'color' => 'lightblue',
      ],
      'magic' => [
        'da' => 'Magic',
        'en' => 'Magic',
        'color' => 'darkgreen',
      ],
      'junior' => [
        'da' => 'Junior',
        'en' => 'Junior',
      ],
      'default' => [
        'da' => 'Øvrige aktiviteter',
        'en' => 'Other activities',
        'color' => 'yellow'
      ],
    ];
    $result->table_headline = [
      'da' => 'Aktiviteter',
      'en' => 'Activities',
    ];
    $result->day_cutoff = 4;
    $result->choices = [
      'prio' => [
        'en' => [
          '1st prio',
          '2nd prio',
          '3rd prio',
          '4th prio',
        ],
        'da' => [
          '1. prio',
          '2. prio',
          '3. prio',
          '4. prio',
        ],
      ],
      'gm' => [
        'default' => [
          'en' => 'GM',
          'da' => 'SL',
        ],
        'braet' => [
          'en' => 'RG',
          'da' => 'RF',
        ],
      ],
    ];
    $result->link_text = [
      'en' => "Read more on the website",
      'da' => "Læs mere på hjemmesiden",
    ];


    $activitities = $this->createEntity('Aktiviteter')->findAll();
    foreach ($activitities as $activity) {
      if ($activity->hidden == 'ja' || $activity->kan_tilmeldes == 'nej' || $activity->type == 'system') continue;
      $activity_info = (object)[];
      $activity_info->exclusive = $activity->tids_eksklusiv == 'ja';
      $activity_info->gm = $activity->spilledere_per_hold > 0;
      $activity_info->min_age = $activity->getMinAge();
      $activity_info->max_age = $activity->getMaxAge();
      $activity_info->type = $activity->type;
      $activity_info->wp_id = $activity->wp_link;
      $activity_info->lang = [
        'en' => str_contains($activity->sprog, 'engelsk'),
        'da' => str_contains($activity->sprog, 'dansk'),
      ];
      $activity_info->desc = [
        'en' => $activity->description_en,
        'da' => $activity->foromtale,
      ];
      $activity_info->title = [
        'da' => $activity->navn,
        'en' => $activity->title_en
      ];

      $result->activities[$activity->id] = $activity_info;
    }
    $runs = $this->createEntity('Afviklinger')->findAll();
    foreach ($runs as $run) {
      if (!isset($result->activities[$run->aktivitet_id])) continue;
      $run_info = (object)[];
      $run_info->id = $run->id;
      $run_info->activity = $run->aktivitet_id;
      $run_info->start = [
        'day' => date('N', strtotime($run->start)),
        'hour' => intval(date('H', strtotime($run->start))),
        'min' => intval(date('i', strtotime($run->start))),
        'stamp' => strtotime($run->start),
      ];
      $run_info->end = [
        'day' => date('N', strtotime($run->slut)),
        'hour' => intval(date('H', strtotime($run->slut))),
        'min' => intval(date('i', strtotime($run->slut))),
        'stamp' => strtotime($run->slut),
      ];
      $day = $run_info->start['day'];
      if($run_info->start['hour'] < $result->day_cutoff) $day--; // Put runs that start late together with the day before
      $result->runs[$day][] = $run_info;
    }
    
    foreach($result->runs as $day => $runs) {
      usort($result->runs[$day], function(&$a, $b) {
        return $a->start['stamp'] - $b->start['stamp'];
      });
    }


    return $result;
  }
}