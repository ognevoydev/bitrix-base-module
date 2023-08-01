<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

global $APPLICATION;

$request = HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialchars($request["mid"] != "" ? $request["mid"] : $request["id"]);
Loader::includeModule($module_id);

$arSelectBox = ["value" => "title"];
$arMultiSelectBox = ["value 1" => "title 1", "value 2" => "title 2"];

$aTabs = [
    [
        "DIV" => "edit",
        "TAB" => Loc::getMessage("BASE_MODULE_MAIN_TAB"),
        "TITLE" => Loc::getMessage("BASE_MODULE_MAIN_TAB_TITLE"),
        "OPTIONS" => [
            Loc::getMessage("BASE_MODULE_SECTION_COMMON"),
            [
                "CHECKBOX",
                Loc::getMessage("BASE_MODULE_CHECKBOX_OPTION"),
                "Y",
                ["checkbox"],
            ],
            [
                "SELECT",
                Loc::getMessage("BASE_MODULE_SELECT_OPTION"),
                "",
                ["selectbox", $arSelectBox],
            ],
            [
                "MULTISELECT",
                Loc::getMessage("BASE_MODULE_MULTISELECT_OPTION"),
                "",
                ["multiselectbox", $arMultiSelectBox],
            ],
            [
                "TEXT",
                Loc::getMessage("BASE_MODULE_TEXT_OPTION"),
                "",
                ["text"],
            ],
            [
                "PASSWORD",
                Loc::getMessage("BASE_MODULE_PASSWORD_OPTION"),
                "",
                ["password"],
            ],
            [
                "TEXTAREA",
                Loc::getMessage("BASE_MODULE_TEXTAREA_OPTION"),
                "",
                ["textarea", 5, 40],
            ],
        ]
    ],
];

if ($request->isPost() && check_bitrix_sessid()) {

    foreach ($aTabs as $aTab) {
        foreach ($aTab["OPTIONS"] as $arOption) {

            if (!is_array($arOption)) {
                continue;
            }

            if ($request["apply"]) {
                $optionValue = $request->getPost($arOption[0]);
                if ($arOption[3][0] == "checkbox" && $optionValue != "Y") {
                    $optionValue = "N";
                }
                if ($arOption[3][0] == "multiselectbox" && is_array($optionValue))
                {
                    $optionValue = implode(", ", $optionValue);
                }
                Option::set($module_id, $arOption[0], $optionValue);
            } elseif ($request["default"]) {
                Option::set($module_id, $arOption[0], $arOption[2]);
            }
        }
    }
    LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . $module_id . "&lang=" . LANG);
}

$tabControl = new CAdminTabControl(
    "tabControl",
    $aTabs
);

$tabControl->Begin();

?>
    <form action="<? echo($APPLICATION->GetCurPage()); ?>?mid=<? echo($module_id); ?>&lang=<? echo(LANG); ?>"
          method="post">

        <?
        foreach ($aTabs as $aTab) {
            if ($aTab["OPTIONS"]) {
                $tabControl->BeginNextTab();
                __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
            }
        }
        $tabControl->Buttons();
        ?>

        <input type="submit" name="apply" value="<?= Loc::getMessage("BASE_MODULE_BUTTON_APPLY") ?>"
               class="adm-btn-save"/>
        <input type="submit" name="default" value="<?= Loc::getMessage("BASE_MODULE_BUTTON_DEFAULT") ?>"/>

        <? echo(bitrix_sessid_post()); ?>
    </form>
<?php
$tabControl->End();
?>