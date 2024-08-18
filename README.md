<h1>Управления параметрами модуля</h1>
<h2>Установка</h2>

``
composer require sholokhov/bitrix-option
``

<h2>Требования</h2>
<li>PHP 8.2 и выше</li>
<li>Bitrix 12.0.7 и выше</li>

<h2>Описание</h2>
Позволяет взаимодействовать с параметрами модуля посредством DTO.

Производит взаимодействие с таблице b_option_site

<h2>Инициализация и настройка</h2>
<h3>Инициализация менеджера параметров</h3>

```injectablephp
use Sholokhov\BitrixOption\Manager;

$manager = new Manager($configuration);
```

<h3>Конфигурация</h3>
Для конфигурации необходимо указать следующие значения:
<li>Идентификатор модуля - <b>обязательный</b></li>
<li>Наименование параметра - <b>обязательный</b></li>
<li>Идентификатор сайта</li>
<li>Хранилище(DTO) - <b>обязательный</b></li>

Если один из обязательных параметров будет не указан или иметь неверный формат, то будет вызвано исключение

```injectablephp
$configuration = [
    'module' => 'my_module',
    'name' => 'connection',
    'siteID' => 's1',
    'storage' => $object
];
```

<h3>Хранилище</h3>
Хренилище обязано релизовывать интерфейс
``
Sholokhov\BitrixOption\StorageInterface
``

<h4>Пример структуры хранилища</h4>

```injectablephp
use Sholokhov\BitrixOption\StorageInterface;

class ConnectionDTO implements StorageInterface
{
    public int $port;
    public string $host;
    public string $login;
    
    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return json_encode([
            'port' => $this->port,
            'host' => $this->host, 
            'login' => $this->login
        ]);
    }

    public static function fromString(string $value): self
    {
        $data = json_decode($value, JSON_OBJECT_AS_ARRAY);

        $connection = new self();
        $connection->port = intval($data['port'] ?? 22);
        $connection->host = (string)($data['host'] ?? '');
        $connection->login = (string)($data['login'] ?? '');
        
        return $connection;
    }
}
```

<h3>Получение параметров модуля</h3>

```injectablephp
use Sholokhov\BitrixOption\Manager;

$configuration = [
    'module' => 'sms.sender',
    'name' => 'connection',
    'siteID' => 's5',
    'storage' => ConnectionDTO::class
];

$manager = new Manager($configuration);
$connection = $manager->get();
```

<h3>Сохранение параметра</h3>

Метод сохранения всегда возвращает объект ``Bitrix\Main\Result``

Если в момент сохранения возникло исключение, то у результата метод ```$result->isSuccess()``` вернет ложь, и будет возможность получения текста ошибки.

```injectablephp
$result = $manager->save();

if (!$result->isSuccess()) {
    your code ...
}
```

<h3>Синхронизация параметра</h3>
При необходимости актуализации значения параметра можно воспользоваться методом

```injectablephp
$storage = $manager->refresh();
```

<b>ВНИМАНИЕ</b>

Если мы имели несохраненные состояния значения, то наши данные будут потеряны. Результатом загрузки будет новый объект - связь с старывм ресурсом(объектом) будет разорвана.

```injectablephp
$manager = new Manager($configuration);
$storage = $manager->get();

$storage->host = 'localhost';
$manager->refresh();

// Сохранится пустое значение, т.к. связь с  $storage разорвана
$manager->save();
```

<h3>Инициализация на основе объекта</h3>
Возможно инициализировать менеджера конфигураций на основе объекта. Объект должен описывать трибут ``Sholokhov\BitrixOption\Attributes\Option``

<h4>Пример описания атрибута</h4>

```injectablephp
use Sholokhov\BitrixOption\Attributes\Option;

#[Option(module: 'sms.sender', name: 'connection', storage: ConnectionDTO::class)]
class Connection
{
    ...
}
```

<h4>Инициализация объекта</h4>

```injectablephp
use Sholokhov\BitrixOption\AttributeManager;

// Способ 1
$manager = new AttributeManager(Connection::class);
$storage = $manager->get();

// Способ 2
$manager = new AttributeManager(new Connection(), 's1');
$storage = $manager->get();
```

<h3>Сборщик конфигураций</h3>
Является оберткой над простой инициализацией объекта. 
Служит, для упрощения кода и абстрагирование от логики инициализации объекта

<h4>Инициализация на основе конфигуратора в виде массива</h4> 

```injectablephp
use Sholokhov\BitrixOption\Manager;
use Sholokhov\BitrixOption\Builder\Loader;

$config = [
    'module' => 'sms.sender',
    'name' => 'connection',
    'siteID' => 's5',
    'storage' => ConnectionDTO::class
];

$manager = new Manager($configuration);

// Аналог
$manager = Loader::load($config);
```

<h4>Инициализация на основе объекта</h4>

```injectablephp
use Sholokhov\BitrixOption\AttributeManager;
use Sholokhov\BitrixOption\Builder\Loader;

$manager = new AttributeManager(Connection::class);

// Аналог
$manager = Loader::loadByEntity(Connection::class);

````