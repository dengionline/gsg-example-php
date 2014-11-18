<?php

// Настройки
$now = time();
$projectId = 1;
$secret = 'secret';

$xml = "<request><project>{$projectId}</project><timestamp>{$now}</timestamp><action>balance</action></request>";

// Формируем XML-пакет
$xml = preg_replace('/>\s+</', '><', $xml);
$parser = xml_parser_create();
xml_parse_into_struct($parser, $xml, $vals, $index);
xml_parser_free($parser);

// Формируем строку для подписи
$result = [];
foreach ($vals as $key => $value) {
    if (!in_array($value['type'], ['open', 'close'])) {
        $result[strtolower($value['tag'])] = strtolower($value['tag']) . '=' . $value['value'];
    }
}
ksort($result);

// Подписываем XML-пакет
$signString = "secret={$secret}&" . implode('&', $result);
$signString = str_replace(' ', '+', $signString);
$sign = sha1($signString);
$xml = str_replace("</request>", "<sign>{$sign}</sign></request>", $xml);

// Запрос к сервису
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://gsg.dengionline.com/api');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('xml' => $xml)));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($ch);
curl_close($ch);

// В $data - XML ответа от сервиса, который можно обработать
echo $xml . "\n\n" . $data . "\n";
