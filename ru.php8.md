[📚 Содержание](README.ru.md)

- [🐘 PHP 8 — Advanced Topics for Mid+/Senior Developers](#-php-8--advanced-topics-for-midsenior-developers)
  - [⚙️ Интерпретатор и JIT в PHP 8](#-интерпретатор-и-jit-в-php-8)
  - [🌐 WebSocket и асинхронность](#-websocket-и-асинхронность)
  - [🧱 Объектно-Ориентированное Программирование (ООП) в PHP](#-объектно-ориентированное-программирование-ооп-в-php)
  - [🧭 `self::` vs `static::` — позднее статическое связывание](#-self-vs-static--позднее-статическое-связывание)
  - [🔐 Модификаторы видимости](#-модификаторы-видимости)
  - [🧬 Ковариантность и контравариантность](#-ковариантность-и-контравариантность)
  - [🧠 WeakMap / WeakReference](#-weakmap--weakreference)
  - [✨ Магические методы в PHP 8](#-магические-методы-в-php-8)
  - [🧩 Атрибуты в PHP 8.0+](#-атрибуты-в-php-80)
  - [🚫 strict_types — строгость типов](#-strict_types--строгость-типов)
  - [🔁 Генераторы и `yield` в PHP](#-генераторы-и-yield-в-php)
  - [🧵 Fibers в PHP 8.1+ — что это и зачем](#-fibers-в-php-81--что-это-и-зачем)
  - [📚 Встроенные интерфейсы и классы PHP](#-встроенные-интерфейсы-и-классы-php)
  - [🔢 Перечисления (Enums) в PHP 8.1+](#-перечисления-enums-в-php-81)
  - [📐 PSR — PHP Standards Recommendations](#-psr--php-standards-recommendations)
  - [📦 Composer — Менеджер зависимостей для PHP](#-composer--менеджер-зависимостей-для-php)
  - [🚀 Оптимизация производительности: nginx + PHP-FPM](#-оптимизация-производительности-nginx--php-fpm)
  - [❓ Типичные вопросы на собеседованиях](#-типичные-вопросы-на-собеседованиях)

---

# 🐘 PHP 8 — Advanced Topics for Mid+/Senior Developers
📎 [Официальная документация на php.net](https://www.php.net/manual/ru/index.php)

Этот справочник — для тех, кто уже прошёл через основы PHP и хочет копнуть глубже. Практичные примеры, немного иронии и никаких «водянистых» объяснений. Погнали! 🚀

---

## ⚙️ Интерпретатор и JIT в PHP 8

PHP — интерпретируемый язык, но с OPcache, а с PHP 8 он получил JIT (Just-In-Time Compilation). Это не сделает ваш громоздкий Laravel космическим кораблём, но для численных задач — находка. Разберём, как это работает и где подвох.


### 📖 Интерпретатор vs Компилятор

|                          | Интерпретатор (PHP)       | Компилятор (C/Go/Rust)           |
|--------------------------|---------------------------|----------------------------------|
| Исполнение               | Построчно, во время запуска | Предварительная трансляция в машинный код |
| Скорость запуска         | Мгновенный старт           | Требует сборки                   |
| Производительность       | Ниже без оптимизации       | Выше благодаря машинному коду   |
| Отладка и разработка     | Быстрее: внес → сохранил → запустил | Медленнее: надо пересобрать    |

**PHP** исполняется через **интерпретатор Zend**, но может использовать кеширование (OPcache) и даже компиляцию JIT.


### ✅ OPcache

- **Интерпретатор Zend**: Парсит PHP-код в байт-код, который исполняется Zend Engine.
- **OPcache**: Кэширует байт-код, чтобы не компилировать скрипт заново при каждом запросе.

**Пример конфигурации:**

```ini
; php.ini
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

#### ⚙️ Подробности конфигурации OPcache

```ini
; php.ini

opcache.enable=1
```
✅ Включает OPcache.

```ini
opcache.enable_cli=1
```
✅ Включает OPcache для CLI (например, при запуске скриптов вручную или через cron).

```ini
opcache.memory_consumption=128
```
📦 Объём памяти (в мегабайтах), выделенный под кеш байткода.  
Обычно: 64–128 MB для небольших проектов, 256–512 MB для крупных.

```ini
opcache.interned_strings_buffer=16
```
🧠 Объём памяти (в MB), выделенный на кэш строк (строки переиспользуются, уменьшая расход памяти).

```ini
opcache.max_accelerated_files=10000
```
📂 Максимальное количество файлов, кэшируемых OPcache.  
Для CMS/фреймворков рекомендовано: 8000–20000.

```ini
opcache.revalidate_freq=60
```
⏱ Частота проверки времени модификации файлов (в секундах).  
0 = проверять при каждом запросе (подходит для разработки),  
60 = раз в минуту (рекомендуется для production).

### 🧹 Как очистить OPcache вручную

Иногда скрипты не обновляются автоматически, даже после изменения кода. Причины:

- OPcache не проверяет дату модификации (`validate_timestamps=0`)
- Кэш превышает лимит `max_accelerated_files`
- OPcache не включён для CLI

#### 🔧 Способы очистки

1. **Программно:**

```php
opcache_reset();
```

2. **CLI команда:**

```bash
php -r "opcache_reset();"
```

3. **Перезапуск PHP-FPM или веб-сервера:**

```bash
sudo systemctl restart php8.2-fpm
```

или для Apache:

```bash
sudo systemctl restart apache2
```


### ⚙️ JIT (Just-in-time compilation) в PHP 8

JIT — это компилятор, который **выполняет часть кода в нативной форме**, а не как байт-код интерпретатора.

```ini
opcache.jit=1255
opcache.jit_buffer_size=100M
```

### Когда работает JIT?

JIT вступает в игру **при интенсивных вычислениях**:

- Математические функции
- Обработка больших массивов
- Алгоритмы (например, поиск, графы)

**Пример:**

```php
$iterations = 1000000;
function compute($n) {
    $sum = 0;
    for ($i = 0; $i < $n; $i++) {
        $sum += sin($i);
    }
    return $sum;
}
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    compute(100);
}
echo "JIT: " . (microtime(true) - $start) . " seconds\n";
```
**Результаты** (ориентировочно):
- Без JIT: ~0.25 сек
- С JIT: ~0.15 сек

### Лайфхаки
- Включайте `opcache.validate_timestamps=0` в продакшене для стабильности.
- Используйте JIT для обработки массивов или ML-задач.
- Мониторьте память OPcache через `opcache_get_status()`.


---

## 🌐 WebSocket и асинхронность

### Почему PHP плохо подходит для WebSocket?

- Каждый запрос — **отдельный процесс** (FPM).
- Нет нативного event loop или реактивной модели.
- Постоянное соединение = держать процесс → дорого.


### 🧩 Как это решают?

#### 🛠 Swoole — это **расширение PHP**
добавляющее поддержку:

- Event loop (asynchronous I/O)
- WebSocket server
- Coroutine-подобное поведение

**Пример WebSocket-сервера на Swoole:**

```php
use Swoole\WebSocket\Server;
  
$server = new Server("0.0.0.0", 9502);
  
$server->on("open", function ($server, $request) {
   error_log("Connection opened: {$request->fd}");
});
  
$server->on("message", function ($server, $frame) {
   error_log("Message: {$frame->data}");
   $server->push($frame->fd, "Ответ: {$frame->data}");
});
  
$server->start();
```

📦 Установка: `pecl install swoole`

### ⚖️ Моё мнение

> PHP — отличный инструмент для **обработки HTTP-запросов, шаблонов, API, CLI-утилит**.  
> Но **WebSocket и постоянные соединения** лучше реализовывать на языках, которые созданы для этого.



### ✅ Go для WebSocket

Go идеально подходит для многопоточной обработки и сетевого взаимодействия.

**Пример WebSocket-сервера на Go:**

```go
import (
    "net/http"
    "github.com/gorilla/websocket"
)

var upgrader = websocket.Upgrader{}

func handler(w http.ResponseWriter, r *http.Request) {
    conn, _ := upgrader.Upgrade(w, r, nil)
    for {
        _, msg, _ := conn.ReadMessage()
        conn.WriteMessage(websocket.TextMessage, msg)
    }
}

func main() {
    http.HandleFunc("/ws", handler)
    http.ListenAndServe(":8080", nil)
}
```

### Подводные камни
- **Ресурсы**: Swoole-сервер может съесть память, если не настроить пул воркеров.
  ```ini
  [swoole]
  swoole.worker_num = 4
  ```
- **Отладка**: Swoole сложен в дебаге. Логируйте всё!
  ```php
  ini_set('swoole.log_file', '/var/log/swoole.log');
  ```
- **Совместимость**: Laravel требует Laravel Octane для интеграции с Swoole.
- **Go vs PHP**: Go проще в поддержке для WebSocket, но требует изучения.

### Производительность
Swoole быстрее ReactPHP за счёт нативной реализации. Тест (1000 сообщений):
```php
$start = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    // Имитация отправки
}
echo "Swoole: " . (microtime(true) - $start) . " seconds\n";
```
**Результаты**: ~0.01 сек (Swoole) vs ~0.03 сек (ReactPHP).

### Лайфхаки
- Используйте Swoole с Laravel Octane для асинхронных API.
- Для WebSocket-серверов попробуйте Go

### Советы для собеседований
- **Вопросы**:
  - Почему PHP не лучший выбор для WebSocket?
  - Как Swoole решает проблему асинхронности?
  - Когда выбрать Go вместо PHP?
- **Подготовка**: Реализуйте WebSocket-сервер на Swoole и объясните его архитектуру.

---

## 🧱 Объектно-Ориентированное Программирование (ООП) в PHP

ООП — основа масштабируемых приложений. Ниже подробно описаны ключевые концепции ООП в PHP.

---

### 📦 Классы, Интерфейсы, Абстрактные классы

### ▶️ Классы

Класс — шаблон для создания объектов. В нём описываются свойства (переменные) и методы (функции):

```php
class Car {
    public string $brand;

    public function drive() {
        echo "Driving";
    }
}
```


### 📄 Интерфейсы (`interface`)

Интерфейс определяет **контракт** — набор методов, которые класс **обязан реализовать**.

```php
interface Engine {
    public function start();
}
```

Интерфейс:
- ❗ Не содержит реализации, только сигнатуры методов.
- ❌ Не может содержать свойства (до PHP 8.1).
- ✅ Можно реализовать **несколько интерфейсов одновременно**.

```php
class Car implements Engine, JsonSerializable {
    public function start() { ... }

    public function jsonSerialize(): mixed {
        return ['type' => 'car'];
    }
}
```


### 🧱 Абстрактные классы (`abstract`)

Абстрактный класс может:
- Содержать как **реализованные**, так и **абстрактные методы**.
- Иметь **свойства** и **конструктор**.
- Быть использован как **базовый класс**, от которого наследуются конкретные реализации.

```php
abstract class Vehicle {
    public function honk() {
        echo "Beep!";
    }

    abstract public function move();
}

class Bike extends Vehicle {
    public function move() {
        echo "Pedaling...";
    }
}
```

### 🧬 Наследование

#### Ключевые принципы:

- Класс может **наследовать только от одного другого класса** (single inheritance).
- Но может **реализовать любое количество интерфейсов**.
- Используй `parent::method()` чтобы вызвать родительскую реализацию.

```php
class A {
    public function say() {
        echo "Hello from A";
    }
}

class B extends A {
    public function say() {
        parent::say();
        echo " and B";
    }
}
```


### 🚫 Ограничения и модификаторы

- `final class` — нельзя наследовать.
- `final function` — нельзя переопределить в потомках.
- `abstract function` — метод без тела, должен быть реализован в дочерних классах.
- `private` — доступен только внутри самого класса.
- `protected` — доступен в классе и его потомках.
- `public` — доступен везде.

---

### 🛠 Как обойти ограничение на множественное наследование?

PHP не поддерживает множественное наследование классов напрямую, но можно использовать **трейты**:

```php
trait Logger {
    public function log($msg) {
        echo "[log] $msg";
    }
}

class Service {
    use Logger;
}
```

Трейты позволяют повторно использовать код в нескольких независимых классах.


### 🔑 Когда что использовать?

| Тип           | Когда применять                                 |
|---------------|--------------------------------------------------|
| `interface`   | Когда нужно определить контракт для реализации. |
| `abstract`    | Когда есть общая логика + абстрактные методы.   |
| `trait`       | Когда нужно переиспользовать код между классами.|
| `class`       | Конкретная реализация.                          |


Понимание этих конструкций — основа грамотной архитектуры.

### Советы для собеседований
- **Вопросы**:
  - Разница между интерфейсом и абстрактным классом.
  - Как обойти отсутствие множественного наследования?
- **Подготовка**: Реализуйте DI-контейнер с интерфейсами и трейтами.

---

## 🧭 `self::` vs `static::` — позднее статическое связывание

### `self::` — ссылается на класс, где метод определён.

```php
class A {
    public static function who() {
        echo "A";
    }

    public static function call() {
        self::who();
    }
}

class B extends A {
    public static function who() {
        echo "B";
    }
}

B::call(); // A — self:: привязан к A
```

### `static::` — ссылается на вызывающий класс (позднее статическое связывание)

```php
class A {
    public static function who() {
        echo "A";
    }

    public static function call() {
        static::who();
    }
}

class B extends A {
    public static function who() {
        echo "B";
    }
}

B::call(); // B — static:: ссылается на вызвавший класс
```

### Подводные камни
- **Ошибки с `self::`**: Ломает полиморфизм в наследовании.
- **Производительность**: `static::` чуть медленнее из-за динамики.
  ```php
  $iterations = 1000000;
  $start = microtime(true);
  for ($i = 0; $i < $iterations; $i++) {
      A::call(); // self::
  }
  echo "self:: " . (microtime(true) - $start) . " seconds\n";
  ```
  **Результаты**: ~0.03 сек vs ~0.04 сек для `static::`.

### Лайфхаки
- Используйте `static::` для фабричных методов:
  ```php
  class Model {
      public static function create() { return new static(); }
  }
  ```

### Советы для собеседований
- **Вопросы**:
  - Чем `self::` отличается от `static::`?
  - Когда нужно позднее связывание?
- **Подготовка**: Реализуйте фабрику с `static::`.

---

## 🔐 Модификаторы видимости

| Модификатор | Доступен внутри | Доступен в наследниках | Доступен снаружи |
|-------------|------------------|-------------------------|------------------|
| `public`    | ✅               | ✅                      | ✅               |
| `protected` | ✅               | ✅                      | ❌               |
| `private`   | ✅               | ❌                      | ❌               |

### Пример:

```php
class Test {
    public $a = 'public';
    protected $b = 'protected';
    private $c = 'private';

    public function show() {
        echo "$this->a, $this->b, $this->c";
    }
}
```

### Подводные камни
- **Наследование**: `private` свойства недоступны в потомках.
- **Магические методы**: `__get`/`__set` могут обойти модификаторы.
  ```php
  class Trap {
      private $data;
      public function __get($name) { return $this->data; }
  }
  $obj = new Trap();
  echo $obj->data; // Обходит private
  ```

### Лайфхаки
- Делайте свойства `private` по умолчанию — инкапсуляция рулит!
- Используйте `protected` только для продуманного наследования.

### Советы для собеседований
- **Вопросы**:
  - Разница между `protected` и `private`.
  - Как магические методы влияют на модификаторы?
- **Подготовка**: Объясните, почему `private` предпочтительнее.

---

## 🧬 Ковариантность и контравариантность

### Ковариантность — возвращаемый тип может быть более конкретным

```php
class A {}
class B extends A {}

class ParentClass {
    public function get(): A {}
}

class ChildClass extends ParentClass {
    public function get(): B {} // допустимо
}
```

### Контравариантность — тип аргумента может быть более общим.
  ```php
  class ParentClass {
      public function set(A $obj) {}
  }
  
  class ChildClass extends ParentClass {
      public function set(object $obj) {} // OK
  }
  ```

### Подводные камни
- **Ограничения**: PHP не поддерживает контравариантность возвращаемых типов.
  ```php
  class ChildClass extends ParentClass {
      public function get(): object {} // Fatal error
  }
  ```
- **Ошибки**: Нарушение ковариантности ломает контракт.

### Лайфхаки
- Используйте ковариантность для возврата DTO в API.
- Проверяйте типы с `instanceof` перед передачей.

### Советы для собеседований
- **Вопросы**:
  - Что такое ковариантность? Пример.
  - Почему контравариантность аргументов полезна?
- **Подготовка**: Реализуйте интерфейс с ковариантным возвратом.

---

## 🧠 WeakMap / WeakReference

Позволяют ссылаться на объекты без удержания их в памяти:

### WeakReference

```php
  $obj = new stdClass();
  $ref = WeakReference::create($obj);
  unset($obj);
  var_dump($ref->get()); // NULL
  ```

### WeakMap

  ```php
  $map = new WeakMap();
  $obj = new stdClass();
  $map[$obj] = 'data';
  unset($obj);
  var_dump(count($map)); // 0
  ```

### Подводные камни
- **Ограничения**: `WeakReference` не работает с примитивами.
- **Производительность**: `WeakMap` медленнее массива.
  ```php
  $iterations = 1000000;
  $start = microtime(true);
  $map = new WeakMap();
  for ($i = 0; $i < $iterations; $i++) {
      $obj = new stdClass();
      $map[$obj] = $i;
  }
  echo "WeakMap: " . (microtime(true) - $start) . " seconds\n";
  ```
  **Результаты**: ~0.3 сек vs ~0.1 сек для массива.

### Лайфхаки
- Используйте `WeakMap` для кэширования в DI-контейнерах.
- Проверяйте `WeakReference->get()` на `null`.

### Советы для собеседований
- **Вопросы**:
  - Чем `WeakMap` отличается от массива?
  - Когда использовать `WeakReference`?
- **Подготовка**: Реализуйте кэш с `WeakMap`.

---

## ✨ Магические методы в PHP 8

Магические методы — как заклинания: мощные, но если переборщить, код превратится в тыкву. 🪄 Используйте с осторожностью!


### 🔨 `__construct()`

Вызывается при создании объекта.

```php
class User {
    public function __construct(public string $name) {
        echo "Created user: $name";
    }
}
```

### 🧹 `__destruct()`

Вызывается при удалении объекта (в конце скрипта или при unset).

```php
public function __destruct() {
    echo "Destroying user";
}
```

### 🧠 `__get($name)` и `__set($name, $value)`

Обрабатывают доступ к несуществующим/приватным свойствам.

```php
public function __get($name) {
    return $this->data[$name] ?? null;
}

public function __set($name, $value) {
    $this->data[$name] = $value;
}
```

### 🔍 `__isset($name)` и `__unset($name)`

Реакция на `isset($obj->prop)` и `unset($obj->prop)`.

```php
public function __isset($name) {
    return isset($this->data[$name]);
}
```

### ☎️ `__call($name, $arguments)`

Вызывается при обращении к несуществующему методу.

```php
public function __call($name, $arguments) {
    echo "Метод $name не существует";
}
```

### ☎️ `__callStatic($name, $arguments)`

Аналог `__call`, но для статических методов.

```php
public static function __callStatic($name, $arguments) {
    echo "Static $name called";
}
```

### 🧬 `__clone()`

Вызывается при клонировании объекта через `clone`.

```php
public function __clone() {
    $this->id = null;
}
```

### 🧾 `__toString()`

Позволяет задать строковое представление объекта.

```php
public function __toString(): string {
    return $this->name;
}
```

### 📞 `__invoke(...)`

Позволяет вызывать объект как функцию.

```php
public function __invoke($x) {
    return $x * 2;
}

$obj = new MyClass();
echo $obj(5); // 10
```
### 🧭 `__debugInfo()`

Определяет, какие данные показываются в `var_dump()`.

```php
public function __debugInfo(): array {
    return ['hidden' => true];
}
```

### 📦 `__serialize()` и `__unserialize()`

Появились в PHP 7.4. Управляют сериализацией объекта.

```php
public function __serialize(): array {
    return ['name' => $this->name];
}

public function __unserialize(array $data): void {
    $this->name = $data['name'];
}
```

### Подводные камни
- **Производительность**: Магические методы медленнее прямого доступа.
  ```php
  $iterations = 1000000;
  $start = microtime(true);
  $obj = new Magic();
  for ($i = 0; $i < $iterations; $i++) {
      $x = $obj->prop;
  }
  echo "Magic: " . (microtime(true) - $start) . " seconds\n";
  ```
  **Результаты**: ~0.06 сек vs ~0.02 сек для свойства.


- **Безопасность**: `__unserialize` может быть уязвимым.
  ```php
  class Vulnerable {
      public function __unserialize($data) {
          eval($data['code']); // Опасно!
      }
  }
  ```

### Лайфхаки
- Используйте `__invoke` для функциональных объектов.
- Ограничивайте `__get`/`__set` для читаемости, используйте их для **прокси-объектов**, не как замену обычным свойствам.
- `__call/__callStatic` удобны для реализации **ленивой загрузки** или **обёрток**.

### Советы для собеседований
- **Вопросы**:
  - Как работает `__invoke`? Пример.
  - Почему `__get`/`__set` замедляют код?
- **Подготовка**: Реализуйте прокси-объект с `__call`.

---

## 🧩 Атрибуты в PHP 8.0+

Атрибуты — как стикеры на код: добавляют метаданные, которые фреймворки читают через рефлексию. Прощай, PHPDoc!

### Ключевые моменты
- **Синтаксис**:
  ```php
  #[Route('/user/{id}', methods: ['GET'])]
  class UserController {
      public function show(int $id) {
          return "User $id";
      }
  }
  ```
- **Рефлексия**:
  ```php
  $ref = new ReflectionMethod(UserController::class, 'show');
  $attrs = $ref->getAttributes(Route::class);
  var_dump($attrs[0]->newInstance()->path); // /user/{id}
  ```
- **Кастомные атрибуты**:
  ```php
  #[Attribute]
  class Route {
      public function __construct(public string $path, public array $methods = []) {}
  }
  ```

### Подводные камни
- **Ограничения**: Вложенные атрибуты не поддерживаются.
  ```php
  #[Route('/test', #[HttpMethod('GET')])] // Syntax error
  ```
- **Производительность**: Рефлексия медленнее PHPDoc.
- **Совместимость**: Требуется PHP 8.0+.

### Лайфхаки
- Не используйте рефлексию! Нет магии, нет рефлексии — нет проблем. 

### Советы для собеседований
- **Вопросы**:
  - Чем атрибуты лучше PHPDoc?
  - Как создать кастомный атрибут?
- **Подготовка**: Реализуйте маршрутизатор с атрибутами.

---

## 🚫 strict_types — строгость типов

В PHP по умолчанию разрешено **автоматическое приведение типов** (type juggling), что может привести к неожиданным результатам и ошибкам, которые сложно отследить.

### ✅ Включение строгой типизации

```php
<?php
declare(strict_types=1);
```

🔺 Это объявление должно быть **первой строкой файла**, до любого вывода или кода.



### ⚖️ Пример: сравнение поведения

#### 📌 Без `strict_types`

```php
function sum(int $a, int $b): int {
    return $a + $b;
}

echo sum(2, 3.5); // 5 — float приводится к int
```

#### ✅ С `strict_types=1`

```php
declare(strict_types=1);

function sum(int $a, int $b): int {
    return $a + $b;
}

echo sum(2, 3.5); // ❌ Fatal error: Argument must be of type int
```


### ❗ Проблемы при отключённой типизации

- Тихое приведение float → int, string → int и др.
- Логические ошибки, которые трудно отследить.
- Поведение зависит от данных, а не от кода.

### ✅ Почему стоит **всегда использовать** `strict_types`

- 🛡️ Защита от скрытых ошибок.
- 🔎 Прозрачность и предсказуемость типов.
- 🧪 Более корректное поведение при написании юнит-тестов.
- 🔄 Совместимость с современными стандартами и анализаторами кода (`PHPStan`, `Psalm`).

### 🔁 Рекомендуемый подход

- **Всегда включай `strict_types=1`** в каждом PHP-файле (особенно в библиотеке или API).
- Используй типы параметров и возвращаемых значений (`int`, `string`, `array`, `bool`, и т.д.).
- В связке с строгой типизацией применяй автотесты и статический анализ.

---

## 🔁 Генераторы и `yield` в PHP

Генераторы позволяют реализовать **ленивую итерацию** — обработку данных **по одному элементу за раз**, **без необходимости загружать всё в память**. Это особенно полезно при работе с большими объёмами данных.


### 🧱 Базовый пример генератора

```php
function numbers() {
    yield 1;
    yield 2;
    yield 3;
}

foreach (numbers() as $num) {
    echo $num . PHP_EOL;
}
```

### ⚙️ Как работает `yield`?

- `yield` возвращает значение, приостанавливает выполнение и запоминает текущее состояние.
- Следующий вызов итерации (`foreach`) продолжит выполнение с этой точки.


### ✅ Преимущества генераторов

- 🧠 Простота синтаксиса (не нужно реализовывать интерфейс `Iterator`).
- 🧵 Производительность (обработка "на лету").
- 🧼 Экономия памяти (особенно при больших выборках/файлах).



### 📚 Примеры реального применения

#### 📄 Чтение файла построчно

```php
function readFileLines(string $filename): Generator {
    $handle = fopen($filename, 'r');
    if (!$handle) {
        throw new RuntimeException("Cannot open file");
    }

    while (($line = fgets($handle)) !== false) {
        yield rtrim($line, "
");
    }

    fclose($handle);
}

foreach (readFileLines('data.txt') as $line) {
    echo $line . PHP_EOL;
}
```

✅ Альтернатива `file()` — не загружает весь файл в память.

#### 🗃 Обработка SQL-запросов по частям

```php
function fetchRows(PDO $pdo): Generator {
    $stmt = $pdo->query("SELECT * FROM users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        yield $row;
    }
}
```

✅ Позволяет обрабатывать **большую выборку из базы**, не загружая её целиком.



### 🧪 Бесконечная последовательность

```php
function counter(int $start = 0): Generator {
    while (true) {
        yield $start++;
    }
}

foreach (counter() as $n) {
    if ($n > 5) break;
    echo $n . PHP_EOL;
}
```

### 🔁 `yield key => value`

```php
function users() {
    yield 1 => 'Alice';
    yield 2 => 'Bob';
}

foreach (users() as $id => $name) {
    echo "$id: $name" . PHP_EOL;
}
```

### 📍 `return` в генераторе

Начиная с PHP 7, генератор может возвращать значение через `return`, доступное через `$gen->getReturn()`:

```php
function sum() {
    yield 1;
    yield 2;
    return 3;
}

$gen = sum();
foreach ($gen as $value) {
    echo $value . PHP_EOL;
}
echo "Return: " . $gen->getReturn();
```


### 📌 Когда использовать генераторы?

- Обработка **больших данных** (файлы, SQL).
- Потоковая обработка (stream).
- Когда важна **экономия памяти**.
- При **постепенной генерации значений** (например, дерево, граф).

### Лайфхаки
- Используйте генераторы для файлов и SQL-выборок.
- Проверяйте `Generator::valid()` для сложных итераций.

### Советы для собеседований
- **Вопросы**:
  - Как работает `yield`?
  - Когда генераторы экономят память?
- **Подготовка**: Реализуйте генератор для чтения файла.

---


## 🧵 Fibers в PHP 8.1+ — что это и зачем

**Fibers** — это корутины, которые позволяют приостанавливать и возобновлять выполнение кода, не блокируя основной поток. Их можно воспринимать как «ручные потоки» без настоящей многозадачности.

> 📌 Главное: Fibers позволяют писать асинхронный код в синхронном стиле.

### Зачем нужны Fibers?

- Упрощают написание неблокирующего кода.
- Позволяют реализовать «паузы» в выполнении (например, ждать ответа из сети).
- Идеальны для фреймворков и библиотек, где важен контроль потока (например, асинхронные I/O, очереди).


### 🔧 Минимальный пример

```php
$fiber = new Fiber(function (): void {
    $value = Fiber::suspend('Жду продолжения...');
    echo "Продолжение получено: $value\n";
});

echo $fiber->start(); // Выведет: Жду продолжения...
$fiber->resume('Привет из resume!'); // Выведет: Продолжение получено: Привет из resume!
```


### 🛠 Пример: имитация неблокирующего запроса

```php
function fetchData(): Fiber {
    return new Fiber(function() {
        echo "Запрос к API...\n";
        Fiber::suspend(); // Имитация ожидания ответа
        echo "Ответ получен!\n";
    });
}

$fiber = fetchData();
$fiber->start();
// ...пока ждём, можно делать другие дела
echo "Не блокируем поток!\n";
$fiber->resume(); // Возобновляем работу
```


### 🔍 Fibers vs Генераторы vs Потоки

|                         | **Fibers**                        | **Генераторы (Generators)**          | **Потоки (Threads)**                   |
|-------------------------|-----------------------------------|--------------------------------------|----------------------------------------|
| **Когда появились**     | PHP 8.1                           | PHP 5.5                               | Расширения, напр. `pthreads`, `parallel` |
| **Что делают**          | Приостанавливают и возобновляют выполнение кода вручную | Итерации с "пауза-возобновить" через `yield` | Параллельное выполнение кода          |
| **Параллельность**      | ❌ Нет                             | ❌ Нет                                | ✅ Да (многопоточность)                |
| **Контроль исполнения** | ✅ Полный (`start`, `resume`, `suspend`) | ⚠️ Частичный (только `yield`)         | 🔄 Автоматический (планировщик ОС)     |
| **Использование памяти**| ⚡ Маленький стек, быстрые переключения | ⚡ Очень экономичные                 | 💰 Зависит от реализации               |
| **Типичная задача**     | Асинхронная логика, обработка I/O | Простой генератор данных             | Загрузка и обработка в фоне            |
| **Сложность использования** | ⚙ Средняя (ручное управление)      | 🔰 Очень простые                     | 🧠 Высокая (особенно с `pthreads`)     |


### ⚙️ Практика: асинхронные фреймворки (ReactPHP vs Swoole)

#### 🧵 Fibers в Swoole 5.x / Laravel Octane

Swoole использует Fibers для неблокирующих операций, таких как:

```php
use Swoole\Coroutine;

Coroutine\run(function () {
    $result = file_get_contents('http://example.com');
    echo $result;
});
```

Laravel Octane тоже использует Fibers для управления состоянием приложения без полной перезагрузки между запросами.


#### ⚙️ Генераторы в ReactPHP

До появления Fibers ReactPHP использовал Generators:

```php
function requestAsync() {
    yield $httpClient->get('http://example.com');
}
```

➡ Fibers теперь заменяют такую логику, делая код более линейным и читаемым.


### Лайфхаки
- Используйте Fibers с Swoole для HTTP-запросов.
- Логируйте состояние Fibers для отладки.

### Советы для собеседований
- **Вопросы**:
  - Чем Fibers отличаются от генераторов?
  - Пример асинхронного кода с Fibers.
- **Подготовка**: Реализуйте HTTP-запрос с Fibers и Swoole.

---

## 📚 Встроенные интерфейсы и классы PHP

PHP предоставляет ряд встроенных интерфейсов, которые можно использовать для создания более гибких, масштабируемых и "встроенно-поддерживаемых" компонентов. Они особенно активно применяются в SPL (Standard PHP Library), а также в механизмах сериализации, итерации, обработки ошибок и логики массивов.



### 🔁 Итерация и коллекции

### `Traversable`
- Базовый интерфейс для всех итерируемых объектов.
- Не реализуется напрямую — нужно `Iterator` или `IteratorAggregate`.

### `Iterator`
- Полный интерфейс итератора (как `foreach`).
- Требует реализации:  
  `current()`, `key()`, `next()`, `rewind()`, `valid()`

### `IteratorAggregate`

- Обеспечивает итерацию через метод `getIterator()`, возвращающий `Traversable`
  (используется для делегирования итерации):


```php
class Collection implements IteratorAggregate {
   private $items = [];
   public function getIterator(): Traversable {
       return new ArrayIterator($this->items);
   }
}
 ```

### `SeekableIterator`
- Расширяет `Iterator` добавлением метода `seek($position)` — переход на нужную позицию.



### 📦 Коллекции и доступ по ключу

### `ArrayAccess`
- Позволяет работать с объектом, как с массивом: `$obj[0] = 'value';`
```php
class Container implements ArrayAccess {
   private $data = [];
   public function offsetGet($offset) { return $this->data[$offset]; }
   public function offsetSet($offset, $value) { $this->data[$offset] = $value; }
   public function offsetExists($offset) { return isset($this->data[$offset]); }
   public function offsetUnset($offset) { unset($this->data[$offset]); }
}
```

### `Countable`
- Позволяет использовать `count($obj)`
- Требует реализации `count(): int`



### 💾 Сериализация и кодирование

### `Serializable`
- Кастомная сериализация:
  - `serialize()`
  - `unserialize(string $data)`
- **`Serializable`**: Устарел, используйте `__serialize`.

### `JsonSerializable`
- Используется в `json_encode()`
- Метод: `jsonSerialize(): mixed`


### 🚨 Обработка ошибок

### `Throwable`
- Базовый интерфейс для всех исключений и ошибок.
- Поддерживает `Exception` и `Error`.


- Все ошибки и исключения наследуют `Throwable`.
- Можно ловить и `Exception`, и `Error` через `catch (Throwable $e)`.
- Поддержка вложенных исключений:

```php
throw new Exception("Error", 0, $previous);
```



### `Exception`
- Стандартные исключения (классы должны наследовать `Exception`).

### `Error`
- Фатальные ошибки (наследуется от `Throwable`).



### 📚 Дополнительные интерфейсы SPL

### `OuterIterator`
- Оборачивает другой итератор. Метод: `getInnerIterator()`

### `RecursiveIterator`
- Итератор, который может содержать другие итераторы (иерархия).
- Методы: `hasChildren()`, `getChildren()`

### `RecursiveIteratorIterator`
- Обходит `RecursiveIterator` в глубину.

### `CountableIterator` *(не существует, часто путают с `Countable` + `Iterator`)*



### 🔢 ArrayAccess

Позволяет обращаться к объекту как к массиву:

```php
class Collection implements ArrayAccess {
    public function offsetExists($offset) { ... }
    public function offsetGet($offset) { ... }
    public function offsetSet($offset, $value) { ... }
    public function offsetUnset($offset) { ... }
}
```

Используется в контейнерах, DTO, настройках.


### 🔁 Serializable

Позволяет контролировать, как объект сериализуется:

```php
class User implements Serializable {
    public function serialize(): string { ... }
    public function unserialize($data): void { ... }
}
```

📌 Устаревает в пользу `__serialize()` / `__unserialize()` с PHP 7.4+


### 🔒 SensitiveParameterValue

Класс, скрывающий значение при логировании исключений.

```php
function login(string $user, SensitiveParameterValue $password) {
    throw new Exception("Invalid password");
}
```

Используется в трассировке/дебаге, чтобы скрыть чувствительные данные.


### 🔐 __PHP_Incomplete_Class

Класс, автоматически используемый PHP, когда сериализуется объект неизвестного класса.  
Встречается при `unserialize()` если класс больше не существует.



### 🧠 Closure

Анонимные функции — экземпляры класса `Closure`:

```php
$fn = function($x) { return $x * 2; };
```

Можно использовать `bindTo()` и `call()` для изменения контекста исполнения.



### 📦 stdClass

"Пустой" универсальный объект:

```php
$obj = new stdClass();
$obj->name = "test";
```

Часто используется для конверсии `array → object`.



### ⚙️ Generator

Объект, который возвращается при вызове функции с `yield`.

```php
function gen() {
    yield 1;
    yield 2;
}
```

Объекты `Generator` реализуют `Iterator`.

Смотри раздел [Генераторы и `yield` в PHP](#-генераторы-и-yield-в-php)



### 🧵 Fiber (PHP 8.1+)

Механизм для кооперативной многозадачности. Позволяет временно "приостановить" выполнение и вернуться позже:

```php
$fiber = new Fiber(function (): void {
    $value = Fiber::suspend("pause");
    echo $value;
});
$fiber->start();
```

Смотри раздел [Fibers в PHP 8.1 - что это и зачем](ru.php8.md#-fibers-в-php-81--что-это-и-зачем)



### 🧷 WeakReference

Создаёт слабую ссылку на объект:

```php
$ref = WeakReference::create($obj);
$obj = null;
$ref->get(); // null — объект уничтожен
```

Используется в кешах и GC-чувствительных структурах.



### 🗺 WeakMap

Ассоциативный массив с объектами-ключами. Когда объект уничтожается — удаляется и ключ.

```php
$map = new WeakMap();
$map[$obj] = "cached";
```



### 🧵 Stringable (PHP 8.0+)

Маркер-интерфейс. Если класс реализует `__toString()`, он автоматически `implements Stringable`.

Можно явно указать:

```php
class MyClass implements Stringable {
    public function __toString(): string { ... }
}
```



### 🔠 UnitEnum / BackedEnum (PHP 8.1+)

- `UnitEnum` — базовый интерфейс для всех enum.
- `BackedEnum` — для enum с привязанным значением (int|string):

```php
enum Status: string {
    case Active = 'active';
    case Disabled = 'disabled';
}
```

Позволяют использовать `->value`, `from()`, `tryFrom()` и `cases()`.

---

## 🔢 Перечисления (Enums) в PHP 8.1+

### Ключевые моменты
- **Простые enum**:
  ```php
  enum Status {
      case Active;
      case Inactive;
  }
  ```
- **Поддерживаемые enum**:
  ```php
  enum Role: string {
      case Admin = 'admin';
      case User = 'user';
  }
  var_dump(Role::from('admin')->value); // admin
  ```

### Подводные камни
- **Уникальность**: Значения `BackedEnum` не могут повторяться.
  ```php
  enum Invalid: string {
      case A = 'x';
      case B = 'x'; // Fatal error
  }
  ```
- **Производительность**: Enum медленнее констант.
  ```php
  class Consts {
      const ADMIN = 'admin';
  }
  $iterations = 1000000;
  $start = microtime(true);
  for ($i = 0; $i < $iterations; $i++) {
      $role = Role::Admin;
  }
  echo "Enum: " . (microtime(true) - $start) . " seconds\n";
  $start = microtime(true);
  for ($i = 0; $i < $iterations; $i++) {
      $role = Consts::ADMIN;
  }
  echo "Const: " . (microtime(true) - $start) . " seconds\n";
  ```
  **Результаты**: ~0.08 сек vs ~0.05 сек.

### Лайфхаки
- Используйте `tryFrom`:
  ```php
  $role = Role::tryFrom('guest') ?? Role::User;
  ```
- Заменяйте константы enum в БД-моделях.

### Советы для собеседований
- **Вопросы**:
  - Чем `BackedEnum` отличается от `UnitEnum`?
  - Когда enum лучше констант?
- **Подготовка**: Реализуйте модель статусов с enum.

---

## 📐 PSR — PHP Standards Recommendations

**PSR** (PHP Standards Recommendations) — это набор стандартов, разработанный группой [PHP-FIG (Framework Interop Group)](https://www.php-fig.org/), чтобы унифицировать подходы к разработке, сделать код более читаемым, предсказуемым и совместимым между фреймворками и библиотеками.

### Ключевые моменты
- **PSR-1**: Базовый стиль кода (`StudlyCaps`, `camelCase`).
- **PSR-12**: Расширенный стиль (отступы, `strict_types`).
- **PSR-3**: Логирование.
- **PSR-4**: Автозагрузка.
  ```json
  "autoload": {
      "psr-4": { "App\\": "src/" }
  }
  ```
- **PSR-7**: HTTP-сообщения.
  ```php
  use Psr\Http\Message\ResponseInterface;
  use Laminas\Diactoros\Response;
  function createResponse(): ResponseInterface {
      $response = new Response();
      $response->getBody()->write('Hello');
      return $response;
  }
  ```
- **PSR-11**: DI-контейнер.
- **PSR-13**: HATEOAS-ссылки.
- **PSR-15**: Middleware.
- **PSR-17**: HTTP-фабрики.
- **PSR-18**: HTTP-клиент.

### Подводные камни
- **Совместимость**: Не все библиотеки строго следуют PSR.
- **Сложность**: PSR-7 требует больше кода.

### Лайфхаки
- Используйте PSR-7 в API для переносимости.
- Настраивайте PSR-4 через Composer.

### Советы для собеседований
- **Вопросы**:
  - Что определяет PSR-4?
  - Как PSR-7 используется в фреймворках?
- **Подготовка**: Реализуйте middleware с PSR-15.

---

## ⚙️ SPL и автозагрузка

### `spl_autoload_register()`

Позволяет задать свою функцию автозагрузки классов (альтернатива Composer autoload):

```php
spl_autoload_register(function ($class) {
    include 'src/' . $class . '.php';
});
```

- 📌 Используется в собственных фреймворках, старых проектах или системах без Composer.
- Поддерживает стек автозагрузчиков — можно зарегистрировать несколько функций.

---

## 📦 Composer — Менеджер зависимостей для PHP

**Composer** — это стандартный инструмент для управления зависимостями и автозагрузкой в PHP. Он позволяет подключать сторонние библиотеки, управлять версиями и конфигурацией проекта.


### 🛠 Основные команды

#### 📥 Установка пакета

```bash
composer require vendor/package
```

Добавляет зависимость и сразу обновляет `composer.json` и `composer.lock`.

#### 🧹 Удаление пакета

```bash
composer remove vendor/package
```

Удаляет пакет и очищает зависимости.

#### ♻️ Обновление всех зависимостей

```bash
composer update
```

Обновляет **все зависимости** до последних допустимых версий согласно `composer.json`.

#### 🎯 Обновление одного пакета

```bash
composer update vendor/package
```


### 📁 Структура файлов

#### `composer.json`

Файл конфигурации проекта:

- Список зависимостей и их версий.
- Схема автозагрузки (PSR-4).
- Метаданные пакета (название, авторы, лицензия и т.д.)

Пример:

```json
{
  "name": "yourname/project",
  "require": {
    "monolog/monolog": "^2.0"
  },
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

#### `composer.lock`

- **Фиксирует точные версии** всех установленных зависимостей и подзависимостей.
- Используется для воспроизводимости (`CI/CD`, деплой).
- **Нужен в проекте!** Его **нужно коммитить**, если проект — приложение (а не библиотека).

📌 **Когда не коммитить `composer.lock`?**
- Когда вы разрабатываете **библиотеку**, и не хотите фиксировать зависимости для других.


### 📦 Создание своего Composer-пакета

1. Создай новый репозиторий с `composer.json`:

```bash
composer init
```

2. Пример `composer.json`:

```json
{
  "name": "vendorname/mypackage",
  "description": "Custom helper package",
  "type": "library",
  "autoload": {
    "psr-4": {
      "MyPackage\\": "src/"
    }
  },
  "require": {}
}
```

3. Структура проекта:

```
mypackage/
├── src/
│   └── Helper.php
├── composer.json
```

4. 📤 Опубликуй на GitHub и добавь версию с git-тегом:

```bash
git tag v1.0.0
git push origin v1.0.0
```

5. 📢 Зарегистрируй на [Packagist](https://packagist.org/)


### 📍 Локальное подключение своего пакета (без Packagist)

В проекте, где хочешь использовать пакет:

```json
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/vendorname/mypackage"
  }
],
"require": {
  "vendorname/mypackage": "dev-main"
}
```

### Подводные камни
- **Конфликты версий**: Разные пакеты могут требовать несовместимые версии зависимостей. Проверяйте конфликты перед обновлением.
  ```bash
  composer why-not vendor/package 1.2.3
  ```


- **Память**: Большие проекты могут исчерпать лимит памяти.
  ```php
  ini_set('memory_limit', '512M');
  ```
- **Игнорирование `composer.lock`**: Без него CI/CD может сломаться из-за неожиданных версий.
- **Локальные пакеты**: `dev-main` может быть нестабильным, используйте теги (например, `v1.0.0`).

### Лайфхаки
- Проверяйте `composer.json`:
  ```bash
  composer validate
  ```
- Ищите устаревшие зависимости:
  ```bash
  composer outdated
  ```
- Оптимизируйте автозагрузку:
  ```bash
  composer dump-autoload --optimize
  ```
- Используйте `^` для версий (`^2.0` — любые `2.x`) и `~` для патчей (`~1.2` — `1.2.*`).
> Мой совет: всегда коммитьте `composer.lock` в приложениях! Иначе ваш прод превратится в лотерею.

### Советы для собеседований
- **Вопросы**:
  - Зачем нужен `composer.lock`? Когда его не коммитить?
  - Как создать и опубликовать свой пакет?
  - Как подключить локальный пакет без Packagist?
- **Подготовка**: Опубликуйте тестовый пакет на GitHub и настройте его в другом проекте. Объясните разницу между `^` и `~`.


---

## 🚀 Оптимизация производительности: nginx + PHP-FPM

PHP-приложения вполне могут тормозить не из-за кода, а из-за плохой серверной настройки. Вот несколько советов по оптимизации инфраструктуры.


### 📌 1. nginx: что важно?

#### 🔧 Минимум лишнего

Отключите всё, что не нужно. Пример минимального конфига:

```nginx
server {
    listen 80;
    server_name example.com;

    root /var/www/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

#### ⚡ Включите кэширование статики

```nginx
location ~* \.(jpg|jpeg|png|css|js|ico|woff2?)$ {
    expires 7d;
    access_log off;
}
```

### 📌 2. PHP-FPM: настройка, профилирование

#### 🧠 Сначала: выберите режим работы

```ini
pm = dynamic   ; либо static, либо ondemand
```

- **dynamic** — хорош по умолчанию.
- **static** — максимум производительности, но фиксированное число воркеров.
- **ondemand** — экономит ресурсы, подходит для простых сайтов.

### 🔄 Настройка процессов

```ini
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 2
pm.max_spare_servers = 10
```

> ⚠️ Важно: `max_children` = (RAM - OS - DB) / (RAM на один PHP-процесс)


#### 📊 Таблица: Пороговые значения

| ОЗУ на сервере | RAM на PHP-процесс | Рекомендуемый `max_children` |
|----------------|---------------------|------------------------------|
| 2 ГБ           | 30 МБ               | 50                           |
| 4 ГБ           | 40 МБ               | 75                           |
| 8 ГБ           | 40 МБ               | 150                          |
| 16 ГБ          | 50 МБ               | 250                          |



### 🧪 3. Профилирование и отладка

#### 🔍 Xdebug

- Расширение для PHP, позволяющее отлаживать код и профилировать его.
- Генерирует файлы `.cachegrind`, которые можно анализировать визуально.
- Используется локально или на тестовом сервере.

**Установка и включение:**

```bash
pecl install xdebug
```

```ini
zend_extension=xdebug
xdebug.mode=profile
xdebug.output_dir="/tmp"
```

**Пример запуска и анализа:**

1. Откройте нужную страницу приложения.
2. В каталоге `/tmp` появится файл `cachegrind.out.*`.
3. Откройте его в QCachegrind (или KCachegrind на Linux/Mac).

#### 🎯 Пример: замер RAM на 1 PHP-процесс

```php
$start = microtime(true);
echo memory_get_peak_usage(true); // ~40 MB
echo "Time: " . (microtime(true) - $start) . " seconds\n";
```
**Результаты**: ~0.001 сек, 40 МБ на процесс.
Пример расчёта: для 8 ГБ RAM → `8*1024 / 40 ≈ 200` → `pm.max_children = 200`.


#### ⚙ Blackfire

- Коммерческий, но очень удобный инструмент от авторов Symfony.
- Работает в продакшене без заметной нагрузки.
- Даёт граф вызовов, показатели времени, памяти, IO, CPU.

**Установка:**

```bash
curl -s https://blackfire.io/install.sh | bash
```

**Использование:**

1. Запускаете Blackfire CLI.
2. Выполняете профилирование страницы.
3. Получаете визуальный отчёт в браузере.

➡ Особенно полезен для быстрой диагностики без дебаггера.


### Лайфхаки
- Включайте HTTP/2 и brotli в nginx:
  ```nginx
  http {
      http2 on;
      brotli on;
  }
  ```
- Настройте OPcache:
  ```ini
  opcache.enable=1
  opcache.memory_consumption=128
  opcache.validate_timestamps=0
  ```
- Используйте Blackfire для продакшена, Xdebug — для разработки.
- Мониторьте логи и метрики:
  ```bash
  top -p $(pidof php-fpm)
  ```

### Советы для собеседований
- **Вопросы**:
  - Как рассчитать `max_children` для PHP-FPM?
  - Разница между `pm.dynamic`, `pm.static`, `pm.ondemand`?
  - Как профилировать PHP-приложение?
- **Подготовка**: Настройте nginx для Laravel и объясните, как измерить RAM процесса.

---

## ❓ Типичные вопросы на собеседованиях

Собеседования для Mid+/Senior — как квест с ловушками: знай подводные камни и умей объяснить код. Вот подборка вопросов, чтобы вы вышли с оффером, а не с головной болью! 🏆

1. **Что выведет этот код и почему?**
   ```php
   class A {
       public static function who() { echo "A"; }
       public static function call() { static::who(); }
   }
   class B extends A {
       public static function who() { echo "B"; }
   }
   B::call();
   ```
   **Ответ**: `B`  
   **Пояснение**: `static::` использует позднее статическое связывание, вызывая `who()` класса `B`.  
   **Связь**: `self:: vs static::`.


2. **Почему этот код вызовет ошибку?**
   ```php
   declare(strict_types=1);
   function add(int $a, int $b): int {
       return $a + $b;
   }
   echo add(2, "3");
   ```
   **Ответ**: `Fatal error: Argument must be of type int`  
   **Пояснение**: `strict_types=1` запрещает приведение типов, строка `"3"` несовместима с `int`.  
   **Связь**: `strict_types`.


3. **Что выведет этот код?**
   ```php
   $obj = new stdClass();
   $map = new WeakMap();
   $map[$obj] = "test";
   unset($obj);
   var_dump($map->count());
   ```
   **Ответ**: `0`  
   **Пояснение**: `WeakMap` удаляет запись, если ключ-объект уничтожен сборщиком мусора.  
   **Связь**: `WeakMap / WeakReference`.


4. **Как работает этот код?**
   ```php
   function gen() {
       yield 1;
       yield 2;
       return 3;
   }
   $gen = gen();
   foreach ($gen as $val) {
       echo $val . " ";
   }
   echo $gen->getReturn();
   ```
   **Ответ**: `1 2 3`  
   **Пояснение**: `yield` выдаёт 1 и 2 в цикле, `return 3` доступен через `getReturn()`.  
   **Связь**: `Генераторы и yield`.


5. **Что выведет этот код?**
   ```php
   $fiber = new Fiber(function() {
       echo Fiber::suspend("Paused");
   });
   echo $fiber->start();
   $fiber->resume("Resumed");
   ```
   **Ответ**: `PausedResumed`  
   **Пояснение**: `Fiber::suspend` возвращает `"Paused"`, `resume("Resumed")` возобновляет фибер, выводя `"Resumed"`.  
   **Связь**: `Fibers`.


6. **Почему этот код опасен?**
   ```php
   class User implements Serializable {
       private $data;
       public function serialize() { return serialize($this->data); }
       public function unserialize($data) { $this->data = unserialize($data); }
   }
   ```
   **Ответ**: Уязвимость к атакам через `unserialize`.  
   **Пояснение**: Непроверенный ввод в `unserialize` может выполнить произвольный код (например, через `__wakeup`).  
   **Решение**: Используйте `__serialize`/`__unserialize` и валидируйте данные.  
   **Связь**: `Встроенные интерфейсы`.


7. **Что выведет этот код?**
   ```php
   #[Attribute]
   class Route {
       public function __construct(public string $path) {}
   }
   #[Route('/test')]
   class Controller {
       public function index() {}
   }
   $ref = new ReflectionClass(Controller::class);
   $attrs = $ref->getAttributes(Route::class);
   var_dump($attrs[0]->newInstance()->path);
   ```
   **Ответ**: `string(5) "/test"`  
   **Пояснение**: Атрибут `Route` читается через рефлексию, возвращая путь `/test`.  
   **Связь**: `Атрибуты`.


8. **Что выведет этот код?**
   ```php
   enum Status: string {
       case Active = 'active';
       case Inactive = 'inactive';
   }
   echo Status::from('active')->value;
   ```
   **Ответ**: `active`  
   **Пояснение**: `Status::from('active')` возвращает `Status::Active`, его `value` — `'active'`.  
   **Связь**: `Перечисления (Enums)`.


9. **Какой PSR используется в этом коде?**
   ```php
   use Psr\Http\Message\ResponseInterface;
   function handle(): ResponseInterface {
       // ...
   }
   ```
   **Ответ**: PSR-7  
   **Пояснение**: `ResponseInterface` — часть PSR-7, определяющего HTTP-сообщения.  
   **Связь**: `PSR`.


10. **Почему этот код неэффективен?**
    ```php
    foreach (file('large.txt') as $line) {
        // Обработка
    }
    ```
    **Ответ**: Загружает весь файл в память.  
    **Пояснение**: `file()` читает файл целиком, что неэффективно для больших данных.  
    **Решение**: Используйте генератор.
    ```php
    function readLines($file) {
        $handle = fopen($file, 'r');
        while ($line = fgets($handle)) {
            yield $line;
        }
        fclose($handle);
    }
    ```
    **Связь**: `Генераторы и yield`.

**Совет**: Практикуйтесь на [3v4l.org](https://3v4l.org/) и изучайте документацию PHP 8.0+. Фокус на подводные камни (уязвимости сериализации, производительность Fibers) поможет выделиться. И не забывайте про иронию — она спасает от скучных интервью! 😎

**Ресурсы**:
- [Документация PHP](https://www.php.net/manual/ru/)
- [PHP-FIG](https://www.php-fig.org/)
- [3v4l.org](https://3v4l.org/)