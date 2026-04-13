<?php

require_once __DIR__ . '/classes/CustomTableProperty.php';

AddEventHandler('iblock', 'OnIBlockPropertyBuildList', ['CustomTableProperty', 'GetUserTypeDescription']);
