# 📘 dev-handbook (на русском)

> Личный справочник разработчика. Тут я постарался маимально кратко, но понятно собрать часть необходимой для хорошей жизни IT специалиста теории. примеры выполнены на ЯП PHP и Go.

---

## 🧠 ООП (Объектно-Ориентированное Программирование)

**Инкапсуляция**  
Упаковка данных и функций в одну единицу — объект.  
Реализуется через модификаторы доступа (`public`, `protected`, `private`).  
Позволяет скрыть внутреннее устройство объекта, защитив данные от внешнего вмешательства.

**Наследование**  
Механизм, позволяющий одному классу унаследовать свойства и методы другого.  
Обеспечивает повторное использование кода и расширяемость.  
Позволяет строить иерархии: `Кошка` наследуется от `Животное`.

**Полиморфизм**  
Возможность использовать один и тот же интерфейс для разных типов объектов.  
Позволяет переопределять методы в наследниках, сохраняя общий интерфейс.  
Пример: `draw()` работает по-разному у `Круга` и `Квадрата`, но вызывается одинаково.

**Абстракция**  
Процесс сокрытия деталей реализации и отображения только необходимых интерфейсов.  
Позволяет сфокусироваться на «что делает объект», а не «как».

---

## 🔠 SOLID — 5 принципов ООП-дизайна

**S — Single Responsibility Principle (Принцип единственной ответственности)**  
Класс должен отвечать только за одну часть функциональности.  
Пример: класс `UserManager` не должен заниматься логированием или отправкой писем.

**O — Open/Closed Principle (Принцип открытости/закрытости)**  
Сущности открыты для расширения, но закрыты для изменения.  
Поведение расширяется через наследование, композицию, стратегии.  
Пример: вместо изменения класса `PaymentService` — добавляем новый `StripePayment`.

**L — Liskov Substitution Principle (Принцип подстановки Лисков)**  
Объекты базового класса должны быть заменяемыми на объекты подкласса без нарушения логики.  
Пример: если `Bird` умеет `fly()`, но `Penguin` — нет, значит `Penguin` не должен наследовать `Bird`.

**I — Interface Segregation Principle (Принцип разделения интерфейса)**  
Лучше несколько специализированных интерфейсов, чем один универсальный.  
Клиент не должен зависеть от методов, которые он не использует.

**D — Dependency Inversion Principle (Принцип инверсии зависимостей)**  
Зависимость от абстракций, а не от конкретных реализаций.  
Реализация достигается через Dependency Injection (внедрение зависимостей).

---

## ⚙ GRASP — Паттерны распределения ответственности

**Information Expert (Информационный эксперт)**  
Отдавай ответственность объекту, у которого есть нужные данные.  
Снижает связанность и дублирование кода.

**Creator (Создатель)**  
Объект создаёт другой, если:
- содержит или агрегирует его;
- использует его;
- управляет его жизненным циклом;
- знает, как его инициализировать.

**Controller (Контроллер)**  
Посредник между пользовательским вводом и моделью.  
Обрабатывает события и делегирует работу нужным объектам.  
Пример: контроллер в MVC.

**Low Coupling (Слабая связанность)**  
Минимизируем зависимости между объектами.  
Облегчает повторное использование и тестирование.

**High Cohesion (Высокая связность)**  
Объект имеет одну чёткую задачу и не делает лишнего.  
Облегчает понимание, поддержку и изменение кода.

**Pure Fabrication (Чистая выдумка)**  
Искусственно созданный класс ради архитектурной цели.  
Пример: `Service`, `Repository` — не из предметной области, но полезны.

**Indirection (Посредник)**  
Вводим прослойку между объектами, чтобы снизить связанность.  
Пример: `EventBus`, `Router`, `Dispatcher`.

**Polymorphism**  
Позволяет обрабатывать объекты с одинаковым интерфейсом по-разному.  
Например, `Formatter` может иметь реализации `HtmlFormatter`, `JsonFormatter`.

**Protected Variations (Устойчивость к изменениям)**  
Изолируем потенциально нестабильные участки кода за абстракциями.  
Пример: работа с внешним API через интерфейс/адаптер.

---

# 🎯 Паттерны проектирования

> Классификация и краткие реализации на **PHP 8** и **Go** (где возможно)  
> Разделено на три группы: порождающие, структурные и поведенческие.

---

## 🧬 Порождающие паттерны

### 🏭 Фабричный метод (Factory Method)

Создаёт объект через общий интерфейс, делегируя создание подклассам.

**PHP 8**
```php
interface Product {
    public function do(): string;
}

class A implements Product {
    public function do(): string { return 'A'; }
}

class Factory {
    public static function create(string $type): Product {
        return match($type) {
            'a' => new A(),
            default => throw new Exception('Unknown type'),
        };
    }
}
```

**Go**
```go
type Product interface {
    Do() string
}

type A struct{}

func (a A) Do() string { return "A" }

func Create(t string) Product {
    if t == "a" {
        return A{}
    }
    panic("unknown type")
}
```

---

### 🧪 Абстрактная фабрика (Abstract Factory)

Создаёт семейства связанных объектов через набор интерфейсов.

**PHP 8**
```php
interface Button { public function render(): string; }
class DarkButton implements Button {
    public function render(): string { return "<button>Dark</button>"; }
}

interface UIFactory { public function createButton(): Button; }

class DarkFactory implements UIFactory {
    public function createButton(): Button { return new DarkButton(); }
}
```

