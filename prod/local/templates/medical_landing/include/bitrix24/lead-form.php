<?php
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_CHECK', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !check_bitrix_sessid()) {
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$name   = trim((string) ($_POST['name'] ?? ''));
$phone  = trim((string) ($_POST['phone'] ?? ''));
$course = trim((string) ($_POST['course'] ?? ''));

if (empty($phone)) {
    echo json_encode(['success' => false, 'error' => 'Укажите номер телефона']);
    exit;
}

if (empty($_POST['consent'])) {
    echo json_encode(['success' => false, 'error' => 'Необходимо согласие на обработку персональных данных']);
    exit;
}

\Bitrix\Main\Mail\Event::send([
    'EVENT_NAME' => 'LEAD_FORM_NEW',
    'LID'        => SITE_ID,
    'C_FIELDS'   => [
        'NAME'   => $name ?: '—',
        'PHONE'  => $phone,
        'COURSE' => $course ?: '—',
    ],
]);

$b24Include = __DIR__ . '/lead_b24.php';
if (is_readable($b24Include)) {
    require_once $b24Include;
    if (function_exists('lead_form_bitrix24_create_lead')) {
        lead_form_bitrix24_create_lead($name, $phone, $course);
    }
}

echo json_encode(['success' => true]);
