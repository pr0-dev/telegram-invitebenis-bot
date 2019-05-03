<?php
/**
 * config.php
 * 
 * Konfiguration für den pr0gramm Invitebenis Bot
 */

/**
 * Einbinden des apiCalls.
 * Download: https://github.com/RundesBalli/pr0gramm-apiCall
 * 
 * Beispielwert: /home/user/apiCall/apiCall.php
 * 
 * @param string
 */
require_once("");

/**
 * Das API Token welches man vom Telegram Bot "@BotFather" bei der Registrierung seines Bots bekommt.
 * 
 * Beispielwert: 000000000:AAAAAAAAAAAAAAAAAAAA-_0000000000
 * 
 * @var string
 */
$apiToken = "";

/**
 * Chat ID, von dem Anfragen zugelassen sind.
 * Auch Gruppen IDs sind erlaubt, dann muss der Bot in die Gruppe eingeladen werden.
 * 
 * Beispielwert: 1234567890
 * kann auch mit negativem Vorzeichen sein!
 * 
 * @var string
 */
$chat_id = "";

/**
 * Die IP-Adresse die für die ausgehende Verbindung genutzt werden soll.
 * 
 * Beispielwert: 1.2.3.4
 * 
 * @var string
 */
$bindTo = "";

/**
 * Der Useragent der an Telegram gesendet wird.
 * 
 * Beispielwert: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:66.0) Gecko/20100101 Firefox/66.0
 * oder          Heinrichs lustige Datenkrake
 * 
 * @var string
 */
$telegamUseragent = "";
?>
