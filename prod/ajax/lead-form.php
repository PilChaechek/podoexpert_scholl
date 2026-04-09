<?php
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_CHECK', true);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !check_bitrix_sessid()) {
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$name   = htmlspecialchars(trim($_POST['name']   ?? ''), ENT_QUOTES);
$phone  = htmlspecialchars(trim($_POST['phone']  ?? ''), ENT_QUOTES);
$course = htmlspecialchars(trim($_POST['course'] ?? ''), ENT_QUOTES);

if (empty($phone)) {
    echo json_encode(['success' => false, 'error' => 'Укажите номер телефона']);
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

echo json_encode(['success' => true]);