**Go**
```go
type Button interface {
    Render() string
}

type DarkButton struct{}

func (DarkButton) Render() string { return "<button>Dark</button>" }

type UIFactory interface {
    CreateButton() Button
}

type DarkFactory struct{}

func (DarkFactory) CreateButton() Button { return DarkButton{} }
```

---

### 🧱 Строитель (Builder)

Создаёт сложный объект пошагово.

**PHP 8**
```php
class Car {
    public function __construct(
        public string $engine = "",
        public string $color = ""
    ) {}
}

class CarBuilder {
    private Car $car;
    public function __construct() { $this->car = new Car(); }
    public function setEngine(string $e): self {
        $this->car->engine = $e; return $this;
    }
    public function build(): Car { return $this->car; }
}
```

**Go**
```go
type Car struct {
    Engine string
    Color  string
}

type CarBuilder struct {
    car Car
}

func (b *CarBuilder) SetEngine(e string) *CarBuilder {
    b.car.Engine = e
    return b
}

func (b *CarBuilder) Build() Car {
    return b.car
}
```

---

### 🧬 Прототип (Prototype)

Копирует объект через интерфейс `clone`.

**PHP 8**
```php
class Sheep {
    public function __construct(public string $name) {}
    public function __clone() {}
}

$s1 = new Sheep("Dolly");
$s2 = clone $s1;
```

**Go** (ручное копирование)
```go
type Sheep struct {
    Name string
}

s1 := Sheep{Name: "Dolly"}
s2 := s1 // копия
```

---

### 🔂 Одиночка (Singleton)

Гарантирует, что у класса только один экземпляр.

**PHP 8**
```php
class Logger {
    private static ?Logger $instance = null;
    private function __construct() {}
    public static function getInstance(): Logger {
        return self::$instance ??= new Logger();
    }
}
```

**Go**
```go
type singleton struct{}

var instance *singleton
var once sync.Once

func GetInstance() *singleton {
    once.Do(func() { instance = &singleton{} })
    return instance
}
```

---

## 🧱 Структурные паттерны

### 🔌 Адаптер (Adapter)

Позволяет объектам с несовместимыми интерфейсами работать вместе.

**PHP 8**
```php
class OldPrinter {
    public function printOld(): string {
        return "Old printing";
    }
}

interface NewPrinter {
    public function print(): string;
}

class Adapter implements NewPrinter {
    public function __construct(private OldPrinter $oldPrinter) {}

    public function print(): string {
        return $this->oldPrinter->printOld();
    }
}
```

**Go**
```go
type OldPrinter struct{}

func (OldPrinter) PrintOld() string {
    return "Old printing"
}

type NewPrinter interface {
    Print() string
}

type Adapter struct {
    old OldPrinter
}

func (a Adapter) Print() string {
    return a.old.PrintOld()
}
```

---

### 🌉 Мост (Bridge)

Разделяет абстракцию и реализацию, позволяя их изменять независимо.

**PHP 8**
```php
interface Renderer {
    public function render(string $text): string;
}

class HTMLRenderer implements Renderer {
    public function render(string $text): string {
        return "<p>$text</p>";
    }
}

class Message {
    public function __construct(private Renderer $renderer) {}
    public function show(string $content): string {
        return $this->renderer->render($content);
    }
}
```

**Go**
```go
type Renderer interface {
    Render(text string) string
}

type HTMLRenderer struct{}

func (HTMLRenderer) Render(text string) string {
    return "<p>" + text + "</p>"
}

type Message struct {
    Renderer Renderer
}

func (m Message) Show(content string) string {
    return m.Renderer.Render(content)
}
```

---

### 🧩 Компоновщик (Composite)

Создаёт древовидную структуру объектов и позволяет обращаться с ними единообразно.

**PHP 8**
```php
interface Component {
    public function render(): string;
}

class Leaf implements Component {
    public function render(): string {
        return "Leaf";
    }
}

class Composite implements Component {
    public function __construct(private array $children = []) {}

    public function render(): string {
        return implode(', ', array_map(fn($c) => $c->render(), $this->children));
    }
}
```

**Go**
```go
type Component interface {
    Render() string
}

type Leaf struct{}

func (Leaf) Render() string { return "Leaf" }

type Composite struct {
    Children []Component
}

func (c Composite) Render() string {
    var result string
    for _, child := range c.Children {
        result += child.Render() + ", "
    }
    return result
}
```

---

### 🎁 Декоратор (Decorator)

Динамически добавляет объекту новое поведение без изменения его структуры.

**PHP 8**
```php
interface Coffee {
    public function cost(): int;
}

class SimpleCoffee implements Coffee {
    public function cost(): int {
        return 5;
    }
}

class MilkDecorator implements Coffee {
    public function __construct(private Coffee $base) {}

    public function cost(): int {
        return $this->base->cost() + 2;
    }
}
```

**Go**
```go
type Coffee interface {
    Cost() int
}

type SimpleCoffee struct{}

func (SimpleCoffee) Cost() int { return 5 }

type MilkDecorator struct {
    Base Coffee
}

func (m MilkDecorator) Cost() int {
    return m.Base.Cost() + 2
}
```

---

### 📦 Фасад (Facade)

Предоставляет простой интерфейс к сложной системе.

