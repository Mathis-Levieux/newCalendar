<?php

date_default_timezone_set('Europe/Paris');

// Fonction qui récupère les données du JSON sélectionné
function getDatasFromJson($jsonFile)
{
    $json = file_get_contents($jsonFile);
    $datas = json_decode($json, true);
    return $datas;
}

// Fonction qui récupère les évènements du JSON sélectionné pour les stocker dans un tableau
function getOtherEventsArray()
{
    $datas = getDatasFromJson('events.json');
    $otherEventsArray = [];

    foreach ($datas['otherEvents'] as $data) {
        $otherEventsArray[] = $data;
    }
    return $otherEventsArray;
}

// Fonction qui récupère les anniversaires du JSON sélectionné pour les stocker dans un tableau
function getBirthdaysArray()
{
    $datas = getDatasFromJson('events.json');
    $birthdaysArray = [];

    foreach ($datas['birthdays'] as $data) {
        $birthdaysArray[] = $data;
    }
    return $birthdaysArray;
}

// Fonction qui génère tous les jours fériés pour les stocker dans un tableau
function getPublicHolidaysArray($year)
{
    $holidaysArray = [];
    $easterDate = easter_date($year);
    $easterDay = date('j', $easterDate);
    $easterMonth = date('n', $easterDate);
    $holidaysArray[date('Y-m-d', mktime(0, 0, 0, 1, 1, $year))] = "Jour de l'An";
    $holidaysArray[date('Y-m-d', mktime(0, 0, 0, 5, 1, $year))] = "Fête du travail";
    $holidaysArray[date('Y-m-d', mktime(0, 0, 0, 5, 8, $year))] = "Victoire des Alliés en 1945";
    $holidaysArray[date('Y-m-d', mktime(0, 0, 0, 7, 14, $year))] = "Fête nationale";
    $holidaysArray[date('Y-m-d', mktime(0, 0, 0, 8, 15, $year))] = "Assomption";
    $holidaysArray[date('Y-m-d', mktime(0, 0, 0, 11, 1, $year))] = "Toussaint";
    $holidaysArray[date('Y-m-d', mktime(0, 0, 0, 11, 11, $year))] = "Armistice 1918";
    $holidaysArray[date('Y-m-d', mktime(0, 0, 0, 12, 25, $year))] = "Jour de Noël";
    $holidaysArray[date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 1, $year))] = "Lundi de Pâques";
    $holidaysArray[date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 39, $year))] = "Ascension";
    $holidaysArray[date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 50, $year))] = "Pentecôte";
    ksort($holidaysArray); // Trie le tableau par date
    return $holidaysArray;
}


