<?php
/**
 * functions.php
 * 
 * Funktionen für den pr0gramm Invitebenis Bot
 */

/**
 * Funktion zum Senden von Nachrichten an einen Telegram Client.
 * 
 * @param string  Der zu sendende Text
 * @param string  Die Chat-ID an die der Text gesendet werden soll
 * @param boolean Wenn die Benachrichtigung des Clients nicht erfolgen soll, dann TRUE.
 * 
 * @return boolean Bei Erfolg TRUE, im Fehlerfall FALSE.
*/
function SendMessageToTelegram($text = NULL, $chat_id = NULL, $disableNotification = FALSE) {
  if($text == NULL OR $chat_id == NULL) {
    return FALSE;
  }
  
  global $apiToken;
  global $bindTo;
  global $telegamUseragent;
  
  $postdata = array(
  'chat_id' => $chat_id,
  'text' => $text,
  'parse_mode' => 'Markdown',
  'disable_notification' => $disableNotification,
  'disable_web_page_preview' => TRUE
  );
  $data = http_build_query($postdata);
  
  $ch = curl_init();
  curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => 'https://api.telegram.org/bot'.$apiToken.'/sendMessage',
    CURLOPT_USERAGENT => $telegamUseragent,
    CURLOPT_INTERFACE => $bindTo,
    CURLOPT_POST => 1,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_POSTFIELDS => $data
  ));
  $response = curl_exec($ch);
  $errno = curl_errno($ch);
  $errstr = curl_error($ch);
  if($errno != 0) {
    return FALSE;
  }
  $http_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
  if($http_code != 200) {
    return FALSE;
  }
  curl_close($ch);
  return TRUE;
}

/**
 * Funktion um Usernamen von pr0gramm.com zu validieren
 * 
 * @param string Der zu prüfende Username.
 * 
 * @return string/boolean Bei Erfolg wird der validierte Username zurückgegeben, bei Misserfolg FALSE.
 */
function validUsername($username) {
  $regex = '/^[a-zA-Z0-9-_]{2,32}$/i';
  return (preg_match($regex, trim($username), $matches) === 1) ? $matches[0] : FALSE;
}
?>
