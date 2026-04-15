<?php

/**
 * Создание лида в Битрикс24 через входящий вебхук (crm.lead.add).
 * Конфиг рядом: lead_b24_webhook.php — return ['incoming_webhook_base' => 'https://...rest/1/код/'];
 * Нет файла или пустой URL — запрос в B24 не отправляется.
 */

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

/**
 * @return bool false только при явной ошибке API (для логирования)
 */
function lead_form_bitrix24_create_lead(string $name, string $phone, string $course): bool
{
    $configPath = __DIR__ . '/lead_b24_webhook.php';
    if (!is_readable($configPath)) {
        return true;
    }

    /** @var array $cfg */
    $cfg = include $configPath;
    $base = isset($cfg['incoming_webhook_base']) ? trim((string) $cfg['incoming_webhook_base']) : '';
    if ($base === '') {
        return true;
    }

    if (substr($base, -1) !== '/') {
        $base .= '/';
    }

    $url = $base . 'crm.lead.add.json';

    $course = html_entity_decode($course, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $isAll = $course === '__all__' || $course === 'Хочу узнать про все';
    $courseLabel = $isAll ? 'Консультация по всем курсам' : $course;

    if ($isAll) {
        $title = 'Заявка на консультацию "По всем курсам"';
    } elseif ($course !== '') {
        $title = "Заявка с сайта на курс \"{$course}\"";
    } else {
        $title = 'Заявка с сайта';
    }

    $comments = [];
    if ($courseLabel !== '') {
        $comments[] = 'Курс: ' . $courseLabel;
    }
    $comments[] = 'Источник: форма записи на сайте';

    $fields = [
        'TITLE' => $title,
        'NAME' => $name !== '' ? $name : 'Без имени',
        'OPENED' => 'Y',
        'SOURCE_ID' => 'WEB',
        'PHONE' => [['VALUE' => $phone, 'VALUE_TYPE' => 'MOBILE']],
        'COMMENTS' => implode("\n", $comments),
    ];

    $body = Json::encode(['fields' => $fields]);

    $http = new HttpClient(['socketTimeout' => 15, 'streamTimeout' => 15]);
    $http->setHeader('Content-Type', 'application/json; charset=UTF-8');
    $response = $http->post($url, $body);

    if ($response === false) {
        lead_form_bitrix24_log_error('HTTP: запрос не выполнен');
        return false;
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        lead_form_bitrix24_log_error('Ответ не JSON: ' . substr((string) $response, 0, 500));
        return false;
    }

    if (!empty($decoded['error'])) {
        $desc = isset($decoded['error_description']) ? (string) $decoded['error_description'] : '';
        lead_form_bitrix24_log_error((string) $decoded['error'] . ($desc !== '' ? ': ' . $desc : ''));
        return false;
    }

    return true;
}

function lead_form_bitrix24_log_error(string $message): void
{
    if (class_exists('\CEventLog')) {
        \CEventLog::Add([
            'SEVERITY' => 'WARNING',
            'AUDIT_TYPE_ID' => 'LEAD_FORM_B24',
            'MODULE_ID' => 'main',
            'ITEM_ID' => 'lead-form',
            'DESCRIPTION' => $message,
        ]);
    }
}
