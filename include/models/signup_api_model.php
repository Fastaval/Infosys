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
  public function getConfig($module) {
    $config = (object)[
      'age_young'   => 18,
      'age_kid'     => 13,
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
      ],
      'sub_total' => [
        'en' => 'Sub total',
        'da' => 'Sub total',
      ],
      'pieces' => [
        'en' => 'pcs.',
        'da' => 'stk.'
      ],
      'dkk' => [
        'en' => 'DKK',
        'da' => 'kr.'
      ]
    ];

    if ($module == 'main') {
      return json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    }

    $config_file = SIGNUP_FOLDER."config/$module.json";
    if(!is_file($config_file)) die("Config data not found");

    return file_get_contents($config_file);
  }

  public function getAllPages() {
    $pages = [];

    $page_files = glob(SIGNUP_FOLDER."pages/*");
    foreach($page_files as $page_file){ // iterate files
      if(!is_file($page_file)) continue;
      $name = basename($page_file, ".json");
      $pages[$name] = json_decode(file_get_contents($page_file));
    }

    return $pages;
  }

  public function getPage($page_id) {
    $page_file = SIGNUP_FOLDER."pages/$page_id.json";
    if(!is_file($page_file)) die("Signup page not found");

    return file_get_contents($page_file);
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
        'position' => $item->position,
        'prices' => [],
        'max_order' => $item->max_order,
        'variants' => $item->getVariants(),
        'images' => $item->getImages(),
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
      if (!$participant || $participant->password != $data['info']['pass']) {
        return [$data['info'],['errors' => ['confirm' => [['type' => 'wrong_info']]]]];
      }
    }
    $result = $this->applySignup($data['signup'], $participant, $data['lang']);
    
    if(count($result['errors']) == 0) {
      // Update/create participant
      if ($participant->isLoaded()) {
        $res = $participant->update();
      } else {
        $res = $participant->insert();
      }
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
          $lookup[$item->infosys_id] = [
            'item' => $item,
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
    $errors = $categories = [];
    $is_alea = $is_organizer = $junior_plus = false;
    $total = 0;
    $junior_note = "";
    $sprog = [];

    $participant->signed_up = date('Y-m-d H:i:s');
    // Reset orders
    $participant->removeEntrance();
    $participant->removeFood();
    $participant->removeActivitySignups();
    $participant->removeAllWear();
    $participant->removeDiySignup();

    $column_info = $this->createEntity('Deltagere')->getColumnInfo();

    foreach($data as $category => $items) {
      $entries = [];
      $category_total = 0;
      
      // Load file for the category/page
      $page_file = SIGNUP_FOLDER."pages/$category.json";
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
          if ($value['item']->no_submit) continue;
          $errors[$category][] = [
            'type' => 'required',
            'info' => $category." ".$key,
            'id' => $key,
          ];
        }
      }

      foreach($items as $key => $value) {
        $price = 0;
        $extra = [];

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
            case 'participant':
              $bk  = $this->createEntity('BrugerKategorier');
              if ($value != 'Organizer') {
                $participant->brugerkategori_id = $bk->findByname($value)->id;
              } else {
                $is_organizer = true;
                if(!isset($participant->brugerkategori_id)) {
                  $participant->brugerkategori_id = $bk->getArrangoer()->id;
                }
              }
              break;

            case 'organizercategory':
              $is_organizer = true;
              $participant->brugerkategori_id = $value;
              break;

            case 'wear_orders':
              // We assume the user category has been set at this point
              $user_category = $this->createEntity('BrugerKategorier')->findById($participant->brugerkategori_id);
              if(!$user_category) {
                $errors[$category][] = [
                  'type' => 'no_user_category',
                  'info' => "$key_cat $key_item",
                ];
                continue 2;
              }

              foreach($value as $wear_order) {
                $wear = $this->createEntity('Wear')->findById($wear_order['wear_id']);
                $wear_prices = $wear->getWearpriser($user_category);
  
                // If there is no price for junior, check regular participant prices
                if (count($wear_prices) == 0 && $junior_plus) {
                  $user_category = $this->createEntity('BrugerKategorier')->getDeltager();
                  $wear_prices = $wear->getWearpriser($user_category);
                }

                if (count($wear_prices) == 0) {
                  $errors[$category][] = [
                    'type' => 'no_wear_price',
                    'info' => "wear_order",
                    'user_category_id' => $user_category->id,
                    'wear_id' => $wear->id,
                  ];
                  continue 3;
                }
                
                if(!$participant->setWearOrder($wear_prices[0], $wear_order['amount'], $wear_order['attributes'])) {
                  $errors[$category][] = [
                    'type' => 'wear_order_fail',
                    'info' => "wear_order",
                    'user_category_id' => $user_category->id,
                    'wear_id' => $wear->id,
                    'amount' => $wear_order['amount'],
                    'attributes' => $wear_order['attributes'],
                  ];
                  continue 3;
                }

                $price = $wear_prices[0]->pris * $wear_order['amount'];
                $entries[] = [
                  'special_module' => 'wear',
                  'key' => $key,
                  'value' => $wear_order,
                  'wear_id' => $wear->id,
                  'price' => $price,
                  'amount' => $wear_order['amount'],
                  'attributes' => $wear_order['attributes'],
                  'single_price' => $wear_prices[0]->pris,
                ];
        
                $category_total += $price;
              }
              continue 2;

            default:
              if (!isset($column_info[$key])) {
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
            case 'junior':
              if ($key_item == 'plus') {
                $junior_plus = true;
                break;
              }

              $labels = [
                'contact_name' => 'Navn',
                'contact_number' => 'Telefon',
                'contact_mail' => 'Email',
              ];
              if (!isset($labels[$key_item])) {
                $errors[$category][] = [
                  'type' => 'no_field',
                  'info' => "$key_cat $key_item",
                ];
                continue 2;
              }
              $junior_note .= "$labels[$key_item]: $value\n";
              break;

            case 'entry':
              $entry = $this->createEntity('Indgang');
              $select = $entry->getSelect();
              if ($key_item == 'partout') {
                $config = json_decode($this->getConfig('main'));
                $age = $participant->getAge(new DateTime($config->con_start));
                $select->setWhere('type', 'like', '%Indgang - Partout%');
                if ($age < $config->age_young) {
                  if ($age < $config->age_kid) {
                    $select->setWhere('type', 'like', '%Barn%');  
                  } else {
                    $select->setWhere('type', 'like', '%Ung%');
                  }
                }
                // NB! We assume Alea signup is earlier or same page
                if ($age >= $config->age_kid && ($is_alea || $items->{'misc:alea'})) {
                  $select->setWhere('type', 'like', '%Alea%');
                }
                // NB! We assume organizer setting is before this
                if ($age >= $config->age_kid && $is_organizer) {
                  $select->setWhere('type', 'like', '%Arrangør%');
                }
              } else {
                $day = intval($key_item) -1;
                $date = new DateTime($this->config->get('con.start'));
                $date->add(new DateInterval("P{$day}D"));
                $select->setWhereDate('start', '=', $date->format('Y-m-d'));
                $select->setWhere('type', '=', 'Indgang - Enkelt');
              }
              $entry = $entry->findBySelect($select);
              if (!$entry) {
                $errors[$category][] = [
                  'type' => 'no_entry',
                  'info' => "$key_cat:$key_item $value",
                  'age'  => $age,
                  'alea' => ($is_alea || $items->{'misc:alea'}),
                  'organizer' => $is_organizer,
                ];
                continue 2;
              }
              $participant->setIndgang($entry);
              $price = $entry->pris;
              break;

            case 'sleeping':
              $entry = $this->createEntity('Indgang');
              $select = $entry->getSelect();
              if ($key_item == 'partout') {
                $select->setWhere('type', 'like', 'Overnatning - Partout%');
                // NB! We assume organizer setting is before this
                if ($age >= $config->age_kid && $is_organizer) {
                  $select->setWhere('type', 'like', '%Arrangør%');
                }
              } else {
                $day = intval($key_item) -1;
                $date = new DateTime($this->config->get('con.start'));
                $date->add(new DateInterval("P{$day}D"));
                $select->setWhereDate('start', '=', $date->format('Y-m-d'));
                $select->setWhere('type', '=', 'Overnatning - Enkelt');
              }
              $entry = $entry->findBySelect($select);
              if (!$entry) {
                $errors[$category][] = [
                  'type' => 'no_entry',
                  'info' => "$key_cat:$key_item $value",
                  'organizer' => $is_organizer,
                ];
                continue 2;
              }
              $participant->setIndgang($entry);
              $price = $entry->pris;
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
                  $is_alea = true;
                  $select->setWhere('type', '=', 'Alea medlemskab');
                  break;
                
                case 'ticket_fee':
                  $select->setWhere('type', '=', 'Billetgebyr');
                  break;

                case 'extra_support':
                  $amount = min(9, floor(intval($value) / 100));
                  $select->setWhere('type', '=', "Rig onkel - {$amount}00");
                  $participant->rig_onkel = 'ja';
                  $value = 'on';
                  break;
  
                case 'secret_support':
                  $amount = min(9, floor(intval($value) / 100));
                  $select->setWhere('type', '=', "Hemmelig onkel - {$amount}00");
                  $participant->hemmelig_onkel = 'ja';
                  $value = 'on';
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
              $price = $entry->pris;
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
              $price = $food->getMad()->pris;
              break;

            case 'hero':
              [$day, $time] = explode("-", $key_item, 2);
              $day = intval($day) -3;
              $date = new DateTime($this->config->get('con.start'));
              $date->add(new DateInterval("P{$day}D"));
              $date_string = $date->format('Y-m-d');
              switch($time) {
                case 'morning':
                  $period = "7-12";
                  break;

                case 'afternoon':
                  $period = "12-17";
                  break;

                case 'evening':
                  $period = "17-24";
                  break;

                default:
                $errors[$category][] = [
                  'type' => 'unknown_time',
                  'info' => "$key_cat:$key_item $value",
                ];
                continue 2;
              }

              $participant->setGDSTilmelding(null, "$date_string $period");
              break;

            case 'activity':
              // TODO check for alder
              // TODO check for max tilmeldinger 
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
              $price = $run->getAktivitet()->pris;
              break;

            case 'activity_language':
              $sprog[] = $key_item;
              break;

            case 'note':
              $participant->setNote($key_item, $value);
              break;

            default:
              $errors[$category][] = [
                'type' => 'no_field',
                'info' => "$key_cat $key_item",
              ];
          }
        }
        $entries[] = array_merge([
          'key' => $key,
          'value' => $value,
          'price' => $price,
        ], $extra);

        $category_total += $price;
      }

      if (count($entries) > 0) {
        $entries[] = [
          'key' => 'sub_total',
          'value' => $category_total,
        ];
        $categories[$category] = $entries;
        $total += $category_total;
      }
    }

    // Languages
    $participant->setSprog($sprog);

    // Notes
    if ($junior_note) $participant->setNote('junior_ward', $junior_note);

    // Set some defaults if missing
    if (!isset($participant->medical_note)) $participant->medical_note = '';
    if (!isset($participant->gcm_id)) $participant->gcm_id = '';

    return [
      'errors' => $errors,
      'categories' => $categories,
      'total' => $total,
    ];
  }

  /**
   * Retrieve participant data in format used by signup plugin
   */
  public function loadSignup($id, $pass) {
    // Find by ID
    $participant = $this->createEntity('Deltagere')->findById($id);
    // Find by email instead
    if(!$participant) {
      $select = $this->createEntity('Deltagere')->getSelect();
      $select->setWhere('email', '=', $id);
      $select->setWhere('password', '=', $pass);

      $participant = $this->createEntity('Deltagere')->findBySelect($select);
    }
    if (!$participant || $participant->password != $pass) {
      return ['signup' => [],'errors' => [['type' => 'wrong_info']]];
    }

    $column_info = $participant->getColumnInfo();
    $signup = $errors = [];
    $entrance = false;

    foreach($this->getAllPages() as $page_id => $page_data) {
      $lookup = $this->createLookup($page_data);
      // Collect simple properties
      foreach($lookup as $key => $info) {
        $value = '';
        $key_parts = explode(":", $key, 2);
        if (count($key_parts) == 1) {
          switch($key) {
            case 'participant':
              $bid = $participant->brugerkategori_id;
              $bk  = $this->createEntity('BrugerKategorier')->findById($bid);
              if ($bk->isArrangoer()) {
                $value = 'Organizer';
              } else {
                $value = $bk->navn;
              }
              break;

            case 'organizercategory':
              $bid = $participant->brugerkategori_id;
              $bk  = $this->createEntity('BrugerKategorier')->findById($bid);
              if ($bk->isArrangoer()) {
                $value = $participant->brugerkategori_id;
              }
              break;
            
            case 'birthdate':
              $value = substr($participant->birthdate, 0, 10);
              break;

            case 'ready_mandag':
            case 'ready_tirsdag':
              $signup['together:prepare'] = 'on';
              // Notice no break here
            default:
              if (!isset($column_info[$key])) {
                // Value is the same as a different field
                if($equals = $lookup[$key]['item']->equals) {
                  $value = $participant->$equals;
                  break;
                }
                // Value isn't submitted
                if($lookup[$key]['item']->no_submit) break;
                $errors[] = [
                  'type' => 'no_field',
                  'info' => "participant $key",
                  'item' => $info['item'],
                ];
                continue 2;
              }

              $value = $participant->$key;
              if ($value == 'ja') $value = 'on';
              if ($value == 'nej') $value = '';
          }
        }
        $signup[$key] = $value;
      }
    }

    // Collect entrance items
    foreach($participant->getIndgang() as $entrance) {
      switch(true) {
        case $entrance->isPartout():
          if ($entrance->isEntrance()) {
            $signup['entry:partout'] = 'on';
            $entrance = true;
          } else {
            $signup['sleeping:partout'] = 'on';
          }
          break;

        case  $entrance->isDayTicket() || $entrance->isSleepTicket():
          $type = $entrance->isDayTicket() ? 'entry' : 'sleeping';
          $entrance = $type == 'entry' ? true : $entrance;
          $start = new DateTime($entrance->start);
          $day = intval($start->format('d')) -2;
          $signup["$type:$day"] = 'on';
          break;

        case $entrance->type == 'Leje af madras':
          $signup['misc:mattres'] = 'on';
          break;

        case $entrance->type == 'Ottofest':
          $signup['misc:party'] = 'on';
          break;

        case $entrance->type == 'Ottofest - Champagne':
          $signup['misc:bubbles'] = 'on';
          break;

        case $entrance->type == 'Alea medlemskab':
          $signup['misc:alea'] = 'on';
          break;

        case $entrance->type == 'Billetgebyr':
          $signup['misc:ticket_fee'] = 'on';
          break;

        case preg_match("/Rig onkel - (\d+)/", $entrance->type, $matches):
          $signup['misc:extra_support'] = $matches[1];
          break;

        case preg_match("/Hemmelig onkel - (\d+)/", $entrance->type, $matches):
          $signup['misc:secret_support'] = $matches[1];
          break;

        default:
          $errors[] = [
            'type' => 'unknown_entry',
            'info' => $entrance->type,
          ];
      }
    }

    // Collect food order
    foreach($participant->getMadtider() as $food) {
      if ($food->isDinner()) {
        $day = date('N', strtotime($food->dato));
        $signup['food:dinner'.$day] = $food->id;
      } else {
        $signup['food:'.$food->id] = $food->id;
      }
    }

    // Collect hero-task signup
    foreach($participant->getGDSTilmeldinger() as $hero_signup) {
      $period = $hero_signup->period;
      preg_match("/(\d{4}-\d{2}-\d{2}) (\d{2}-\d{2})/", $period, $match);
      $day = date('N', strtotime($match[1]));
      switch($match[2]) {
        case "7-12":
          $time = 'morning';
          break;

        case "12-17":
          $time = 'afternoon';
          break;

        case "17-24":
          $time = 'evening';
          break;

        default:
          $errors[] = [
            'type' => 'unknown_time',
            'period' => $period,
          ];
          continue 2;
      }
      $signup["hero:$day-$time"] = 'on';
      $signup['together:hero'] = 'on';
    }

    // Collect activity signup
    foreach ($participant->getTilmeldinger() as $run_signup) {
      $run_id = $run_signup->afvikling_id;
      $prio = $run_signup->prioritet;
      $type = $run_signup->tilmeldingstype;
      if ($type == 'spilleder') {
        $prio = count(self::ACTIVITY_CHOICES['prio']['en']) +1;
        if($run_signup->getAktivitet()->type == 'braet') {
          $signup['together:gm'] = 'on';
        } else {
          $signup['together:rules'] = 'on';
        }
      }
      if (isset($signup["activity:$run_id"])) {
        // We have a combination of player and GM signup
        $signup["activity:$run_id"] += $prio;
      } else {
        $signup["activity:$run_id"] = $prio;
      }
    }

    // Activity languages
    foreach($participant->getSprog() as $sprog) {
      $signup['activity_language:'.$sprog] = 'on';
    }

    // Collect wear orders
    $signup["wear_orders"] = [];
    foreach($participant->getWear() as $wear_order) {
      $wearprice = $wear_order->getWearpris();
      
      $order = [
        'wear_id' => $wear_order->getWear()->id,
        'amount' => $wear_order->antal,
        'price' => $wearprice->pris,
        'price_category' => $wearprice->brugerkategori_id,
        'attributes' => [],
      ];
      
      foreach($wear_order->getAttributes() as $type => $id) {
        $order['attributes'][$type] = $id;
      }

      $signup["wear_orders"][$wear_order->id] = $order;
    }

    // Get notes
    foreach($participant->note as $key => $note) {
      if ($key == 'junior_ward') {
        $ids = [
          'Navn' => 'contact_name',
          'Telefon' => 'contact_number',
          'Email' => 'contact_mail',
        ];
        foreach(explode("\n", $note->content) as $line) {
          [$label, $value] = explode(":", $line);
          $signup['junior:'.$ids[$label]] = trim($value);
        }
      } else {
        $signup['note:'.$key] = $note->content;
      }
    }

    // Check for junior:plus
    if ($entrance && $signup['participant'] == 'Juniordeltager') {
      $signup['junior:plus'] = 'on';
    }

    return [
      'signup' => $signup,
      'errors' => $errors,
    ];
  }
}