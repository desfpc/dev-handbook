[📚 Садржај](README.sr.md)

### 🧠 [ООП и принципи дизајна](#-ооп-и-принципи-дизајна)
- [ООП (Објектно-Оријентисано Програмирање)](#-ооп-објектно-оријентисано-програмирање)
- [SOLID — 5 принципа ООП дизајна](#-solid--5-принципа-ооп-дизајна)
- [GRASP — Патерни расподеле одговорности](#-grasp--патерни-расподеле-одговорности)

### 🎯 [Дизајн патерни](#-дизајн-патерни)
- [Креациони патерни](#-креациони-патерни)
  - Фабрички метод, Апстрактна фабрика, Градитељ, Прототип, Синглтон
- [Структурни патерни](#-структурни-патерни)
  - Адаптер, Мост, Композит, Декоратор, Фасада, Флајвејт, Прокси
- [Бихевиорални патерни](#-бихевиорални-патерни-дизајна)
  - Ланац одговорности, Команда, Итератор, Посредник, Мементо, Посматрач, Стање, Стратегија, Шаблонски метод, Посетилац

---

# 🧠 ООП и принципи дизајна

## 🧠 ООП (Објектно-Оријентисано Програмирање)

**Инкапсулација**  
Паковање података и функција у једну јединицу — објекат.  
Реализује се кроз модификаторе приступа (`public`, `protected`, `private`).  
Омогућава сакривање унутрашње структуре објекта, штитећи податке од спољног мешања.

**Наслеђивање**  
Механизам који омогућава једној класи да наследи својства и методе друге.  
Обезбеђује поновну употребу кода и проширивост.  
Омогућава изградњу хијерархија: `Мачка` наслеђује од `Животиња`.

**Полиморфизам**  
Могућност коришћења истог интерфејса за различите типове објеката.  
Омогућава преклапање метода у наследницима, задржавајући општи интерфејс.  
Пример: `draw()` ради различито код `Круга` и `Квадрата`, али се позива на исти начин.

**Апстракција**  
Процес сакривања детаља имплементације и приказивања само неопходних интерфејса.  
Омогућава фокусирање на "шта објекат ради", а не "како".

---

## 🔠 SOLID — 5 принципа ООП дизајна

**S — Single Responsibility Principle (Принцип једне одговорности)**  
Класа треба да буде одговорна само за један део функционалности.  
Пример: класа `UserManager` не треба да се бави логовањем или слањем мејлова.

**O — Open/Closed Principle (Принцип отворено/затворено)**  
Ентитети су отворени за проширење, али затворени за измене.  
Понашање се проширује кроз наслеђивање, композицију, стратегије.  
Пример: уместо измене класе `PaymentService` — додајемо нову `StripePayment`.

**L — Liskov Substitution Principle (Принцип замене Лисков)**  
Објекти основне класе морају бити заменљиви објектима поткласе без нарушавања логике.  
Пример: ако `Bird` уме да `fly()`, али `Penguin` — не, онда `Penguin` не треба да наслеђује `Bird`.

**I — Interface Segregation Principle (Принцип сегрегације интерфејса)**  
Боље је имати више специјализованих интерфејса него један универзални.  
Клијент не треба да зависи од метода које не користи.

**D — Dependency Inversion Principle (Принцип инверзије зависности)**  
Зависност од апстракција, а не од конкретних имплементација.  
Реализација се постиже кроз Dependency Injection (убризгавање зависности).

---

## ⚙ GRASP — Патерни расподеле одговорности

**Information Expert (Информациони експерт)**  
Додели одговорност објекту који има потребне податке.  
Смањује повезаност и дуплирање кода.

**Creator (Креатор)**  
Објекат ствара други, ако:
- садржи га или агрегира;
- користи га;
- управља његовим животним циклусом;
- зна како да га иницијализује.

**Controller (Контролер)**  
Посредник између корисничког уноса и модела.  
Обрађује догађаје и делегира рад одговарајућим објектима.  
Пример: контролер у MVC.

**Low Coupling (Слаба повезаност)**  
Минимизирамо зависности између објеката.  
Олакшава поновну употребу и тестирање.

**High Cohesion (Висока кохезија)**  
Објекат има један јасан задатак и не ради ништа сувишно.  
Олакшава разумевање, одржавање и измену кода.

**Pure Fabrication (Чиста фабрикација)**  
Вештачки створена класа ради архитектурног циља.  
Пример: `Service`, `Repository` — нису из домена проблема, али су корисни.

**Indirection (Посредник)**  
Уводимо слој између објеката да смањимо повезаност.  
Пример: `EventBus`, `Router`, `Dispatcher`.

**Polymorphism**  
Омогућава обраду објеката са истим интерфејсом на различите начине.  
На пример, `Formatter` може имати имплементације `HtmlFormatter`, `JsonFormatter`.

**Protected Variations (Отпорност на промене)**  
Изолујемо потенцијално нестабилне делове кода иза апстракција.  
Пример: рад са спољним API-јем кроз интерфејс/адаптер.

---

# 🎯 Дизајн патерни

> Класификација и кратки примери имплементације на **PHP 8** и **Go** (где је могуће)  
> Подељено у три групе: креациони, структурни и бихевиорални.

---

## 🧬 Креациони патерни

### 🏭 Фабрички метод (Factory Method)

Ствара објекат кроз општи интерфејс, делегирајући стварање поткласама.

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

### 🧪 Апстрактна фабрика (Abstract Factory)

Ствара фамилије повезаних објеката кроз скуп интерфејса.

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

### 🧱 Градитељ (Builder)

Ствара сложен објекат корак по корак.

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

Копира објекат кроз интерфејс `clone`.

**PHP 8**
```php
class Sheep {
    public function __construct(public string $name) {}
    public function __clone() {}
}

$s1 = new Sheep("Dolly");
$s2 = clone $s1;
```

**Go** (ручно копирање)
```go
type Sheep struct {
    Name string
}

s1 := Sheep{Name: "Dolly"}
s2 := s1 // копија
```

---

### 🔂 Синглтон (Singleton)

Гарантује да класа има само једну инстанцу.

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

## 🧱 Структурни патерни

### 🔌 Адаптер (Adapter)

Омогућава објектима са некомпатибилним интерфејсима да раде заједно.

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

Раздваја апстракцију и имплементацију, омогућавајући њихову независну промену.

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

### 🧩 Композит (Composite)

Ствара структуру стабла објеката и омогућава јединствен приступ њима.

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

Динамички додаје објекту ново понашање без промене његове структуре.

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

### 📦 Фасада (Facade)

Пружа једноставан интерфејс ка сложеном систему.

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

### 🪶 Флајвејт (Flyweight)

Штеди меморију поновним коришћењем истих објеката.

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

### 🕵 Прокси (Proxy)

Објекат-замена који контролише приступ стварном објекту.

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

# 🔄 Бихевиорални патерни дизајна

> Бихевиорални патерни описују интеракцију између објеката.  

---

## 🔗 Ланац одговорности (Chain of Responsibility)

Прослеђује захтев кроз ланац руковалаца, док га неко не обради.

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

Омотава захтев у објекат, омогућавајући одложено извршавање.

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

Омогућава постепено обилажење елемената колекције.

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

Смањује повезаност објеката, издвајајући интеракцију у посебан објекат.

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

## 🧠 Мементо (Memento)

Чува и враћа стање објекта.

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

## 👀 Посматрач (Observer)

Обавештава претплатнике о променама.

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

## 🔄 Стање (State)

Мења понашање објекта у зависности од његовог тренутног стања.

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

## 🧠 Стратегија (Strategy)

Инкапсулира фамилију алгоритама и чини их међусобно заменљивим.

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

## 🧱 Шаблонски метод (Template Method)

Дефинише алгоритам, остављајући неке кораке поткласама.

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

## 👣 Посетилац (Visitor)

Омогућава додавање понашања без мењања класа.

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