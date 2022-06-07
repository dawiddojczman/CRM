<?php
session_start();
echo $_SESSION['user'] ? $_SESSION['user'] : "<script>window.location='index.php'</script>"; //sprawdzenie czy jest zalogowany
$user = $_SESSION['user'];
require_once 'DB_connect.php';
mysqli_set_charset($DB_connection, "utf8");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8_polish_ci">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <title>Dojczman - Panel kliencki</title>
    <style>
        tr.response {
            background-color: #dedede;
        }
    </style>
</head>

<body>
    <form action="logout.php" method="post"><button type="submit">Wyloguj!</button></form>

    <form class='question' action="" method="post">
        <h3>Hi! You can ask a question</h3>
        <select name="theme" id="theme" required>
            <?php // pobranie zagadnień z bazy i wpisanie ich jako opcje selectu
            $q = "SELECT * FROM zagadnienia";
            $res = $DB_connection->query($q) or die("Zapytanie $q nie działa");
            while ($row = $res->fetch_object()) {
                echo "<option value='$row->nazwa'> $row->nazwa </option>";
            }
            ?>
        </select>
        <textarea name='content' id='content' cols='30' rows='5' required></textarea>
        <button name='makeAQuestion' type="submit">Wyślij</button>
    </form>

    <div class="addedQuestions">
        <?php // zapytanie pobiera nazwy zagadnień, w których mamy jakąś aktywność
        $q = "SELECT idz,nazwa from posty inner join zagadnienia using(idz)  where 
            idk=(SELECT idk from klienci where nazwisko = '$user') group by idz";

        $res = $DB_connection->query($q) or die("Zapytanie $q nie działa");
        while ($resObj = $res->fetch_object()) {
            echo "
            <div class='thread'>
                <h3>$resObj->nazwa</h3>
                <table>
                    <thead><td>Client</td><td>Message</td><td>Date and time</td><td>Ocena</td></thead>
                     <tbody>";
            getMessages($resObj->idz);  // dla każdego zagadnienia pobieramy wiadomości i odpowiedzi od pracownika
            echo  '</tbody></table>';
            echo '</div>';
        }
        ?>

    </div>
</body>

</html>

<?php
// obsługa dodania pytania przez kliknięcie przycisku makeAQuestion
if (isset($_POST['makeAQuestion'])) {
    $theme = $_POST['theme'];
    $content = $_POST['content'];
    $q = "INSERT INTO posty(idk,idz,post_klienta) 
    VALUES(
        (select idk from klienci where nazwisko = '$user'),
        (select idz from zagadnienia where nazwa = '$theme'),
        '$content')"; //inrestuje do bazy id usera, temat zadanego pytania i jego treść
    $DB_connection->query($q) or die("Zapytanie $q nie działa");
    echo "<script>window.location='clientPanel.php'</script>"; //odświerzenie strony
}


function getMessages($idz) //pobieramy id zagadnienia dla jakiego szukamy wiadomości
{
    global $user;         // od danego użytkownika
    global $DB_connection;

    $q = "SELECT * from posty where
         idz=$idz and idk=(SELECT idk from klienci where nazwisko='$user')";

    $res = $DB_connection->query($q) or die("Zapytanie $q nie działa");
    $i = 1;
    while ($resObj = $res->fetch_object()) {
        if ($resObj->post_klienta) {      /// formularz do oceniania odpowiedzi - wyświetlnay w przypadku gdy odpowiedź na pytania jest udzielona
            $ocena = $resObj->ocena ? "<td>$resObj->ocena</td>" : "<td><form method='post'><select name='ocena' id='ocena'>
                                                    <option value='5' selected>5</option>
                                                    <option value='4' >4</option>
                                                    <option value='3' >3</option>
                                                    <option value='2' >2</option>
                                                    <option value='1' >1</option>
                                </select><input type='submit' value='OK' name='$i'>
                                <input type='number' value='$resObj->idr' name='idr' style='display:none;'></form></td>";

            if ($resObj->idp) {
                // $odp = $resObj->post_pracownika ? $resObj->post_pracownika : 'przypisane, oczekuje'; // obsługa stanu przypisania
                echo "<tr class='response'><td>$user</td><td>$resObj->post_klienta</td><td>$resObj->datagodzina</td></tr>";
                $worker = $DB_connection->query(
                    "SELECT nazwisko from pracownicy where idp = $resObj->idp"
                )->fetch_object()->nazwisko;
                //jeśli pracownik odpowiedział na pytanie to je wyświetl
                echo $resObj->post_pracownika ? "<tr><td>$worker</td><td>$resObj->post_pracownika</td><td>$resObj->Rdatagodzina</td>$ocena</tr>" : '';

                // echo "<tr><td>$worker</td><td>$odp</td><td>$resObj->Rdatagodzina</td>$ocena</tr>";
            } else {
                // echo "<tr class='response'><td>$user</td><td>$resObj->post_klienta</td><td>$resObj->datagodzina</td></tr>";
                // echo "<tr><td>-</td><td>nieprzypisane</td><td>$resObj->Rdatagodzina</td>-</tr>"; //obsługa stanu przypisania
            }

            $i += 1; //iterator dzięki któremu obsłużymy więcej niż jeden formularz oceniania w prosty sposób
        }
    }
    while ($i) { //jeśli na którymś z pytań ustawiona jest ocena, to zostanie ona wpisana pojedynczo,
        // aby wpisane były wszystkiei wystarczy przenieść odświerzenie skryptem JS poza pętlę while;
        if (isset($_POST[$i])) {
            echo $ocena = $_POST['ocena'];
            echo $idr = $_POST['idr'];
            $DB_connection->query("UPDATE posty SET ocena=$ocena where idr=$idr")
                or die('Nie działa');
            echo "<script>window.location = window.location</script>";
        }
        $i -= 1;
    }
}
?>