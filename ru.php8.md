# 🐘 PHP 8 — Advanced Topics for Mid+/Senior Developers

Этот документ охватывает менее очевидные, но крайне важные темы для понимания PHP 8 на глубоком уровне. Подойдет для миддл+ и сеньор разработчиков, а также будет понятен внимательным джунам.

---

# 📚 Встроенные интерфейсы и классы PHP

PHP предоставляет ряд встроенных интерфейсов, которые можно использовать для создания более гибких, масштабируемых и "встроенно-поддерживаемых" компонентов. Они особенно активно применяются в SPL (Standard PHP Library), а также в механизмах сериализации, итерации, обработки ошибок и логики массивов.

📎 [Официальная документация на php.net](https://www.php.net/manual/ru/reserved.interfaces.php)

---

## 🔁 Итерация и коллекции

### `Traversable`
- Базовый интерфейс для всех итерируемых объектов.
- Не реализуется напрямую — нужно `Iterator` или `IteratorAggregate`.

### `Iterator`
- Полный интерфейс итератора (как `foreach`).
- Требует реализации:  
  `current()`, `key()`, `next()`, `rewind()`, `valid()`

### `IteratorAggregate`

Используется для делегирования итерации:

```php
class Collection implements IteratorAggregate {
    public function getIterator(): Traversable {
        return new ArrayIterator($this->items);
    }
}
```

### `SeekableIterator`

Дополнительный метод: `seek(int $position)`

---

### `IteratorAggregate`
- Обеспечивает итерацию через метод `getIterator()`, возвращающий `Traversable`.

### `SeekableIterator`
- Расширяет `Iterator` добавлением метода `seek($position)` — переход на нужную позицию.

---

## 📦 Коллекции и доступ по ключу

### `ArrayAccess`
- Позволяет работать с объектом, как с массивом: `$obj[0] = 'value';`
- Методы:  
  `offsetExists()`, `offsetGet()`, `offsetSet()`, `offsetUnset()`

### `Countable`
- Позволяет использовать `count($obj)`
- Требует реализации `count(): int`

---

## 💾 Сериализация и кодирование

### `Serializable`
- Кастомная сериализация:
    - `serialize()`
    - `unserialize(string $data)`

### `JsonSerializable`
- Используется в `json_encode()`
- Метод: `jsonSerialize(): mixed`

---

## 🚨 Обработка ошибок

### `Throwable`
- Базовый интерфейс для всех исключений и ошибок.
- Поддерживает `Exception` и `Error`.


- Все ошибки и исключения наследуют `Throwable`.
- Можно ловить и `Exception`, и `Error` через `catch (Throwable $e)`.
- Поддержка вложенных исключений:

```php
throw new Exception("Error", 0, $previous);
```

---

### `Exception`
- Стандартные исключения (классы должны наследовать `Exception`).

### `Error`
- Фатальные ошибки (наследуется от `Throwable`).

---

## 📚 Дополнительные интерфейсы SPL

### `OuterIterator`
- Оборачивает другой итератор. Метод: `getInnerIterator()`

### `RecursiveIterator`
- Итератор, который может содержать другие итераторы (иерархия).
- Методы: `hasChildren()`, `getChildren()`

### `RecursiveIteratorIterator`
- Обходит `RecursiveIterator` в глубину.

### `CountableIterator` *(не существует, часто путают с `Countable` + `Iterator`)*

---

## 🔢 ArrayAccess

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

---

## 🔁 Serializable

Позволяет контролировать, как объект сериализуется:

```php
class User implements Serializable {
    public function serialize(): string { ... }
    public function unserialize($data): void { ... }
}
```

📌 Устаревает в пользу `__serialize()` / `__unserialize()` с PHP 7.4+

---

## 🔒 SensitiveParameterValue

Класс, скрывающий значение при логировании исключений.

```php
function login(string $user, SensitiveParameterValue $password) {
    throw new Exception("Invalid password");
}
```

Используется в трассировке/дебаге, чтобы скрыть чувствительные данные.

---

## 🔐 __PHP_Incomplete_Class

Класс, автоматически используемый PHP, когда сериализуется объект неизвестного класса.  
Встречается при `unserialize()` если класс больше не существует.

---

## 🧠 Closure

Анонимные функции — экземпляры класса `Closure`:

```php
$fn = function($x) { return $x * 2; };
```

Можно использовать `bindTo()` и `call()` для изменения контекста исполнения.

---

## 📦 stdClass

"Пустой" универсальный объект:

```php
$obj = new stdClass();
$obj->name = "test";
```

Часто используется для конверсии `array → object`.

---

## ⚙️ Generator

Объект, который возвращается при вызове функции с `yield`.

```php
function gen() {
    yield 1;
    yield 2;
}
```

Объекты `Generator` реализуют `Iterator`.

---

## 🧵 Fiber (PHP 8.1+)

Механизм для кооперативной многозадачности:

```php
$fiber = new Fiber(function (): void {
    $value = Fiber::suspend("pause");
    echo $value;
});
$fiber->start();
```

Позволяет временно "приостановить" выполнение и вернуться позже.

---

## 🧷 WeakReference

Создаёт слабую ссылку на объект:

```php
$ref = WeakReference::create($obj);
$obj = null;
$ref->get(); // null — объект уничтожен
```

Используется в кешах и GC-чувствительных структурах.

---

## 🗺 WeakMap

Ассоциативный массив с объектами-ключами. Когда объект уничтожается — удаляется и ключ.

```php
$map = new WeakMap();
$map[$obj] = "cached";
```

---

## 🧵 Stringable (PHP 8.0+)

Маркер-интерфейс. Если класс реализует `__toString()`, он автоматически `implements Stringable`.

Можно явно указать:

```php
class MyClass implements Stringable {
    public function __toString(): string { ... }
}
```

---

## 🔠 UnitEnum / BackedEnum (PHP 8.1+)

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

Эти интерфейсы — основа многих встроенных и сторонних библиотек. Понимание их и умение применять на практике выделяет уверенного разработчика.


---

# 🧱 Объектно-Ориентированное Программирование (ООП) в PHP

ООП — основа масштабируемых приложений. Ниже подробно описаны ключевые концепции ООП в PHP.

---

## 📦 Классы, Интерфейсы, Абстрактные классы

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

---

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

---

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

---

## 🧬 Наследование

### Ключевые принципы:

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

---

## 🚫 Ограничения и модификаторы

- `final class` — нельзя наследовать.
- `final function` — нельзя переопределить в потомках.
- `abstract function` — метод без тела, должен быть реализован в дочерних классах.
- `private` — доступен только внутри самого класса.
- `protected` — доступен в классе и его потомках.
- `public` — доступен везде.

---

## 🛠 Как обойти ограничение на множественное наследование?

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

---

## 🔑 Когда использовать что?

| Тип           | Когда применять                                 |
|---------------|--------------------------------------------------|
| `interface`   | Когда нужно определить контракт для реализации. |
| `abstract`    | Когда есть общая логика + абстрактные методы.   |
| `trait`       | Когда нужно переиспользовать код между классами.|
| `class`       | Конкретная реализация.                          |

---

Понимание этих конструкций — основа грамотной архитектуры. Умение выбирать между интерфейсом, абстракцией и трейтом отличает уверенного разработчика.


---

## 🧭 `self::` vs `static::` — разница в позднем статическом связывании

### `self::` — привязка к классу, где метод определён

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

### `static::` — позднее статическое связывание (late static binding)

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

Используй `static::` если ты хочешь, чтобы метод работал корректно при наследовании.

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

---

## ✅ Рекомендации

- Используй `private` по умолчанию — инкапсуляция!
- `protected` — если ожидается наследование с расширением поведения.
- `public` — только для внешнего API.

- `self::` — если хочешь зафиксировать вызов на текущем классе (например, фабричный метод в базовом классе).
- `static::` — если метод должен учитывать полиморфизм.

---

### Передача объектов по ссылке
PHP объекты в коде - это фактически не сами объекты, а их ссылки:

```php
$a = new StdClass();
$b = $a; // $b и $a — это ссылка на один и тот же объект;
$b->test = 1;
echo $a->test; // $a->test === $b->test === 1, так как $a и $b - ссылки
```

### Клонирование

```php
$c = $a; // новый объект не создается; $c - это ссылка на тот же объект, что и $a
$b = clone $a; // создаётся копия объекта $a
```

Можно определить `__clone()` для модификации поведения.

---

## 🧬 Ковариантность и контравариантность

### Ковариантность (возвращаемый тип может быть более конкретным)

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

### Контравариантность (аргумент может быть более абстрактным)

---

## 🧠 WeakMap / WeakReference

Позволяют ссылаться на объекты без удержания их в памяти:

### WeakReference

```php
$ref = WeakReference::create($object);
$obj = $ref->get(); // может вернуть null
```

### WeakMap

```php
$map = new WeakMap();
$map[$object] = 'value'; // когда объект удалится — пара исчезнет
```

Используется в кешировании и замыкании циклических зависимостей.

---

# ✨ Магические методы в PHP 8

Магические методы — это специальные методы в PHP, которые автоматически вызываются в определённых ситуациях. Они всегда начинаются с двойного подчёркивания `__`.

---

## 📋 Полный список магических методов

### 🔨 `__construct()`

Вызывается при создании объекта.

```php
class User {
    public function __construct(public string $name) {
        echo "Created user: $name";
    }
}
```

---

### 🧹 `__destruct()`

Вызывается при удалении объекта (в конце скрипта или при unset).

```php
public function __destruct() {
    echo "Destroying user";
}
```

---

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

---

### 🔍 `__isset($name)` и `__unset($name)`

Реакция на `isset($obj->prop)` и `unset($obj->prop)`.

```php
public function __isset($name) {
    return isset($this->data[$name]);
}
```

---

### ☎️ `__call($name, $arguments)`

Вызывается при обращении к несуществующему методу.

```php
public function __call($name, $arguments) {
    echo "Метод $name не существует";
}
```

---

### ☎️ `__callStatic($name, $arguments)`

Аналог `__call`, но для статических методов.

```php
public static function __callStatic($name, $arguments) {
    echo "Static $name called";
}
```

---

### 🧬 `__clone()`

Вызывается при клонировании объекта через `clone`.

```php
public function __clone() {
    $this->id = null;
}
```

---

### 🧾 `__toString()`

Позволяет задать строковое представление объекта.

```php
public function __toString(): string {
    return $this->name;
}
```

---

### 📞 `__invoke(...)`

Позволяет вызывать объект как функцию.

```php
public function __invoke($x) {
    return $x * 2;
}

$obj = new MyClass();
echo $obj(5); // 10
```

---

### 🧭 `__debugInfo()`

Определяет, какие данные показываются в `var_dump()`.

```php
public function __debugInfo(): array {
    return ['hidden' => true];
}
```

---

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

---

## ⚠️ Рекомендации

- Не злоупотребляй магией — код может стать нечитаемым.
- Используй `__get/__set` для **прокси-объектов**, не как замену обычным свойствам.
- `__call/__callStatic` удобны для реализации **ленивой загрузки** или **обёрток**.

---

# 🚫 strict_types — строгость типов

В PHP по умолчанию разрешено **автоматическое приведение типов** (type juggling), что может привести к неожиданным результатам и ошибкам, которые сложно отследить.

## ✅ Включение строгой типизации

```php
<?php
declare(strict_types=1);
```

🔺 Это объявление должно быть **первой строкой файла**, до любого вывода или кода.

---

## ⚖️ Пример: сравнение поведения

### 📌 Без `strict_types`

```php
function sum(int $a, int $b): int {
    return $a + $b;
}

echo sum(2, 3.5); // 5 — float приводится к int
```

### ✅ С `strict_types=1`

```php
declare(strict_types=1);

function sum(int $a, int $b): int {
    return $a + $b;
}

echo sum(2, 3.5); // ❌ Fatal error: Argument must be of type int
```

---

## ❗ Проблемы при отключённой типизации

- Тихое приведение float → int, string → int и др.
- Логические ошибки, которые трудно отследить.
- Поведение зависит от данных, а не от кода.

---

## ✅ Почему стоит **всегда использовать** `strict_types`

- 🛡️ Защита от скрытых ошибок.
- 🔎 Прозрачность и предсказуемость типов.
- 🧪 Более корректное поведение при написании юнит-тестов.
- 🔄 Совместимость с современными стандартами и анализаторами кода (`PHPStan`, `Psalm`).

---

## 🔁 Рекомендуемый подход

- **Всегда включай `strict_types=1`** в каждом PHP-файле (особенно в библиотеке или API).
- Используй типы параметров и возвращаемых значений (`int`, `string`, `array`, `bool`, и т.д.).
- В связке с строгой типизацией применяй автотесты и статический анализ.

---

# 🔁 Генераторы и `yield` в PHP

Генераторы позволяют реализовать **ленивую итерацию** — обработку данных **по одному элементу за раз**, **без необходимости загружать всё в память**. Это особенно полезно при работе с большими объёмами данных.

---

## 🧱 Базовый пример генератора

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

---

## ⚙️ Как работает `yield`?

- `yield` возвращает значение, приостанавливает выполнение и запоминает текущее состояние.
- Следующий вызов итерации (`foreach`) продолжит выполнение с этой точки.

---

## ✅ Преимущества генераторов

- 🧠 Простота синтаксиса (не нужно реализовывать интерфейс `Iterator`).
- 🧵 Производительность (обработка "на лету").
- 🧼 Экономия памяти (особенно при больших выборках/файлах).

---

## 📚 Примеры реального применения

### 📄 Чтение файла построчно

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

---

### 🗃 Обработка SQL-запросов по частям

```php
function fetchRows(PDO $pdo): Generator {
    $stmt = $pdo->query("SELECT * FROM users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        yield $row;
    }
}
```

✅ Позволяет обрабатывать **большую выборку из базы**, не загружая её целиком.

---

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

---

## 🔁 `yield key => value`

```php
function users() {
    yield 1 => 'Alice';
    yield 2 => 'Bob';
}

foreach (users() as $id => $name) {
    echo "$id: $name" . PHP_EOL;
}
```

---

## 📍 `return` в генераторе

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

---

## 📌 Когда использовать генераторы?

- Обработка **больших данных** (файлы, SQL).
- Потоковая обработка (stream).
- Когда важна **экономия памяти**.
- При **постепенной генерации значений** (например, дерево, граф).

---

# 📐 PSR — PHP Standards Recommendations

**PSR** (PHP Standards Recommendations) — это набор стандартов, разработанный группой [PHP-FIG (Framework Interop Group)](https://www.php-fig.org/), чтобы унифицировать подходы к разработке, сделать код более читаемым, предсказуемым и совместимым между фреймворками и библиотеками.

---

## ❓ Зачем нужны PSR?

- 🧩 Упрощают интеграцию сторонних библиотек.
- 🤝 Повышают совместимость между фреймворками.
- 🧼 Стандартизуют стиль и архитектуру.
- 📦 Поддерживаются в Composer и IDE.

---

## 📋 Основные PSR стандарты

### 🎨 PSR-1: Базовый стиль кода
- Стандартизирует имена классов, файлов, методов.
- Обязателен `<?php` тег.
- Названия классов — в `StudlyCaps`, методов — в `camelCase`.

### 🧼 PSR-12: Расширенный стиль кода (преемник PSR-2)
- Правила форматирования: отступы, пробелы, фигурные скобки.
- Один класс на файл, обязательный declare(strict_types=1).

### 📦 PSR-4: Автозагрузка
- Простая схема автозагрузки на основе namespace + путь.
```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

### 📝 PSR-3: Интерфейс логирования
- Определяет универсальный `LoggerInterface` с методами: `debug()`, `info()`, `error()` и др.

### 🌐 PSR-7: HTTP-сообщения
- Интерфейсы `RequestInterface`, `ResponseInterface`, `StreamInterface` и др.
- Используется во фреймворках (Slim, Symfony HTTPFoundation).

### ⚙️ PSR-11: Контейнер зависимостей
- Интерфейс `ContainerInterface` с методами `get()` и `has()`.

### 🔀 PSR-13: Ссылки на объекты (Hypermedia Links)
- Интерфейс `LinkInterface`, для REST/HATEOAS-ссылок.

### 📚 PSR-14: События
- Event Dispatcher интерфейсы: `EventDispatcherInterface`, `StoppableEventInterface`.

### 🧵 PSR-15: Middleware
- Определяет интерфейсы для HTTP middleware (обработка запросов слоями).

### 🧪 PSR-17: HTTP Factory
- Factory-интерфейсы для создания объектов PSR-7 (`RequestFactoryInterface`, и др.)

### 🧰 PSR-18: HTTP Client
- Стандарт для HTTP-клиентов (`sendRequest()` и др.).

---

## ✅ Как использовать PSR

- Подключай только нужные интерфейсы через Composer.
- Не обязательно использовать весь стек.
- Совместим с любой архитектурой.

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

# 📦 Composer — Менеджер зависимостей для PHP

**Composer** — это стандартный инструмент для управления зависимостями и автозагрузкой в PHP. Он позволяет подключать сторонние библиотеки, управлять версиями и конфигурацией проекта.

---

## 🛠 Основные команды

### 📥 Установка пакета

```bash
composer require vendor/package
```

Добавляет зависимость и сразу обновляет `composer.json` и `composer.lock`.

### 🧹 Удаление пакета

```bash
composer remove vendor/package
```

Удаляет пакет и очищает зависимости.

### ♻️ Обновление всех зависимостей

```bash
composer update
```

Обновляет **все зависимости** до последних допустимых версий согласно `composer.json`.

### 🎯 Обновление одного пакета

```bash
composer update vendor/package
```

---

## 📁 Структура файлов

### `composer.json`

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

### `composer.lock`

- **Фиксирует точные версии** всех установленных зависимостей и подзависимостей.
- Используется для воспроизводимости (`CI/CD`, деплой).
- **Нужен в проекте!** Его **нужно коммитить**, если проект — приложение (а не библиотека).

📌 **Когда не коммитить `composer.lock`?**
- Когда вы разрабатываете **библиотеку**, и не хотите фиксировать зависимости для других.

---

## 📦 Создание своего Composer-пакета

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

---

## 📍 Локальное подключение своего пакета (без Packagist)

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

---

## 🧪 Полезные команды

- `composer validate` — проверка `composer.json`
- `composer outdated` — список устаревших зависимостей
- `composer dump-autoload` — пересоздать автозагрузку

---

## ✅ Рекомендации

- Коммить `composer.lock` во всех проектах, кроме библиотек.
- Устанавливай зависимости через `composer require`, а не вручную в JSON.
- Используй `^` и `~` для контроля версий (`^2.0` — любые `2.x`, `~1.2` — `1.2.*`).

---