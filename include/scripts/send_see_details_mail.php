<?php
exit;
/**
 * Copyright (C) 2010-2012 Peter Lind
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 * PHP version 5
 *
 * this file inits the framework. It needs a couple of set definitions (both paths should end with '/':
 * - INCLUDE_PATH: the full path to the include folder
 * - PUBLIC_PATH: the full path to the public folder
 * this one is optional, but as parts of the framework use it, best to init it
 * - PUBLIC_URI: the base part of the uri for the public folder
 *
 * @category  Infosys
 * @package   Scripts
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2010-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

set_time_limit(0);

require_once __DIR__ . '/../lib/swift/lib/swift_required.php';

putenv('ENVIRONMENT=live');
require __DIR__  . '/../bootstrap.php';

$infosys = createInfosys()
    ->setup();
$dic = $infosys->getDIC();

$log    = $dic->get('Log');
$db     = $dic->get('DB');

$factory = $dic->get('EntityFactory');

$sent = 0;

$emails_seen = array();

$select = $factory->create('Deltagere')->getSelect();
$select->setWhere('annulled', '=', 'nej')
    ->setWhere('signed_up', '>', '0000-00-00')
    ->setWhere('email', '!=', '')
//    ->setWhere('id', '=', '100')
    ->setField('deltagere.*', false);

$config = new FileConfig(__DIR__ . '/../config.ini', 'live');

$participants = $factory->create('Deltagere')->findBySelectMany($select);

foreach ($participants as $participant) {

    if (isset($emails_seen[$participant->email])) {
        continue;
    }

    $emails_seen[$participant->email] = true;

    if ($participant->speaksDanish()) {
        $text = <<<TXT
<p>Kære {$participant->fornavn},</p>
<p></p>
<p>Der er nogle få ting, vi gerne vil oplyse dig om, nu hvor Fastaval nærmer sig.</p>

<p>Du kan altid komme i kontakt med vagthavende general på <strong>40 38 82 87</strong>.</p>

<p>Du kan komme i kontakt med en tryghedsvært på <strong>29 39 60 94</strong>. Tryghedsværterne er tilgængelige fra kl. 10 til baren lukker. Læs mere her, om hvad tryghedsværterne kan hjælpe med: <a href="https://www.fastaval.dk/tryghed/">https://www.fastaval.dk/tryghed/</a>.</p>

<p>Vi har opdateret vores alkoholregler og sanktioner, læs dem her: <a href="https://www.fastaval.dk/praktisk/praktiske-informationer/">https://www.fastaval.dk/praktisk/praktiske-informationer/</a>.</p>

<p><strong>Onsdag kl. 19</strong> afholdes Introtur for nye deltagere på Fastaval. Her kan du stille alle dine spørgsmål, samt lære andre deltagere at kende. Duk op kl. 19 i Fællesrummet.</p>

<p>Holdlæggerne er tæt på at være færdige med at få aktiviteterne på plads, og du kan nu se nogle af de ting, du kommer til at lave på årets val. Vær opmærksom på, at der stadig kan forekomme ændringer.</p>

<p>Du får adgang til at se dine detaljer (din tilmelding plus de forskellige aktiviteter du er kommet på, spil som GDS) ved at gå ind på: <a href="http://infosys.fastaval.dk/deltager/showsignup/{$participant->id}">http://infosys.fastaval.dk/deltager/showsignup/{$participant->id}</a></p>

<p>Du skal bruge din personlige kode for at få adgang - den er: <strong>{$participant->password}</strong></p>

<p>Samme kode bruges også til vores Android og iPhone apps - søg på Fastaval eller Fastavappen hvor du henter apps. Så gem e-mailen.</p>

<p>GDS-puslespillet er blevet lagt men husk at der stadig kan ske ændringer så tjek din deltagerseddel og opdater din app når du ankommer på Fastaval.</p>

<p>Vi vil bede dig tjekke om du har fået en tjans på et tidspunkt hvor du ikke endnu er ankommet på Fastaval - eller i den anden ende, er taget afsted igen. Hvis du står i en situation hvor du allerede nu kan se at du ikke kan tage din tjans vil vi bede dig om at skrive til os på <a href="mailto:bunker@fastaval.dk">bunker@fastaval.dk</a> - så finder vi en løsning! Skulle det under Fastaval vise sig at du ikke er i stand til at tage din tjans, det kan f.eks. være ved sygdom, så kom og fortæl os det så snart det er muligt for så har vi nemlig længere tid til at finde en til at tage over. Jo mere tid vi har til at finde et par ekstra hænder jo mere overskud og det kan vi godt li’ det der overskud.</p>

<p>Hvis du har spørgsmål ang. din tilmelding eller andet, så kontakt os på <a href="mailto:info@fastaval.dk">info@fastaval.dk</a></p>
<p></p>

<p>Vi glæder os til at se dig på Fastaval!</p>

TXT;
        } else {
            $text = <<<TXT
<p>Dear {$participant->fornavn},</p>

<p>There are a few things we would like you to know now that Fastaval is coming up.</p>

<p>You can always call the General at <strong>+45 40 38 82 87</strong>.</p>

<p>You can get in contact with a Tryghedsvært (“Safety Host”) at <strong>+45 29 39 60 94</strong>. The Safety Hosts are available between 10 am and until the bar closes. Read more about what they can help you with here: <a href="https://www.fastaval.dk/safety/?lang=en">https://www.fastaval.dk/safety/?lang=en</a>.</p>

<p>We have updated our alcohol policy, read it here: <a href="https://www.fastaval.dk/practical/practical-information/?lang=en">https://www.fastaval.dk/practical/practical-information/?lang=en</a>.</p>

<p><strong>Wednesday at 19</strong> there will be an Introtour for new participants at Fastaval, where you can ask any questions. Just show up at 19 in the Common Area.</p>

<p>The people assigning players to teams are very busy and in fact you can already see some of the activities you will be participating in at this years convention.  Please note that nothing is set in stone - there may still be last-minute changes.</p>

<p>You can see your details (signup info plus activities you will be participating in) by browsing to: <a href="http://infosys.fastaval.dk/deltager/showsignup/{$participant->id}">http://infosys.fastaval.dk/deltager/showsignup/{$participant->id}</a></p>

<p>You need your personal code to get access - it is: <strong>{$participant->password}</strong></p>

<p>The same code is used for the Android and iPhone apps - search for Fastaval or Fastavappen where you get your apps. So don't delete the email.</p>

<p>The DIY-puzzle is finished but remember that changes can still happen, so check your participant details and update your app when you arrive at Fastaval.</p>

<p>We would like to ask you to check if you have received a shift for a time when you have not yet arrived at Fastaval - or when you have already left. If you know already that you cannot do a shift then please write us at <a href="mailto:bunker@fastaval.dk">bunker@fastaval.dk</a> - and we will find a solution! If during Fastaval it should happen that you will not be capable of doing your shift, for illness or other reasons, please tell us as soon as possible, as that gives us much better chances of finding someone to take over. The more time we have to find helpers the easier the job for us, the better for everyone.</p>

<p>If you have any questions regarding your signup or other matters, please contact us at: <a href="mailto:info@fastaval.dk">info@fastaval.dk</a></p>
<p></p>

<p>We are looking forward to seeing you at Fastaval!</p>

TXT;
    }

    try {

        $text_body = strip_tags($text);

        $e = new Mail($config);
        $e->setFrom('info@fastaval.dk', 'Fastaval')
            ->setRecipient($participant->email)
            ->setSubject('Fastaval 2019: almost there ...')
            ->setPlainTextBody($text_body)
            ->setHtmlBody($text);

        $e->send();
        $log->logToDB("Email script har sendt email til " . $participant->email, "Script", 0);

    } catch (Exception $e) {
        echo "Failed to email participant {$deltager['email']}." . PHP_EOL;
        echo $e->getMessage() . PHP_EOL;
    }

    $sent++;
}

$log->logToDB("Email script har sendt emails ud til " . $sent . " deltagere.", "Script", 0);
