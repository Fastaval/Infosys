<?php

class SignupApiModel extends Model {

  const ACTIVITY_CHOICES = [
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

  /**
   * Get general configuration related to signup
   */
  public function getConfig() {
    $config = (object)[
      'con_start'   => $this->config->get('con.start'),
      'birth'       => 'birthdate',
      'participant' => 'participant',
      'organizer'   => 'organizercategory',
      'errors' => [
        'required' => [
          'en' => 'This input may not be empty',
          'da' => 'Feltet må ikke være tomt',
        ],
        'disabled' => [
          'en' => 'Has been disabled',
          'da' => 'Skal ikke bruges',
        ],
        'excludes' => [
          'en' => 'May not be selected at the same time',
          'da' => 'Må ikke vælges samtidig',
        ]
      ]
    ];
    
    return $config;
  }

  /**
   * Get available food items for signup
   */
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

  /**
   * Get all activities available for signup
   */
  public function getActivities() {
    // TODO Multiblock
    // TODO Signup Maximum
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
        'color' => 'lightgreen',
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
    $result->choices = self::ACTIVITY_CHOICES;
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
      usort($result->runs[$day], function($a, $b) {
        return $a->start['stamp'] - $b->start['stamp'];
      });
    }


    return $result;
  }

  /**
   * Get available wear for signup
   */
  public function getWear() {
    $result = (object) [];
    $result->wear = [];

    $wear = $this->createEntity('Wear');
    $wear_list = $wear->findAll();
    foreach($wear_list as $item) {
      $result_item = [
        'id' => $item->id,
        'name' => [
          'en' => $item->title_en,
          'da' => $item->navn,
        ],
        'desc' => [
          'en' => $item->description_en,
          'da' => $item->beskrivelse,
        ],
        'min_size' => $item->min_size,
        'max_size' => $item->max_size,
        'order' => $item->wear_order,
        'prices' => [],
      ];
      $prices = $item->getWearpriserSquashed();
      foreach($prices as $price) {
        $result_item['prices'][] = [
          'user_category' => $price->brugerkategori_id,
          'price' => $price->pris,
        ];
      }
      $result->wear[] = $result_item;
    }

    $result->sizes = [];
    $sizes = $wear->getWearSizes();
    foreach($sizes as $size) {
      $result->sizes[$size['size_id']] = [
        'order' => $size['size_order'],
        'name' => [
          'en' => $size['size_name_en'],
          'da' => $size['size_name_da'],
        ]
      ];
    }

    return $result;
  }

  public function submitSignup($data) {
    $participant = $this->createEntity('DummyParticipant');
    return $this->applySignup($data['signup'], $participant, $data['lang']);
  }

  public function confirmSignup($data) {
    if (!isset($data['info'])) {
      $participant = $this->createEntity('Deltagere');
      $participant->password = sprintf('%06d', mt_rand(0, 999999));
    } else {
      $participant = $this->createEntity('Deltagere')->findById($data['info']['id']);
      if ($participant->password != $data['info']['pass']) {
        return [$data['info'],['errors' => ['confirm' => [['type' => 'wrong_info']]]]];
      }
    }
    $result = $this->applySignup($data['signup'], $participant, $data['lang']);
    if ($participant->isLoaded()) {
      $res = $participant->update();
    } else {
      $res = $participant->insert();
    }

    if(!$res) {
      $result['errors']['confirm'][] = ['type' => 'database'];
    }

    return [
      [
        'id' => $participant->id,
        'pass' => $participant->password,
      ],
      $result,
    ];
  }

  /**
   * Create reverse lookup from infosys_id to page item
   */
  private function createLookup($page) {
    $lookup = [];
    foreach($page->sections as $skey => $section) {
      foreach($section->items as $ikey => $item) {
        if (isset($item->infosys_id)) {
          $section = $page->sections[$skey];
          $item = $section->$items[$ikey];
          $lookup[$item->infosys_id] = [
            'section' => $skey,
            'item' => $ikey,
            'disabled' => $item->disabled || $section->disabled,
            'required' => $item->required,
          ];
        }
      }
    }
    return $lookup;
  }

