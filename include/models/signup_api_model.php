<?php

class SignupApiModel extends Model {

  /**
   * Get list of configuration files related to signup
   */
  public function getConfigList() {
    $config_files = glob(SIGNUP_FOLDER."config/*");
    foreach($config_files as $config_file){ // iterate files
      if(!is_file($config_file)) continue;
      
      $list[] = basename($config_file, ".json");
    }

    return $list;
  }

  /**
   * Get a configuration related to signup
   */
  public function getConfig($module) {
    $config_file = SIGNUP_FOLDER."config/$module.json";
    if(!is_file($config_file)) die("Config data not found");
    $content = file_get_contents($config_file);

    if ($module == 'main') {
      $config = json_decode($content);
      $config->signup_end = $this->config->get('con.signupend');
      $config->con_start = $this->config->get('con.start');
      $config->autocomplete = [
        'organizer_categories' => $this->loadOrganizerCategories(),
        'countries' => $this->loadCountries(),
        'game_titles' => $this->loadGameTitles(),
      ];

      // Count financial support
      $query = "SELECT COUNT(*) as count FROM deltagere WHERE financial_struggle = 'ja'";
      $result = $this->db->query($query);
      if (count($result) == 1) {
        $config->current_financial_support = $result[0]['count'];
      }

      // Count junior participants
      $query = "SELECT COUNT(*) as count FROM deltagere WHERE brugerkategori_id = 10"; //Junior participants have ID 10
      $result = $this->db->query($query);
      if (count($result) == 1) {
        $config->current_junior_participants = $result[0]['count'];
        $config->junior_closed = $result[0]['count'] >= ($config->max_junior_tickets + $config->max_junior_reserve);
      }

      // Count bus tickets
      $query = 
        "SELECT COUNT(*) as count FROM deltagere d 
          JOIN deltagere_indgang di ON d.id = di.deltager_id 
          JOIN indgang i ON di.indgang_id = i.id
        WHERE i.type = 'Fastavalbussen'";
      $result = $this->db->query($query);
      if (count($result) == 1) {
        $config->current_bus_tickets = $result[0]['count'];
      }

      return json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    }

    return $content;
  }

  public function getAllPages() {
    $pages = [];

    $skip_pages = null;
    $disabled_items = null;
    // Skip certain pages for late signup
    if (strtotime('now') > strtotime($this->config->get('con.signupend'))) {
      $config_file = SIGNUP_FOLDER."config/main.json";
      $config = json_decode(file_get_contents($config_file));
      $skip_pages = $config->main_signup_only->pages;
      $disabled_items = $config->main_signup_only->items;
      $disabled_options = $config->main_signup_only->options;
    }

    $page_files = glob(SIGNUP_FOLDER."pages/*");
    foreach($page_files as $page_file){ // iterate files
      if(!is_file($page_file)) continue;
      
      $name = basename($page_file, ".json");
      
      if ($skip_pages && in_array($name, $skip_pages)) continue;
      $page = json_decode(file_get_contents($page_file));

      if (isset($disabled_items->$name)) {
        $page->disabled_items = $disabled_items->$name;
      }

      if (isset($disabled_options->$name)) {
        $page->disabled_options = $disabled_options->$name;
      }

      $pages[$name] = $page;
    }

    return $pages;
  }

  public function getPage($page_id) {
    $page_file = SIGNUP_FOLDER."pages/$page_id.json";
    if(!is_file($page_file)) throw new FrameworkException("Signup page not found");

    $content = file_get_contents($page_file);

    // If this is not a late signup, just return file content
    if (strtotime('now') <= strtotime($this->config->get('con.signupend'))) return $content;

    $config_file = SIGNUP_FOLDER."config/main.json";
    $config = json_decode(file_get_contents($config_file));
    $disabled_items = $config->main_signup_only->items;
    $disabled_options = $config->main_signup_only->options;

    // If page dosen't have any disabled items or options, just return file content
    if (!isset($disabled_items->$page_id) && !isset($disabled_options->$page_id)) return $content;

    // Add disabled items or options to output
    $page = json_decode($content);

    if (isset($disabled_items->$page_id)) {
      $page->disabled_items = $disabled_items->$page_id;
    }

    if (isset($disabled_options->$page_id)) {
      $page->disabled_options = $disabled_options->$page_id;
    }

    return $page;
  }