**PHP 8**
```php
class CPU {
    public function start(): string { return "CPU start"; }
}
class Disk {
    public function read(): string { return "Disk read"; }
}
class Computer {
    public function __construct(
        private CPU $cpu,
        private Disk $disk
    ) {}
    public function boot(): string {
        return $this->cpu->start() . " + " . $this->disk->read();
    }
}
```

**Go**
```go
type CPU struct{}
func (CPU) Start() string { return "CPU start" }

type Disk struct{}
func (Disk) Read() string { return "Disk read" }

type Computer struct {
    CPU  CPU
    Disk Disk
}

func (c Computer) Boot() string {
    return c.CPU.Start() + " + " + c.Disk.Read()
}
```

---

### 🪶 Легковес (Flyweight)

Экономит память путём повторного использования одинаковых объектов.

**PHP 8**
```php
class Char {
    public function __construct(public string $symbol) {}
}

class CharFactory {
    private array $pool = [];
    public function get(string $s): Char {
        return $this->pool[$s] ??= new Char($s);
    }
}
```

**Go**
```go
type Char struct {
    Symbol string
}

type CharFactory struct {
    Pool map[string]Char
}

func (f *CharFactory) Get(s string) Char {
    if f.Pool == nil {
        f.Pool = make(map[string]Char)
    }
    if val, ok := f.Pool[s]; ok {
        return val
    }
    ch := Char{Symbol: s}
    f.Pool[s] = ch
    return ch
}
```

---

### 🕵 Заместитель (Proxy)

Объект-заменитель, контролирующий доступ к реальному объекту.

**PHP 8**
```php
interface Image {
    public function display(): string;
}

class RealImage implements Image {
    public function display(): string {
        return "Image shown";
    }
}

class ProxyImage implements Image {
    private ?RealImage $real = null;
    public function display(): string {
        $this->real ??= new RealImage();
        return $this->real->display();
    }
}
```

**Go**
```go
type Image interface {
    Display() string
}

type RealImage struct{}
func (RealImage) Display() string { return "Image shown" }

type ProxyImage struct {
    real *RealImage
}

func (p *ProxyImage) Display() string {
    if p.real == nil {
        p.real = &RealImage{}
    }
    return p.real.Display()
}
```

---

# 🔄 Поведенческие паттерны проектирования

> Поведенческие паттерны описывают взаимодействие между объектами.  

---

## 🔗 Цепочка обязанностей (Chain of Responsibility)

Передаёт запрос по цепочке обработчиков, пока кто-то не обработает.

**PHP 8**
```php
abstract class Handler {
    public function __construct(protected ?Handler $next = null) {}
    abstract public function handle(string $req): ?string;
}

class AuthHandler extends Handler {
    public function handle(string $req): ?string {
        return $req === "auth" ? "Auth OK" : $this->next?->handle($req);
    }
}
```

**Go**
```go
type Handler interface {
    Handle(req string) string
}

type AuthHandler struct {
    Next Handler
}

func (h AuthHandler) Handle(req string) string {
    if req == "auth" {
        return "Auth OK"
    }
    if h.Next != nil {
        return h.Next.Handle(req)
    }
    return "Not handled"
}
```

---

## 🧾 Команда (Command)

Оборачивает запрос в объект, позволяя отложить выполнение.

**PHP 8**
```php
interface Command {
    public function execute(): string;
}

class HelloCommand implements Command {
    public function execute(): string { return "Hello"; }
}
```

**Go**
```go
type Command interface {
    Execute() string
}

type HelloCommand struct{}

func (HelloCommand) Execute() string { return "Hello" }
```

---

## 🔁 Итератор (Iterator)

Позволяет поэтапно обходить элементы коллекции.

**PHP 8**
```php
class MyCollection implements IteratorAggregate {
    public function __construct(private array $items) {}
    public function getIterator(): Traversable {
        return new ArrayIterator($this->items);
    }
}
```

**Go**
```go
items := []string{"a", "b", "c"}
for _, item := range items {
    fmt.Println(item)
}
```

---

## 🤝 Посредник (Mediator)

Уменьшает связанность объектов, вынося взаимодействие в отдельный объект.

**PHP 8**
```php
class Mediator {
    public function notify(object $sender, string $event): void {
        echo "Event $event triggered";
    }
}
```

**Go**
```go
type Mediator struct{}

func (Mediator) Notify(event string) {
    fmt.Println("Event", event)
}
```

---

## 🧠 Снимок (Memento)

Сохраняет и восстанавливает состояние объекта.

**PHP 8**
```php
class Editor {
    public string $text = '';
    public function save(): string {
        return $this->text;
    }
    public function restore(string $snapshot): void {
        $this->text = $snapshot;
    }
}
```

**Go**
```go
type Editor struct {
    Text string
}

func (e *Editor) Save() string {
    return e.Text
}

func (e *Editor) Restore(snap string) {
    e.Text = snap
}
```

---

## 👀 Наблюдатель (Observer)

Оповещает подписчиков об изменениях.

**PHP 8**
```php
class Event {
    private array $subs = [];
    public function subscribe(callable $cb): void {
        $this->subs[] = $cb;
    }
    public function fire(): void {
        foreach ($this->subs as $cb) $cb();
    }
}
```

**Go**
```go
type Event struct {
    Subscribers []func()
}

func (e *Event) Subscribe(cb func()) {
    e.Subscribers = append(e.Subscribers, cb)
}

func (e Event) Fire() {
    for _, cb := range e.Subscribers {
        cb()
    }
}
```

