[📚 Садржај](README.sr.md)

- [💾 Базе података](#-базе-података)
  - [Трансакције, нивои изолације и колизије у СУБД](#-трансакције-нивои-изолације-и-колизије-у-субд)
  - [Индекси и анализа SQL упита](#-индекси-и-анализа-sql-упита-explain)
  - [SQL: Груписање, JOIN-ови и технике оптимизације](#-sql-груписање-joinови-и-технике-оптимизације-сложених-упита)
  - [Партиционисање, репликација и шардинг](#-партиционисање-репликација-и-шардинг-у-субд)
  - [Колонске базе података и ClickHouse](#-колонске-базе-података-и-clickhouse)
  - [NoSQL и MongoDB](#увод-у-nosql-и-mongodb)
  - [Redis и KeyDB](#-redis-и-keydb-основе-архитектура-и-предности)

---

# 💾 Базе података

# 📁 Трансакције, нивои изолације и колизије у СУБД

## ACID

**ACID** — то су четири фундаментална својства трансакција:

- **Atomicity (Атомичност)**
  > Трансакција се или извршава у потпуности, или се не извршава уопште. Ако се догоди грешка у било којој фази, долази до **повратка (rollback)**.

- **Consistency (Конзистентност)**
  > Након завршетка трансакције, база података мора бити у **конзистентном стању**: сва ограничења су испоштована, подаци су валидни. Успешна трансакција **не нарушава** логички интегритет.

- **Isolation (Изолација)**
  > Паралелне трансакције **не смеју утицати једна на другу**. Конфликтне операције се изолују. У реалности се постиже **кроз нивое изолације** (види доле).

- **Durability (Трајност)**
  > Након потврде (COMMIT), промене се чувају **заувек**, чак и у случају нестанка струје или отказа хардвера.


## 🏰 Нивои изолације (Isolation Levels)

| Ниво              | Шта дозвољава                               | Рањивост                  |
|-------------------|---------------------------------------------|-----------------------------|
| **READ UNCOMMITTED** | Видљиве су чак и **непотврђене** промене     | Dirty Read                 |
| **READ COMMITTED**   | Видљиве су само **потврђене** промене     | Non-repeatable Read        |
| **REPEATABLE READ** *(InnoDB подразумевано)* | Поновљени SELECT у оквиру трансакције враћа исто* | Phantom Read              |
| **SERIALIZABLE**     | Потпуна изолација, све трансакције као да су **секвенцијалне** | Смањење паралелизма     |

### Примери:
```sql
SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
START TRANSACTION;
SELECT * FROM users WHERE status = 'active';
COMMIT;
```


## 📂 Потенцијалне колизије и грешке

- **Deadlock (узајамно блокирање)**  
  T1 блокира ред који чека T2. T2 блокира ресурс који чека T1. MySQL **аутоматски завршава једну од њих**.
  ✅ Анализа преко: `SHOW ENGINE INNODB STATUS`

- **Phantom Read**  
  `SELECT COUNT(*)` враћа 100. Док је трансакција активна, друга додаје ред. Поновљени `SELECT` — већ 101.

- **Non-repeatable Read**  
  Прочитали смо ред, затим га је неко други ажурирао. Поновно читање — већ други резултат.


## 🏦 MySQL (MariaDB) — InnoDB, трансакције и закључавања

### 🛠 InnoDB (основни мотор за складиштење)

- Подржава **трансакције**, **изолацију**, **аутоматски recovery**, **foreign keys**, **MVCC**.
- Чува податке у **кластерским индексима**, логује промене у redo/undo логовима.
- Подразумевано користи ниво **REPEATABLE READ**.

### 🔒 Закључавања InnoDB:

- **Shared (S)** — читање дозвољено. Више трансакција може истовремено узети `S` на један ред.
- **Exclusive (X)** — писање. Само једна трансакција може држати `X`.

#### Примери:
```sql
SELECT * FROM accounts WHERE id = 1 FOR UPDATE; -- закључавање X (exclusive)
SELECT * FROM products WHERE id = 5 LOCK IN SHARE MODE; -- закључавање S (shared)
```

### ⚡ Пример Deadlock:
```sql
-- T1
START TRANSACTION;
UPDATE products SET stock = stock - 1 WHERE id = 1;
-- чека UPDATE другог реда...

-- T2
START TRANSACTION;
UPDATE products SET stock = stock - 2 WHERE id = 2;
UPDATE products SET stock = stock - 1 WHERE id = 1; -- чека T1
```

MySQL ће открити deadlock и завршити једну од трансакција.


### ✅ Препоруке за програмера:
- Користи **BEGIN / COMMIT / ROLLBACK** када радиш са више од једне табеле.
- За закључавање реда при променама — `SELECT ... FOR UPDATE`
- Додај индексе на спољне кључеве (`FOREIGN KEY`) — иначе ће `JOIN`-ови бити спори.
- Пре имплементације сложених `JOIN`, `ORDER BY`, `GROUP BY` — покрени `EXPLAIN`

---

## 🔷 MyISAM (алтернатива InnoDB, застарела)

- Не подржава трансакције!
- **Табеларна закључавања** уместо редова.
- Добро ради за **читање**, али се лоше скалира за писање.
- Не подржава `FOREIGN KEY`.

Користи се ретко. Препоручује се избегавање у новим системима.


---

## 🔷 PostgreSQL

- Подржава **све нивое изолације** (подразумевано: **READ COMMITTED**).
- Трансакције строго одговарају ACID.
- Користи сопствену имплементацију **MVCC**, не захтева закључавања за читање.
- Добро управља deadlock-овима, пружа `pg_stat_activity`, `pg_locks`.
- Подршка за `SAVEPOINT`, `ROLLBACK TO`, `SET TRANSACTION ISOLATION LEVEL`

#### Пример трансакције:
```sql
BEGIN;
UPDATE users SET balance = balance - 100 WHERE id = 1;
UPDATE users SET balance = balance + 100 WHERE id = 2;
COMMIT;
```

---

# 📊 Индекси и анализа SQL упита (`EXPLAIN`)

---

## 📙 Шта је индекс

Индекс у СУБД — то је **структура података која убрзава претрагу**, филтрирање и сортирање редова у табели. У суштини, то је **сортирана структура** која омогућава брзо проналажење потребних вредности **без скенирања целе табеле**.

### ✅ Општи појмови

- **Прост индекс**: једна колона
- **Сложени (композитни)**: више колона, редослед **је битан!**
- **Покривајући (covering index)**: садржи **све колоне** потребне упиту (читање се врши само из индекса)
- **Кластерски индекс**: садржи **стварне редове** у листовима стабла (InnoDB)

### 🔍 Где помажу индекси:
- `WHERE column = value`
- `JOIN ON column`
- `ORDER BY indexed_column`
- `GROUP BY indexed_column`
- `DISTINCT indexed_column`

### ❌ Где индекси не раде:

| Лош образац               | Зашто је лош                                      |
|-----------------------------|---------------------------------------------------|
| `LEFT(name, 3) = 'abc'`     | Функција нарушава редослед → **индекс не ради** |
| `status + 1 = 2`            | Аритметика искључује индекс                      |
| `OR` између колона        | Често доводи до `FULL SCAN`                     |
| `LIKE '%abc'`               | Нема префикса → индекс није применљив               |
| `ORDER BY non_indexed_col` | Сортирање у меморији, `Using filesort`            |


---

## 🔹 Типови индекса

| Тип         | Намена |
|--------------|------------|
| `PRIMARY`    | Кластерски индекс InnoDB (по `id`) |
| `UNIQUE`     | Логичка јединственост (`email`, `username`) |
| `INDEX`      | Обичан индекс за убрзање претраге |
| `COMPOSITE`  | Више колона (`(a, b)`) |
| `COVERING`   | Индекс који покрива све колоне SELECT-а |
| `FULLTEXT`   | Претрага по тексту (`MATCH ... AGAINST`) |
| `SPATIAL`    | Гео-подаци (`POINT`, `POLYGON`) |

### Примери:
```sql
-- Индекс по email-у
CREATE INDEX idx_users_email ON users(email);

-- Композитни индекс
CREATE INDEX idx_status_created ON orders(status, created_at);
```

---

## 🔧 Структуре података индекса

### 🔹 B-Tree / B+Tree (MySQL InnoDB, PostgreSQL)
- Основни тип за већину индекса
- Уређена структура → **брза бинарна претрага**

### 🔹 Hash
- Неуређен (MySQL: MEMORY engine)
- Погодан само за **једнакост `=`**
- Не ради са опсезима, сортирањем, `LIKE`

### 🔹 FULLTEXT
- Само у MyISAM/InnoDB
- Ради са `MATCH() AGAINST()`
- Добар за претрагу пуног текста, али не замењује LIKE у потпуности

### 🔹 SPATIAL
- За геометрију: `POINT`, `LINE`, `POLYGON`
- Користи се у GIS сценаријима

---

## 📈 `EXPLAIN` и анализа SQL упита

Команда `EXPLAIN` показује **како ће се извршити упит**: који индекси се користе, колико редова се проверава и да ли постоје привремене табеле.

### Пример:
```sql
EXPLAIN SELECT * FROM users WHERE email = 'user@example.com';
```

### Поља `EXPLAIN`:

| Поље           | Значење |
|----------------|----------|
| `id`           | Број упита или подупита |
| `select_type`  | Тип упита (`SIMPLE`, `PRIMARY`, `DERIVED` итд.) |
| `type`         | Тип приступа (најбоље: `const`, `ref`, `range`; најгоре: `ALL`) |
| `possible_keys`| Који индекси су могли бити коришћени |
| `key`          | Који индекс се стварно користи |
| `rows`         | Приближан број редова за проверу |
| `Extra`        | Додатне информације (`Using filesort`, `Using temporary` и др.) |

### Дијагностика:
- **`ALL`** — потпуно скенирање (full scan)
- **`Using filesort`** — сортирање у меморији (споро)
- **`Using temporary`** — креирање привремене табеле

#### Пример:
```sql
EXPLAIN SELECT * FROM orders WHERE status = 'paid' ORDER BY created_at DESC LIMIT 10;
```
Ако **нема индекса по `(status, created_at)`** → биће `filesort`.  
Креирај индекс:
```sql
CREATE INDEX idx_status_created ON orders(status, created_at);
```

---

## 🗄 Специфичности MySQL (InnoDB)

- Кластерски индекс = структура складиштења табеле. `PRIMARY KEY` → физички редослед редова.
- Ако нема PK или UNIQUE, креира се **скривени сурогатни кључ** (`row_id`).
- Сваки секундарни индекс садржи **референцу на кластерски (PK)**.
- MySQL користи **B+Tree** скоро за све типове индекса.
- Можеш користити **`USE INDEX`**, `FORCE INDEX` за навођење жељеног.

---

## 🗄 Специфичности PostgreSQL

- Сваки индекс је **засебна структура** (није кластеризован по дефолту)
- `btree` — подразумевано
- Подршка за **вишеколонске и делимичне индексе**
- Има **GIN**, **GiST**, **BRIN**, **HASH**, **SP-GiST**:
  - **GIN** — брза претрага у `jsonb`, `array`, `tsvector`
  - **BRIN** — ефикасан на великим табелама са подацима уређеним по опсегу

### Пример делимичног индекса:
```sql
CREATE INDEX idx_active_users ON users(email) WHERE active = true;
```

### Анализа упита:
```sql
EXPLAIN ANALYZE SELECT * FROM users WHERE active = true;
```

- `EXPLAIN` — процена плана
- `EXPLAIN ANALYZE` — **стварно извршавање** са временом извршења

---

# 📊 SQL: Груписање, JOIN-ови и технике оптимизације сложених упита

---

## 📃 Груписање и агрегација

SQL омогућава **агрегацију података** по групама редова помоћу конструкције `GROUP BY` и агрегатних функција.

### 📄 Стандардна форма:
```sql
SELECT col1, SUM(col2) AS total
FROM tableName
WHERE condition
GROUP BY col1
HAVING total > 100;
```

| Елемент         | Намена |
|-----------------|------------|
| `SUM(col2)`     | Агрегатна функција: сума вредности `col2` |
| `FROM`          | Извор података, може бити `JOIN` |
| `WHERE`         | Филтер пре груписања |
| `GROUP BY`      | Груписање по колонама |
| `HAVING`        | Филтер након агрегације |

---

### 🔗 Напредне технике груписања

#### `ROLLUP`
Додаје **међузбирне редове**:
```sql
SELECT department, team, SUM(salary)
FROM employees
GROUP BY department, team WITH ROLLUP;
```
Добићемо збирове за сваки тим, одељење и укупан збир.

#### `CUBE`
Додаје **све могуће комбинације** груписања:
```sql
SELECT region, product, SUM(sales)
FROM sales_data
GROUP BY CUBE(region, product);
```

#### `GROUPING SETS`
Флексибилна контрола над групама:
```sql
SELECT manufacturer, product_count, SUM(price)
FROM products
GROUP BY GROUPING SETS (
  ROLLUP(manufacturer),
  (product_count),
  (manufacturer, product_count)
);
```

#### `OVER()` и функције прозора
Омогућава **агрегацију**, али **оставља редове непромењене**:
```sql
SELECT user_id, region, salary,
       SUM(salary) OVER (PARTITION BY region) AS regional_total
FROM employees;
```
> Користи се у PostgreSQL, MySQL 8+, SQLite, SQL Server, Oracle

---

## 🔀 JOIN — спајање табела

Спајања омогућавају **комбиновање редова из две или више табела** на основу логичког услова.

### 📋 Типови JOIN:
<img src="https://raw.githubusercontent.com/desfpc/dev-handbook/master/sql_joins.png" alt="dev-handbook" width="500">

| JOIN | Намена | Пример |
|------|------------|--------|
| `INNER JOIN` | Само подударајући редови | `users INNER JOIN orders ON users.id = orders.user_id` |
| `LEFT JOIN`  | Сви из леве + подударајући из десне | `users LEFT JOIN orders` — чак и они без наруџбина |
| `RIGHT JOIN` | Сви из десне + подударајући из леве | Скоро се не користи у MySQL, боље променити редослед |
| `FULL OUTER JOIN` | Сви редови из обе табеле | Није директно подржан у MySQL, али постоји у PostgreSQL |
| `CROSS JOIN` | Декартов производ (све комбинације) | Користи се ретко и опрезно |
| `SELF JOIN` | Табела се спаја сама са собом | `employees e1 JOIN employees e2 ON e1.manager_id = e2.id` |

### 🔍 Примери:
```sql
-- Корисници без наруџбина
SELECT u.*
FROM users u
LEFT JOIN orders o ON u.id = o.user_id
WHERE o.id IS NULL;

-- Последња наруџбина сваког клијента (са функцијом прозора)
SELECT *
FROM (
  SELECT *,
         ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY created_at DESC) as rn
  FROM orders
) t
WHERE rn = 1;
```

---

## 🧠 Корисне технике за изградњу сложених упита

### 1. ❌ Замена `OR` — са `UNION`

```sql
-- Лоше за индексе:
SELECT * FROM users
WHERE status = 'active' OR email LIKE '%@example.com';

-- Боље:
SELECT * FROM users WHERE status = 'active'
UNION
SELECT * FROM users WHERE email LIKE '%@example.com';
```
> Зашто: `OR` нарушава коришћење индекса, `UNION` омогућава извршавање сваког SELECT-а независно и коришћење одговарајућих индекса.

### 2. ✅ Анти-`JOIN`: проналажење записа без везе
```sql
SELECT p.*
FROM products p
LEFT JOIN orders o ON p.id = o.product_id
WHERE o.id IS NULL;
```

### 3. 👤 `EXISTS` уместо `IN`
```sql
-- Уместо
SELECT * FROM users WHERE id IN (SELECT user_id FROM orders);

-- Боље
SELECT * FROM users WHERE EXISTS (
  SELECT 1 FROM orders WHERE orders.user_id = users.id
);
```

### 4. 🌐 `WITH` (CTE) за побољшање читљивости
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

# 📂 Партиционисање, репликација и шардинг у СУБД

---

## 🌐 Основни појмови

### 🔄 Партиционисање (Partitioning)
> Подела **једне табеле** на логичке или физичке делове (партиције) по одређеном правилу. То омогућава убрзање упита, поједностављење брисања старих података и побољшање скалабилности **у оквиру једног сервера**.

- Ради **унутар једне табеле**
- Партиције се могу **аутоматски бирати у упитима**
- Користи се за **велике количине** података: логове, телеметрију, историје операција

#### 🔹 Врсте партиционисања:
| Врста            | Опис |
|------------------|----------|
| **Range**        | По опсегу вредности (`date < '2023-01-01'`) |
| **List**         | По вредности у листи (`region IN ('US', 'EU')`) |
| **Hash**         | По хешу вредности (`user_id % 4`) |
| **Key (MySQL)**  | Специјална форма хеша са аутооптимизацијом |
| **Composite**    | Комбинација две стратегије (нпр: Range + Hash) |

#### 📚 Пример у **MySQL**:
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

#### 📚 Пример у **PostgreSQL**:
```sql
CREATE TABLE sales (
  id INT,
  region TEXT,
  sale_date DATE
) PARTITION BY LIST (region);

CREATE TABLE sales_us PARTITION OF sales FOR VALUES IN ('US');
CREATE TABLE sales_eu PARTITION OF sales FOR VALUES IN ('EU');
```

> PostgreSQL подржава декларативно партиционисање од верзије 10+

---

## 💠 Репликација (Replication)
> Аутоматско **копирање података** са једног сервера (primary/master) на други (secondary/replica).

### Зашто је потребна:
- Повећање отпорности на отказе (HA)
- Расподела оптерећења: читање са реплика, писање на мастер
- Креирање бекапа без оптерећења продукције

### Врсте репликације:

| Тип              | Карактеристике |
|------------------|-------------|
| **Асинхрона**   | Мастер не чека реплику → могу бити кашњења |
| **Полусинхрона**| Мастер чека бар 1 потврду од реплике |
| **Синхрона**    | Мастер чека све реплике → висок интегритет, али нижа перформанса |

#### 📚 Пример подешавања у **MySQL** (GTID-based):
```sql
-- На мастеру
SET GLOBAL gtid_mode = ON;
SET GLOBAL enforce_gtid_consistency = ON;
SHOW MASTER STATUS;

-- На реплици
CHANGE MASTER TO MASTER_HOST='master_ip',
  MASTER_USER='repl',
  MASTER_PASSWORD='password',
  MASTER_AUTO_POSITION = 1;
START SLAVE;
```

#### 📚 Пример у **PostgreSQL** (Streaming replication):
```bash
# На мастеру (postgresql.conf)
wal_level = replica
max_wal_senders = 5

# На реплици (recovery.conf / standby.signal)
primary_conninfo = 'host=master_ip user=replicator password=secret'
```

---

## 🚀 Шардинг (Sharding)
> Подела **података на различите физичке сервере** по неком критеријуму.  
> Ово **није уграђено у SQL**, имплементира се на нивоу апликација или middleware-а.

### Зашто је потребан:
- Хоризонтално скалирање
- Превазилажење ограничења величине и оптерећења једног сервера

### 🔹 Врсте шардинга:

| Врста             | Принцип |
|------------------|---------|
| **Хоризонтални** | Подаци се деле **по редовима** (по user_id, регионима, датуму) |
| **Вертикални**   | Табеле/колоне се деле по различитим базама (нпр: profile → DB1, logs → DB2) |

### Пример хоризонталног шардинга:
```sql
-- users_0, users_1, users_2, users_3 по хешу user_id % 4
SELECT * FROM users_2 WHERE id = 123;
```
Избор потребне табеле се врши **у апликацији**, на пример:
```python
shard_id = user_id % 4
query = f"SELECT * FROM users_{shard_id} WHERE id = {user_id}"
```

### Популарни системи за шардинг:
- **Citus** (PostgreSQL проширење)
- **Vitess** (MySQL скалирање)
- **ProxySQL**, **PgBouncer** + ручно рутирање
- **Custom sharding layer** (на нивоу backend-а)

---

## 🔍 Поређење приступа

| Приступ       | Обим | Користи се за | Ниво |
|---------------|---------|------------------|---------|
| Partitioning  | Један сервер | Повећање перформанси, архивирање | SQL/СУБД |
| Replication   | Више сервера | HA, скалирање читања | SQL/СУБД |
| Sharding      | Више сервера | Скалирање писања, обим података | Апликација/Proxy |



---

# 📊 Колонске базе података и ClickHouse

---

## 🧱 Шта је колонска (column-oriented) БД?

**Колонска СУБД** — то је база података у којој се подаци чувају **по колонама**, а не по редовима.

### 🔍 Разлике од класичних (редних) СУБД:
| Карактеристика         | Row-oriented (PostgreSQL, MySQL) | Column-oriented (ClickHouse) |
|------------------------|----------------------------------|-------------------------------|
| Организација складиштења   | По редовима (сва поља заредом)     | По колонама (све вредности једне колоне чувају се заједно) |
| Читање                 | Ефикасно при избору целих редова | Ефикасно при аналитици по неколико колона |
| Писање                 | Добро одговара за OLTP         | Оптимизовано за велике батчеве (OLAP) |
| Подршка трансакција   | Потпуна (ACID)                    | Делимична (eventual consistency, no UPDATE) |

У ClickHouse-у свака колона се чува одвојено, у виду сегмената (part-ова), што омогућава ефикасно читање само потребних колона и избегавање читања непотребних података. Свака колона је фајл или група фајлова на диску. То чини ClickHouse посебно брзим при агрегацији и филтрирању по појединачним пољима.

---

## 🚀 ClickHouse: високоперформансна аналитичка БД

**ClickHouse** — то је open-source колонска СУБД, развијена у Јандексу, намењена за обраду **аналитичких упита у реалном времену** на огромним количинама података.

### 🔹 Кључне карактеристике:
- Ултрабрзи аналитички SELECT-ови
- Колонско складиштење + компресија података
- Не захтева класичне B-tree индексе, али користи **спарс-индексе (primary indexes)** за убрзање приступа
- Подршка за кластере, шардинг и репликацију
- SQL-сличан језик упита
- Једноставан протокол: може се радити преко HTTP/HTTPS, TCP, Native API

---

## 🧠 Специфичности модела података

- Табеле се креирају са навођењем **ENGINE** (нпр: `MergeTree`)
- Сви подаци се **само додају** — нема UPDATE или DELETE у уобичајеном смислу
- **Primary Key** се користи за сортирање података и креирање **спарс-индекса** (не обезбеђује јединственост!)

### 📌 Како ради primary index у ClickHouse-у:
- То није индекс у класичном смислу (као B-tree или Hash).
- Представља **низ маркера (marks)** који указују на положај података на диску по сортираном кључу.
- Провера филтера WHERE прво се врши по овим маркерима, да би се **прескочили непотребни блокови** — то убрзава упите.