  /**
   * Get available food items for signup
   */
  public function getFood() {
    $result = (object)[];

    $categories = [
      12 => [
        'exclude' => 18,
      ],
      18 => [
        'exclude' => 12,
      ],
    ];
    $food_entity = $this->createEntity('Mad');
    $food_select = $food_entity->getSelect();
    $food_select->setWhere('hidden', '=', 'false');
    foreach($food_entity->findbySelectMany($food_select) as $food) {
      $categories[$food->id]['price'] = $food->pris;
      $categories[$food->id]['en'] = $food->title_en;
      $categories[$food->id]['da'] = $food->kategori;
    }

    $result->categories = $categories;

    $food =  $this->createEntity('Madtider')->findAll();
    usort($food, function($a, $b) {
      return strtotime($a->dato) - strtotime($b->dato);
    });
    
    foreach($food as $fooditem) {
      // Skip hidden food
      if (!isset($categories[$fooditem->mad_id])) continue;

      $resultitem = (object) [];
      $resultitem->id = $fooditem->id;
      $resultitem->text = [
        'da' => $fooditem->description_da,
        'en' => $fooditem->description_en,
      ];
      $day = date('N', strtotime($fooditem->dato));
      $result->days[$day][$fooditem->mad_id] = $resultitem;
    }
    return $result;
  }

