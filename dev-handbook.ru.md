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