  /**
   * Apply signup data to a participant
   */
  public function applySignup($data, $participant, $lang) {
    $gdpr_accept = false;
    $errors = [];
    $categories = [];

    $participant->signed_up = date('Y-m-d H:i:s');
    // Reset orders
    $participant->removeEntrance();
    $participant->removeFood();
    $participant->removeActivitySignups();
    $participant->removeAllWear();
    $participant->removeDiySignup();

    foreach($data as $category => $items) {
      $entries = [];
      
      // Load file for the category/page
      $page_file = INCLUDE_PATH."signup-pages/$category.json";
      if(!is_file($page_file)) {
        $errors[$category][] = [
          'type' => 'missing_page',
          'info' => $category,
        ];
        continue;
      }
      $page = json_decode(file_get_contents($page_file));
      $lookup = $this->createLookup($page);

      // Check for missing
      foreach($lookup as $key => $value) {
        if ($value['required'] && !$value['disabled'] && !isset($items[$key])) {
          $errors[$category][] = [
            'type' => 'required',
            'info' => $category." ".$key,
            'id' => $key,
          ];
        }
      }

      foreach($items as $key => $value) {
        if ($lookup[$key]['disabled']) {
          $errors[$category][] = [
            'type' => 'disabled',
            'info' => $category." ".$key,
            'id' => $key,
          ];
          continue;
        }

        $key_parts = explode(":", $key, 2);
        if (count($key_parts) == 1) {
          switch($key) {
            case 'gdpr_accept':
              //TODO add to database
              $gdpr_accept = true;
              break;

            case 'participant':
              $bk  = $this->createEntity('BrugerKategorier');
              if ($value != 'organizer') {
                $sel = $bk->getSelect()->setWhere('navn', '=', $value);
                $bk->findBySelect($sel);
                $participant->brugerkategori_id = $bk->id;
              } else {
                if(!isset($participant->brugerkategori_id)) {
                  $participant->brugerkategori_id = $bk->getArrangoer()->id;
                }
              }
              break;

            case 'organizercategory':
              $participant->brugerkategori_id = $value;
              break;
            
            default:
              $columns = $this->createEntity('Deltagere')->getColumnInfo();
              if (!isset($columns[$key])) {
                $errors[$category][] = [
                  'type' => 'no_field',
                  'info' => "participant $key",
                ];
                continue 2;
              }

              $do_value = $value;
              if ($value == 'on' && !$participant->is_dummy) {
                $valid = $participant->getValidColumnValues($key);
                if ($valid['type'] == 'enum') {
                  $do_value = 'ja';
                }
              }

              $participant->$key = $do_value;
          }
        } else {
          $key_cat = $key_parts[0];
          $key_item = $key_parts[1];

          switch($key_cat) {
            case 'entry':
              $entry = $this->createEntity('Indgang');
              $select = $entry->getSelect();
              if ($key_item == 'partout') {
                // TODO find correct entrance price
                $select->setWhere('type', 'like', '%Indgang - Partout%');
              } else {
                $day = intval($key_item) -1;
                $date = new DateTime($this->config->get('con.start'));
                $date->add(new DateInterval("P{$day}D"));
                $select->setWhereDate('start', '=', $date->format('Y-m-d'));
                $select->setWhere('type', '=', 'Indgang - Enkelt');
              }
              $entry = $entry->findBySelect($select);
              $participant->setIndgang($entry);
              break;

            case 'sleeping':
              $entry = $this->createEntity('Indgang');
              $select = $entry->getSelect();
              if ($key_item == 'partout') {
                $select->setWhere('type', '=', 'Overnatning - Partout');
              } else {
                $day = intval($key_item) -1;
                $date = new DateTime($this->config->get('con.start'));
                $date->add(new DateInterval("P{$day}D"));
                $select->setWhereDate('start', '=', $date->format('Y-m-d'));
                $select->setWhere('type', '=', 'Overnatning - Enkelt');
              }
              $entry = $entry->findBySelect($select);
              $participant->setIndgang($entry);
              break;

            case 'misc':
              $entry = $this->createEntity('Indgang');
              $select = $entry->getSelect();
          
              switch($key_item) {
                case 'mattres':
                  $select->setWhere('type', '=', 'Leje af madras');
                  break;

                case 'party':
                  $select->setWhere('type', '=', 'Ottofest');
                  break;
  
                case 'bubbles':
                  $select->setWhere('type', '=', 'Ottofest - Champagne');
                  break;

                case 'alea':
                  $select->setWhere('type', 'like', 'Alea%');
                  break;
  
                default:
                  $errors[$category][] = [
                    'type' => 'no_field',
                    'info' => "$key_cat $key_item",
                  ];
                  continue 3;
              }

              $entry = $entry->findBySelect($select);
              $participant->setIndgang($entry);
              break;

            case 'food':
              if (intval($value) != 0) {
                $food = $this->createEntity('Madtider')->findById($value);  
              } else {
                $food = $this->createEntity('Madtider')->findById($key_item);
              }
              if (!$food) {
                $errors[$category][] = [
                  'type' => 'no_food',
                  'info' => "$key_cat:$key_item $value",
                ];
                continue 2;
              }
              $participant->setMad($food);
              break;

            case 'activity':
              $run = $this->createEntity('Afviklinger')->findbyId($key_item);
              if (!$run) {
                $errors[$category][] = [
                  'type' => 'no_run',
                  'info' => "$key_cat:$key_item $value",
                ];
                continue 2;
              }
              $value = intval($value);
              $choice_count = count(self::ACTIVITY_CHOICES['prio'][$lang]);
              if ($value <= $choice_count) {
                $participant->setAktivitetTilmelding($run, $value, 'spiller');
              } else {
                $participant->setAktivitetTilmelding($run, 0, 'spilleder');
                if ($value == $choice_count + 2) {// SL + 1st prio
                  $participant->setAktivitetTilmelding($run, 1, 'spiller');
                }
              }
              break;

            case 'wear':
              $user_category = $this->createEntity('BrugerKategorier')->findById($participant->brugerkategori_id);
              if(!$user_category) {
                $errors[$category][] = [
                  'type' => 'no_user_category',
                  'info' => "$key_cat $key_item",
                ];
                continue 2;
              }

              $wear = $this->createEntity('Wear')->findById($key_item);
              $wear_prices = $wear->getWearpriser($user_category);

              if (count($wear_prices) == 0) {
                $errors[$category][] = [
                  'type' => 'no_wear_price',
                  'info' => "$key_cat $key_item",
                  'user_category_id' => $user_category->id,
                  'wear_id' => $wear->id,
                ];
                continue 2;
              }

              preg_match('/amount:(\d+)/', $value, $amount_match);
              preg_match('/size:(\d+)/', $value, $size_match);
              $participant->setWearOrder($wear_prices[0], $size_match[1], $amount_match[1]);
              $entries[] = [
                'key' => $key,
                'size' => $size_match[1],
                'amount' => $amount_match[1],
              ];
              continue 2;
  
            default:
              $errors[$category][] = [
                'type' => 'no_field',
                'info' => "$key_cat $key_item",
              ];
              continue 2;
          }
        }
        $entries[] = [
          'key' => $key,
          'value' => $value,
        ];
      }

      if (count($entries) > 0) {
        $categories[$category] = $entries;
      }
    }

    // Set some defaults if missing
    if (!isset($participant->medical_note)) $participant->medical_note = '';
    if (!isset($participant->gcm_id)) $participant->gcm_id = '';

    return [
      'errors' => $errors,
      'categories' => $categories,
    ];
  }
}