---

## 🔄 Состояние (State)

Меняет поведение объекта в зависимости от его текущего состояния.

**PHP 8**
```php
interface State { public function act(): string; }

class Happy implements State {
    public function act(): string { return "😊"; }
}

class Person {
    public function __construct(private State $state) {}
    public function behave(): string {
        return $this->state->act();
    }
}
```

**Go**
```go
type State interface {
    Act() string
}

type Happy struct{}

func (Happy) Act() string { return "😊" }

type Person struct {
    State State
}

func (p Person) Behave() string {
    return p.State.Act()
}
```

---

## 🧠 Стратегия (Strategy)

Инкапсулирует семейство алгоритмов и делает их взаимозаменяемыми.

**PHP 8**
```php
interface SortStrategy { public function sort(array $data): array; }

class QuickSort implements SortStrategy {
    public function sort(array $data): array {
        sort($data); return $data;
    }
}
```

**Go**
```go
type SortStrategy interface {
    Sort(data []int) []int
}

type QuickSort struct{}

func (QuickSort) Sort(data []int) []int {
    sort.Ints(data); return data
}
```

---

## 🧱 Шаблонный метод (Template Method)

Определяет алгоритм, оставляя некоторые шаги подклассам.

**PHP 8**
```php
abstract class Game {
    public function play(): string {
        return $this->start() . " - " . $this->end();
    }
    abstract protected function start(): string;
    abstract protected function end(): string;
}
```

**Go**
```go
type Game interface {
    Start() string
    End() string
}

func Play(g Game) string {
    return g.Start() + " - " + g.End()
}
```

---

## 👣 Посетитель (Visitor)

Позволяет добавлять поведение, не изменяя классы.

**PHP 8**
```php
interface Element {
    public function accept(Visitor $v): string;
}

interface Visitor {
    public function visit(Element $e): string;
}
```

**Go**
```go
type Element interface {
    Accept(v Visitor) string
}

type Visitor interface {
    Visit(e Element) string
}
```

---

# 📁 Транзакции, уровни изоляции и коллизии в СУБД

## ACID

**ACID** — это четыре фундаментальных свойства транзакций:

- **Atomicity (Атомарность)**
  > Транзакция либо выполняется полностью, либо не выполняется вовсе. Если произошла ошибка на любом этапе, происходит **откат (rollback)**.

- **Consistency (Согласованность)**
  > После завершения транзакции база данных должна быть в **согласованном состоянии**: все ограничения соблюдены, данные валидны. Успешная транзакция **не нарушает** логическую целостность.

- **Isolation (Изоляция)**
  > Параллельные транзакции **не должны влиять друг на друга**. Конфликтные операции изолируются. В реальности достигается **через уровни изоляции** (см. ниже).

- **Durability (Устойчивость)**
  > После подтверждения (COMMIT), изменения сохраняются **навсегда**, даже в случае сбоев питания или отказов оборудования.


## 🏰 Уровни изоляции (Isolation Levels)

| Уровень            | Что позволяет                                | Уязвимость                  |
|--------------------|-----------------------------------------------|-----------------------------|
| **READ UNCOMMITTED** | Видны даже **неподтверждённые** изменения     | Dirty Read                 |
| **READ COMMITTED**   | Видны только **подтверждённые** изменения     | Non-repeatable Read        |
| **REPEATABLE READ** *(InnoDB по умолчанию)* | Повторный SELECT в рамках транзакции возвращает то же* | Phantom Read              |
| **SERIALIZABLE**     | Полная изоляция, все транзакции как бы **последовательны** | Снижение параллелизма     |

### Примеры:
```sql
SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
START TRANSACTION;
SELECT * FROM users WHERE status = 'active';
COMMIT;
```


## 📂 Потенциальные коллизии и ошибки

- **Deadlock (взаимоблокировка)**  
  T1 блокирует строку, которую ждёт T2. T2 блокирует ресурс, который ждёт T1. MySQL **автоматически завершает одну из них**.
  ✅ Анализ через: `SHOW ENGINE INNODB STATUS`

- **Phantom Read**  
  `SELECT COUNT(*)` возвращает 100. Пока транзакция активна, другая добавляет строку. Повторный `SELECT` — уже 101.

- **Non-repeatable Read**  
  Прочитали строку, затем кто-то другой обновил её. Повторное чтение — уже другой результат.


## 🏦 MySQL (MariaDB) — InnoDB, транзакции и блокировки

### 🛠 InnoDB (основной движок хранения)

- Поддерживает **транзакции**, **изоляцию**, **автоматический recovery**, **foreign keys**, **MVCC**.
- Хранит данные в **кластерных индексах**, логирует изменения в redo/undo логах.
- По умолчанию использует уровень **REPEATABLE READ**.

### 🔒 Блокировки InnoDB:

- **Shared (S)** — чтение разрешено. Несколько транзакций могут одновременно брать `S` на одну строку.
- **Exclusive (X)** — запись. Только одна транзакция может удерживать `X`.

#### Примеры:
```sql
SELECT * FROM accounts WHERE id = 1 FOR UPDATE; -- блокировка X (exclusive)
SELECT * FROM products WHERE id = 5 LOCK IN SHARE MODE; -- блокировка S (shared)
```

