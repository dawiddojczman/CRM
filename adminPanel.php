<?php
session_start();
echo $_SESSION['user'] ? $_SESSION['user'] : "<script>window.location='workerLogin.php'</script>"; //sprawdzenie czy jest zalogowany
echo $_SESSION['response'];
require_once 'DB_connect.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8_polish_ci">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dojczman - Panel administacyjny</title>
</head>

<body>
    <form action="logout.php" method="post"><button type="submit">Wyloguj!</button></form>

    <?php
    $q = "SELECT COUNT(idr) as liczba_postow from posty where post_klienta is not null";
    $res = $DB_connection->query($q) or die("Zapytanie $q nie działa");
    $res = $res->fetch_object();
    echo "<p>Ilość wszystkich zapytań wygenerowanych przez wszystkich klientów:
        $res->liczba_postow </p>";

    $q = "SELECT COUNT(idr) as liczba_postow from posty where post_pracownika is not null";
    $res = $DB_connection->query($q) or die("Zapytanie $q nie działa");
    $res = $res->fetch_object();
    echo "<p>Ilość wszystkich odpowiedzi udzielonych przez wszystkich pracowników:
        $res->liczba_postow </p>";

    $q = "SELECT nazwisko, COUNT(idr) as liczba_postow from 
                    posty inner join pracownicy using(idp) where
             post_pracownika is not null group by idp";
    $res = $DB_connection->query($q) or die("Zapytanie $q nie działa");
    echo "<p>Ilość wszystkich odpowiedzi udzielonych przez wszystkich pracowników<br>
        z pogrupowaniem według pracowników: </p>";
    echo "<table>
                    <thead><td>Pracownik</td><td>odpowiedzi</td></thead>
                    <tbody>";
    while ($resObj = $res->fetch_object()) {
        echo "<tr>
                            <td>$resObj->nazwisko</td><td>$resObj->liczba_postow</td>
                        </tr>";
    }
    echo " </tbody>
            </table>";


    $q = "SELECT nazwa,count(idz) as l_odp from
                    posty inner join zagadnienia using(idz) where
                    post_pracownika is not null group by idz";
    $res = $DB_connection->query($q) or die("Zapytanie $q nie działa");
    echo "<p>ilość wszystkich odpowiedzi udzielonych przez wszystkich pracowników<br>
            z pogrupowaniem według zagadnień: </p>";

    echo "<table>
            <thead><td>Tematyka</td><td>odpowiedzi</td></thead>
                    <tbody>";
    while ($resObj = $res->fetch_object()) {
        echo "<tr>
                            <td>$resObj->nazwa</td><td>$resObj->l_odp</td>
                        </tr>";
    }
    echo " </tbody>
            </table>";


    $q = "SELECT nazwisko, (sum(ocena)/count(ocena))as srednia from
                            posty inner join pracownicy using(idp) where
                            ocena is not null group by idp";
    $res = $DB_connection->query($q) or die("Zapytanie $q nie działa");
    echo "<p>Wyświetlenie średniej ocen wszystkich pracowników, nadanych im przez klientów,<br>
            z pogrupowaniem na pracowników : </p>";

    echo "<table>
            <thead><td>Pracownik</td><td>Średnia</td></thead>
                    <tbody>";
    while ($resObj = $res->fetch_object()) {
        echo "<tr>
                            <td>$resObj->nazwisko</td><td>$resObj->srednia</td>
                        </tr>";
    }
    echo " </tbody>
            </table>";
    ?>

</body>

</html>