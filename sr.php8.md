[📚 Садржај](README.sr.md)

- [🐘 PHP 8 — Напредне теме за Mid+/Senior програмере](#-php-8--напредне-теме-за-midsenior-програмере)
  - [⚙️ Интерпретатор, оптимизација и перформансе у PHP 8](#-интерпретатор-оптимизација-и-перформансе-у-php-8)
  - [🧱 Објектно-оријентисано програмирање (ООП) у PHP](#-објектно-оријентисано-програмирање-ооп-у-php)
  - [🧭 `self::` vs `static::` — разлика у касном статичком повезивању](#-self-vs-static--разлика-у-касном-статичком-повезивању)
  - [🔐 Модификатори видљивости](#-модификатори-видљивости)
  - [🧬 Коваријантност и контраваријантност](#-коваријантност-и-контраваријантност)
  - [🧠 WeakMap / WeakReference](#-weakmap--weakreference)
  - [✨ Магичне методе у PHP 8](#-магичне-методе-у-php-8)
  - [🚫 strict_types — строгост типова](#-strict_types--строгост-типова)
  - [🔁 Генератори и `yield` у PHP](#-генератори-и-yield-у-php)
  - [📚 Уграђени интерфејси и класе PHP](#-уграђени-интерфејси-и-класе-php)
  - [📐 PSR — PHP Standards Recommendations](#-psr--php-standards-recommendations)
  - [📦 Composer — Менаџер зависности за PHP](#-composer--менаџер-зависности-за-php)

---

# 🐘 PHP 8 — Напредне теме за Mid+/Senior програмере
📎 [Званична документација на php.net](https://www.php.net/manual/ru/index.php)

Овај документ обухвата неке основне и мање очигледне, али изузетно важне теме за разумевање PHP 8.

---

# ⚙️ Интерпретатор, оптимизација и перформансе у PHP 8

PHP је **интерпретирани језик**, али са изласком PHP 8 у њега су уграђени алати за компилацију (JIT), као и моћни алати за оптимизацију извршавања — као што је **OPcache**.

---

## 📖 Интерпретатор vs Компилатор

|                          | Интерпретатор (PHP)       | Компилатор (C/Go/Rust)           |
|--------------------------|---------------------------|----------------------------------|
| Извршавање               | Линија по линија, током покретања | Претходна транслација у машински код |
| Брзина покретања         | Тренутно покретање           | Захтева компилацију                   |
| Перформансе       | Ниже без оптимизације       | Више захваљујући машинском коду   |
| Дебаговање и развој     | Брже: унеси → сачувај → покрени | Спорије: мора се поново компилирати    |

**PHP** се извршава кроз **Zend интерпретатор**, али може користити кеширање (OPcache) и чак JIT компилацију.

---

## 🚀 Оптимизација перформанси у PHP

### ✅ OPcache

**OPcache** (први пут се појавио у PHP 5.5 и подразумевано је укључен од PHP 7) кешира **компилирани бајткод** скрипти, тако да их не мора поново парсирати при сваком захтеву.

**Пример конфигурације:**

```ini
; php.ini
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

🔸 Користите `opcache.validate_timestamps=0` у продукцији да не бисте поново рачунали време модификације фајлова.

---

### ⚙️ JIT (Just-in-time compilation) у PHP 8

JIT је компилатор који **извршава део кода у нативном облику**, а не као бајткод интерпретатора.

**Режими:**

```ini
opcache.jit=1255
opcache.jit_buffer_size=100M
```

### Када ради JIT?

JIT ступа на сцену **при интензивним израчунавањима**:

- Математичке функције
- Обрада великих низова
- Алгоритми (на пример, претрага, графови)

🔍 На типичном Laravel сајту убрзање може бити незнатно. Али за прилагођену логику — могуће је убрзање x2–x3.

**Пример:**

```php
function fib($n) {
    if ($n <= 1) return $n;
    return fib($n - 1) + fib($n - 2);
}
```

Са JIT — извршавање постаје приметно брже при великим вредностима `$n`.

---

## ❌ Ограничења PHP: WebSocket и стална веза

### Зашто PHP није добар за WebSocket?

- Сваки захтев је **засебан процес** (FPM).
- Нема нативни event loop или реактивни модел.
- Стална веза = држати процес → скупо.

---

## 🧩 Како се то решава?

### 🛠 Swoole

Swoole је **PHP проширење** које додаје подршку за:

- Event loop (asynchronous I/O)
- WebSocket server
- Coroutine-слично понашање

**Пример WebSocket-сервера на Swoole:**

```php
use Swoole\WebSocket\Server;

$server = new Server("0.0.0.0", 9502);

$server->on("message", function ($server, $frame) {
    $server->push($frame->fd, "Одговор: {$frame->data}");
});

$server->start();
```

📦 Инсталација: `pecl install swoole`

---

## ⚖️ Моје мишљење

> PHP је одличан алат за **обраду HTTP захтева, шаблона, API, CLI-алата**.  
> Али **WebSocket и сталне везе** боље је имплементирати на језицима који су створени за то.

---

## ✅ Go за WebSocket

Go је идеалан за вишенитну обраду и мрежну комуникацију.

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

---

## 🧭 Архитектура: подела одговорности

- ✅ PHP — API, CMS, SSR, рад са базом, документација.
- ✅ Go/Node — сталне везе, редови, обавештења.
- ✅ Nginx или Traefik — за рутирање између сервиса.

---

## 📌 Закључак

- PHP је интерпретатор, али са JIT и OPcache може бити веома брз.
- За WebSocket — боље користити Swoole, или издвојити у Go-сервис.
- Архитектура "ради оно у чему је језик јак" — најбоља стратегија.


---

## ⚙️ Детаљи конфигурације OPcache

```ini
; php.ini

opcache.enable=1
```
✅ Укључује OPcache.

```ini
opcache.enable_cli=1
```
✅ Укључује OPcache за CLI (на пример, при ручном покретању скрипти или преко cron-а).

```ini
opcache.memory_consumption=128
```
📦 Количина меморије (у мегабајтима) додељена за кеш бајткода.  
Обично: 64–128 MB за мале пројекте, 256–512 MB за велике.

```ini
opcache.interned_strings_buffer=16
```
🧠 Количина меморије (у MB) додељена за кеш стрингова (стрингови се поново користе, смањујући потрошњу меморије).

```ini
opcache.max_accelerated_files=10000
```
📂 Максималан број фајлова које OPcache кешира.  
За CMS/фрејмворке препоручено: 8000–20000.

```ini
opcache.revalidate_freq=60
```
⏱ Учесталост провере времена модификације фајлова (у секундама).  
0 = проверавати при сваком захтеву (погодно за развој),  
60 = једном у минути (препоручено за продукцију).

---

## 🧹 Како ручно очистити OPcache

Понекад се скрипте не ажурирају аутоматски, чак и након измене кода. Разлози:

- OPcache не проверава датум модификације (`validate_timestamps=0`)
- Кеш прелази лимит `max_accelerated_files`
- OPcache није укључен за CLI

### 🔧 Начини чишћења

1. **Програмски:**

```php
opcache_reset();
```

2. **CLI команда:**

```bash
php -r "opcache_reset();"
```

3. **Рестартовање PHP-FPM или веб-сервера:**

```bash
sudo systemctl restart php8.2-fpm
```

или за Apache:

```bash
sudo systemctl restart apache2
```

---

# 🧱 Објектно-оријентисано програмирање (ООП) у PHP

ООП је основа скалабилних апликација. У наставку су детаљно описани кључни концепти ООП у PHP.

---

## 📦 Класе, Интерфејси, Апстрактне класе

### ▶️ Класе

Класа је шаблон за креирање објеката. У њој се описују својства (променљиве) и методе (функције):

```php
class Car {
    public string $brand;

    public function drive() {
        echo "Driving";
    }
}
```

---

### 📄 Интерфејси (`interface`)

Интерфејс дефинише **уговор** — скуп метода које класа **мора имплементирати**.

```php
interface Engine {
    public function start();
}
```

Интерфејс:
- ❗ Не садржи имплементацију, само потписе метода.
- ❌ Не може садржати својства (до PHP 8.1).
- ✅ Може се имплементирати **више интерфејса истовремено**.

```php
class Car implements Engine, JsonSerializable {
    public function start() { ... }

    public function jsonSerialize(): mixed {
        return ['type' => 'car'];
    }
}
```

---

### 🧱 Апстрактне класе (`abstract`)

Апстрактна класа може:
- Садржати и **имплементиране** и **апстрактне методе**.
- Имати **својства** и **конструктор**.
- Бити коришћена као **базна класа**, од које наслеђују конкретне имплементације.

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

## 🧬 Наслеђивање

### Кључни принципи:

- Класа може **наследити само од једне друге класе** (single inheritance).
- Али може **имплементирати било који број интерфејса**.
- Користи `parent::method()` да позовеш родитељску имплементацију.

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

## 🚫 Ограничења и модификатори

- `final class` — не може се наследити.
- `final function` — не може се преклопити у потомцима.
- `abstract function` — метода без тела, мора бити имплементирана у класама наследницама.
- `private` — доступно само унутар саме класе.
- `protected` — доступно у класи и њеним потомцима.
- `public` — доступно свуда.

---

## 🛠 Како заобићи ограничење на вишеструко наслеђивање?

PHP не подржава вишеструко наслеђивање класа директно, али можеш користити **трејтове**:

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

Трејтови омогућавају поновно коришћење кода у више независних класа.

---

## 🔑 Када користити шта?

| Тип           | Када применити                                 |
|---------------|--------------------------------------------------|
| `interface`   | Када треба дефинисати уговор за имплементацију. |
| `abstract`    | Када постоји заједничка логика + апстрактне методе.   |
| `trait`       | Када треба поново користити код између класа.|
| `class`       | Конкретна имплементација.                          |

---

Разумевање ових конструкција је основа добре архитектуре. Способност избора између интерфејса, апстракције и трејта разликује сигурног програмера.


---

## 🧭 `self::` vs `static::` — разлика у касном статичком повезивању

### `self::` — везивање за класу где је метода дефинисана

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

B::call(); // A — self:: је везан за A
```

### `static::` — касно статичко повезивање (late static binding)

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

B::call(); // B — static:: се односи на класу која је позвала
```

Користи `static::` ако желиш да метода ради исправно при наслеђивању.

---

## 🔐 Модификатори видљивости

| Модификатор | Доступан унутар | Доступан у наследницима | Доступан споља |
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

## ✅ Препоруке

- Користи `private` подразумевано — инкапсулација!
- `protected` — ако се очекује наслеђивање са проширењем понашања.
- `public` — само за спољни API.

- `self::` — ако желиш да фиксираш позив на тренутну класу (на пример, фабрички метод у базној класи).
- `static::` — ако метода треба да узме у обзир полиморфизам.

---

### Прослеђивање објеката по референци
PHP објекти у коду су заправо не сами објекти, већ њихове референце:

```php
$a = new StdClass();
$b = $a; // $b и $a — то је референца на исти објекат;
$b->test = 1;
echo $a->test; // $a->test === $b->test === 1, јер су $a и $b референце
```

### Клонирање

```php
$c = $a; // нови објекат се не креира; $c је референца на исти објекат као и $a
$b = clone $a; // креира се копија објекта $a
```

Можеш дефинисати `__clone()` за модификацију понашања.

---

## 🧬 Коваријантност и контраваријантност

### Коваријантност (повратни тип може бити конкретнији)

```php
class A {}
class B extends A {}

class ParentClass {
    public function get(): A {}
}

class ChildClass extends ParentClass {
    public function get(): B {} // дозвољено
}
```

### Контраваријантност (аргумент може бити апстрактнији)

---

## 🧠 WeakMap / WeakReference

Омогућавају референцирање објеката без задржавања у меморији:

### WeakReference

```php
$ref = WeakReference::create($object);
$obj = $ref->get(); // може вратити null
```

### WeakMap

```php
$map = new WeakMap();
$map[$object] = 'value'; // када се објекат обрише — пар нестаје
```

Користи се у кеширању и затварању цикличних зависности.

---

# ✨ Магичне методе у PHP 8

Магичне методе су специјалне методе у PHP које се аутоматски позивају у одређеним ситуацијама. Увек почињу са двоструком доњом цртом `__`.

---

## 📋 Комплетна листа магичних метода

### 🔨 `__construct()`

Позива се при креирању објекта.

```php
class User {
    public function __construct(public string $name) {
        echo "Created user: $name";
    }
}
```

---

### 🧹 `__destruct()`

Позива се при брисању објекта (на крају скрипте или при unset).

```php
public function __destruct() {
    echo "Destroying user";
}
```

---

### 🧠 `__get($name)` и `__set($name, $value)`

Обрађују приступ непостојећим/приватним својствима.

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

Реакција на `isset($obj->prop)` и `unset($obj->prop)`.

```php
public function __isset($name) {
    return isset($this->data[$name]);
}
```

---

### ☎️ `__call($name, $arguments)`

Позива се при обраћању непостојећој методи.

```php
public function __call($name, $arguments) {
    echo "Метода $name не постоји";
}
```

---

### ☎️ `__callStatic($name, $arguments)`

Аналог `__call`, али за статичке методе.

```php
public static function __callStatic($name, $arguments) {
    echo "Static $name called";
}
```

---

### 🧬 `__clone()`

Позива се при клонирању објекта преко `clone`.

```php
public function __clone() {
    $this->id = null;
}
```

---

### 🧾 `__toString()`

Омогућава дефинисање стринг репрезентације објекта.

```php
public function __toString(): string {
    return $this->name;
}
```

---

### 📞 `__invoke(...)`

Омогућава позивање објекта као функције.

```php
public function __invoke($x) {
    return $x * 2;
}

$obj = new MyClass();
echo $obj(5); // 10
```

---

### 🧭 `__debugInfo()`

Дефинише које се информације приказују у `var_dump()`.

```php
public function __debugInfo(): array {
    return ['hidden' => true];
}
```

---

### 📦 `__serialize()` и `__unserialize()`

Појавили су се у PHP 7.4. Управљају серијализацијом објекта.

```php
public function __serialize(): array {
    return ['name' => $this->name];
}

public function __unserialize(array $data): void {
    $this->name = $data['name'];
}
```

---

## ⚠️ Препоруке

- Не злоупотребљавај магију — код може постати нечитљив.
- Користи `__get/__set` за **прокси-објекте**, не као замену за обична својства.
- `__call/__callStatic` су корисни за имплементацију **лењог учитавања** или **омотача**.

---

# 🚫 strict_types — строгост типова

У PHP је подразумевано дозвољено **аутоматско претварање типова** (type juggling), што може довести до неочекиваних резултата и грешака које је тешко пратити.

## ✅ Укључивање строге типизације

```php
<?php
declare(strict_types=1);
```

🔺 Ова декларација мора бити **прва линија фајла**, пре било каквог излаза или кода.

---

## ⚖️ Пример: поређење понашања

### 📌 Без `strict_types`

```php
function sum(int $a, int $b): int {
    return $a + $b;
}

echo sum(2, 3.5); // 5 — float се претвара у int
```

### ✅ Са `strict_types=1`

```php
declare(strict_types=1);

function sum(int $a, int $b): int {
    return $a + $b;
}

echo sum(2, 3.5); // ❌ Fatal error: Argument must be of type int
```

---

## ❗ Проблеми при искљученој типизацији

- Тихо претварање float → int, string → int и др.
- Логичке грешке које је тешко пратити.
- Понашање зависи од података, а не од кода.

---

## ✅ Зашто треба **увек користити** `strict_types`

- 🛡️ Заштита од скривених грешака.
- 🔎 Транспарентност и предвидљивост типова.
- 🧪 Коректније понашање при писању јединичних тестова.
- 🔄 Компатибилност са савременим стандардима и анализаторима кода (`PHPStan`, `Psalm`).

---

## 🔁 Препоручени приступ

- **Увек укључи `strict_types=1`** у сваком PHP фајлу (посебно у библиотеци или API).
- Користи типове параметара и повратних вредности (`int`, `string`, `array`, `bool`, итд.).
- У комбинацији са строгом типизацијом примењуј аутотестове и статичку анализу.

---

# 🔁 Генератори и `yield` у PHP

Генератори омогућавају имплементацију **лењог итерирања** — обраду података **по једном елементу одједном**, **без потребе да се све учита у меморију**. Ово је посебно корисно при раду са великим количинама података.

---

## 🧱 Основни пример генератора

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

## ⚙️ Како ради `yield`?

- `yield` враћа вредност, паузира извршавање и памти тренутно стање.
- Следећи позив итерације (`foreach`) наставља извршавање од те тачке.

---

## ✅ Предности генератора

- 🧠 Једноставност синтаксе (није потребно имплементирати интерфејс `Iterator`).
- 🧵 Перформансе (обрада "у лету").
- 🧼 Уштеда меморије (посебно код великих упита/фајлова).

---

## 📚 Примери реалне примене

### 📄 Читање фајла линију по линију

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

✅ Алтернатива `file()` — не учитава цео фајл у меморију.

---

### 🗃 Обрада SQL упита по деловима

```php
function fetchRows(PDO $pdo): Generator {
    $stmt = $pdo->query("SELECT * FROM users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        yield $row;
    }
}
```

✅ Омогућава обраду **великог упита из базе**, без учитавања целог у меморију.

---

### 🧪 Бесконачна секвенца

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

## 📍 `return` у генератору

Од PHP 7, генератор може вратити вредност преко `return`, доступну преко `$gen->getReturn()`:

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

## 📌 Када користити генераторе?

- Обрада **великих података** (фајлови, SQL).
- Стримовање података (stream).
- Када је важна **уштеда меморије**.
- При **постепеном генерисању вредности** (на пример, стабло, граф).

---

# 📚 Уграђени интерфејси и класе PHP

PHP пружа низ уграђених интерфејса који се могу користити за креирање флексибилнијих, скалабилнијих и "уграђено-подржаних" компоненти. Они се посебно активно користе у SPL (Standard PHP Library), као и у механизмима серијализације, итерације, обраде грешака и логике низова.

---

## 🔁 Итерација и колекције

### `Traversable`
- Базни интерфејс за све итерабилне објекте.
- Не имплементира се директно — потребан је `Iterator` или `IteratorAggregate`.

### `Iterator`
- Потпуни интерфејс итератора (као `foreach`).
- Захтева имплементацију:  
  `current()`, `key()`, `next()`, `rewind()`, `valid()`

### `IteratorAggregate`

- Обезбеђује итерацију кроз методу `getIterator()`, која враћа `Traversable`
  (користи се за делегирање итерације):


```php
class Collection implements IteratorAggregate {
    public function getIterator(): Traversable {
        return new ArrayIterator($this->items);
    }
}
```

### `SeekableIterator`
- Проширује `Iterator` додавањем методе `seek($position)` — прелазак на жељену позицију.

---

## 📦 Колекције и приступ по кључу

### `ArrayAccess`
- Омогућава рад са објектом као са низом: `$obj[0] = 'value';`
- Методе:  
  `offsetExists()`, `offsetGet()`, `offsetSet()`, `offsetUnset()`

### `Countable`
- Омогућава коришћење `count($obj)`
- Захтева имплементацију `count(): int`

---

## 💾 Серијализација и кодирање

### `Serializable`
- Прилагођена серијализација:
  - `serialize()`
  - `unserialize(string $data)`

### `JsonSerializable`
- Користи се у `json_encode()`
- Метода: `jsonSerialize(): mixed`

---

## 🚨 Обрада грешака

### `Throwable`
- Базни интерфејс за све изузетке и грешке.
- Подржава `Exception` и `Error`.


- Све грешке и изузеци наслеђују `Throwable`.
- Могу се хватати и `Exception`, и `Error` преко `catch (Throwable $e)`.
- Подршка за угнежђене изузетке:

```php
throw new Exception("Error", 0, $previous);
```

---

### `Exception`
- Стандардни изузеци (класе морају наследити `Exception`).

### `Error`
- Фаталне грешке (наслеђује се од `Throwable`).

---

## 📚 Додатни интерфејси SPL

### `OuterIterator`
- Омотава други итератор. Метода: `getInnerIterator()`

### `RecursiveIterator`
- Итератор који може садржати друге итераторе (хијерархија).
- Методе: `hasChildren()`, `getChildren()`

### `RecursiveIteratorIterator`
- Обилази `RecursiveIterator` у дубину.

### `CountableIterator` *(не постоји, често се меша са `Countable` + `Iterator`)*

---

## 🔢 ArrayAccess

Омогућава приступ објекту као низу:

```php
class Collection implements ArrayAccess {
    public function offsetExists($offset) { ... }
    public function offsetGet($offset) { ... }
    public function offsetSet($offset, $value) { ... }
    public function offsetUnset($offset) { ... }
}
```

Користи се у контејнерима, DTO, подешавањима.

---

## 🔁 Serializable

Омогућава контролу над тиме како се објекат серијализује:

```php
class User implements Serializable {
    public function serialize(): string { ... }
    public function unserialize($data): void { ... }
}
```

📌 Застарева у корист `__serialize()` / `__unserialize()` од PHP 7.4+

---

## 🔒 SensitiveParameterValue

Класа која скрива вредност при логовању изузетака.

```php
function login(string $user, SensitiveParameterValue $password) {
    throw new Exception("Invalid password");
}
```

Користи се у праћењу/дебаговању, да би се сакрили осетљиви подаци.

---

## 🔐 __PHP_Incomplete_Class

Класа коју PHP аутоматски користи када се серијализује објекат непознате класе.  
Јавља се при `unserialize()` ако класа више не постоји.

---

## 🧠 Closure

Анонимне функције — инстанце класе `Closure`:

```php
$fn = function($x) { return $x * 2; };
```

Могу се користити `bindTo()` и `call()` за промену контекста извршавања.

---

## 📦 stdClass

"Празан" универзални објекат:

```php
$obj = new stdClass();
$obj->name = "test";
```

Често се користи за конверзију `array → object`.

---

## ⚙️ Generator

Објекат који се враћа при позиву функције са `yield`.

```php
function gen() {
    yield 1;
    yield 2;
}
```

Објекти `Generator` имплементирају `Iterator`.

Погледај одељак [Генератори и `yield` у PHP](#-генератори-и-yield-у-php)

---

## 🧵 Fiber (PHP 8.1+)

Механизам за кооперативну вишезадачност:

```php
$fiber = new Fiber(function (): void {
    $value = Fiber::suspend("pause");
    echo $value;
});
$fiber->start();
```

Омогућава привремено "паузирање" извршавања и каснији повратак.

---

## 🧷 WeakReference

Креира слабу референцу на објекат:

```php
$ref = WeakReference::create($obj);
$obj = null;
$ref->get(); // null — објекат је уништен
```

Користи се у кешевима и GC-осетљивим структурама.

---

## 🗺 WeakMap

Асоцијативни низ са објектима-кључевима. Када се објекат уништи — брише се и кључ.

```php
$map = new WeakMap();
$map[$obj] = "cached";
```

---

## 🧵 Stringable (PHP 8.0+)

Маркер-интерфејс. Ако класа имплементира `__toString()`, она аутоматски `implements Stringable`.

Може се експлицитно навести:

```php
class MyClass implements Stringable {
    public function __toString(): string { ... }
}
```

---

## 🔠 UnitEnum / BackedEnum (PHP 8.1+)

- `UnitEnum` — базни интерфејс за све енумерације.
- `BackedEnum` — за енумерације са везаном вредношћу (int|string):

```php
enum Status: string {
    case Active = 'active';
    case Disabled = 'disabled';
}
```

Омогућавају коришћење `->value`, `from()`, `tryFrom()` и `cases()`.

---

Ови интерфејси су основа многих уграђених и сторонских библиотека. Разумевање и способност њихове примене у пракси издваја сигурног програмера.

---

# 📐 PSR — PHP Standards Recommendations

**PSR** (PHP Standards Recommendations) је скуп стандарда које је развила група [PHP-FIG (Framework Interop Group)](https://www.php-fig.org/), како би унифицирала приступе развоју, учинила код читљивијим, предвидљивијим и компатибилнијим између фрејмворка и библиотека.

---

## ❓ Зашто су потребни PSR?

- 🧩 Олакшавају интеграцију сторонских библиотека.
- 🤝 Повећавају компатибилност између фрејмворка.
- 🧼 Стандардизују стил и архитектуру.
- 📦 Подржани су у Composer-у и IDE.

---

## 📋 Основни PSR стандарди

### 🎨 PSR-1: Основни стил кода
- Стандардизује имена класа, фајлова, метода.
- Обавезан `<?php` таг.
- Називи класа — у `StudlyCaps`, метода — у `camelCase`.

### 🧼 PSR-12: Проширени стил кода (наследник PSR-2)
- Правила форматирања: увлачења, размаци, витичасте заграде.
- Једна класа по фајлу, обавезан declare(strict_types=1).

### 📦 PSR-4: Аутоучитавање
- Једноставна шема аутоучитавања на основу namespace + путања.
```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

### 📝 PSR-3: Интерфејс логовања
- Дефинише универзални `LoggerInterface` са методама: `debug()`, `info()`, `error()` и др.

### 🌐 PSR-7: HTTP-поруке
- Интерфејси `RequestInterface`, `ResponseInterface`, `StreamInterface` и др.
- Користи се у фрејмворцима (Slim, Symfony HTTPFoundation).

### ⚙️ PSR-11: Контејнер зависности
- Интерфејс `ContainerInterface` са методама `get()` и `has()`.

### 🔀 PSR-13: Везе на објекте (Hypermedia Links)
- Интерфејс `LinkInterface`, за REST/HATEOAS-везе.

### 📚 PSR-14: Догађаји
- Event Dispatcher интерфејси: `EventDispatcherInterface`, `StoppableEventInterface`.

### 🧵 PSR-15: Middleware
- Дефинише интерфејсе за HTTP middleware (обрада захтева слојевима).

### 🧪 PSR-17: HTTP Factory
- Factory-интерфејси за креирање објеката PSR-7 (`RequestFactoryInterface`, и др.)

### 🧰 PSR-18: HTTP Client
- Стандард за HTTP-клијенте (`sendRequest()` и др.).

---

## ✅ Како користити PSR

- Повежи само потребне интерфејсе преко Composer-а.
- Није обавезно користити цео стек.
- Компатибилан је са било којом архитектуром.

---

## ⚙️ SPL и аутоучитавање

### `spl_autoload_register()`

Омогућава дефинисање сопствене функције за аутоучитавање класа (алтернатива Composer autoload):

```php
spl_autoload_register(function ($class) {
    include 'src/' . $class . '.php';
});
```

- 📌 Користи се у сопственим фрејмворцима, старим пројектима или системима без Composer-а.
- Подржава стек аутоучитавача — може се регистровати више функција.

---

# 📦 Composer — Менаџер зависности за PHP

**Composer** је стандардни алат за управљање зависностима и аутоучитавањем у PHP. Омогућава повезивање сторонских библиотека, управљање верзијама и конфигурацијом пројекта.

---

## 🛠 Основне команде

### 📥 Инсталација пакета

```bash
composer require vendor/package
```

Додаје зависност и одмах ажурира `composer.json` и `composer.lock`.

### 🧹 Уклањање пакета

```bash
composer remove vendor/package
```

Уклања пакет и чисти зависности.

### ♻️ Ажурирање свих зависности

```bash
composer update
```

Ажурира **све зависности** до најновијих дозвољених верзија према `composer.json`.

### 🎯 Ажурирање једног пакета

```bash
composer update vendor/package
```

---

## 📁 Структура фајлова

### `composer.json`

Фајл конфигурације пројекта:

- Листа зависности и њихових верзија.
- Шема аутоучитавања (PSR-4).
- Метаподаци пакета (назив, аутори, лиценца итд.)

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

- **Фиксира тачне верзије** свих инсталираних зависности и подзависности.
- Користи се за репродуктивност (`CI/CD`, деплој).
- **Потребан у пројекту!** Треба га **комитовати**, ако је пројекат — апликација (а не библиотека).

📌 **Када не комитовати `composer.lock`?**
- Када развијаш **библиотеку**, и не желиш да фиксираш зависности за друге.

---

## 📦 Креирање сопственог Composer-пакета

1. Креирај нови репозиторијум са `composer.json`:

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

3. Структура пројекта:

```
mypackage/
├── src/
│   └── Helper.php
├── composer.json
```

4. 📤 Објави на GitHub и додај верзију са git-тагом:

```bash
git tag v1.0.0
git push origin v1.0.0
```

5. 📢 Региструј на [Packagist](https://packagist.org/)

---

## 📍 Локално повезивање сопственог пакета (без Packagist-а)

У пројекту где желиш да користиш пакет:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/vendorname/mypackage"
    }
  ],
  "require": {
    "vendorname/mypackage": "dev-main"
  }
}
```

---

## 🧪 Корисне команде

- `composer validate` — провера `composer.json`
- `composer outdated` — листа застарелих зависности
- `composer dump-autoload` — поновно креирање аутоучитавања

---

## ✅ Препоруке

- Комитуј `composer.lock` у свим пројектима, осим библиотека.
- Инсталирај зависности преко `composer require`, а не ручно у JSON.
- Користи `^` и `~` за контролу верзија (`^2.0` — било који `2.x`, `~1.2` — `1.2.*`).

---