### ⚡ Пример Deadlock:
```sql
-- T1
START TRANSACTION;
UPDATE products SET stock = stock - 1 WHERE id = 1;
-- ждёт UPDATE другой строки...

-- T2
START TRANSACTION;
UPDATE products SET stock = stock - 2 WHERE id = 2;
UPDATE products SET stock = stock - 1 WHERE id = 1; -- ждёт T1
```

MySQL обнаружит deadlock и завершит одну из транзакций.


### ✅ Рекомендации для разработчика:
- Используй **BEGIN / COMMIT / ROLLBACK** при работе с более чем одной таблицей.
- Для блокировки строки при изменениях — `SELECT ... FOR UPDATE`
- Добавляй индексы к внешним ключам (`FOREIGN KEY`) — иначе `JOIN`'ы будут медленными.
- Перед внедрением сложных `JOIN`, `ORDER BY`, `GROUP BY` — запускай `EXPLAIN`

---

## 🔷 MyISAM (альтернатива InnoDB, устаревшая)

- Не поддерживает транзакции!
- **Табличные блокировки** вместо строчных.
- Хорошо работает для **чтения**, но плохо масштабируется на запись.
- Не поддерживает `FOREIGN KEY`.

Используется редко. Рекомендуется избегать в новых системах.


---

## 🔷 PostgreSQL

- Поддерживает **все уровни изоляции** (по умолчанию: **READ COMMITTED**).
- Транзакции строго соответствуют ACID.
- Использует собственную реализацию **MVCC**, не требует блокировок на чтение.
- Хорошо управляет deadlock'ами, предоставляет `pg_stat_activity`, `pg_locks`.
- Поддержка `SAVEPOINT`, `ROLLBACK TO`, `SET TRANSACTION ISOLATION LEVEL`

#### Пример транзакции:
```sql
BEGIN;
UPDATE users SET balance = balance - 100 WHERE id = 1;
UPDATE users SET balance = balance + 100 WHERE id = 2;
COMMIT;
```

---

# 📊 Индексы и анализ SQL-запросов (`EXPLAIN`)

---

## 📙 Что такое индекс

Индекс в СУБД — это **структура данных, которая ускоряет поиск**, фильтрацию и сортировку строк в таблице. По сути, это **отсортированная структура**, позволяющая быстро находить нужные значения **без сканирования всей таблицы**.

### ✅ Общие понятия

- **Простой индекс**: один столбец
- **Составной (композитный)**: несколько столбцов, порядок **имеет значение!**
- **Покрывающий (covering index)**: содержит **все столбцы**, нужные запросу (чтение происходит только из индекса)
- **Кластерный индекс**: содержит **реальные строки** в листьях дерева (InnoDB)

### 🔍 Где помогают индексы:
- `WHERE column = value`
- `JOIN ON column`
- `ORDER BY indexed_column`
- `GROUP BY indexed_column`
- `DISTINCT indexed_column`

### ❌ Где индексы не работают:

| Плохой паттерн              | Почему плохо                                      |
|-----------------------------|---------------------------------------------------|
| `LEFT(name, 3) = 'abc'`     | Функция нарушает порядок → **не работает индекс** |
| `status + 1 = 2`            | Арифметика отключает индекс                      |
| `OR` между колонками        | Часто приводит к `FULL SCAN`                     |
| `LIKE '%abc'`               | Нет префикса → индекс не применим               |
| `ORDER BY non_indexed_col` | Сортировка в памяти, `Using filesort`            |


---

## 🔹 Типы индексов

| Тип         | Назначение |
|--------------|------------|
| `PRIMARY`    | Кластерный индекс InnoDB (по `id`) |
| `UNIQUE`     | Логическая уникальность (`email`, `username`) |
| `INDEX`      | Обычный индекс для ускорения поиска |
| `COMPOSITE`  | Несколько колонок (`(a, b)`) |
| `COVERING`   | Индекс, покрывающий все столбцы SELECT |
| `FULLTEXT`   | Поиск по тексту (`MATCH ... AGAINST`) |
| `SPATIAL`    | Гео-данные (`POINT`, `POLYGON`) |

### Примеры:
```sql
-- Индекс по email
CREATE INDEX idx_users_email ON users(email);

-- Композитный индекс
CREATE INDEX idx_status_created ON orders(status, created_at);
```

---

## 🔧 Структуры данных индексов

### 🔹 B-Tree / B+Tree (MySQL InnoDB, PostgreSQL)
- Основной тип для большинства индексов
- Упорядоченная структура → **быстрый бинарный поиск**

### 🔹 Hash
- Неупорядоченный (MySQL: MEMORY engine)
- Подходит только для **равенства `=`**
- Не работает с диапазонами, сортировкой, `LIKE`

### 🔹 FULLTEXT
- Только в MyISAM/InnoDB
- Работает с `MATCH() AGAINST()`
- Хорош для полнотекстового поиска, но не заменяет LIKE полностью

### 🔹 SPATIAL
- Для геометрии: `POINT`, `LINE`, `POLYGON`
- Используется в GIS-сценариях

---

## 📈 `EXPLAIN` и анализ SQL-запросов

Команда `EXPLAIN` показывает **как будет выполняться запрос**: какие индексы используются, сколько строк проверяется и есть ли временные таблицы.

### Пример:
```sql
EXPLAIN SELECT * FROM users WHERE email = 'user@example.com';
```

### Поля `EXPLAIN`:

