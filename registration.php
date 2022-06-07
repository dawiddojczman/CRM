<?php
session_start();
$nazwisko = $_POST['Rnazwisko'];
$haslo1 = $_POST['Rhaslo1'];
$haslo2 = $_POST['Rhaslo2'];
setcookie('Rnazwisko', $nazwisko);  //ustawienie ciasteczka;

require_once 'DB_connect.php';

if ($haslo1 !== $haslo2) {
    $_SESSION['registrationResponse'] = "Hasła nie są takie same"; //odpowiedź zwrotna w razie 
    echo "<script>window.location='index.php'</script>";         // gdy hasła nie są identynczne
} else {

    $q = "SELECT * FROM klienci WHERE nazwisko='$nazwisko' ";
    $res = $DB_connection->query($q) or die("Zapytanie $q nie działa");

    if ($res->fetch_object()) { // sprawdzenie czy podany użytkownik już istnieje
        $_SESSION['registrationResponse'] = "Podany użytkownik już istnieje"; //
    } else {
        $q = "INSERT INTO klienci(nazwisko,haslo) VALUES('$nazwisko','$haslo1')";
        $res = $DB_connection->query($q) or die("Zapytanie $q nie działa");
        // pomyślne zarejestrowanie i odpowiedź zwrotna
        $_SESSION['registrationResponse'] = $res ? "Dodano użytkownika" : '';
    }
}

echo "<script>window.location='index.php'</script>"; // przekierowanie na stronę główną
