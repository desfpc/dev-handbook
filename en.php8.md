[📚 Contents](README.md)

- [🐘 PHP 8 — Advanced Topics for Mid+/Senior Developers](#-php-8--advanced-topics-for-midsenior-developers)
  - [📚 Built-in Interfaces and Classes in PHP](#-built-in-interfaces-and-classes-in-php)
  - [🧱 Object-Oriented Programming (OOP) in PHP](#-object-oriented-programming-oop-in-php)
  - [🧭 `self::` vs `static::` — Late Static Binding Difference](#-self-vs-static--late-static-binding-difference)
  - [🔐 Visibility Modifiers](#-visibility-modifiers)
  - [🧬 Covariance and Contravariance](#-covariance-and-contravariance)
  - [🧠 WeakMap / WeakReference](#-weakmap--weakreference)
  - [✨ Magic Methods in PHP 8](#-magic-methods-in-php-8)
  - [🚫 strict_types — Type Strictness](#-strict_types--type-strictness)
  - [🔁 Generators and `yield` in PHP](#-generators-and-yield-in-php)
  - [📐 PSR — PHP Standards Recommendations](#-psr--php-standards-recommendations)
  - [📦 Composer — PHP Dependency Manager](#-composer--php-dependency-manager)

---

# 🐘 PHP 8 — Advanced Topics for Mid+/Senior Developers
📎 [Official documentation on php.net](https://www.php.net/manual/en/index.php)

This document covers some basic and less obvious but extremely important topics for understanding PHP 8.

---

# 📚 Built-in Interfaces and Classes in PHP

PHP provides a number of built-in interfaces that can be used to create more flexible, scalable, and "natively supported" components. They are especially actively used in SPL (Standard PHP Library), as well as in serialization mechanisms, iteration, error handling, and array logic.

---

## 🔁 Iteration and Collections

### `Traversable`
- Base interface for all iterable objects.
- Cannot be implemented directly — need `Iterator` or `IteratorAggregate`.

### `Iterator`
- Complete iterator interface (like `foreach`).
- Requires implementation of:  
  `current()`, `key()`, `next()`, `rewind()`, `valid()`

### `IteratorAggregate`

- Provides iteration through the `getIterator()` method, returning a `Traversable`
  (used for delegation of iteration):


```php
class Collection implements IteratorAggregate {
    public function getIterator(): Traversable {
        return new ArrayIterator($this->items);
    }
}
```

### `SeekableIterator`
- Extends `Iterator` by adding the `seek($position)` method — moving to a specific position.

---

## 📦 Collections and Key Access

### `ArrayAccess`
- Allows working with an object as an array: `$obj[0] = 'value';`
- Methods:  
  `offsetExists()`, `offsetGet()`, `offsetSet()`, `offsetUnset()`

### `Countable`
- Allows using `count($obj)`
- Requires implementation of `count(): int`

---

## 💾 Serialization and Encoding

### `Serializable`
- Custom serialization:
    - `serialize()`
    - `unserialize(string $data)`

### `JsonSerializable`
- Used in `json_encode()`
- Method: `jsonSerialize(): mixed`

---

## 🚨 Error Handling

### `Throwable`
- Base interface for all exceptions and errors.
- Supports `Exception` and `Error`.


- All errors and exceptions inherit from `Throwable`.
- You can catch both `Exception` and `Error` via `catch (Throwable $e)`.
- Support for nested exceptions:

```php
throw new Exception("Error", 0, $previous);
```

---

### `Exception`
- Standard exceptions (classes must inherit from `Exception`).

### `Error`
- Fatal errors (inherits from `Throwable`).

---

## 📚 Additional SPL Interfaces

### `OuterIterator`
- Wraps another iterator. Method: `getInnerIterator()`

### `RecursiveIterator`
- An iterator that can contain other iterators (hierarchy).
- Methods: `hasChildren()`, `getChildren()`

### `RecursiveIteratorIterator`
- Traverses a `RecursiveIterator` in depth.

### `CountableIterator` *(doesn't exist, often confused with `Countable` + `Iterator`)*

---

## 🔢 ArrayAccess

Allows accessing an object as an array:

```php
class Collection implements ArrayAccess {
    public function offsetExists($offset) { ... }
    public function offsetGet($offset) { ... }
    public function offsetSet($offset, $value) { ... }
    public function offsetUnset($offset) { ... }
}
```

Used in containers, DTOs, settings.

---

## 🔁 Serializable

Allows controlling how an object is serialized:

```php
class User implements Serializable {
    public function serialize(): string { ... }
    public function unserialize($data): void { ... }
}
```

📌 Deprecated in favor of `__serialize()` / `__unserialize()` since PHP 7.4+

---

## 🔒 SensitiveParameterValue

A class that hides the value when logging exceptions.

```php
function login(string $user, SensitiveParameterValue $password) {
    throw new Exception("Invalid password");
}
```

Used in tracing/debugging to hide sensitive data.

---

## 🔐 __PHP_Incomplete_Class

A class automatically used by PHP when an object of an unknown class is serialized.  
Encountered when using `unserialize()` if the class no longer exists.

---

## 🧠 Closure

Anonymous functions are instances of the `Closure` class:

```php
$fn = function($x) { return $x * 2; };
```

You can use `bindTo()` and `call()` to change the execution context.

---

## 📦 stdClass

An "empty" universal object:

```php
$obj = new stdClass();
$obj->name = "test";
```

Often used for converting `array → object`.

---

## ⚙️ Generator

An object that is returned when calling a function with `yield`.

```php
function gen() {
    yield 1;
    yield 2;
}
```

`Generator` objects implement `Iterator`.

See the section [Generators and `yield` in PHP](#-generators-and-yield-in-php)

---

## 🧵 Fiber (PHP 8.1+)

A mechanism for cooperative multitasking:

```php
$fiber = new Fiber(function (): void {
    $value = Fiber::suspend("pause");
    echo $value;
});
$fiber->start();
```

Allows temporarily "suspending" execution and returning later.

---

## 🧷 WeakReference

Creates a weak reference to an object:

```php
$ref = WeakReference::create($obj);
$obj = null;
$ref->get(); // null — object destroyed
```

Used in caches and GC-sensitive structures.

---

## 🗺 WeakMap

An associative array with object keys. When an object is destroyed, the key is also removed.

```php
$map = new WeakMap();
$map[$obj] = "cached";
```

---

## 🧵 Stringable (PHP 8.0+)

A marker interface. If a class implements `__toString()`, it automatically `implements Stringable`.

You can explicitly specify:

```php
class MyClass implements Stringable {
    public function __toString(): string { ... }
}
```

---

## 🔠 UnitEnum / BackedEnum (PHP 8.1+)

- `UnitEnum` — base interface for all enums.
- `BackedEnum` — for enums with a bound value (int|string):

```php
enum Status: string {
    case Active = 'active';
    case Disabled = 'disabled';
}
```

Allows using `->value`, `from()`, `tryFrom()` and `cases()`.

---

These interfaces are the foundation of many built-in and third-party libraries. Understanding them and knowing how to apply them in practice distinguishes a confident developer.


---

# 🧱 Object-Oriented Programming (OOP) in PHP

OOP is the foundation of scalable applications. The key concepts of OOP in PHP are described in detail below.

---

## 📦 Classes, Interfaces, Abstract Classes

### ▶️ Classes

A class is a template for creating objects. It describes properties (variables) and methods (functions):

```php
class Car {
    public string $brand;

    public function drive() {
        echo "Driving";
    }
}
```

---

### 📄 Interfaces (`interface`)

An interface defines a **contract** — a set of methods that a class **must implement**.

```php
interface Engine {
    public function start();
}
```

An interface:
- ❗ Contains no implementation, only method signatures.
- ❌ Cannot contain properties (until PHP 8.1).
- ✅ You can implement **multiple interfaces simultaneously**.

```php
class Car implements Engine, JsonSerializable {
    public function start() { ... }

    public function jsonSerialize(): mixed {
        return ['type' => 'car'];
    }
}
```

---

### 🧱 Abstract Classes (`abstract`)

An abstract class can:
- Contain both **implemented** and **abstract methods**.
- Have **properties** and a **constructor**.
- Be used as a **base class** from which concrete implementations inherit.

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

## 🧬 Inheritance

### Key Principles:

- A class can **inherit from only one other class** (single inheritance).
- But it can **implement any number of interfaces**.
- Use `parent::method()` to call the parent implementation.

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

## 🚫 Restrictions and Modifiers

- `final class` — cannot be inherited.
- `final function` — cannot be overridden in descendants.
- `abstract function` — a method without a body, must be implemented in child classes.
- `private` — accessible only within the class itself.
- `protected` — accessible in the class and its descendants.
- `public` — accessible everywhere.

---

## 🛠 How to Overcome the Limitation on Multiple Inheritance?

PHP does not support multiple inheritance of classes directly, but you can use **traits**:

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

Traits allow code reuse across multiple independent classes.

---

## 🔑 When to Use What?

| Type          | When to Apply                                   |
|---------------|--------------------------------------------------|
| `interface`   | When you need to define a contract for implementation. |
| `abstract`    | When there is common logic + abstract methods.   |
| `trait`       | When you need to reuse code between classes.     |
| `class`       | Concrete implementation.                         |

---

Understanding these constructs is the foundation of good architecture. The ability to choose between an interface, abstraction, and trait distinguishes a confident developer.


---

## 🧭 `self::` vs `static::` — Late Static Binding Difference

### `self::` — binding to the class where the method is defined

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

B::call(); // A — self:: is bound to A
```

### `static::` — late static binding

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

B::call(); // B — static:: refers to the calling class
```

Use `static::` if you want the method to work correctly with inheritance.

---

## 🔐 Visibility Modifiers

| Modifier    | Accessible Inside | Accessible in Descendants | Accessible Outside |
|-------------|------------------|-------------------------|------------------|
| `public`    | ✅               | ✅                      | ✅               |
| `protected` | ✅               | ✅                      | ❌               |
| `private`   | ✅               | ❌                      | ❌               |

### Example:

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

## ✅ Recommendations

- Use `private` by default — encapsulation!
- `protected` — if inheritance with behavior extension is expected.
- `public` — only for external API.

- `self::` — if you want to fix the call to the current class (e.g., a factory method in a base class).
- `static::` — if the method should account for polymorphism.

---

### Passing Objects by Reference
PHP objects in code are actually not the objects themselves, but references to them:

```php
$a = new StdClass();
$b = $a; // $b and $a are references to the same object;
$b->test = 1;
echo $a->test; // $a->test === $b->test === 1, since $a and $b are references
```

### Cloning

```php
$c = $a; // no new object is created; $c is a reference to the same object as $a
$b = clone $a; // creates a copy of object $a
```

You can define `__clone()` to modify the behavior.

---

## 🧬 Covariance and Contravariance

### Covariance (return type can be more specific)

```php
class A {}
class B extends A {}

class ParentClass {
    public function get(): A {}
}

class ChildClass extends ParentClass {
    public function get(): B {} // allowed
}
```

### Contravariance (argument can be more abstract)

---

## 🧠 WeakMap / WeakReference

Allow referencing objects without keeping them in memory:

### WeakReference

```php
$ref = WeakReference::create($object);
$obj = $ref->get(); // may return null
```

### WeakMap

```php
$map = new WeakMap();
$map[$object] = 'value'; // when the object is deleted, the pair will disappear
```

Used in caching and breaking circular dependencies.

---

# ✨ Magic Methods in PHP 8

Magic methods are special methods in PHP that are automatically called in certain situations. They always start with a double underscore `__`.

---

## 📋 Complete List of Magic Methods

### 🔨 `__construct()`

Called when creating an object.

```php
class User {
    public function __construct(public string $name) {
        echo "Created user: $name";
    }
}
```

---

### 🧹 `__destruct()`

Called when an object is deleted (at the end of the script or with unset).

```php
public function __destruct() {
    echo "Destroying user";
}
```

---

### 🧠 `__get($name)` and `__set($name, $value)`

Handle access to non-existent/private properties.

```php
public function __get($name) {
    return $this->data[$name] ?? null;
}

public function __set($name, $value) {
    $this->data[$name] = $value;
}
```

---

### 🔍 `__isset($name)` and `__unset($name)`

Reaction to `isset($obj->prop)` and `unset($obj->prop)`.

```php
public function __isset($name) {
    return isset($this->data[$name]);
}
```

---

### ☎️ `__call($name, $arguments)`

Called when accessing a non-existent method.

```php
public function __call($name, $arguments) {
    echo "Method $name does not exist";
}
```

---

### ☎️ `__callStatic($name, $arguments)`

Analog of `__call`, but for static methods.

```php
public static function __callStatic($name, $arguments) {
    echo "Static $name called";
}
```

---

### 🧬 `__clone()`

Called when cloning an object via `clone`.

```php
public function __clone() {
    $this->id = null;
}
```

---

### 🧾 `__toString()`

Allows setting a string representation of an object.

```php
public function __toString(): string {
    return $this->name;
}
```

---

### 📞 `__invoke(...)`

Allows calling an object as a function.

```php
public function __invoke($x) {
    return $x * 2;
}

$obj = new MyClass();
echo $obj(5); // 10
```

---

### 🧭 `__debugInfo()`

Defines what data is shown in `var_dump()`.

```php
public function __debugInfo(): array {
    return ['hidden' => true];
}
```

---

### 📦 `__serialize()` and `__unserialize()`

Introduced in PHP 7.4. Control object serialization.

```php
public function __serialize(): array {
    return ['name' => $this->name];
}

public function __unserialize(array $data): void {
    $this->name = $data['name'];
}
```

---

## ⚠️ Recommendations

- Don't abuse magic — code can become unreadable.
- Use `__get/__set` for **proxy objects**, not as a replacement for regular properties.
- `__call/__callStatic` are convenient for implementing **lazy loading** or **wrappers**.

---

# 🚫 strict_types — Type Strictness

In PHP, by default, **automatic type conversion** (type juggling) is allowed, which can lead to unexpected results and errors that are difficult to track.

## ✅ Enabling Strict Typing

```php
<?php
declare(strict_types=1);
```

🔺 This declaration must be the **first line of the file**, before any output or code.

---

## ⚖️ Example: Behavior Comparison

### 📌 Without `strict_types`

```php
function sum(int $a, int $b): int {
    return $a + $b;
}

echo sum(2, 3.5); // 5 — float is converted to int
```

### ✅ With `strict_types=1`

```php
declare(strict_types=1);

function sum(int $a, int $b): int {
    return $a + $b;
}

echo sum(2, 3.5); // ❌ Fatal error: Argument must be of type int
```

---

## ❗ Problems with Disabled Typing

- Silent conversion of float → int, string → int, etc.
- Logical errors that are difficult to track.
- Behavior depends on data, not code.

---

## ✅ Why You Should **Always Use** `strict_types`

- 🛡️ Protection against hidden errors.
- 🔎 Transparency and predictability of types.
- 🧪 More correct behavior when writing unit tests.
- 🔄 Compatibility with modern standards and code analyzers (`PHPStan`, `Psalm`).

---

## 🔁 Recommended Approach

- **Always include `strict_types=1`** in every PHP file (especially in a library or API).
- Use parameter and return value types (`int`, `string`, `array`, `bool`, etc.).
- Use automated tests and static analysis in conjunction with strict typing.

---

# 🔁 Generators and `yield` in PHP

Generators allow implementing **lazy iteration** — processing data **one element at a time**, **without the need to load everything into memory**. This is especially useful when working with large volumes of data.

---

## 🧱 Basic Generator Example

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

## ⚙️ How `yield` Works?

- `yield` returns a value, suspends execution, and remembers the current state.
- The next iteration call (`foreach`) will continue execution from that point.

---

## ✅ Advantages of Generators

- 🧠 Syntax simplicity (no need to implement the `Iterator` interface).
- 🧵 Performance (processing "on the fly").
- 🧼 Memory efficiency (especially with large queries/files).

---

## 📚 Real-World Application Examples

### 📄 Reading a File Line by Line

```php
function readFileLines(string $filename): Generator {
    $handle = fopen($filename, 'r');
    if (!$handle) {
        throw new RuntimeException("Cannot open file");
    }

    while (($line = fgets($handle)) !== false) {
        yield rtrim($line, "\n");
    }

    fclose($handle);
}

foreach (readFileLines('data.txt') as $line) {
    echo $line . PHP_EOL;
}
```

✅ Alternative to `file()` — doesn't load the entire file into memory.

---

### 🗃 Processing SQL Queries in Parts

```php
function fetchRows(PDO $pdo): Generator {
    $stmt = $pdo->query("SELECT * FROM users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        yield $row;
    }
}
```

✅ Allows processing **large database queries** without loading them entirely.

---

### 🧪 Infinite Sequence

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

## 📍 `return` in a Generator

Starting from PHP 7, a generator can return a value via `return`, accessible through `$gen->getReturn()`:

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

## 📌 When to Use Generators?

- Processing **large data** (files, SQL).
- Stream processing.
- When **memory efficiency** is important.
- For **gradual value generation** (e.g., tree, graph).

---

# 📐 PSR — PHP Standards Recommendations

**PSR** (PHP Standards Recommendations) is a set of standards developed by the [PHP-FIG (Framework Interop Group)](https://www.php-fig.org/) to unify development approaches, make code more readable, predictable, and compatible between frameworks and libraries.

---

## ❓ Why PSR?

- 🧩 Simplifies integration of third-party libraries.
- 🤝 Increases compatibility between frameworks.
- 🧼 Standardizes style and architecture.
- 📦 Supported in Composer and IDEs.

---

## 📋 Main PSR Standards

### 🎨 PSR-1: Basic Coding Style
- Standardizes class names, file names, methods.
- Requires `<?php` tag.
- Class names in `StudlyCaps`, methods in `camelCase`.

### 🧼 PSR-12: Extended Coding Style (successor to PSR-2)
- Formatting rules: indentation, spaces, curly braces.
- One class per file, mandatory declare(strict_types=1).

### 📦 PSR-4: Autoloading
- Simple autoloading scheme based on namespace + path.
```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  }
}
```

### 📝 PSR-3: Logger Interface
- Defines a universal `LoggerInterface` with methods: `debug()`, `info()`, `error()`, etc.

### 🌐 PSR-7: HTTP Messages
- Interfaces `RequestInterface`, `ResponseInterface`, `StreamInterface`, etc.
- Used in frameworks (Slim, Symfony HTTPFoundation).

### ⚙️ PSR-11: Container Interface
- `ContainerInterface` with methods `get()` and `has()`.

### 🔀 PSR-13: Hypermedia Links
- `LinkInterface` for REST/HATEOAS links.

### 📚 PSR-14: Event Dispatcher
- Event Dispatcher interfaces: `EventDispatcherInterface`, `StoppableEventInterface`.

### 🧵 PSR-15: HTTP Middleware
- Defines interfaces for HTTP middleware (processing requests in layers).

### 🧪 PSR-17: HTTP Factory
- Factory interfaces for creating PSR-7 objects (`RequestFactoryInterface`, etc.)

### 🧰 PSR-18: HTTP Client
- Standard for HTTP clients (`sendRequest()`, etc.).

---

## ✅ How to Use PSR

- Connect only the interfaces you need via Composer.
- You don't have to use the entire stack.
- Compatible with any architecture.

---

## ⚙️ SPL and Autoloading

### `spl_autoload_register()`

Allows setting your own class autoloading function (alternative to Composer autoload):

```php
spl_autoload_register(function ($class) {
    include 'src/' . $class . '.php';
});
```

- 📌 Used in custom frameworks, old projects, or systems without Composer.
- Supports a stack of autoloaders — you can register multiple functions.

---

# 📦 Composer — PHP Dependency Manager

**Composer** is the standard tool for managing dependencies and autoloading in PHP. It allows connecting third-party libraries, managing versions, and project configuration.

---

## 🛠 Basic Commands

### 📥 Installing a Package

```bash
composer require vendor/package
```

Adds a dependency and immediately updates `composer.json` and `composer.lock`.

### 🧹 Removing a Package

```bash
composer remove vendor/package
```

Removes the package and cleans up dependencies.

### ♻️ Updating All Dependencies

```bash
composer update
```

Updates **all dependencies** to the latest allowed versions according to `composer.json`.

### 🎯 Updating a Single Package

```bash
composer update vendor/package
```

---

## 📁 File Structure

### `composer.json`

Project configuration file:

- List of dependencies and their versions.
- Autoloading scheme (PSR-4).
- Package metadata (name, authors, license, etc.)

Example:

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

- **Fixes exact versions** of all installed dependencies and sub-dependencies.
- Used for reproducibility (`CI/CD`, deployment).
- **Needed in the project!** It **should be committed** if the project is an application (not a library).

📌 **When not to commit `composer.lock`?**
- When you're developing a **library** and don't want to fix dependencies for others.

---

## 📦 Creating Your Own Composer Package

1. Create a new repository with `composer.json`:

```bash
composer init
```

2. Example `composer.json`:

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

3. Project structure:

```
mypackage/
├── src/
│   └── Helper.php
├── composer.json
```

4. 📤 Publish on GitHub and add a version with a git tag:

```bash
git tag v1.0.0
git push origin v1.0.0
```

5. 📢 Register on [Packagist](https://packagist.org/)

---

## 📍 Local Connection of Your Package (without Packagist)

In the project where you want to use the package:

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

## 🧪 Useful Commands

- `composer validate` — check `composer.json`
- `composer outdated` — list of outdated dependencies
- `composer dump-autoload` — recreate autoloading

---

## ✅ Recommendations

- Commit `composer.lock` in all projects except libraries.
- Install dependencies via `composer require`, not manually in JSON.
- Use `^` and `~` for version control (`^2.0` — any `2.x`, `~1.2` — `1.2.*`).

---
