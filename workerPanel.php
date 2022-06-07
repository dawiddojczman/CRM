<?php
session_start();
echo $_SESSION['user'] ? $_SESSION['user'] : "<script>window.location='index.php'</script>"; //sprawdzenie czy jest ktoś zalogowany
$user = $_SESSION['user'];
echo $_SESSION['response'];
require_once 'DB_connect.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8_polish_ci">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dojczman - Panel pracowniczy</title>
    <link rel="stylesheet" href="style.css">
    <style>
        tr.response {
            background-color: #dedede;
        }
    </style>
</head>

<body>
    <form action="logout.php" method="post"><button type="submit">Wyloguj!</button></form>

    <div class="unsubscribed">

        <h3>Przypisz do siebie pytanie</h3>
        <form action="" method="get">
            <select name="category">
                <?php
                // filtrowanie kategoriami
                $res = $DB_connection->query("SELECT nazwa from zagadnienia") or die("Nie działa");
                while ($resObj = $res->fetch_object()) {
                    $sel =  $_GET['category'] == $resObj->nazwa ? 'selected' : '';
                    echo "<option $sel value='$resObj->nazwa'>$resObj->nazwa</option>";
                }
                ?>
            </select>
            <button name='filterButton' type="submit">Zatwierdź!</button>
        </form>
        <form action="" method="post">
            <table>
                <thead>
                    <td>Category</td>
                    <td>Content</td>
                    <td>Date and time</td>
                    <td>Wybierz</td>
                </thead>
                <tbody>
                    <?php
                    $checkbox_i = 1;
                    if (isset($_GET['filterButton'])) { //jeśli jest kliknięty button od filtrowania, to ustawiamy nazwę kategorii w queryString
                        $category = $_GET['category'];
                        $q = "SELECT idr,nazwa,post_klienta,datagodzina from posty inner join zagadnienia using(idz)
                        where idp is NULL and nazwa = '$category' order by datagodzina asc";
                    } else {
                        //pobranie wszstkich nieprzypisancyh do pracownia pytań i wyświetlenie
                        $q = "SELECT idr,nazwa,post_klienta,datagodzina from posty inner join zagadnienia using(idz)
                        where idp is NULL order by datagodzina asc";
                    }
                    $res = $DB_connection->query($q) or die("Nie działa $q");
                    while ($resObj = $res->fetch_object()) {   /// pytania do przypisania
                        echo "<tr><td>$resObj->nazwa</td> 
                                  <td>$resObj->post_klienta</td>
                                  <td>$resObj->datagodzina</td>
                                  <td><label for='select'><input type='checkbox' value='$resObj->idr' name='$checkbox_i' id='select'></label></td>
                              </tr>";
                        $checkbox_i += 1; /// iterator do znakowania  chceckboxów
                    }
                    ?>
                </tbody>
            </table>
            <input type="submit" name='subscribe' value="Przypisz!">
        </form>
    </div>
    <div class="subscribed">
        <?php
        // pobranie zagadnień w których udzileił się pracownik w  celu wyświetlenia jego pytań
        $q = "SELECT idz,nazwa from posty inner join zagadnienia using(idz)  where 
                idp=(SELECT idp from pracownicy where nazwisko = '$user') group by idz";

        $res = $DB_connection->query($q) or die("Zapytanie $q nie działa");
        while ($resObj = $res->fetch_object()) {
            echo "
                <div class='thread'>
                    <h3>$resObj->nazwa</h3>
                    <table>
                        <thead><td>Client</td><td>Message</td><td>Date and time</td></thead>
                        <tbody>";
            getMessages($resObj->idz); //domyka tabele z automatu
            echo '</div>';
        }
        ?>
    </div>

</body>

</html>


<?php



if (isset($_POST['responseButton'])) { // obsługa kliknięcia w przycisk odpowiedzi
    $idk = $_POST['idk'];
    $datagodzina = $_POST['datagodzina'];
    $content = $_POST['content'];

    $q = "UPDATE posty SET post_pracownika='$content', Rdatagodzina=now() WHERE
        idk = $idk and 
        idp = (SELECT idp from pracownicy where nazwisko = '$user') and
        datagodzina = '$datagodzina'";
    $DB_connection->query($q) or die("Zapytanie $q nie działa");

    echo "<script>window.location='workerPanel.php'</script>";
}


if (isset($_POST['subscribe'])) { // obsługa przyppisania sobie pytania
    echo "Dodano\n";
    $checkbox_i = 1;
    $res = $DB_connection->query("SELECT count(nazwa)as lr from zagadnienia")->fetch_object()->lr;
    while ($res--) {
        echo $idr = $_POST[$checkbox_i];
        if ($idr) {                 //ustawiamy idp na numer zalogowanego pracownika
            $q = "UPDATE posty SET 
                    idp=(SELECT idp from pracownicy WHERE nazwisko='$user'),
                    Rdatagodzina=now()
                    where idr = $idr";
            $DB_connection->query($q) or die("Zapytanie $q nie działa");
        }
        echo $checkbox_i += 1;
    }
    echo "<script>window.location='workerPanel.php'</script>";
}


function getMessages($idz)
{
    global $DB_connection;
    global $user;                       // pobraie danych na temap dotyczących nas postów w danej kategorii
    $q = "SELECT idk,nazwisko,post_klienta,post_pracownika,datagodzina,Rdatagodzina FROM posty
                                            inner join zagadnienia using(idz)
                                            inner join klienci using(idk) WHERE
        idp=(SELECT idp from pracownicy where nazwisko = '$user') and
        idz=$idz";
    $res = $DB_connection->query($q) or die("Zapytanie $q nie działa");
    while ($resObj = $res->fetch_object()) {
        echo "<tr><td>$resObj->nazwisko</td><td>$resObj->post_klienta</td><td>$resObj->datagodzina</td></tr>";
        echo "<tr class='response'><td>$user</td><td>$resObj->post_pracownika</td><td>$resObj->Rdatagodzina</td></tr>";
        if (!$resObj->post_pracownika) { //jeśli brak odpowiedzi, to wyświetl formularz do odpowiedzenia
            echo "</tbody>
            </table>";

            echo "<form action='' method='post'>
                <input style='display:none' readonly type='number' value='$resObj->idk' name='idk' id=''>
                <input style='display:none' readonly type='text' value='$resObj->datagodzina' name='datagodzina' id=''>
                        <textarea  name='content' id='' cols='40' rows='3'></textarea>
                        <button type='submit' name='responseButton'>Odpowiedz!</button>
            </form>";
        }
    }
    echo "</tbody>
        </table>";
}
?>