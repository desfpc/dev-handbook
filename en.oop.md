[📚 Contents](README.md)

### 🧠 [OOP and Design Principles](#-oop-and-design-principles)
- [OOP (Object-Oriented Programming)](#-oop-object-oriented-programming)
- [SOLID — 5 OOP Design Principles](#-solid--5-oop-design-principles)
- [GRASP — General Responsibility Assignment Software Patterns](#-grasp--general-responsibility-assignment-software-patterns)

### 🎯 [Design Patterns](#-design-patterns)
- [Creational Patterns](#-creational-patterns)
  - Factory Method, Abstract Factory, Builder, Prototype, Singleton
- [Structural Patterns](#-structural-patterns)
  - Adapter, Bridge, Composite, Decorator, Facade, Flyweight, Proxy
- [Behavioral Patterns](#-behavioral-design-patterns)
  - Chain of Responsibility, Command, Iterator, Mediator, Memento, Observer, State, Strategy, Template Method, Visitor

---

# 🧠 OOP and Design Principles

## 🧠 OOP (Object-Oriented Programming)

**Encapsulation**  
Packaging data and functions into a single unit — an object.  
Implemented through access modifiers (`public`, `protected`, `private`).  
Allows hiding the internal structure of an object, protecting data from external interference.

**Inheritance**  
A mechanism that allows one class to inherit properties and methods of another.  
Provides code reuse and extensibility.  
Allows building hierarchies: `Cat` inherits from `Animal`.

**Polymorphism**  
The ability to use the same interface for different types of objects.  
Allows overriding methods in subclasses while maintaining a common interface.  
Example: `draw()` works differently for `Circle` and `Square`, but is called the same way.

**Abstraction**  
The process of hiding implementation details and displaying only necessary interfaces.  
Allows focusing on "what an object does" rather than "how it does it".

---

## 🔠 SOLID — 5 OOP Design Principles

**S — Single Responsibility Principle**  
A class should be responsible for only one part of functionality.  
Example: the `UserManager` class should not handle logging or sending emails.

**O — Open/Closed Principle**  
Entities are open for extension but closed for modification.  
Behavior is extended through inheritance, composition, strategies.  
Example: instead of modifying the `PaymentService` class — add a new `StripePayment`.

**L — Liskov Substitution Principle**  
Base class objects should be replaceable with subclass objects without breaking logic.  
Example: if `Bird` can `fly()`, but `Penguin` cannot, then `Penguin` should not inherit from `Bird`.

**I — Interface Segregation Principle**  
Better to have several specialized interfaces than one universal one.  
A client should not depend on methods it does not use.

**D — Dependency Inversion Principle**  
Depend on abstractions, not concrete implementations.  
Implementation is achieved through Dependency Injection.

---

## ⚙ GRASP — General Responsibility Assignment Software Patterns

**Information Expert**  
Assign responsibility to the object that has the necessary data.  
Reduces coupling and code duplication.

**Creator**  
An object creates another if:
- it contains or aggregates it;
- it uses it;
- it manages its lifecycle;
- it knows how to initialize it.

**Controller**  
Mediator between user input and the model.  
Processes events and delegates work to appropriate objects.  
Example: controller in MVC.

**Low Coupling**  
Minimize dependencies between objects.  
Facilitates reuse and testing.

**High Cohesion**  
An object has one clear task and doesn't do extra things.  
Makes code easier to understand, maintain, and change.

**Pure Fabrication**  
Artificially created class for architectural purposes.  
Example: `Service`, `Repository` — not from the domain, but useful.

**Indirection**  
Introduce a layer between objects to reduce coupling.  
Example: `EventBus`, `Router`, `Dispatcher`.

**Polymorphism**  
Allows handling objects with the same interface differently.  
For example, `Formatter` can have implementations like `HtmlFormatter`, `JsonFormatter`.

**Protected Variations**  
Isolate potentially unstable parts of code behind abstractions.  
Example: working with external API through an interface/adapter.

---

# 🎯 Design Patterns

> Classification and brief implementations in **PHP 8** and **Go** (where possible)  
> Divided into three groups: creational, structural, and behavioral.

---

## 🧬 Creational Patterns

### 🏭 Factory Method

Creates an object through a common interface, delegating creation to subclasses.

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

### 🧪 Abstract Factory

Creates families of related objects through a set of interfaces.

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

### 🧱 Builder

Creates a complex object step by step.

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

### 🧬 Prototype

Copies an object through the `clone` interface.

**PHP 8**
```php
class Sheep {
    public function __construct(public string $name) {}
    public function __clone() {}
}

$s1 = new Sheep("Dolly");
$s2 = clone $s1;
```

**Go** (manual copying)
```go
type Sheep struct {
    Name string
}

s1 := Sheep{Name: "Dolly"}
s2 := s1 // copy
```

---

### 🔂 Singleton

Ensures that a class has only one instance.

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

## 🧱 Structural Patterns

### 🔌 Adapter

Allows objects with incompatible interfaces to work together.

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

### 🌉 Bridge

Separates abstraction and implementation, allowing them to change independently.

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

### 🧩 Composite

Creates a tree structure of objects and allows treating them uniformly.

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

### 🎁 Decorator

Dynamically adds new behavior to an object without changing its structure.

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

### 📦 Facade

Provides a simple interface to a complex system.

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

### 🪶 Flyweight

Saves memory by reusing identical objects.

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

### 🕵 Proxy

A substitute object that controls access to the real object.

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

# 🔄 Behavioral Design Patterns

> Behavioral patterns describe interactions between objects.  

---

## 🔗 Chain of Responsibility

Passes a request along a chain of handlers until one of them handles it.

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

## 🧾 Command

Wraps a request in an object, allowing delayed execution.

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

## 🔁 Iterator

Allows step-by-step traversal of collection elements.

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

## 🤝 Mediator

Reduces coupling between objects by moving interaction to a separate object.

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

## 🧠 Memento

Saves and restores an object's state.

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

## 👀 Observer

Notifies subscribers about changes.

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

## 🔄 State

Changes an object's behavior depending on its current state.

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

## 🧠 Strategy

Encapsulates a family of algorithms and makes them interchangeable.

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

## 🧱 Template Method

Defines an algorithm, leaving some steps to subclasses.

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

## 👣 Visitor

Allows adding behavior without changing classes.

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