| Поле           | Значение |
|----------------|----------|
| `id`           | Номер запроса или подзапроса |
| `select_type`  | Тип запроса (`SIMPLE`, `PRIMARY`, `DERIVED` и т.д.) |
| `type`         | Тип доступа (лучшее: `const`, `ref`, `range`; худшее: `ALL`) |
| `possible_keys`| Какие индексы могли быть использованы |
| `key`          | Какой индекс реально используется |
| `rows`         | Примерное число строк для проверки |
| `Extra`        | Доп. информация (`Using filesort`, `Using temporary` и др.) |

### Диагностика:
- **`ALL`** — полный перебор (full scan)
- **`Using filesort`** — сортировка в памяти (медленно)
- **`Using temporary`** — создание временной таблицы

#### Пример:
```sql
EXPLAIN SELECT * FROM orders WHERE status = 'paid' ORDER BY created_at DESC LIMIT 10;
```
Если **нет индекса по `(status, created_at)`** → будет `filesort`.  
Создай индекс:
```sql
CREATE INDEX idx_status_created ON orders(status, created_at);
```

---

## 🗄 Особенности MySQL (InnoDB)

- Кластерный индекс = структура хранения таблицы. `PRIMARY KEY` → физический порядок строк.
- Если нет PK или UNIQUE, создаётся **скрытый суррогатный ключ** (`row_id`).
- Каждый вторичный индекс содержит **ссылку на кластерный (PK)**.
- MySQL использует **B+Tree** почти для всех типов индексов.
- Можно использовать **`USE INDEX`**, `FORCE INDEX` для указания нужного.

---

## 🗄 Особенности PostgreSQL

- Каждый индекс — **отдельная структура** (не кластеризован по умолчанию)
- `btree` — по умолчанию
- Поддержка **многоколоночных и частичных индексов**
- Есть **GIN**, **GiST**, **BRIN**, **HASH**, **SP-GiST**:
  - **GIN** — быстрый поиск в `jsonb`, `array`, `tsvector`
  - **BRIN** — эффективен на больших таблицах с упорядоченными по диапазону данными

### Пример частичного индекса:
```sql
CREATE INDEX idx_active_users ON users(email) WHERE active = true;
```

### Анализ запроса:
```sql
EXPLAIN ANALYZE SELECT * FROM users WHERE active = true;
```

- `EXPLAIN` — оценка плана
- `EXPLAIN ANALYZE` — **фактическое выполнение** с временем выполнения

---

# 📊 SQL: Группировка, JOIN'ы и приёмы оптимизации сложных запросов

---

## 📃 Группировка и агрегирование

SQL позволяет выполнять **агрегацию данных** по группам строк с помощью конструкции `GROUP BY` и агрегатных функций.

### 📄 Стандартная форма:
```sql
SELECT col1, SUM(col2) AS total
FROM tableName
WHERE condition
GROUP BY col1
HAVING total > 100;
```

| Элемент         | Назначение |
|-----------------|------------|
| `SUM(col2)`     | Агрегатная функция: сумма значений `col2` |
| `FROM`          | Источник данных, может быть `JOIN` |
| `WHERE`         | Фильтр до группировки |
| `GROUP BY`      | Группировка по столбцам |
| `HAVING`        | Фильтр после агрегации |

---

### 🔗 Расширенные техники группировки

#### `ROLLUP`
Добавляет **промежуточные итоговые строки**:
```sql
SELECT department, team, SUM(salary)
FROM employees
GROUP BY department, team WITH ROLLUP;
```
Получим итоги по каждой команде, департаменту и общий итог.

#### `CUBE`
Добавляет **все возможные комбинации** группировок:
```sql
SELECT region, product, SUM(sales)
FROM sales_data
GROUP BY CUBE(region, product);
```

#### `GROUPING SETS`
Гибкий контроль над группами:
```sql
SELECT manufacturer, product_count, SUM(price)
FROM products
GROUP BY GROUPING SETS (
  ROLLUP(manufacturer),
  (product_count),
  (manufacturer, product_count)
);
```

#### `OVER()` и оконные агрегатные функции
Позволяет **агрегировать**, но **оставить строки неизменными**:
```sql
SELECT user_id, region, salary,
       SUM(salary) OVER (PARTITION BY region) AS regional_total
FROM employees;
```
> Используется в PostgreSQL, MySQL 8+, SQLite, SQL Server, Oracle

---

## 🔀 JOIN — соединение таблиц

Соединения позволяют **объединять строки из двух или более таблиц** на основе логического условия.

### 📋 Типы JOIN:

| JOIN | Назначение | Пример |
|------|------------|--------|
| `INNER JOIN` | Только совпадающие строки | `users INNER JOIN orders ON users.id = orders.user_id` |
| `LEFT JOIN`  | Все из левой + совпадающие из правой | `users LEFT JOIN orders` — даже те, у кого нет заказов |
| `RIGHT JOIN` | Все из правой + совпадающие из левой | Почти не используется в MySQL, лучше поменять порядок |
| `FULL OUTER JOIN` | Все строки из обеих таблиц | Не поддерживается в MySQL напрямую, но есть в PostgreSQL |
| `CROSS JOIN` | Декартово произведение (все комбинации) | Используется редко и с осторожностью |
| `SELF JOIN` | Таблица соединяется сама с собой | `employees e1 JOIN employees e2 ON e1.manager_id = e2.id` |

