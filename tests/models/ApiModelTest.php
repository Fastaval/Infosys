<?php

namespace Fv\Tests;

require_once __DIR__ . '/../bootstrap.php';

class ApiModelTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->config = new \ArrayConfig([]);
        $this->dic    = new \DIC();

        $this->db = $this->getMockBuilder('DB')
            ->disableOriginalConstructor()
            ->setMethods(['query'])
            ->getMock();

        $autoloader = new \Autoloader(['../../../includes/entities/']);

        $this->dic->addReusableObject($this->db, 'DB');
        $this->dic->addReusableObject(new \EntityFactory($this->db, $autoloader));
    }

    public function testParseSignupConfirmation()
    {
        $model = new \ApiModel($this->db, $this->config, $this->dic);

        $data = [
                 'participant' => [
                                    'fornavn'                       => 'test1',
                                    'efternavn'                     => 'test2',
                                    'gender'                        => 'test3',
                                    'birthdate'                     => '1940-01-02',
                                    'alder'                         => 'test5',
                                    'email'                         => 'test6',
                                    'tlf'                           => 'test7',
                                    'mobiltlf'                      => 'test8',
                                    'adresse1'                      => 'test9',
                                    'adresse2'                      => 'test10',
                                    'postnummer'                    => 'test11',
                                    'by'                            => 'test12',
                                    'land'                          => 'test13',
                                    'medbringer_mobil'              => 'test14',
                                    'sprog'                         => 'test15',
                                    'forfatter'                     => 'test16',
                                    'international'                 => 'test17',
                                    'arrangoer_naeste_aar'          => 'test18',
                                    'deltager_note'                 => 'test19',
                                    'flere_gdsvagter'               => 'test20',
                                    'supergm'                       => 'test21',
                                    'supergds'                      => 'test22',
                                    'rig_onkel'                     => 'test23',
                                    'arbejdsomraade'                => 'test24',
                                    'hemmelig_onkel'                => 'test25',
                                    'ready_mandag'                  => 'test26',
                                    'ready_tirsdag'                 => 'test27',
                                    'oprydning_tirsdag'             => 'test28',
                                    'tilmeld_scenarieskrivning'     => 'test29',
                                    'may_contact'                   => 'test30',
                                    'desired_activities'            => 'test31',
                                    'game_reallocation_participant' => 'test32',
                                    'dancing_with_the_clans'        => 'test33',
                                    'sovesal'                       => 'test34',
                                    'ungdomsskole'                  => 'test35',
                                    'original_price'                => 'test36',
                                    'scenarie'                      => 'test37',
                                    'medical_note'                  => 'test38',
                                    'interpreter'                   => 'test39',
                                    'skills'                        => 'test40',
                                    'brugertype'                    => 'Arrangør',
                                  ],
                 'wear' => [
                            ['id' => 1, 'size' => 'XL', 'amount' => 3],
                            ['id' => 2, 'size' => 'L', 'amount' => 2],
                           ],
                 'activity' => [
                                ['schedule_id' => 10, 'priority' => 1, 'type' => 'spiller'],
                                ['schedule_id' => 20, 'priority' => 2, 'type' => 'spiller'],
                                ['schedule_id' => 30, 'priority' => 1, 'type' => 'spilleder'],
                               ],
                 'entrance' => [
                                ['entrance_id' => 1],
                                ['entrance_id' => 2],
                                ['entrance_id' => 5],
                                ['entrance_id' => 8],
                               ],
                 'gds' => [
                           ['kategori_id' => 4, 'period' => '2015-04-05 12-17'],
                           ['kategori_id' => 5, 'period' => '2015-04-05 12-17'],
                           ['kategori_id' => 2, 'period' => '2015-04-05 12-17'],
                          ],
                 'food' => [
                            ['madtid_id' => 141],
                            ['madtid_id' => 142],
                            ['madtid_id' => 143],
                            ['madtid_id' => 144],
                            ['madtid_id' => 129],
                            ['madtid_id' => 135],
                            ['madtid_id' => 131],
                            ['madtid_id' => 132],
                            ['madtid_id' => 133],
                           ],
                ];

        $this->db->method('query')
            ->will($this->onConsecutiveCalls(
                [['id' => 2, 'navn' => 'Arrangør', 'arrangoer' => 'ja']],
                [
                 ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => 'NULL'],
                 ['Field' => 'navn', 'Type' => 'varchar(256)', 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'arrangoer', 'Type' => "enum('ja','nej')", 'Null' => 'NO', 'Key' => '', 'Default' => 'nej'],
                 ['Field' => 'beskrivelse', 'Type' => 'varchar(512)', 'Null' => 'YES', 'Key' => '', 'Default' => 'NULL'],
                ],
                [['id' => 2, 'wear_id' => '1', 'brugerkategori_id' => '2', 'pris' => 80]],
                [
                 ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => 'NULL'],
                 ['Field' => 'wear_id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'MUL', 'Default' => 'NULL'],
                 ['Field' => 'brugerkategori_id', 'Type' => "int(11)", 'Null' => 'NO', 'Key' => 'MUL', 'Default' => 'NULL'],
                 ['Field' => 'pris', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => '', 'Default' => '0'],
                ],
                [['id' => 8, 'wear_id' => '2', 'brugerkategori_id' => '2', 'pris' => 80]],
                [
                 ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => 'NULL'],
                 ['Field' => 'aktivitet_id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'MUL', 'Default' => 'NULL'],
                 ['Field' => 'start', 'Type' => "datetime", 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'slut', 'Type' => 'datetime', 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'lokale_id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'note', 'Type' => 'text', 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                ],
                [['id' => 10, 'aktivitet_id' => '1', 'start' => '2016-03-24 11:00:00', 'slut' => '2016-03-24 16:00:00', 'lokale_id' => 1, 'note' => '']],
                [['id' => 20, 'aktivitet_id' => '2', 'start' => '2016-03-25 11:00:00', 'slut' => '2016-03-25 16:00:00', 'lokale_id' => 1, 'note' => '']],
                [['id' => 30, 'aktivitet_id' => '3', 'start' => '2016-03-26 11:00:00', 'slut' => '2016-03-26 16:00:00', 'lokale_id' => 1, 'note' => '']],
                [
                 ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => 'NULL'],
                 ['Field' => 'pris', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'start', 'Type' => "datetime", 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'type', 'Type' => 'varchar(64)', 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                ],
                [['id' => 1, 'pris' => '210', 'start' => '2016-03-23 12:00:00', 'type' => 'Indgang - Partout']],
                [['id' => 2, 'pris' => '85', 'start' => '2016-03-23 12:00:00', 'type' => 'Indgang - Partout - ALEA']],
                [['id' => 5, 'pris' => '55', 'start' => '2016-03-23 12:00:00', 'type' => 'Indgang - Enkelt']],
                [['id' => 8, 'pris' => '175', 'start' => '2016-03-23 12:00:00', 'type' => 'Overnatning - Partout']],
                [
                 ['Field' => 'id', 'Type' => 'int(10)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => 'NULL'],
                 ['Field' => 'name_da', 'Type' => 'varchar(64)', 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'name_en', 'Type' => "varchar(64)", 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                ],
                [['id' => 4, 'name_da' => 'Manuelt arbejde', 'name_en' => 'Manual labor']],
                [['id' => 5, 'name_da' => 'Brandvagt', 'name_en' => 'Night watch']],
                [['id' => 2, 'name_da' => 'Service', 'name_en' => 'Service']],
                [
                 ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => 'NULL'],
                 ['Field' => 'mad_id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'MUL', 'Default' => 'NULL'],
                 ['Field' => 'dato', 'Type' => "datetime", 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'description_da', 'Type' => "varchar(128)", 'Null' => 'NO', 'Key' => '', 'Default' => ''],
                 ['Field' => 'description_en', 'Type' => "varchar(128)", 'Null' => 'NO', 'Key' => '', 'Default' => ''],
                ],
                [['id' => 141, 'mad_id' => 1, 'dato' => '2016-04-23', 'description_da' => 'Aftensmad', 'description_en' => 'supper']],
                [['id' => 142, 'mad_id' => 1, 'dato' => '2016-04-24', 'description_da' => 'Aftensmad', 'description_en' => 'supper']],
                [['id' => 143, 'mad_id' => 1, 'dato' => '2016-04-25', 'description_da' => 'Aftensmad', 'description_en' => 'supper']],
                [['id' => 144, 'mad_id' => 1, 'dato' => '2016-04-26', 'description_da' => 'Aftensmad', 'description_en' => 'supper']],
                [['id' => 129, 'mad_id' => 1, 'dato' => '2016-04-27', 'description_da' => 'Aftensmad', 'description_en' => 'supper']],
                [['id' => 135, 'mad_id' => 4, 'dato' => '2016-04-24', 'description_da' => 'Morgenmad', 'description_en' => 'breakfast']],
                [['id' => 131, 'mad_id' => 4, 'dato' => '2016-04-25', 'description_da' => 'Morgenmad', 'description_en' => 'breakfast']],
                [['id' => 132, 'mad_id' => 4, 'dato' => '2016-04-26', 'description_da' => 'Morgenmad', 'description_en' => 'breakfast']],
                [['id' => 133, 'mad_id' => 4, 'dato' => '2016-04-27', 'description_da' => 'Morgenmad', 'description_en' => 'breakfast']]
            ));

        $participant = $model->parseSignupConfirmation($data);

        foreach ($data['participant'] as $key => $value) {
            if ($key === 'brugertype') {
                $this->assertEquals(2, $participant->brugerkategori_id);
                continue;
            }

            $this->assertEquals($value, $participant->$key);
        }

        $wear_orders = $participant->getWear();

        $this->assertEquals(2, count($wear_orders));
        $this->assertEquals('XL', $wear_orders[0]->size);
        $this->assertEquals('3', $wear_orders[0]->antal);
        $this->assertEquals('L', $wear_orders[1]->size);
        $this->assertEquals('2', $wear_orders[1]->antal);

        $signups = $participant->getTilmeldinger();

        $this->assertEquals(3, count($signups));
        $this->assertEquals(10, $signups[0]->afvikling_id);
        $this->assertEquals(1, $signups[0]->prioritet);
        $this->assertEquals('spiller', $signups[0]->tilmeldingstype);
        $this->assertEquals(20, $signups[1]->afvikling_id);
        $this->assertEquals(2, $signups[1]->prioritet);
        $this->assertEquals('spiller', $signups[1]->tilmeldingstype);
        $this->assertEquals(30, $signups[2]->afvikling_id);
        $this->assertEquals(1, $signups[2]->prioritet);
        $this->assertEquals('spilleder', $signups[2]->tilmeldingstype);

        $entrances = $participant->getIndgang();

        $this->assertEquals(4, count($entrances));

        $this->assertEquals(1, $entrances[0]->id);
        $this->assertEquals(2, $entrances[1]->id);
        $this->assertEquals(5, $entrances[2]->id);
        $this->assertEquals(8, $entrances[3]->id);

        $diy_signups = $participant->getGDSTilmeldinger();

        $this->assertEquals(3, count($diy_signups));

        $this->assertEquals(4, $diy_signups[0]->category_id);
        $this->assertEquals(5, $diy_signups[1]->category_id);
        $this->assertEquals(2, $diy_signups[2]->category_id);

        $this->assertEquals('2015-04-05 12-17', $diy_signups[0]->period);
        $this->assertEquals('2015-04-05 12-17', $diy_signups[1]->period);
        $this->assertEquals('2015-04-05 12-17', $diy_signups[2]->period);

        $foods = $participant->getMadtider();

        $this->assertEquals(9, count($foods));

        $this->assertEquals(141, $foods[0]->id);
        $this->assertEquals(142, $foods[1]->id);
        $this->assertEquals(143, $foods[2]->id);
        $this->assertEquals(144, $foods[3]->id);
        $this->assertEquals(129, $foods[4]->id);
        $this->assertEquals(135, $foods[5]->id);
        $this->assertEquals(131, $foods[6]->id);
        $this->assertEquals(132, $foods[7]->id);
        $this->assertEquals(133, $foods[8]->id);
    }
}
