<?php
/**
 * pr0gramm Invitebenis Bot
 * 
 * @author    RundesBalli <rundesballi@rundesballi.com>
 * @copyright 2019 RundesBalli / https://github.com/pr0-dev
 * @version   2.0
 * @license   MIT-License
 */

/**
 * Einbinden der Konfigurationsdatei.
 */
require_once(__DIR__.DIRECTORY_SEPARATOR."config.php");

/**
 * Einbinden der Funktionsdatei.
 */
require_once(__DIR__.DIRECTORY_SEPARATOR."functions.php");

/**
 * Zeitzone
 */
date_default_timezone_set("Europe/Berlin");

/**
 * Letzten Stichtag herausfinden
 * Hinweis: Invitevergabe immer Anfang Februar, Mai, August und November.
 * Gerechnet wird mit dem 01. des jeweiligen Monats um 00:00:00
 */
$keymonth = array(1 => 11, 2 => 2, 3 => 2, 4 => 2, 5 => 5, 6 => 5, 7 => 5, 8 => 8, 9 => 8, 10 => 8, 11 => 11, 12 => 11);
$keydate = mktime(0, 0, 0, $keymonth[date("n")], 1, (date("n") < 2 ? date("Y")-1 : date("Y")));

/**
 * Input von Telegram auffangen.
 */
$content = file_get_contents("php://input");
$response = json_decode($content, true);
if(empty($response)) {
  die();
}

/**
 * Emoji Byte Konstanten
 */
const TICK = "\xE2\x9C\x85";
const CROSS = "\xE2\x9B\x94";

/**
 * Prüfen ob der Anfragende die Anfrage ausführen darf.
 */
if($response['message']['chat']['id'] != $chat_id) {
  SendMessageToTelegram("Zugriff nicht erlaubt", $response['message']['chat']['id']);
  die();
}

/**
 * Weiteres Vorgehen nur, wenn der richtige Befehl gesendet wurde.
 */
