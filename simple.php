<?php

// Настройки
$now = time();
$projectId = 1;
$secret = 'my_project_secret_key';

// Формируем XML-пакет
$xml = "
<request>
    <project>{$projectId}</project>
    <timestamp>{$now}</timestamp>
    <action>check</action>
    <params>
        <paysystem>qiwi</paysystem>
        <account>9217435256</account>
        <amount>10</amount>
    </params>
</request>";

// Формируем строку для подписи
preg_match('|<action>(.+)</action>|is', $xml, $matches);
$action = isset($matches[1]) ? $matches[1] : null;
preg_match('|<params>(.+)</params>|is', $xml, $matches);
$params = isset($matches[1]) ? $matches[1] : null;
if ($params) {
        preg_match_all('|<([a-zA-Z\_]+)>([^<]+)</[a-zA-Z\_]+>|iu', $params, $matches);
        $params = array_combine($matches[1], $matches[2]);
        ksort($params);        
} else {
        $params = array();
}

// Формируем подпись и добавляем ее в XML-пакет
$signString = $now . $projectId . $action . implode('', $params) . $secret;
$sign = md5($signString);
$xml = str_replace("</request>", "<sign>{$sign}</sign></request>", $xml);

// Запрос к сервису
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://gsg.dengionline.com/api/{$projectId}/");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('xml' => $xml)));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($ch);
curl_close($ch);

// В $data - XML ответа от сервиса, который можно обработать