function displayWeekWithEvents($date)
{
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::SHORT, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, "EEEE dd MMMM"); // Formate la date format "Lundi 20 Janvier"
    $formatter2 = new IntlDateFormatter('fr_FR', IntlDateFormatter::SHORT, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, "dd"); // Formate la date format "20"
    $formatter3 = new IntlDateFormatter('fr_FR', IntlDateFormatter::SHORT, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, "dd MMMM YYYY"); // Formate la date format "20 janvier 2023"
    $birthdaysArray = getBirthdaysArray();  // Récupère les anniversaires
    $otherEventsArray = getOtherEventsArray(); // Récupère les autres évènements 
    $year = date('Y', strtotime($date)); // Récupère l'année pour la fonction getPublicHolidaysArray
    $publicHolidaysArray = getPublicHolidaysArray($year); // Récupère les jours fériés pour l'année sélectionnée
    $week_start = date('N', strtotime($date)) === 1 ? $date : date('Y-m-d', strtotime('last Monday', strtotime($date))); // Récupère la date du lundi de la semaine
    $week_end = date('Y-m-d', strtotime('+6 days', strtotime($week_start))); // Récupère la date du dimanche de la semaine

    echo "<div class='calendar container col-lg col-sm-12 w-auto h-auto'>"; // Début container    

    // Affiche la date sélectionnée au format "Semaine du 01 au 07 Janvier 2023"
    echo "<h2 class='text-center'>";
    echo "<a class='button' href='?date=" . date('Y-m-d', strtotime('-7 days', strtotime($date))) . "'> < </a>";
    echo "Semaine du " . $formatter2->format(strtotime($week_start)) . " au " . $formatter3->format(strtotime($week_end)) . "";
    echo "<a class='button' href='?date=" . date('Y-m-d', strtotime('+7 days', strtotime($date))) . "'> > </a>";
    echo "</h2>";

    echo "<div class='row gap-1'>"; // Début row

    // Commence la boucle pour chaque jour de la semaine et vérifie si il y a des évènements

    for ($i = $week_start; $i <= $week_end; $i = date("Y-m-d", strtotime("+1 day", strtotime($i)))) {
        $dateToCheck = $i;
        echo "<div class='p-0 daywrapper col-lg col-sm-12'>";
        echo "<div class='day text-center'>" . ucwords($formatter->format(strtotime($i))) . "</div>";


        // Vérifie si une date correspond à un anniversaire
        foreach ($birthdaysArray as $birthday) {
            // Formate les dates pour comparer uniquement le mois et le jour
            $birthdayDate = date('md', strtotime($birthday['date']));
            $currentDate = date('md', strtotime($dateToCheck));
            // Si les dates correspondent, affiche l'anniversaire et appelle la fonction qui génère la modale
            if ($birthdayDate == $currentDate) {
                echo "<p class='cell bdaycell' data-bs-toggle='modal' data-bs-target='#" . $birthday['lastname'] . "'>Anniversaire de " . $birthday['firstname']  . "<i class='bi bi-gift'></i></p>";
                createBirthdayModals($birthday);
            }
        }

        // Vérifie si une date correspond à un évènement
        foreach ($otherEventsArray as $otherEvent) {
            // Si les dates correspondent, affiche l'évènement et appelle la fonction qui génère la modale
            if ($otherEvent['date'] == $dateToCheck) {
                echo "<p class='cell' data-bs-toggle='modal' data-bs-target='#" . str_replace(' ', "", $otherEvent['name']) . "'> " . $otherEvent['name'] . "</p>";
                createOtherEventModals($otherEvent);
            }
        }
        // Vérifie si c'est un lundi ou un jeudi pour afficher l'entraînement
        if (date('N', strtotime($i)) == 1 || date('N', strtotime($i)) == 4) {
            echo "<p class='cell trainingday'>Entraînement de 19h00 à 21h00<img class='punchbutton' src='punchbutton.png'></p>";
        }
        // Vérifie si c'est un mardi pour afficher l'entraînement
        if (date('N', strtotime($i)) == 2) {
            echo "<p class='cell trainingday'>Entraînement de 18h30 à 20h30<img class='punchbutton' src='punchbutton.png'></p>";
        }
        // Vérifie si une date correspond à un jour férié
        if (array_key_exists($dateToCheck, $publicHolidaysArray)) {
            echo "<p class='cell'>Jour férié : " . $publicHolidaysArray[$dateToCheck] . "</p>";
        }
        echo "</div>"; // Fin jour
    }
    echo "</div>"; // Fin row
    echo "</div>"; // Fin container
}


// Fonction qui génère les jours de la semaine en haut du tableau avec leur date

function generateWeekDays($week_start, $week_end, $formatter)
{
    for ($i = $week_start; $i <= $week_end; $i = date("Y-m-d", strtotime("+1 day", strtotime($i)))) {
        echo "<th class='col-lg-1'>" . ucwords($formatter->format(strtotime($i))) . "</th>";
    }
}

// Fonction qui génère les modales pour les anniversaires

function createBirthdayModals($birthday)
{
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Paris', IntlDateFormatter::GREGORIAN, "dd MMMM yyyy"); // Formate la date format "20 janvier 2013"
    echo '<div class="modal fade" id=' . $birthday['lastname'] . ' tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-title fs-5" id="exampleModalLabel">Naissance de ' . $birthday['firstname'] . ' le ' . $formatter->format(strtotime($birthday['date'])) . '</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <p>Joyeux Anniversaire ! </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Retour</button>
        </div>
      </div>
    </div>
    </div>';
}

// Fonction qui génère les modales pour les évènements

function createOtherEventModals($event)
{
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Paris', IntlDateFormatter::GREGORIAN, "EEEE dd MMMM yyyy"); // Formate la date format "vendredi 20 janvier 2023"
    echo '<div class="modal fade" id=' . str_replace(' ', "", $event['name']) . ' tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="text-center modal-title fs-5" id="exampleModalLabel">' . $event['name'] . '</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p> Le ' . ucwords($formatter->format(strtotime($event['date']))) . ' à </p> 
          <p>' . $event['adress'] . '</p>
          <p>Plus d\'infos: </p>
          <p>' . $event['comment'] . '</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Retour</button>
        </div>
      </div>
    </div>
    </div>';
}
