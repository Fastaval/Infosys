<?php

namespace Fv\Tests\Models;
use PHPUnit\Framework\TestCase;
use ArrayConfig;
use DIC;
use Autoloader;
use EntityFactory;
use ApiModel;

class ApiModelTest extends TestCase
{
    public function setup(): void
    {
        $this->config = new ArrayConfig([]);
        $this->dic    = new DIC();

        $this->db = $this->getMockBuilder('DB')
            ->disableOriginalConstructor()
            ->setMethods(['query'])
            ->getMock();

        $autoloader = new Autoloader(['../../../includes/entities/']);

        $this->dic->addReusableObject($this->db, 'DB');
        $this->dic->addReusableObject(new EntityFactory($this->db, $autoloader));
    }

    public function testParseSignupConfirmation()
    {
        $model = new ApiModel($this->db, $this->config, $this->dic);

        $data = [
                 'participant' => [
                                    'fornavn'                       => 'test1',
                                    'efternavn'                     => 'test2',
                                    'nickname'                      => 'test3',
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

        $valueMap = [
            'DESCRIBE `brugerkategorier`' => [
                 ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => 'NULL'],
                 ['Field' => 'navn', 'Type' => 'varchar(256)', 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'arrangoer', 'Type' => "enum('ja','nej')", 'Null' => 'NO', 'Key' => '', 'Default' => 'nej'],
                 ['Field' => 'beskrivelse', 'Type' => 'varchar(512)', 'Null' => 'YES', 'Key' => '', 'Default' => 'NULL'],
            ],
            'SELECT `brugerkategorier`.`id`,`brugerkategorier`.`navn`,`brugerkategorier`.`arrangoer`,`brugerkategorier`.`beskrivelse` FROM `brugerkategorier` WHERE `navn` = Arrangør' => [
                ['id' => 2, 'navn' => 'Arrangør', 'arrangoer' => 'ja'],
            ],
            'SELECT `brugerkategorier`.`id`,`brugerkategorier`.`navn`,`brugerkategorier`.`arrangoer`,`brugerkategorier`.`beskrivelse` FROM `brugerkategorier` WHERE `navn` = Deltager' => [['id' => 1, 'navn' => 'Deltager', 'arrangoer' => 'nej']],
            'DESCRIBE `wearpriser`' => [
                 ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => 'NULL'],
                 ['Field' => 'wear_id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'MUL', 'Default' => 'NULL'],
                 ['Field' => 'brugerkategori_id', 'Type' => "int(11)", 'Null' => 'NO', 'Key' => 'MUL', 'Default' => 'NULL'],
                 ['Field' => 'pris', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => '', 'Default' => '0'],
            ],
            'SELECT `wearpriser`.`id`,`wearpriser`.`wear_id`,`wearpriser`.`brugerkategori_id`,`wearpriser`.`pris` FROM `wearpriser` WHERE `wear_id` = 1 AND `brugerkategori_id` = 2' => [],
            'SELECT `wearpriser`.`id`,`wearpriser`.`wear_id`,`wearpriser`.`brugerkategori_id`,`wearpriser`.`pris` FROM `wearpriser` WHERE `wear_id` = 1 AND `brugerkategori_id` = 1' => [],
            'SELECT `wearpriser`.`id`,`wearpriser`.`wear_id`,`wearpriser`.`brugerkategori_id`,`wearpriser`.`pris` FROM `wearpriser` WHERE `wear_id` = 2 AND `brugerkategori_id` = 2' => [
                ['id' => 8, 'wear_id' => '2', 'brugerkategori_id' => '2', 'pris' => 80],
            ],
            "DESCRIBE `afviklinger`" => [
                 ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => 'NULL'],
                 ['Field' => 'aktivitet_id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'MUL', 'Default' => 'NULL'],
                 ['Field' => 'start', 'Type' => "datetime", 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'slut', 'Type' => 'datetime', 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'lokale_id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'note', 'Type' => 'text', 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
            ],
            "SELECT * FROM `afviklinger` WHERE `id` = 10" => [
                ['id' => 10, 'aktivitet_id' => '1', 'start' => '2016-03-24 11:00:00', 'slut' => '2016-03-24 16:00:00', 'lokale_id' => 1, 'note' => ''],
            ],
            "SELECT * FROM `afviklinger` WHERE `id` = 20" => [
                ['id' => 20, 'aktivitet_id' => '2', 'start' => '2016-03-25 11:00:00', 'slut' => '2016-03-25 16:00:00', 'lokale_id' => 1, 'note' => ''],
            ],
            "SELECT * FROM `afviklinger` WHERE `id` = 30" => [
                ['id' => 30, 'aktivitet_id' => '3', 'start' => '2016-03-26 11:00:00', 'slut' => '2016-03-26 16:00:00', 'lokale_id' => 1, 'note' => ''],
            ],
            "DESCRIBE `indgang`" => [
                 ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => 'NULL'],
                 ['Field' => 'pris', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'start', 'Type' => "datetime", 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'type', 'Type' => 'varchar(64)', 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
            ],
            "SELECT * FROM `indgang` WHERE `id` = 1" => [['id' => 1, 'pris' => '210', 'start' => '2016-03-23 12:00:00', 'type' => 'Indgang - Partout']],
            "SELECT * FROM `indgang` WHERE `id` = 2" => [['id' => 2, 'pris' => '85', 'start' => '2016-03-23 12:00:00', 'type' => 'Indgang - Partout - ALEA']],
            "SELECT * FROM `indgang` WHERE `id` = 5" => [['id' => 5, 'pris' => '55', 'start' => '2016-03-23 12:00:00', 'type' => 'Indgang - Enkelt']],
            "SELECT * FROM `indgang` WHERE `id` = 8" => [['id' => 8, 'pris' => '175', 'start' => '2016-03-23 12:00:00', 'type' => 'Overnatning - Partout']],
            "DESCRIBE `gdscategories`" => [
                 ['Field' => 'id', 'Type' => 'int(10)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => 'NULL'],
                 ['Field' => 'name_da', 'Type' => 'varchar(64)', 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'name_en', 'Type' => "varchar(64)", 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
            ],
            "SELECT * FROM `gdscategories` WHERE `id` = 4" => [['id' => 4, 'name_da' => 'Manuelt arbejde', 'name_en' => 'Manual labor']],
            "SELECT * FROM `gdscategories` WHERE `id` = 5" => [['id' => 5, 'name_da' => 'Brandvagt', 'name_en' => 'Night watch']],
            "SELECT * FROM `gdscategories` WHERE `id` = 2" => [['id' => 2, 'name_da' => 'Service', 'name_en' => 'Service']],
            "DESCRIBE `madtider`" => [
                 ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => 'NULL'],
                 ['Field' => 'mad_id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'MUL', 'Default' => 'NULL'],
                 ['Field' => 'dato', 'Type' => "datetime", 'Null' => 'NO', 'Key' => '', 'Default' => 'NULL'],
                 ['Field' => 'description_da', 'Type' => "varchar(128)", 'Null' => 'NO', 'Key' => '', 'Default' => ''],
                 ['Field' => 'description_en', 'Type' => "varchar(128)", 'Null' => 'NO', 'Key' => '', 'Default' => ''],
            ],
            "SELECT * FROM `madtider` WHERE `id` = 141" => [['id' => 141, 'mad_id' => 1, 'dato' => '2016-04-23', 'description_da' => 'Aftensmad', 'description_en' => 'supper']],
            "SELECT * FROM `madtider` WHERE `id` = 142" => [['id' => 142, 'mad_id' => 1, 'dato' => '2016-04-24', 'description_da' => 'Aftensmad', 'description_en' => 'supper']],
            "SELECT * FROM `madtider` WHERE `id` = 143" => [['id' => 143, 'mad_id' => 1, 'dato' => '2016-04-25', 'description_da' => 'Aftensmad', 'description_en' => 'supper']],
            "SELECT * FROM `madtider` WHERE `id` = 144" => [['id' => 144, 'mad_id' => 1, 'dato' => '2016-04-26', 'description_da' => 'Aftensmad', 'description_en' => 'supper']],
            "SELECT * FROM `madtider` WHERE `id` = 129" => [['id' => 129, 'mad_id' => 1, 'dato' => '2016-04-27', 'description_da' => 'Aftensmad', 'description_en' => 'supper']],
            "SELECT * FROM `madtider` WHERE `id` = 135" => [['id' => 135, 'mad_id' => 4, 'dato' => '2016-04-24', 'description_da' => 'Morgenmad', 'description_en' => 'breakfast']],
            "SELECT * FROM `madtider` WHERE `id` = 131" => [['id' => 131, 'mad_id' => 4, 'dato' => '2016-04-25', 'description_da' => 'Morgenmad', 'description_en' => 'breakfast']],
            "SELECT * FROM `madtider` WHERE `id` = 132" => [['id' => 132, 'mad_id' => 4, 'dato' => '2016-04-26', 'description_da' => 'Morgenmad', 'description_en' => 'breakfast']],
            "SELECT * FROM `madtider` WHERE `id` = 133" => [['id' => 133, 'mad_id' => 4, 'dato' => '2016-04-27', 'description_da' => 'Morgenmad', 'description_en' => 'breakfast']]
        ];

        $this->db->method('query')
             ->willReturnCallback(function ($query) use ($valueMap) {
                 if (is_object($query)) {
                     $arguments = $query->getArguments();
                     $query = $query->assemble();
                     $parts = explode('?', $query);
                     $stringParts = [];

                     while (count($arguments)) {
                         $stringParts[] = array_shift($parts);
                         $stringParts[] = array_shift($arguments);
                     }

                     $stringParts[] = array_shift($arguments);

                     $query = implode('', $stringParts);
                 }

                 if (is_string($query) && isset($valueMap[$query])) {
                     return $valueMap[$query];
                 }

                 throw new \Exception("Unexpected query: " . $query);
             });

        $participant = $model->parseSignupConfirmation($data);

        foreach ($data['participant'] as $key => $value) {
            if ($key === 'brugertype') {
                $this->assertEquals(2, $participant->brugerkategori_id);
                continue;
            }

            $this->assertEquals($value, $participant->$key, $key . " failed to match");
        }
/* this bit needs fixin in the model - it looks horribly broken
        $wear_orders = $participant->getWear();

        $this->assertEquals(2, count($wear_orders));
        $this->assertEquals('XL', $wear_orders[0]->size);
        $this->assertEquals('3', $wear_orders[0]->antal);
        $this->assertEquals('L', $wear_orders[1]->size);
        $this->assertEquals('2', $wear_orders[1]->antal);
 */
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
