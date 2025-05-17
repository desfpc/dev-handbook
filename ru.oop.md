[📚 Содержание](README.ru.md)

### 🧠 [ООП и принципы проектирования](#-ооп-и-принципы-проектирования)
- [ООП (Объектно-Ориентированное Программирование)](#-ооп-объектно-ориентированное-программирование)
- [SOLID — 5 принципов ООП-дизайна](#-solid--5-принципов-ооп-дизайна)
- [GRASP — Паттерны распределения ответственности](#-grasp--паттерны-распределения-ответственности)

### 🎯 [Паттерны проектирования](#-паттерны-проектирования)
- [Порождающие паттерны](#-порождающие-паттерны)
  - Фабричный метод, Абстрактная фабрика, Строитель, Прототип, Одиночка
- [Структурные паттерны](#-структурные-паттерны)
  - Адаптер, Мост, Компоновщик, Декоратор, Фасад, Легковес, Заместитель
- [Поведенческие паттерны](#-поведенческие-паттерны-проектирования)
  - Цепочка обязанностей, Команда, Итератор, Посредник, Снимок, Наблюдатель, Состояние, Стратегия, Шаблонный метод, Посетитель

---

# 🧠 ООП и принципы проектирования

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