<?php
session_start();
$nazwisko = $_POST['nazwisko'];
setcookie('nazwisko', $nazwisko); //ciasteczko do celów logowania
$haslo = $_POST['haslo'];
require_once 'DB_connect.php'; //połączenie z bazą danych

$response = '';

$q = "SELECT nazwisko FROM klienci where nazwisko='$nazwisko' limit 1";
$res = $DB_connection->query($q) or die("Nie działa $q");
$res = $res->fetch_object(); //res z CRM klienci

if (!$res->nazwisko) {
    $q = "SELECT nazwisko FROM pracownicy where nazwisko='$nazwisko' limit 1";
    $res = $DB_connection->query($q) or die("Nie działa $q");
    $res = $res->fetch_object(); //res z CRM pracownicy

    if (!$res->nazwisko) { // brak kogoś o takim nazwisku
        $response = 'Nazwisko jest niepoprawne';
    } else {        // jest taki pracownik
        $response = checkWorker($res->nazwisko, $haslo);
    }
} else {        // jest taki Klient
    $response = checkClient($res->nazwisko, $haslo);
}

$_SESSION['response'] = $response; // wysłanie odpowiedzi zwrotnej
echo "<script>window.location = 'index.php';</script>"; //przekierowanie do strony głównej



function checkClient($nazwisko, $haslo)
{
    global $DB_connection;
    $q = "SELECT * from klienci where haslo='$haslo'";
    $res = $DB_connection->query($q) or die("Nie działa $q");
    $resObj = $res->fetch_object();

    if ($resObj->nazwisko == $nazwisko) {
        catchClient($resObj->idk); //rejestracja logowania klienta 
        $_SESSION['user'] = $resObj->nazwisko;
        $_SESSION['userID'] = $resObj->idk;
        echo "<script>window.location = 'clientPanel.php';</script>";
    } else {
        $response = 'Hasło jest niepoprawne';
    }
    return $response;
}

function checkWorker($nazwisko, $haslo)
{
    global $DB_connection;
    $q = "SELECT * from pracownicy where haslo='$haslo'";
    $res = $DB_connection->query($q) or die("Nie działa $q");
    $resObj = $res->fetch_object();

    if ($resObj->nazwisko == $nazwisko) {
        catchWorker($resObj->idp); //rejestracja logowania pracownika 
        echo ($resObj->nazwisko == 'admin' ? "<script>window.location = 'adminPanel.php';</script>" : ''); // sprawdzenie czy ten pracownik to admin
        $_SESSION['user'] = $resObj->nazwisko;                                                   // i ewentualne przekierowanie do panelu admina    
        $_SESSION['userID'] = $resObj->idp;
        echo "<script>window.location = 'workerPanel.php';</script>";
    } else {
        $response = 'Hasło jest niepoprawne';
    }
    return $response;
}

function catchClient($idk) //rejestracja użytkownika w logach
{
    global $DB_connection;
    $result = get_browser(null, true);
    $IP = $_SERVER['REMOTE_ADDR']; //adres IP
    $browser = $result['browser']; // przeglądarka klienta
    $system = $result['platform']; // System operacyjny klienta

    $q = "INSERT INTO logi_klientow(idk,przegladarka,system,adresIP)
     VALUES($idk,'$browser','$system','$IP')";
    $DB_connection->query($q) or die("Zapytanie $q nie działa");
}

function catchWorker($idp) //rejestracja pracownika w logach
{
    global $DB_connection;

    $q = "INSERT INTO logi_pracownikow(idp) VALUES($idp)";
    $DB_connection->query($q) or die("zapytanie $q nie działa");
}