if(substr($response['message']['text'], 0, 13 ) === "/checkinvites") {
  $username = explode(" ", $response['message']['text']);
  if(!($username = validUsername($username[1]))) {
    SendMessageToTelegram("Kein oder fehlerhafter Username übergeben.\nErlaubt sind `[a-zA-Z0-9-_]{2,32}`\nzum Beispiel `/checkinvites pr0gramm`", $chat_id);
    die();
  }
  $response = apiCall("https://pr0gramm.com/api/profile/info/?name=".$username);
  /**
   * Prüfung ob der User zu viel Inhalte hat, damit der Bot nicht ewig läuft.
   * Pro Upload-Anfrage 120 Uploads und pro Kommentar-Anfrage 50 Kommentare, daher die Limits.
   */
  if($response['uploadCount'] > 2500 OR $response['commentCount'] > 2000) {
    SendMessageToTelegram("Crawle [".$username."](https://pr0gramm.com/user/".$username.") NICHT, da der User mehr als 2.500 Uploads und/oder mehr als 2.000 Kommentare hat.", $chat_id);
    die();
  }
  /**
   * Prüfung ob die Mindestanforderungen erfüllt wurden, damit der Inviteverteiler den Nutzer überhaupt in Betracht zieht.
   * - Es müssen mindestens 10 Uploads ODER 50 Kommentare vorhanden sein.
   * - Der User muss mindestens 0 Benis haben, damit der Inviteverteiler prüft, ob der User durch seine nicht-NSFW Inhalte 3000 Benis erreicht hat.
   * - Der User muss zum Stichtag 180 Tage registriert sein.
   */
  if(($response['uploadCount'] < 10 AND $response['commentCount'] < 50) OR ($response['user']['score'] < 0 OR ($keydate-$response['user']['registered']) < 15552000)) {
    SendMessageToTelegram("Crawle [".$username."](https://pr0gramm.com/user/".$username.") obwohl folgende Bedingungen noch nicht erfüllt sind, damit der Inviteverteiler den Nutzer überhaupt prüft:\n".(($response['uploadCount'] > 10 OR $response['commentCount'] > 50) ? TICK : CROSS)." mindestens 10 Uploads (".$response['uploadCount'].") oder 50 Kommentare (".$response['commentCount']."), \n".(($response['user']['score'] > 1000) ? TICK : CROSS)." mindestens 1000 Benis Profilsumme und\n".(($keydate-$response['user']['registered']) < 15552000 ? CROSS : TICK)." mindestens 180 Tage Mitgliedschaft.", $chat_id);
    $inviteberechtigt = "NICHT inviteberechtigt ".CROSS;
  } else {
    SendMessageToTelegram("Crawle [".$username."](https://pr0gramm.com/user/".$username.")...", $chat_id);
  }
  /**
   * Voraussetzungen wurden erfüllt, jetzt wird der nicht-NSFW Benis von bestehendem Usercontent gezählt.
   * Formel und Voraussetzungen von Gamb bestätigt.
   * Der Bot meldet jetzt kurz in den Chat, dass er anfängt zu arbeiten.
   */
  $totalbenis = 0;
  $totalbenis_keydate = 0;
  /**
   * Posts crawlen
   */
  $older = 9999999;
  $atEnd = FALSE;
  do {
    $response = apiCall('https://pr0gramm.com/api/items/get?older='.$older.'&flags=13&user='.$username);
    if($response['atEnd'] === TRUE OR $response['error'] !== NULL) {
      $atEnd = TRUE;
    }
    if($response['error'] === NULL) {
      foreach($response['items'] AS $itemkey => $itemcontent) {
        $totalbenis= $totalbenis+$itemcontent['up']-$itemcontent['down'];
        if($itemcontent['id'] < $older) {
          $older = $itemcontent['id'];
        }
        if($itemcontent['created'] <= $keydate) {
          $totalbenis_keydate = $totalbenis_keydate+$itemcontent['up']-$itemcontent['down'];
        }
      }
    } else {
      $atEnd = TRUE;
    }
  } while($atEnd == FALSE);
  /**
   * Kommentare crawlen
   */
  $before = 9999999999;
  $hasOlder = TRUE;
  do {
    $response = apiCall('https://pr0gramm.com/api/profile/comments?name='.$username.'&flags=13&before='.$before);
    if($response['hasOlder'] === FALSE) {
      $hasOlder = FALSE;
    }
    foreach($response['comments'] AS $itemkey => $itemcontent) {
      $totalbenis= $totalbenis+$itemcontent['up']-$itemcontent['down'];
      if($itemcontent['created'] < $before) {
        $before = $itemcontent['created'];
      }
      if($itemcontent['created'] <= $keydate) {
        $totalbenis_keydate = $totalbenis_keydate+$itemcontent['up']-$itemcontent['down'];
      }
    }
  } while($hasOlder == TRUE);
  if(!isset($inviteberechtigt)) {
    if($totalbenis > 3000) {
      $inviteberechtigt = "inviteberechtigt ".TICK;
    } else {
      $inviteberechtigt = "NICHT inviteberechtigt ".CROSS;
    }
    SendMessageToTelegram("Der User [".$username."](https://pr0gramm.com/user/".$username.") ist *".$inviteberechtigt."*\nGesamtbenis sfw/nsfl/nsfp: ".$totalbenis.(($totalbenis < 3000) ? " (Fehlend: ".(3000-$totalbenis).")" : "")."\nGesamtbenis zum Stichtag: ".$totalbenis_keydate, $chat_id);
  } else {
    SendMessageToTelegram("Gesamtbenis sfw/nsfl/nsfp: ".$totalbenis.(($totalbenis < 3000) ? " (Fehlend: ".(3000-$totalbenis).")" : "\n_(ausreichend, aber der User wird aus o.g. Gründen nicht berücksichtigt)_"), $chat_id);
  }
}
?>