  /**
   * Get all activities available for signup
   */
  public function getActivities() {
    $result = (object)[];
    $config = json_decode($this->getConfig('activities'));

    // Collect activity info
    $activitities = $this->createEntity('Aktiviteter')->findAll();
    foreach ($activitities as $activity) {
      if ($activity->hidden == 'ja' || $activity->type == 'system') continue;
      $activity_info = (object)[];
      $activity_info->can_sign_up = $activity->kan_tilmeldes == 'ja';
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
      $activity_info->lang_gm = $activity->lang_gm;
      $activity_info->desc = [
        'en' => $activity->description_en,
        'da' => $activity->foromtale,
      ];
      $activity_info->title = [
        'da' => $activity->navn,
        'en' => $activity->title_en
      ];
      $activity_info->max_signups = $activity->max_signups;

      $result->activities[$activity->id] = $activity_info;
    }

    // Collect signups
    $query = "SELECT afvikling_id, count(deltager_id) AS signed_up FROM deltagere_tilmeldinger WHERE tilmeldingstype = 'spiller' GROUP BY afvikling_id";
    $signups = $this->db->query($query);
    $run_signups = [];
    foreach($signups as $row) {
      $run_signups[$row['afvikling_id']] = $row['signed_up'];
    }

    // Get info on multiblock runs
    $has_multi = [];
    $multiblocks = $this->createEntity('AfviklingerMultiblok')->findAll();
    if (count($multiblocks) > 0) foreach ($multiblocks as $key => $multiblock) {
      $has_multi[$multiblock->afvikling_id][] = $key;
    }

    $set_time = function($time) {
      return [
        'day' => date('N', strtotime($time)),
        'hour' => intval(date('H', strtotime($time))),
        'min' => intval(date('i', strtotime($time))),
        'stamp' => strtotime($time),
      ];
    };

    // Collect info on runs
    $runs = $this->createEntity('Afviklinger')->findAll();
    foreach ($runs as $run) {
      if (!isset($result->activities[$run->aktivitet_id])) continue;
      $run_info = (object)[];
      $run_info->id = $run->id;
      $run_info->activity = $run->aktivitet_id;
      $run_info->activity_name = $result->activities[$run->aktivitet_id]->title['da'];
      $run_info->start = $set_time($run->start);
      $run_info->end = $set_time($run->slut);
      $run_info->signups = $run_signups[$run->id] ?? 0;
      $day = $run_info->start['day'];
      if($run_info->start['hour'] < $config->day_cutoff) $day--; // Put runs that start late together with the day before
      
      if(!empty($has_multi[$run->id])) {
        $run_info->multi = true;
        $result->runs[$day][] = $run_info;

        // Copy info for each multi block
        foreach ($has_multi[$run->id] as $multi_id) {
          $multirun = $multiblocks[$multi_id];
          $run_info = clone $run_info;
          $run_info->start = $set_time($multirun->start);
          $run_info->end = $set_time($multirun->slut);
          $day = $run_info->start['day'];
          if($run_info->start['hour'] < $config->day_cutoff) $day--; // Put runs that start late together with the day before
          $result->runs[$day][] = $run_info;
        } 
      } else {
        $result->runs[$day][] = $run_info;
      }
    }
    
    foreach($result->runs as $day => $runs) {
      usort($result->runs[$day], function($a, $b) {
        if ($a->start['stamp'] == $b->start['stamp']) {
          if($a->end['stamp'] == $b->end['stamp']) {
            return strcmp($a->activity_name, $b->activity_name);
          }
          return $a->end['stamp'] - $b->end['stamp'];
        }
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
        'required' => $item->is_required,
        'variants' => $item->getVariants(),
        'images' => $item->getImages(),
      ];
      $prices = $item->getWearpriser();
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
    if (isset($data['info']['id'])) $participant->id = $data['info']['id'];
    return $this->applySignup($data['signup'], $participant, $data['lang']);
  }

  public function confirmSignup($data) {
    if (!isset($data['info'])) {
      // Create barebone participant
      $participant = $this->createEntity('Deltagere');
      $participant->password = sprintf('%06d', mt_rand(0, 999999));
      $participant->fornavn = "";
      $participant->efternavn = "";
      $participant->adresse1 = "";
      $participant->postnummer = "";
      $participant->by = "";
      $participant->brugerkategori_id = 1;
      $participant->birthdate = '0000-00-00 00:00:00';
      $participant->medical_note = '';
      $participant->gcm_id = '';
      $participant->insert();
    } else {
      $participant = $this->createEntity('Deltagere')->findById($data['info']['id']);
      if (!$participant || $participant->password != $data['info']['pass']) {
        return [$data['info'],['errors' => ['confirm' => [['type' => 'wrong_info']]]]];
      }
    }
    $result = $this->applySignup($data['signup'], $participant, $data['lang']);
    
    if(count($result['errors']) == 0) {
      // Update participant
      try {
        if(!$participant->update()) {
          throw new FrameworkException("Failed update on participant\nParticipant:".print_r($participant, true));
        }
      } catch (FrameworkException $error) {
        $this->fileLog("Failed to create participant from data with hash: $data[hash]");
        $error->logException();
        $result['errors']['confirm'][] = ['type' => 'database'];

        // Delete participant if this was a new entry that failed
        if (!isset($data['info'])) $participant->delete();
      }
    }

    return [
      $result,
      $participant,
    ];
  }

  /**
   * Create reverse lookup from infosys_id to page item
   */
  private function createLookup($page) {
    $lookup = [];
    $disabled_items = $page->disabled_items ?? [];
    foreach($page->sections as $skey => $section) {
      if(!isset($section->items)) continue;
      foreach($section->items as $ikey => $item) {
        if (isset($item->infosys_id)) {
          $lookup[$item->infosys_id] = [
            'item' => $item,
            'disabled' => ($item->disabled ?? false) || ($section->disabled ?? false) || in_array($item->infosys_id, $disabled_items),
            'required' => $item->required ?? false,
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
    // Initialize variables
    $errors = $categories = [];
    $is_alea = $is_organizer = $junior_plus = false;
    $total = 0;
    $junior_contact = "";
    $sprog = [];
    $sleeping_areas = [];
    $food_credits = [
      'breakfast' => 0,
      'dinner' => 0,
    ];
    
    // Load config and other info 
    $late_signup = strtotime('now') > strtotime($this->config->get('con.signupend'));
    $config = [
      'main' => json_decode($this->getConfig('main')),
      'activities' => json_decode($this->getConfig('activities')),
    ];
    $choice_count = count($config['activities']->choices->prio->$lang);
    $column_info = $this->createEntity('Deltagere')->getColumnInfo();

    // Load full runs
    $args = [];
    $id_query = '';
    if (isset($participant->id)) {
      $id_query = "AND deltager_id <> ?";
      $args[] = $participant->id;
    }
    
    $query = 
      "SELECT afvikling_id, max_signups, count(deltager_id) AS signed_up
      FROM deltagere_tilmeldinger AS dt
      JOIN afviklinger AS af ON dt.afvikling_id = af.id
      JOIN aktiviteter AS ak ON af.aktivitet_id = ak.id
      WHERE tilmeldingstype = 'spiller' $id_query GROUP BY afvikling_id";
    $run_signup_result = $this->db->query($query, $args);
    $full_runs = [];
    foreach($run_signup_result as $row) {
      if ($row['max_signups'] != 0 && $row['signed_up'] >= $row['max_signups']) {
        $full_runs[$row['afvikling_id']] = true;
      } 
    }

    // Set signup time
    $participant->signed_up = date('Y-m-d H:i:s');
    
    // Reset entrance types
    // Don't remove bank transfer fee (37)
    // Don't remove sparkling wine (81) from late signup
    if ($late_signup) {
      $participant->removeEntranceExcept([81, 37]);
    } else {
      $participant->removeEntranceExcept([37]);
    }

    // Reset DIY
    $participant->removeDiySignup();

    foreach($data as $category => $items) {
      // Check for disabled pages on late signup
      if ($late_signup && in_array($category, $config['main']->main_signup_only->pages)) {
        $categories[$category] = [[
          'special_module' => 'submit',
          'warning' => 'page_disabled',
        ]];
        continue;
      }

      $entries = [];
      $category_total = 0;

      // Reset certain areas before processing page
      switch ($category) {
        case "food":
          $participant->removeOrderedFood();
          break;
        
        case "activity":
          $participant->removeActivitySignups();
          break;

        case "wear":
          $participant->removeAllWear();
          break;
      }
      
      // Load file for the category/page
      try {
        $page = $this->getPage($category);
        if (is_string($page)) $page = json_decode($page);
      } catch (FrameworkException $e) {
        $errors[$category][] = [
          'type' => 'missing_page',
          'info' => $category,
        ];
        continue;
      }
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

        if (isset($lookup[$key]) && $lookup[$key]['disabled']) {
          $entries[] = [
            'special_module' => 'submit',
            'warning' => 'input_disabled',
            'id' => $key,
          ];
          continue;
        }

        $key_parts = explode(":", $key, 2);
        if (count($key_parts) == 1) {
          switch($key) {
            case 'participant':
              $bk  = $this->createEntity('BrugerKategorier');
              $participant->brugerkategori_id = $bk->findByname($value)->id;
              $is_organizer = $value == 'Arrangør';
              break;

            case 'wear_orders':
              // We assume the user category has been set at this point
              $user_category = $this->createEntity('BrugerKategorier')->findById($participant->brugerkategori_id);
              if(!$user_category) {
                $errors[$category][] = [
                  'type' => 'no_user_category',
                  'info' => "$key $value",
                ];
                continue 2;
              }

              foreach($value as $wear_order) {
                $wear = $this->createEntity('Wear')->findById($wear_order['wear_id']);
                $wear_prices = $wear->getWearpriser($user_category);
  
                // Check if we have a price for regular participants
                if (count($wear_prices) == 0) {
                  $regular_participant = $this->createEntity('BrugerKategorier')->getDeltager();
                  $wear_prices = $wear->getWearpriser($regular_participant);
                }

                if (count($wear_prices) == 0) {
                  $errors[$category][] = [
                    'type' => 'no_wear_price',
                    'module' => 'wear',
                    'info' => "wear_order",
                    'user_category_id' => $user_category->id,
                    'wear_id' => $wear->id,
                  ];
                  continue;
                }
                
                // Check for allowed amount 
                $wear_count = intval($wear_order['amount']);
                $max = $wear->max_order > 0 ? $wear->max_order : 10;
                if ($wear_count > $max || $wear_count < 1) {
                  $errors[$category][] = [
                    'type' => 'wear_order_range',
                    'amount' => $wear_order['amount'],
                    'max_order' => $max,
                    'wear_id' => $wear->id,
                    'attributes' => $wear_order['attributes'],
                    'module' => 'wear',
                  ];
                  continue 3;
                }

                if(!$participant->setWearOrder($wear_prices[0], $wear_order['amount'], $wear_order['attributes'] ?? [])) {
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
                  'attributes' => $wear_order['attributes'] ?? [],
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

              if ($value == 'on') {
                if ($key == 'sober_sleeping') $sleeping_areas[] = 'sober';
                if ($key == 'sovesal') $sleeping_areas[] = 'organizer';
              }

              // Shorten nickname
              if ($key == 'nickname') $value = substr($value, 0, 17);

              $do_value = $value;
              if (!$participant->is_dummy) {
                $valid = $participant->getValidColumnValues($key);
                if (isset($valid['type']) && $valid['type'] == 'enum') {
                  $do_value = $value == 'on' ? 'ja' : $do_value;
                  $do_value = $value == 'off' ? 'nej' : $do_value;
                  if (!in_array($do_value, $valid['values'])) {
                    $errors[$category][] = [
                      'type' => 'wrong_enum',
                      'id' => $key,
                      'value' => $value,
                      'valid' => $valid,
                    ];
                    continue 2;
                  }
                }
                if (isset($valid['type']) && $valid['type'] == 'int' && $value == "") $do_value = 0;
              }

              $participant->$key = $do_value;

              // Save age for later
              if ($key == 'birthdate') {
                $age = $participant->getAge(new DateTime($config['main']->con_start));
              }

              // Set food credits for financial support
              if ($key == 'financial_struggle' && $value == 'on') {
                $food_credits['breakfast'] = 2;
                $food_credits['dinner'] = 2;
              }

              if ($value == "" || $value == 0 || $value == 'off') {
                // No need to show empty fields on breakdown
                continue 2;
              }
          }
        } else {
          $key_cat = $key_parts[0];
          $key_item = $key_parts[1];

          if ($value === 'off') continue;
          
          switch($key_cat) {
            case 'junior':

              if ($key_item == 'entrance') {
                // Add entry for junior participants
                $entry = $this->createEntity('Indgang');
                $select = $entry->getSelect();
                $select->setWhere('type', 'like', '%Junior%');
                $entry = $entry->findBySelect($select);
                $participant->setIndgang($entry);
                $price = $entry->pris;
                break;
              }

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
              $junior_contact .= "$labels[$key_item]: $value\n";
              break;

            case 'entry':
              $entry = $this->createEntity('Indgang');
              $select = $entry->getSelect();
              if ($key_item == 'partout') {
                $select->setWhere('type', 'like', '%Indgang - Partout%');
                if ($age < $config['main']->age_young) {
                  if ($age < $config['main']->age_kid) {
                    $select->setWhere('type', 'like', '%Barn%');  
                  } else {
                    $select->setWhere('type', 'like', '%Ung%');
                  }
                }
                // NB! We assume Alea signup is earlier or same page
                if ($age >= $config['main']->age_kid && ($is_alea || $items['misc:alea'])) {
                  $select->setWhere('type', 'like', '%Alea%');
                 } else {
                  $select->setWhere('type', 'not like', '%Alea%');
                 }
                // NB! We assume organizer setting is before this
                if ($age >= $config['main']->age_kid && $is_organizer) {
                  $select->setWhere('type', 'like', '%Arrangør%');
                } else {
                  $select->setWhere('type', 'not like', '%Arrangør%');
                }
              } else {
                $day = intval($key_item) -1;
                $date = new DateTime($this->config->get('con.start'));
                $date->add(new DateInterval("P{$day}D"));
                $select->setWhereDate('start', '=', $date->format('Y-m-d'));
                if ($age < $config['main']->age_kid) {
                  $select->setWhere('type', '=', 'GRATIST Dagsbillet');
                } else {
                  $select->setWhere('type', '=', 'Indgang - Enkelt');
                }
              }
              $entry = $entry->findBySelect($select);
              if (!$entry) {
                $errors[$category][] = [
                  'type' => 'no_entry',
                  'info' => "$key_cat:$key_item $value",
                  'age'  => $age,
                  'alea' => ($is_alea || $items['misc:alea']),
                  'organizer' => $is_organizer,
                ];
                continue 2;
              }
              $participant->setIndgang($entry);
              $price = $entry->pris;
              break;

            case 'sleeping':
              // Check age
              // if ($age < $config['main']->age_kid) {
              //   $errors[$category][] = [
              //     'type' => 'sleeping_too_young',
              //     'id' => "$key",
              //     'age'  => $age,
              //   ];
              //   continue 2;
              // }

              $entry = $this->createEntity('Indgang');
              $select = $entry->getSelect();
              if ($key_item == 'partout') {
                $select->setWhere('type', 'like', 'Overnatning - Partout%');
                // NB! We assume organizer setting is before this
                if ($age >= $config['main']->age_kid && $is_organizer) {
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

            case 'sleeping_area':
              // TODO check sleeping area age restrictions
              $sleeping_areas[] = $key_item;
              break;

            case 'misc':
              $entry = $this->createEntity('Indgang');
              $select = $entry->getSelect();
          
              switch($key_item) {
                case 'mattres':
                  $select->setWhere('type', '=', 'Leje af madras');
                  break;

                case 'party1':
                  // Check age
                  if ($age < 18) {
                    $errors[$category][] = [
                      'type' => 'wine_too_young',
                      'id' => "$key",
                      'age'  => $age,
                    ];
                    continue 3;
                  }
                  $select->setWhere('id', '=', 81); // Sparkling wine ID
                  break;

                case 'alea':
                  $is_alea = true;
                  $select->setWhere('type', '=', 'Alea medlemskab');
                  break;
                
                case 'ticket_fee':
                  if ($age < $config['main']->age_kid) continue 3; // No fee for kids
                  $select->setWhere('type', '=', 'Billetgebyr');
                  break;

                case 'fastaval_bus':
                  $select->setWhere('type', '=', 'Fastavalbussen');
                  break;

                case 'extra_support':
                  $amount = min(15, floor(intval($value) / 100));
                  if ($amount == 0) continue 3;

                  $select->setWhere('type', '=', "Rig onkel - {$amount}00");
                  $value = 'on';
                  break;
  
                case 'secret_support':
                  $amount = min(15, floor(intval($value) / 100));
                  if ($amount == 0) continue 3;

                  $select->setWhere('type', '=', "Hemmelig onkel - {$amount}00");
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

              $foodprice = $food->getMad()->pris;
              if ($food->isBreakfast() && $food_credits['breakfast'] > 0 && $foodprice > 0) {
                $food_credits['breakfast']--;
                $price = 0;
              } elseif ($food->isDinner() && $food_credits['dinner'] > 0 && $foodprice > 0) {
                $food_credits['dinner']--;
                $price = 0;
              } else {
                $price = $foodprice;
              }
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
                continue 3;
              }

              $participant->setGDSTilmelding(null, "$date_string $period");
              break;

            case 'activity':
              // Check run id
              $run = $this->createEntity('Afviklinger')->findById($key_item);
              if (!$run) {
                $errors[$category][] = [
                  'type' => 'no_run',
                  'info' => "$key_cat:$key_item $value",
                ];
                continue 2;
              }

              // Check age
              $activity = $run->getActivity();
              if(
                ($activity->getMaxAge() && $age > $activity->getMaxAge()) || 
                ($activity->getMinAge() && $age < $activity->getMinAge())
              ){
                $errors[$category][] = [
                  'type' => 'age_'.(($activity->getMaxAge() && $age > $activity->getMaxAge()) ? 'max' : 'min'),
                  'age' => $age,
                  'max_age' => $activity->getMaxAge(),
                  'min_age' => $activity->getMinAge(),
                  'activity' => $run->aktivitet_id,
                  'module' => 'activities',
                ];
                continue 2;
              }

              $value = intval($value);
              // Check if run is full
              if (isset($full_runs[$run->id]) && $value != $choice_count + 1) {
                $errors[$category][] = [
                  'type' => 'full_run',
                  'activity' => $run->aktivitet_id,
                  'module' => 'activities',
                ];
                continue 2;
              }
              if ($value <= $choice_count) {
                $participant->setAktivitetTilmelding($run, $value, 'spiller');
              } else {
                $participant->setAktivitetTilmelding($run, 0, 'spilleder');
                if ($value == $choice_count + 2) {// SL + 1st prio
                  $participant->setAktivitetTilmelding($run, 1, 'spiller');
                }
              }
              $price = $run->getAktivitet()->pris;

              // Add multiblock info
              if ($run->hasMultiBlok()) {
                $extra['multi'] = true;
              }
              break;

            case 'activity_language':
              $sprog[] = $key_item;
              break;

            case 'note':
              $participant->setNote($key_item, $value);
              if ($value == "") continue 2; // Don't show empty values on breakdown
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

      // Check if we used our food credits
      if ($food_credits['breakfast'] == 2 && $food_credits['dinner'] == 2) {
        // We didn't use any credits and get an error
        $errors[$category][] = [
          'type' => 'no_food_credits_used',
          'id' => 'financial_struggle',
          'module' => 'food',
        ];
      } 
      if ($food_credits['breakfast'] > 0 || $food_credits['dinner'] > 0) {
        // We didn't use all credits and get a warning
        $entries[] = [
          'special_module' => 'food',
          'warning' => 'unused_food_credits',
          'key' => 'financial_struggle',
          'breakfast_remaining' => $food_credits['breakfast'],
          'dinner_remaining' => $food_credits['dinner'],
        ];
        // Reset food credits
        $food_credits['breakfast'] = 0;
        $food_credits['dinner'] = 0;
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
    $result = $participant->setCollection('sprog', $sprog);
    if( $result != 'success' ){
      $errors['activities'][] = [
        'type' => 'unknown_language',
        'languages' => $sprog,
      ];
    }

    // Sleeping area
    $result = $participant->setCollection('sleeping_area', $sleeping_areas);
    if( $result != 'success' ){
      $errors['entry'][] = [
        'type' => 'unknown_sleeping_area',
        'areas' => $sleeping_areas,
        'id' => 'sleeping_area:'.$result['value'],
      ];
    }

    // Check for actual organizer selection
    if ($is_organizer && !$participant->work_area) {
      $errors['organizer'][] = [
        'type' => 'no_organizer_selection',
        'id' => 'participant',
      ];
    // Author of rpg or designer of board game
    } elseif (in_array($participant->work_area, [1,2])) {
      $participant->forfatter = 'ja';
      $author = [];
      if ($participant->work_area == 1) $author[] = 'role';
      if ($participant->work_area == 2) $author[] = 'board';
      $participant->setCollection('author', $author);
    } else {
      $participant->forfatter = 'nej';
      $participant->game_id = null;
      $participant->setCollection('author', []);
    }

    // Notes
    if ($junior_contact) $participant->setNote('junior_ward', $junior_contact);

    return [
      'errors' => $errors,
      'categories' => $categories,
      'total' => $total,
      'paid' => $participant->betalt_beloeb,
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
    $signup = ['id' => $participant->id]; // We return participant id in case they used email for loading.
    $errors = [];
    $has_entrance = false;
    $config = [
      'activities' => json_decode($this->getConfig('activities')),
    ];

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
                $value = 'Arrangør';
              } else {
                $value = $bk->navn;
              }
              break;

            case 'organizer':
              $value = $participant->arbejdsomraade ? 'on' : '';
              break;

            case 'author':
              $value = in_array('role', $participant->getCollection('author'));
              break;

            case 'designer':
              $value = in_array('board', $participant->getCollection('author'));
              break;
              
            case 'birthdate':
              $value = substr($participant->birthdate, 0, 10);
              break;

            case 'ready_mandag':
            case 'ready_tirsdag':
              if ($participant->$key == 'ja') $signup['together:prepare'] = 'on';
              // Notice no break here
            default:
              if (!isset($column_info[$key])) {
                
                // Value is the same as a different field (like email confirm)
                if($equals = $lookup[$key]['item']->equals) {
                  $value = $participant->$equals;
                  break;
                }
                
                // Value isn't submitted
                if($lookup[$key]['item']->no_submit) continue 2;
                
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
    $signup['misc:extra_support'] = 0;
    $signup['misc:secret_support'] = 0;

    foreach($participant->getIndgang() as $entrance) {
      switch(true) {
        case $entrance->isPartout():
          if ($entrance->isEntrance()) {
            $signup['entry:partout'] = 'on';
            $has_entrance = true;
          } else {
            $signup['sleeping:partout'] = 'on';
          }
          break;

        case  $entrance->isDayTicket() || $entrance->isSleepTicket():
          $type = $entrance->isDayTicket() ? 'entry' : 'sleeping';
          $has_entrance = $type == 'entry' ? true : $has_entrance;
          $start = new DateTime($entrance->start);
          $day = intval($start->format('N')) -2;
          $signup["$type:$day"] = 'on';
          break;

        case $entrance->type == 'Indgang - Junior':
          $signup['junior:entrance'] = 'on';
          break;

        case $entrance->type == 'Alea medlemskab':
          $signup['misc:alea'] = 'on';
          break;

        case $entrance->type == 'Billetgebyr':
          $signup['misc:ticket_fee'] = 'on';
          break;

        case $entrance->type == 'Leje af madras':
          $signup['misc:mattres'] = 'on';
          break;

        case $entrance->type == 'Fastavalbussen':
          $signup['misc:fastaval_bus'] = 'on';
          break;
    
        case preg_match("/Rig onkel - (\d+)/", $entrance->type, $matches):
          $signup['misc:extra_support'] = $matches[1];
          break;

        case preg_match("/Hemmelig onkel - (\d+)/", $entrance->type, $matches):
          $signup['misc:secret_support'] = $matches[1];
          break;

        case in_array($entrance->id, [81, 82, 83]): // Ottofest extra
          $signup['misc:party'.($entrance->id - 80)] = 'on';
          break;

        case $entrance->id == 37: // Bank transfer fee
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
      $signup['food:'.$food->id] = 'on';
    }

    // Collect hero-task signup
    foreach($participant->getGDSTilmeldinger() as $hero_signup) {
      $period = $hero_signup->period;
      preg_match("/(\d{4}-\d{2}-\d{2}) (\d{1,2}-\d{2})/", $period, $match);
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
            'match' => $match,
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
        $prio = count($config['activities']->choices->prio->en) +1;
        if($run_signup->getAktivitet()->type == 'braet') {
          $signup['together:rules'] = 'on';
        } else {
          $signup['together:gm'] = 'on';
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
    foreach($participant->getCollection('sprog') as $sprog) {
      $signup['activity_language:'.$sprog] = 'on';
    }

    // Sleeping areas
    foreach($participant->getCollection('sleeping_area') as $area) {
      $signup['sleeping_area:'.$area] = 'on';
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
      
      foreach($wear_order->getAttributes() as $type => $att) {
        $order['attributes'][$type] = $att['id'];
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
    if ($has_entrance && $signup['participant'] == 'Juniordeltager') {
      $signup['junior:plus'] = 'on';
    }

    return [
      'signup' => $signup,
      'errors' => $errors,
    ];
  }

  private function loadOrganizerCategories() {
    $query = "SELECT id, name_da, name_en FROM organizer_categories";
    $result = $this->db->query($query);
    $categories = [];
    foreach($result as $row) {
      $categories[$row['id']] = [
        'en' => $row['name_en'],
        'da' => $row['name_da'],
        'id' => $row['id'],
      ];
    }
  
    return $categories;
  }

  private function loadCountries() {
    $query = "SELECT code, name_da, name_en FROM countries";
    $result = $this->db->query($query);
    $countries = [];
    foreach($result as $row) {
      $countries[$row['code']] = [
        'en' => $row['name_en'],
        'da' => $row['name_da'],
        'code' => $row['code'],
      ];
    }
  
    return $countries;
  }

  private function loadGameTitles() {
    $query = "SELECT id, navn, title_en FROM aktiviteter WHERE type = 'braet' OR type = 'rolle'";
    $result = $this->db->query($query);
    $titles = [];
    foreach($result as $row) {
      $titles[$row['id']] = [
        'en' => $row['title_en'],
        'da' => $row['navn'],
        'id' => $row['id'],
      ];
    }
  
    return $titles;
  }
}