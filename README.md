# base.module

Базовый шаблон для модуля со страницей настроек, консольным скриптом и сервисом.

## Использование

1. Поместить папку `base.module/` в `local/modules/`

2. Установить модуль через Маркетплейс

## Структура модуля

### Файлы и папки

```php
/*
local/
└─ modules/
    └─ base.module/
        ├─ console
        |   └─ console.php
        ├─ install
        |   ├─ components
        |   ├─ js
        |   ├─ public
        |   ├─ index.php
        |   └─ version.php
        ├─ lang
        ├─ lib
        |   ├─ EventHandler.php
        |   └─ Service.php
        ├─ include.php
        └─ options.php
*/
```

### options.php

Отвечает за отображение страницы настроек в административной части и установку параметров модуля. Содержит все вкладки и
параметры, которые можно использовать в процессе реализации логики работы модуля.

Параметры добавляются в массив `$aTabs`, обрабатываются и устанавливаются по нажатию кнопки формы. Параметр создаётся в
формате:

```php
[
    "parameter_id",
    "parameter_name",
    "default_value",
    [
        "parameter_type", // text, password, textarea, checkbox, selectbox, multiselectbox
        "additional_values", 
        /* Передаётся массив типа [ключ => значение] для selectbox и multiselectbox,
        или количество строк и столбцов для textarea */
    ],
],
```

Чтобы получить параметр, используется метод:

```php
Option::get("module_id", "parameter_id")
```

### include.php

Файл, подключаемый при вызове модуля в коде, например, в консольном скрипте. Можно оставить пустым.

### index.php

Необходим для установки модуля, содержит обязательные константы: 

```php
var $MODULE_ID = "base.module";
var $MODULE_VERSION;
var $MODULE_VERSION_DATE;
var $MODULE_NAME;
var $MODULE_DESCRIPTION;
var $PARTNER_NAME;
var $PARTNER_URI;
```

Установка и удаление модуля происходят в теле методов `DoInstall` и `DoUnInstall` соответственно. Помимо того, могут быть добавлены методы для установки компонентов и публичных страниц, создания таблиц в базе данных, пользовательских групп, почтовых шаблонов и событий: 

```php
public function DoInstall()
    {
        global $APPLICATION;

        if ($this->isVersionD7()) {
            ModuleManager::registerModule($this->MODULE_ID);

            $this->installFiles();
            $this->installDB();
            $this->installEvents();
            $this->installOptions();
        } else {
            $APPLICATION->ThrowException(Loc::getMessage("BASE_MODULE_INSTALL_ERROR_VERSION"));
        }
    }

public function DoUninstall()
    {
        $this->unInstallFiles();
        $this->unInstallDB();
        $this->unInstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);
    }
```

### version.php

Содержит информацию о текущей версии модуля в виде: 

```php
$arModuleVersion = [
    "VERSION" => "1.0.0",
    "VERSION_DATE" => "2023-04-14 13:06:00"
];
```

### console.php

Пример консольного скрипта, использующего функционал модуля. Подключает пролог и модуль, после чего проверяет значение параметра и вызывает метод сервиса: 

```php
if (Option::get(MODULE_NAME, "CHECKBOX") == "Y") {
    $service = new Service();
    $service->foo();
}
```

### Service.php

Пример обработчика событий.

### Service.php

Пример сервиса, используемого модулем.