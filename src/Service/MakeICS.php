<?php

namespace App\Service;

use DateInterval;
use Symfony\Component\Filesystem\Filesystem;

class MakeICS
{
    private $tmp;

    const DT_FORMAT = 'Ymd\THis\Z';

    public function __construct($tmp)
    {
        $this->tmp = $tmp;
    }

    public function getICS($dateDebut, $dateFin, $demi)
    {
        if ($demi == null) {
            $dateDebut->setTime(0, 0, 0);
            $dateFin->setTime(24, 0, 0);
        } elseif ($demi == 'Matin') {
            $dateDebut->setTime(9, 0, 0);
            $dateFin->setTime(12, 0, 0);
        } elseif ($demi == 'Aprés-midi') {
            $dateDebut->setTime(14, 0, 0);
            $dateFin->setTime(18, 0, 0);
        }

        // Create the ics file
        $fs = new Filesystem();
        //temporary folder, it has to be writable
        $tmpFolder = $this->tmp;

        //the name of your file to attach
        $fileName = 'meeting.ics';



            $icsContent = '
BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
DTSTART:'.$dateDebut->format(self::DT_FORMAT).'
DTEND:'.$dateFin->format(self::DT_FORMAT).'
ORGANIZER;CN=Adeo Informatique:mailto:vacance@adeo-informatique.fr
UID:'.rand(5, 1500).'
DESCRIPTION:'.' Congés du '.$dateDebut->format('d/m/Y').' au '.$dateFin->format('d/m/Y').'
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:Vacances
TRANSP:OPAQUE
END:VEVENT
END:VCALENDAR'
            ;

        $icsFile = $fs->dumpFile($tmpFolder.$fileName, $icsContent);

        return $tmpFolder.$fileName;
    }
}