### 🔍 Примеры:
```sql
-- Пользователи без заказов
SELECT u.*
FROM users u
LEFT JOIN orders o ON u.id = o.user_id
WHERE o.id IS NULL;

-- Последний заказ каждого клиента (с оконной функцией)
SELECT *
FROM (
  SELECT *,
         ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY created_at DESC) as rn
  FROM orders
) t
WHERE rn = 1;
```

---

## 🧠 Полезные приёмы построения сложных запросов

### 1. ❌ Замена `OR` — на `UNION`

```sql
-- Плохо для индексов:
SELECT * FROM users
WHERE status = 'active' OR email LIKE '%@example.com';

-- Лучше:
SELECT * FROM users WHERE status = 'active'
UNION
SELECT * FROM users WHERE email LIKE '%@example.com';
```
> Почему: `OR` нарушает использование индексов, `UNION` позволяет выполнять каждый SELECT независимо и использовать соответствующие индексы.

### 2. ✅ Анти-`JOIN`: найти записи без связи
```sql
SELECT p.*
FROM products p
LEFT JOIN orders o ON p.id = o.product_id
WHERE o.id IS NULL;
```

### 3. 👤 `EXISTS` вместо `IN`
```sql
-- Вместо
SELECT * FROM users WHERE id IN (SELECT user_id FROM orders);

-- Лучше
SELECT * FROM users WHERE EXISTS (
  SELECT 1 FROM orders WHERE orders.user_id = users.id
);
```

### 4. 🌐 `WITH` (CTE) для улучшения читаемости
```sql
WITH top_customers AS (
  SELECT user_id, COUNT(*) as order_count
  FROM orders
  GROUP BY user_id
  HAVING COUNT(*) > 10
)
SELECT u.* FROM users u
JOIN top_customers t ON u.id = t.user_id;
```

---

# 📂 Партицирование, репликация и шардинг в СУБД

---

## 🌐 Основные понятия

