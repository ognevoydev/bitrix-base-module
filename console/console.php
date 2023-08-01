<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Base\Module\Service;

if (php_sapi_name() != "cli") {
    die();
}

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("NEED_AUTH", false);
define("MODULE_NAME", "base.module");

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

Loader::IncludeModule(MODULE_NAME);

if (Option::get(MODULE_NAME, "CHECKBOX") == "Y") {
    $service = new Service();
    $service->foo();
}