### 🔄 Партицирование (Partitioning)
> Разделение **одной таблицы** на логические или физические части (partition'ы) по определённому правилу. Это позволяет ускорить запросы, упростить удаление старых данных и улучшить масштабируемость **в пределах одного сервера**.

- Работает **внутри одной таблицы**
- Партиции можно **автоматически выбирать в запросах**
- Используется для **высоких объёмов** данных: логов, телеметрии, историй операций

#### 🔹 Виды партицирования:
| Вид             | Описание |
|------------------|----------|
| **Range**        | По диапазону значений (`date < '2023-01-01'`) |
| **List**         | По значению в списке (`region IN ('US', 'EU')`) |
| **Hash**         | По хешу значения (`user_id % 4`) |
| **Key (MySQL)**  | Специальная форма hash с автооптимизацией |
| **Composite**    | Комбинация двух стратегий (например: Range + Hash) |

#### 📚 Пример в **MySQL**:
```sql
CREATE TABLE logs (
  id INT,
  created_at DATE
)
PARTITION BY RANGE (YEAR(created_at)) (
  PARTITION p2022 VALUES LESS THAN (2023),
  PARTITION p2023 VALUES LESS THAN (2024)
);
```

#### 📚 Пример в **PostgreSQL**:
```sql
CREATE TABLE sales (
  id INT,
  region TEXT,
  sale_date DATE
) PARTITION BY LIST (region);

CREATE TABLE sales_us PARTITION OF sales FOR VALUES IN ('US');
CREATE TABLE sales_eu PARTITION OF sales FOR VALUES IN ('EU');
```

> PostgreSQL поддерживает декларативное партицирование с версий 10+

---

## 💠 Репликация (Replication)
> Автоматическое **копирование данных** с одного сервера (primary/master) на другой (secondary/replica).

### Зачем нужна:
- Повышение отказоустойчивости (HA)
- Разделение нагрузки: чтение с реплик, запись в мастер
- Создание бэкапов без нагрузки на продакшн

### Виды репликации:

| Тип              | Особенности |
|------------------|-------------|
| **Асинхронная**   | Мастер не ждёт реплику → могут быть задержки |
| **Полусинхронная**| Мастер ждёт хотя бы 1 подтверждение от реплики |
| **Синхронная**    | Мастер ждёт все реплики → высокая целостность, но ниже производительность |

#### 📚 Пример настройки в **MySQL** (GTID-based):
```sql
-- На мастере
SET GLOBAL gtid_mode = ON;
SET GLOBAL enforce_gtid_consistency = ON;
SHOW MASTER STATUS;

-- На реплике
CHANGE MASTER TO MASTER_HOST='master_ip',
  MASTER_USER='repl',
  MASTER_PASSWORD='password',
  MASTER_AUTO_POSITION = 1;
START SLAVE;
```

#### 📚 Пример в **PostgreSQL** (Streaming replication):
```bash
# На мастере (postgresql.conf)
wal_level = replica
max_wal_senders = 5

# На реплике (recovery.conf / standby.signal)
primary_conninfo = 'host=master_ip user=replicator password=secret'
```

---

## 🚀 Шардинг (Sharding)
> Разделение **данных по разным физическим серверам** по какому-либо критерию.  
> Это **не встроено в SQL**, реализуется на уровне приложений или middleware.

### Зачем нужен:
- Горизонтальное масштабирование
- Обход ограничений размера и нагрузки одного сервера

### 🔹 Виды шардинга:

| Вид              | Принцип |
|------------------|---------|
| **Горизонтальный** | Данные разделяются **по строкам** (по user_id, регионам, дате) |
| **Вертикальный**   | Таблицы/колонки разделяются по разным БД (например: profile → DB1, logs → DB2) |

### Пример горизонтального шардинга:
```sql
-- users_0, users_1, users_2, users_3 по хешу user_id % 4
SELECT * FROM users_2 WHERE id = 123;
```
Выбор нужной таблицы делается **в приложении**, например:
```python
shard_id = user_id % 4
query = f"SELECT * FROM users_{shard_id} WHERE id = {user_id}"
```

### Популярные системы для шардинга:
- **Citus** (PostgreSQL расширение)
- **Vitess** (MySQL масштабирование)
- **ProxySQL**, **PgBouncer** + ручной роутинг
- **Custom sharding layer** (на уровне backend)

---

## 🔍 Сравнение подходов

| Подход        | Масштаб | Используется для | Уровень |
|---------------|---------|------------------|---------|
| Partitioning  | Один сервер | Повышение производительности, архивация | SQL/СУБД |
| Replication   | Несколько серверов | HA, масштаб чтения | SQL/СУБД |
| Sharding      | Несколько серверов | Масштаб записи, объём данных | Приложение/Proxy |

---

# 🗂️ Введение в NoSQL и MongoDB

---

## ☁️ Что такое NoSQL

**NoSQL (Not Only SQL)** — это общее название для баз данных, не основанных на реляционной модели. Они ориентированы на **гибкость хранения, горизонтальное масштабирование и высокую доступность**.

### 🔹 Ключевые черты NoSQL:
- Гибкая схема (или отсутствие схемы)
- Хорошо подходят для Big Data и реального времени
- Масштабируемость "вширь" (на новые серверы)
- Простота репликации и шардинга
- Разнообразие моделей данных

---

## 📚 Типы NoSQL-БД:

| Тип | Пример | Где применяется |
|-----|--------|-----------------|
| Документные | MongoDB, Couchbase | Web, API, CMS, аналитика |
| Колонковые | Cassandra, HBase | Хранилища телеметрии, логи |
| Ключ-значение | Redis, DynamoDB | Кэш, очереди, сессии |
| Графовые | Neo4j, ArangoDB | Социальные сети, связи между объектами |

---

## 🍃 MongoDB: Основы и архитектура

**MongoDB** — это самая популярная документно-ориентированная NoSQL-БД.  
Документ = JSON-структура (в Mongo — это BSON: Binary JSON).

### 🔸 Основные элементы:
- **Database** — база данных
- **Collection** — аналог таблицы
- **Document** — аналог строки (JSON/BSON)

```json
{
  "_id": ObjectId("...")
  "name": "Alice",
  "age": 30,
  "email": "alice@example.com",
  "tags": ["admin", "editor"]
}
```

### 🔸 Сильные стороны:
- Нет фиксированной схемы — документы могут отличаться
- Гибкость и вложенность (вложенные документы, массивы)
- Поддержка транзакций (начиная с MongoDB 4.0)
- Масштабируемость за счёт **шардинга**
- Репликация через **Replica Set**

---

## 🔧 CRUD в MongoDB

### 🟢 Create
```js
db.users.insertOne({ name: "Bob", age: 25 });
db.users.insertMany([...]);
```

### 🔵 Read
```js
db.users.find({ age: { $gt: 20 } });
db.users.findOne({ name: "Alice" });
```

### 🟡 Update
```js
db.users.updateOne(
  { name: "Bob" },
  { $set: { age: 26 } }
);
```

### 🔴 Delete
```js
db.users.deleteOne({ name: "Bob" });
db.users.deleteMany({ age: { $lt: 18 } });
```

---

## 🧠 Индексация

MongoDB поддерживает **различные виды индексов**:
- `db.collection.createIndex({ name: 1 })` — по возрастанию
- `db.collection.createIndex({ name: -1 })` — по убыванию
- Составные, уникальные, TTL, текстовые, геоиндексы

### Используй `explain()`:
```js
db.users.find({ name: "Alice" }).explain("executionStats")
```

---

## 🔀 Репликация и масштабирование

### 🔁 Replica Set
- Несколько узлов: Primary + Secondaries
- Автоматический failover
- Все записи идут в Primary, чтение — на выбор

### 🧩 Шардинг
- Деление коллекции на чанки по ключу (shard key)
- Увеличивает масштаб, снижает нагрузку на отдельные узлы

---

## 📦 Aggregation Framework

Mongo поддерживает мощный **pipeline для обработки данных**:
```js
db.orders.aggregate([
  { $match: { status: "paid" }},
  { $group: { _id: "$customer_id", total: { $sum: "$amount" }}},
  { $sort: { total: -1 }}
]);
```
- Можно фильтровать, группировать, сортировать, преобразовывать
- Подходит для отчетов и аналитики внутри Mongo

---

## 🔐 Безопасность и ограничения
- Авторизация (`role`, `user`) + TLS
- Ограничение по размеру документа: 16 MB
- Транзакции на уровне нескольких документов с версии 4.0
- Транзакции между коллекциями и базами — с 4.2+

---

## 📎 Когда использовать MongoDB?

✅ Отлично подходит:
- Гибкие данные, без строгой схемы
- Быстрая разработка API
- Сложные вложенные структуры
- Большой объём данных с шардированием

⛔ Не лучший выбор:
- Сложные SQL-запросы и join’ы
- Сильная согласованность ACID для всех операций
- Ограничения схемы и строгие типы

---
