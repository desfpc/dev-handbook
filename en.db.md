[📚 Contents](README.md)

- [💾 Databases](#-databases)
  - [Transactions, Isolation Levels, and Collisions in DBMS](#-transactions-isolation-levels-and-collisions-in-dbms)
  - [Indexes and SQL Query Analysis](#-indexes-and-sql-query-analysis-explain)
  - [SQL: Grouping, JOINs, and Optimization Techniques](#-sql-grouping-joins-and-complex-query-optimization-techniques)
  - [Partitioning, Replication, and Sharding](#-partitioning-replication-and-sharding-in-dbms)
  - [Column-oriented Databases and ClickHouse](#-column-oriented-databases-and-clickhouse)
  - [NoSQL and MongoDB](#introduction-to-nosql-and-mongodb)
  - [Redis and KeyDB](#-redis-and-keydb-basics-architecture-and-advantages)

---

# 💾 Databases

# 📁 Transactions, Isolation Levels, and Collisions in DBMS

## ACID

**ACID** — four fundamental properties of transactions:

- **Atomicity**
  > A transaction is either fully executed or not executed at all. If an error occurs at any stage, a **rollback** happens.

- **Consistency**
  > After a transaction completes, the database must be in a **consistent state**: all constraints are met, data is valid. A successful transaction **does not violate** logical integrity.

- **Isolation**
  > Parallel transactions **should not affect each other**. Conflicting operations are isolated. In reality, this is achieved **through isolation levels** (see below).

- **Durability**
  > After confirmation (COMMIT), changes are saved **permanently**, even in case of power failures or hardware failures.


## 🏰 Isolation Levels

| Level            | What it allows                                | Vulnerability                  |
|------------------|-----------------------------------------------|-----------------------------|
| **READ UNCOMMITTED** | Even **uncommitted** changes are visible     | Dirty Read                 |
| **READ COMMITTED**   | Only **committed** changes are visible     | Non-repeatable Read        |
| **REPEATABLE READ** *(InnoDB default)* | Repeated SELECT within a transaction returns the same* | Phantom Read              |
| **SERIALIZABLE**     | Complete isolation, all transactions are as if **sequential** | Reduced parallelism     |

### Examples:
```sql
SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
START TRANSACTION;
SELECT * FROM users WHERE status = 'active';
COMMIT;
```


## 📂 Potential Collisions and Errors

- **Deadlock**  
  T1 locks a row that T2 is waiting for. T2 locks a resource that T1 is waiting for. MySQL **automatically terminates one of them**.
  ✅ Analysis via: `SHOW ENGINE INNODB STATUS`

- **Phantom Read**  
  `SELECT COUNT(*)` returns 100. While the transaction is active, another adds a row. Repeated `SELECT` — now 101.

- **Non-repeatable Read**  
  Read a row, then someone else updates it. Reading again — different result.


## 🏦 MySQL (MariaDB) — InnoDB, Transactions and Locks

### 🛠 InnoDB (main storage engine)

- Supports **transactions**, **isolation**, **automatic recovery**, **foreign keys**, **MVCC**.
- Stores data in **clustered indexes**, logs changes in redo/undo logs.
- Uses **REPEATABLE READ** level by default.

### 🔒 InnoDB Locks:

- **Shared (S)** — reading allowed. Multiple transactions can simultaneously take `S` on one row.
- **Exclusive (X)** — writing. Only one transaction can hold `X`.

#### Examples:
```sql
SELECT * FROM accounts WHERE id = 1 FOR UPDATE; -- X (exclusive) lock
SELECT * FROM products WHERE id = 5 LOCK IN SHARE MODE; -- S (shared) lock
```

### ⚡ Deadlock Example:
```sql
-- T1
START TRANSACTION;
UPDATE products SET stock = stock - 1 WHERE id = 1;
-- waiting for UPDATE of another row...

-- T2
START TRANSACTION;
UPDATE products SET stock = stock - 2 WHERE id = 2;
UPDATE products SET stock = stock - 1 WHERE id = 1; -- waiting for T1
```

MySQL will detect the deadlock and terminate one of the transactions.


### ✅ Recommendations for developers:
- Use **BEGIN / COMMIT / ROLLBACK** when working with more than one table.
- For row locking during changes — `SELECT ... FOR UPDATE`
- Add indexes to foreign keys (`FOREIGN KEY`) — otherwise `JOIN`s will be slow.
- Before implementing complex `JOIN`, `ORDER BY`, `GROUP BY` — run `EXPLAIN`

---

## 🔷 MyISAM (InnoDB alternative, deprecated)

- Does not support transactions!
- **Table-level locks** instead of row-level.
- Works well for **reading**, but scales poorly for writing.
- Does not support `FOREIGN KEY`.

Rarely used. Recommended to avoid in new systems.


---

## 🔷 PostgreSQL

- Supports **all isolation levels** (default: **READ COMMITTED**).
- Transactions strictly adhere to ACID.
- Uses its own implementation of **MVCC**, doesn't require locks for reading.
- Manages deadlocks well, provides `pg_stat_activity`, `pg_locks`.
- Support for `SAVEPOINT`, `ROLLBACK TO`, `SET TRANSACTION ISOLATION LEVEL`

#### Transaction example:
```sql
BEGIN;
UPDATE users SET balance = balance - 100 WHERE id = 1;
UPDATE users SET balance = balance + 100 WHERE id = 2;
COMMIT;
```

---

# 📊 Indexes and SQL Query Analysis (`EXPLAIN`)

---

## 📙 What is an index

An index in a DBMS is a **data structure that speeds up search**, filtering, and sorting of rows in a table. Essentially, it's a **sorted structure** that allows quickly finding needed values **without scanning the entire table**.

### ✅ General concepts

- **Simple index**: one column
- **Composite index**: multiple columns, order **matters!**
- **Covering index**: contains **all columns** needed by a query (reading happens only from the index)
- **Clustered index**: contains **actual rows** in the tree leaves (InnoDB)

### 🔍 Where indexes help:
- `WHERE column = value`
- `JOIN ON column`
- `ORDER BY indexed_column`
- `GROUP BY indexed_column`
- `DISTINCT indexed_column`

### ❌ Where indexes don't work:

| Bad pattern              | Why it's bad                                      |
|-----------------------------|---------------------------------------------------|
| `LEFT(name, 3) = 'abc'`     | Function breaks order → **index doesn't work** |
| `status + 1 = 2`            | Arithmetic disables the index                      |
| `OR` between columns        | Often leads to `FULL SCAN`                     |
| `LIKE '%abc'`               | No prefix → index not applicable               |
| `ORDER BY non_indexed_col` | In-memory sorting, `Using filesort`            |


---

## 🔹 Index Types

| Type         | Purpose |
|--------------|------------|
| `PRIMARY`    | InnoDB clustered index (by `id`) |
| `UNIQUE`     | Logical uniqueness (`email`, `username`) |
| `INDEX`      | Regular index for search acceleration |
| `COMPOSITE`  | Multiple columns (`(a, b)`) |
| `COVERING`   | Index covering all SELECT columns |
| `FULLTEXT`   | Text search (`MATCH ... AGAINST`) |
| `SPATIAL`    | Geo-data (`POINT`, `POLYGON`) |

### Examples:
```sql
-- Index on email
CREATE INDEX idx_users_email ON users(email);

-- Composite index
CREATE INDEX idx_status_created ON orders(status, created_at);
```

---

## 🔧 Index Data Structures

### 🔹 B-Tree / B+Tree (MySQL InnoDB, PostgreSQL)
- Main type for most indexes
- Ordered structure → **fast binary search**

### 🔹 Hash
- Unordered (MySQL: MEMORY engine)
- Suitable only for **equality `=`**
- Doesn't work with ranges, sorting, `LIKE`

### 🔹 FULLTEXT
- Only in MyISAM/InnoDB
- Works with `MATCH() AGAINST()`
- Good for full-text search, but doesn't completely replace LIKE

### 🔹 SPATIAL
- For geometry: `POINT`, `LINE`, `POLYGON`
- Used in GIS scenarios

---

## 📈 `EXPLAIN` and SQL Query Analysis

The `EXPLAIN` command shows **how a query will be executed**: which indexes are used, how many rows are checked, and if there are temporary tables.

### Example:
```sql
EXPLAIN SELECT * FROM users WHERE email = 'user@example.com';
```

### `EXPLAIN` Fields:

| Field           | Meaning |
|----------------|----------|
| `id`           | Query or subquery number |
| `select_type`  | Query type (`SIMPLE`, `PRIMARY`, `DERIVED`, etc.) |
| `type`         | Access type (best: `const`, `ref`, `range`; worst: `ALL`) |
| `possible_keys`| Which indexes could be used |
| `key`          | Which index is actually used |
| `rows`         | Approximate number of rows to check |
| `Extra`        | Additional info (`Using filesort`, `Using temporary`, etc.) |

### Diagnostics:
- **`ALL`** — full table scan
- **`Using filesort`** — in-memory sorting (slow)
- **`Using temporary`** — creating a temporary table

#### Example:
```sql
EXPLAIN SELECT * FROM orders WHERE status = 'paid' ORDER BY created_at DESC LIMIT 10;
```
If there's **no index on `(status, created_at)`** → will use `filesort`.  
Create an index:
```sql
CREATE INDEX idx_status_created ON orders(status, created_at);
```

---

## 🗄 MySQL (InnoDB) Specifics

- Clustered index = table storage structure. `PRIMARY KEY` → physical row order.
- If no PK or UNIQUE, a **hidden surrogate key** (`row_id`) is created.
- Each secondary index contains a **reference to the clustered index (PK)**.
- MySQL uses **B+Tree** for almost all index types.
- You can use **`USE INDEX`**, `FORCE INDEX` to specify which index to use.

---

## 🗄 PostgreSQL Specifics

- Each index is a **separate structure** (not clustered by default)
- `btree` — default
- Support for **multi-column and partial indexes**
- Has **GIN**, **GiST**, **BRIN**, **HASH**, **SP-GiST**:
  - **GIN** — fast search in `jsonb`, `array`, `tsvector`
  - **BRIN** — efficient on large tables with range-ordered data

### Partial index example:
```sql
CREATE INDEX idx_active_users ON users(email) WHERE active = true;
```

### Query analysis:
```sql
EXPLAIN ANALYZE SELECT * FROM users WHERE active = true;
```

- `EXPLAIN` — plan estimation
- `EXPLAIN ANALYZE` — **actual execution** with execution time

---

# 📊 SQL: Grouping, JOINs, and Complex Query Optimization Techniques

---

## 📃 Grouping and Aggregation

SQL allows **data aggregation** by groups of rows using the `GROUP BY` construct and aggregate functions.

### 📄 Standard form:
```sql
SELECT col1, SUM(col2) AS total
FROM tableName
WHERE condition
GROUP BY col1
HAVING total > 100;
```

| Element         | Purpose |
|-----------------|------------|
| `SUM(col2)`     | Aggregate function: sum of `col2` values |
| `FROM`          | Data source, can be a `JOIN` |
| `WHERE`         | Filter before grouping |
| `GROUP BY`      | Group by columns |
| `HAVING`        | Filter after aggregation |

---

### 🔗 Advanced Grouping Techniques

#### `ROLLUP`
Adds **intermediate total rows**:
```sql
SELECT department, team, SUM(salary)
FROM employees
GROUP BY department, team WITH ROLLUP;
```
We get totals for each team, department, and an overall total.

#### `CUBE`
Adds **all possible combinations** of groupings:
```sql
SELECT region, product, SUM(sales)
FROM sales_data
GROUP BY CUBE(region, product);
```

#### `GROUPING SETS`
Flexible control over groups:
```sql
SELECT manufacturer, product_count, SUM(price)
FROM products
GROUP BY GROUPING SETS (
  ROLLUP(manufacturer),
  (product_count),
  (manufacturer, product_count)
);
```

#### `OVER()` and window aggregate functions
Allows **aggregation** while **keeping rows unchanged**:
```sql
SELECT user_id, region, salary,
       SUM(salary) OVER (PARTITION BY region) AS regional_total
FROM employees;
```
> Used in PostgreSQL, MySQL 8+, SQLite, SQL Server, Oracle

---

## 🔀 JOIN — Table Joining

Joins allow **combining rows from two or more tables** based on a logical condition.

### 📋 JOIN Types:
<img src="https://raw.githubusercontent.com/desfpc/dev-handbook/master/sql_joins.png" alt="dev-handbook" width="500">

| JOIN | Purpose | Example |
|------|------------|--------|
| `INNER JOIN` | Only matching rows | `users INNER JOIN orders ON users.id = orders.user_id` |
| `LEFT JOIN`  | All from left + matching from right | `users LEFT JOIN orders` — even those without orders |
| `RIGHT JOIN` | All from right + matching from left | Rarely used in MySQL, better to swap the order |
| `FULL OUTER JOIN` | All rows from both tables | Not directly supported in MySQL, but available in PostgreSQL |
| `CROSS JOIN` | Cartesian product (all combinations) | Used rarely and with caution |
| `SELF JOIN` | Table joins with itself | `employees e1 JOIN employees e2 ON e1.manager_id = e2.id` |

### 🔍 Examples:
```sql
-- Users without orders
SELECT u.*
FROM users u
LEFT JOIN orders o ON u.id = o.user_id
WHERE o.id IS NULL;

-- Last order of each customer (with window function)
SELECT *
FROM (
  SELECT *,
         ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY created_at DESC) as rn
  FROM orders
) t
WHERE rn = 1;
```

---

## 🧠 Useful Techniques for Building Complex Queries

### 1. ❌ Replacing `OR` with `UNION`

```sql
-- Bad for indexes:
SELECT * FROM users
WHERE status = 'active' OR email LIKE '%@example.com';

-- Better:
SELECT * FROM users WHERE status = 'active'
UNION
SELECT * FROM users WHERE email LIKE '%@example.com';
```
> Why: `OR` disrupts index usage, `UNION` allows each SELECT to run independently and use appropriate indexes.

### 2. ✅ Anti-`JOIN`: finding records without a relationship
```sql
SELECT p.*
FROM products p
LEFT JOIN orders o ON p.id = o.product_id
WHERE o.id IS NULL;
```

### 3. 👤 `EXISTS` instead of `IN`
```sql
-- Instead of
SELECT * FROM users WHERE id IN (SELECT user_id FROM orders);

-- Better
SELECT * FROM users WHERE EXISTS (
  SELECT 1 FROM orders WHERE orders.user_id = users.id
);
```

### 4. 🌐 `WITH` (CTE) for improved readability
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

# 📂 Partitioning, Replication, and Sharding in DBMS

---

## 🌐 Basic Concepts

### 🔄 Partitioning
> Dividing **one table** into logical or physical parts (partitions) according to a specific rule. This speeds up queries, simplifies deletion of old data, and improves scalability **within a single server**.

- Works **within a single table**
- Partitions can be **automatically selected in queries**
- Used for **high volumes** of data: logs, telemetry, operation histories

#### 🔹 Partitioning Types:
| Type             | Description |
|------------------|----------|
| **Range**        | By value range (`date < '2023-01-01'`) |
| **List**         | By value in a list (`region IN ('US', 'EU')`) |
| **Hash**         | By value hash (`user_id % 4`) |
| **Key (MySQL)**  | Special form of hash with auto-optimization |
| **Composite**    | Combination of two strategies (e.g., Range + Hash) |

#### 📚 Example in **MySQL**:
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

#### 📚 Example in **PostgreSQL**:
```sql
CREATE TABLE sales (
  id INT,
  region TEXT,
  sale_date DATE
) PARTITION BY LIST (region);

CREATE TABLE sales_us PARTITION OF sales FOR VALUES IN ('US');
CREATE TABLE sales_eu PARTITION OF sales FOR VALUES IN ('EU');
```

> PostgreSQL supports declarative partitioning from version 10+

---

## 💠 Replication
> Automatic **data copying** from one server (primary/master) to another (secondary/replica).

### Why it's needed:
- Increased fault tolerance (HA)
- Load distribution: reading from replicas, writing to master
- Creating backups without production load

### Replication types:

| Type              | Features |
|------------------|-------------|
| **Asynchronous**   | Master doesn't wait for replica → can have delays |
| **Semi-synchronous**| Master waits for at least 1 confirmation from replica |
| **Synchronous**    | Master waits for all replicas → high integrity, but lower performance |

#### 📚 Setup example in **MySQL** (GTID-based):
```sql
-- On master
SET GLOBAL gtid_mode = ON;
SET GLOBAL enforce_gtid_consistency = ON;
SHOW MASTER STATUS;

-- On replica
CHANGE MASTER TO MASTER_HOST='master_ip',
  MASTER_USER='repl',
  MASTER_PASSWORD='password',
  MASTER_AUTO_POSITION = 1;
START SLAVE;
```

#### 📚 Example in **PostgreSQL** (Streaming replication):
```bash
# On master (postgresql.conf)
wal_level = replica
max_wal_senders = 5

# On replica (recovery.conf / standby.signal)
primary_conninfo = 'host=master_ip user=replicator password=secret'
```

---

## 🚀 Sharding
> Dividing **data across different physical servers** by some criterion.  
> This is **not built into SQL**, implemented at the application or middleware level.

### Why it's needed:
- Horizontal scaling
- Bypassing size and load limitations of a single server

### 🔹 Sharding types:

| Type              | Principle |
|------------------|---------|
| **Horizontal** | Data is divided **by rows** (by user_id, regions, date) |
| **Vertical**   | Tables/columns are divided across different DBs (e.g., profile → DB1, logs → DB2) |

### Horizontal sharding example:
```sql
-- users_0, users_1, users_2, users_3 by hash user_id % 4
SELECT * FROM users_2 WHERE id = 123;
```
The choice of the right table is made **in the application**, for example:
```python
shard_id = user_id % 4
query = f"SELECT * FROM users_{shard_id} WHERE id = {user_id}"
```

### Popular sharding systems:
- **Citus** (PostgreSQL extension)
- **Vitess** (MySQL scaling)
- **ProxySQL**, **PgBouncer** + manual routing
- **Custom sharding layer** (at backend level)

---

## 🔍 Approach Comparison

| Approach        | Scale | Used for | Level |
|---------------|---------|------------------|---------|
| Partitioning  | Single server | Performance improvement, archiving | SQL/DBMS |
| Replication   | Multiple servers | HA, read scaling | SQL/DBMS |
| Sharding      | Multiple servers | Write scaling, data volume | Application/Proxy |


---

# 📊 Column-oriented Databases and ClickHouse

---

## 🧱 What is a column-oriented DB?

**Column-oriented DBMS** is a database where data is stored **by columns**, not by rows.

### 🔍 Differences from classical (row-oriented) DBMS:
| Characteristic         | Row-oriented (PostgreSQL, MySQL) | Column-oriented (ClickHouse) |
|------------------------|----------------------------------|-------------------------------|
| Storage organization   | Row-based (all fields in sequence)     | Column-based (all values of one column stored together) |
| Reading                 | Efficient when selecting entire rows | Efficient for analytics on a few columns |
| Writing                 | Well-suited for OLTP         | Optimized for large batches (OLAP) |
| Transaction support   | Full (ACID)                    | Partial (eventual consistency, no UPDATE) |

In ClickHouse, each column is stored separately, in segments (parts), which allows efficiently reading only the needed columns and avoiding reading unnecessary data. Each column is a file or group of files on disk. This makes ClickHouse particularly fast for aggregation and filtering by individual fields.

---

## 🚀 ClickHouse: High-performance Analytical DB

**ClickHouse** is an open-source column-oriented DBMS, developed by Yandex, designed for processing **analytical queries in real-time** on huge volumes of data.

### 🔹 Key features:
- Ultra-fast analytical SELECTs
- Column storage + data compression
- Doesn't require classical B-tree indexes, but uses **sparse indexes (primary indexes)** to speed up access
- Support for clusters, sharding, and replication
- SQL-like query language
- Simple protocol: can work via HTTP/HTTPS, TCP, Native API

---

## 🧠 Data Model Features

- Tables are created with an **ENGINE** specification (e.g., `MergeTree`)
- All data is **append-only** — no UPDATE or DELETE in the usual sense
- **Primary Key** is used for data sorting and creating a **sparse index** (it doesn't ensure uniqueness!)

### 📌 How primary index works in ClickHouse:
- It's not an index in the classical sense (like B-tree or Hash).
- It represents a **sequence of marks** pointing to data positions on disk by the sorted key.
- WHERE filter checks are first done against these marks to **skip unnecessary blocks** — this is what speeds up queries.

> Example: if you set `ORDER BY (date, user_id)`, ClickHouse will create a sparse-index on these fields.

```sql
CREATE TABLE visits (
  date Date,
  user_id UInt64,
  url String
) ENGINE = MergeTree()
ORDER BY (date, user_id);
```

---

## 🔒 ACID in ClickHouse

ClickHouse **is not a fully ACID-compliant DBMS**:

| Property | Support | Comment |
|----------|-----------|-------------|
| **Atomicity**    | ✅ at insertion level | INSERT is atomic, but no `BEGIN/COMMIT` |
| **Consistency**  | ⚠️ weak            | Foreign keys or constraints not checked |
| **Isolation**    | ⚠️ weak            | No locks, no full transaction isolation |
| **Durability**   | ✅                   | All data is written to disk in columnar format |

ClickHouse is an OLAP engine, and **it's optimized for analytics speed, not transactional integrity**.

---

## ✏️ Data Insertion (INSERT)

### 🔥 Best practices for inserting data:
- **In batches!** For example: 10,000+ rows at once — significantly faster
- Use **`INSERT INTO table FORMAT ...`** with external formats:
  - `TabSeparated`
  - `CSV`
  - `JSONEachRow`

### Examples:
```sql
INSERT INTO visits (date, user_id, url) VALUES
('2024-01-01', 1, 'google.com'),
('2024-01-01', 2, 'yandex.ru');

-- via file
cat data.tsv | curl 'http://localhost:8123/?query=INSERT+INTO+visits+FORMAT+TabSeparated' --data-binary @-
```

---

## 📄 SELECT and Analytics

ClickHouse supports:
- Aggregations: `SUM()`, `AVG()`, `COUNT()`, `uniq()`, `median()`, `quantile()`
- Grouping and window functions (`OVER`, `PARTITION BY`)
- Working with time series (functions for `toStartOfDay`, `now()`, `interval`)
- Arrays, JSON, nested structures
- **PREWHERE** — pre-filter before disk reading

### Analytics example:
```sql
SELECT region, count() AS views
FROM visits
WHERE event_date >= today() - 7
GROUP BY region
ORDER BY views DESC
LIMIT 10;
```

---

## 🏗️ Table Engines

Most common: `MergeTree` and its derivatives

| ENGINE            | Description |
|-------------------|----------|
| `MergeTree`       | Basic and most flexible engine |
| `ReplacingMergeTree` | Removes duplicates by replacement key |
| `SummingMergeTree`   | Automatic aggregation |
| `AggregatingMergeTree` | For pre-aggregated data |
| `Log`, `TinyLog`  | For small volumes or debugging |

```sql
CREATE TABLE metrics (
  date Date,
  user_id UInt64,
  views UInt32
) ENGINE = MergeTree()
ORDER BY (date, user_id);
```

---

## 📊 Replication and Sharding

- **Replication**: via `ReplicatedMergeTree` (ZooKeeper required)
- **Sharding**: division by shard key, works in a cluster via config
- Uses **Distributed table** as a facade for queries to shards

---

## 🧪 Reliability Mechanisms

- Each insertion is append-only, doesn't overwrite old data
- Has `DETACH PART` and `DROP PART` for manual fragment removal
- TTL support: `ALTER TABLE ... MODIFY TTL ...` — auto-deletion of data

---

## ✅ When to Use ClickHouse

| Suitable for | Not suitable for |
|----------|-------------|
| Real-time analytics (dashboards, BI) | Transactional applications (CRM, accounting) |
| Large logs and metrics | Frequent UPDATE/DELETE |
| Time series | Storing complex related objects |
| Streaming analytics (event tracking) | Complex referential integrity constraints |

---

# Introduction to NoSQL and MongoDB

---

## ☁️ What is NoSQL

**NoSQL (Not Only SQL)** is a general term for databases not based on the relational model. They focus on **storage flexibility, horizontal scaling, and high availability**.

### 🔹 Key NoSQL features:
- Flexible schema (or schema-less)
- Well-suited for Big Data and real-time
- "Horizontal" scalability (to new servers)
- Simple replication and sharding
- Variety of data models

---

## 📚 NoSQL DB Types:

| Type | Example | Where used |
|-----|--------|-----------------|
| Document | MongoDB, Couchbase | Web, API, CMS, analytics |
| Column | Cassandra, HBase | Telemetry storage, logs |
| Key-value | Redis, DynamoDB | Cache, queues, sessions |
| Graph | Neo4j, ArangoDB | Social networks, object relationships |

---

## 🍃 MongoDB: Basics and Architecture

**MongoDB** is the most popular document-oriented NoSQL DB.  
Document = JSON structure (in Mongo — BSON: Binary JSON).

### 🔸 Main elements:
- **Database** — database
- **Collection** — table equivalent
- **Document** — row equivalent (JSON/BSON)

```json
{
  "_id": ObjectId("...")
  "name": "Alice",
  "age": 30,
  "email": "alice@example.com",
  "tags": ["admin", "editor"]
}
```

### 🔸 Strengths:
- No fixed schema — documents can differ
- Flexibility and nesting (nested documents, arrays)
- Transaction support (starting from MongoDB 4.0)
- Scalability through **sharding**
- Replication via **Replica Set**

---

## 🔧 CRUD in MongoDB

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

## 🧠 Indexing

MongoDB supports **various types of indexes**:
- `db.collection.createIndex({ name: 1 })` — ascending
- `db.collection.createIndex({ name: -1 })` — descending
- Compound, unique, TTL, text, geo-indexes

### Use `explain()`:
```js
db.users.find({ name: "Alice" }).explain("executionStats")
```

---

## 🔀 Replication and Scaling

### 🔁 Replica Set
- Multiple nodes: Primary + Secondaries
- Automatic failover
- All writes go to Primary, reading — optional

### 🧩 Sharding
- Dividing collection into chunks by key (shard key)
- Increases scale, reduces load on individual nodes

---

## 📦 Aggregation Framework

Mongo supports a powerful **pipeline for data processing**:
```js
db.orders.aggregate([
  { $match: { status: "paid" }},
  { $group: { _id: "$customer_id", total: { $sum: "$amount" }}},
  { $sort: { total: -1 }}
]);
```
- Can filter, group, sort, transform
- Suitable for reports and analytics within Mongo

---

## 🔐 Security and Limitations
- Authorization (`role`, `user`) + TLS
- Document size limit: 16 MB
- Multi-document transactions from version 4.0
- Transactions across collections and databases — from 4.2+

---

## 📎 When to Use MongoDB?

✅ Great for:
- Flexible data, without strict schema
- Rapid API development
- Complex nested structures
- Large data volumes with sharding

⛔ Not the best choice:
- Complex SQL queries and joins
- Strong ACID consistency for all operations
- Schema constraints and strict types

---

# 🚀 Redis and KeyDB: Basics, Architecture, and Advantages

---

## 🔸 What is Redis

**Redis** is a high-performance **key-value store** that works in memory. It can be used as:

- Cache
- Message broker (pub/sub)
- Session storage
- Task queue
- Lightweight DB with TTL

Supports **persistence**, replication, LUA scripts, and multiple data types.

---

## ⚙️ Architecture and Working Principles

- **All operations are performed in RAM**
- Can be configured to write to disk (RDB, AOF)
- Supports **master → replica replication**
- Supports **Redis Cluster** for scaling

### Main data types:
| Type         | Usage example |
|--------------|-----------------------|
| `String`     | Value caching, tokens |
| `Hash`       | Object storage (user:1 → {"name": "Alice"}) |
| `List`       | FIFO/LIFO queues |
| `Set`        | Unique values (unordered) |
| `Sorted Set` | Leaderboards by score, priority lists |
| `Bitmap`     | Flags, online status |
| `HyperLogLog`| Unique element estimation |

---

## 🔧 Basic Commands

### 🔹 Keys and strings
```bash
SET key value
GET key
DEL key
EXPIRE key 60
```

### 🔹 Hashes
```bash
HSET user:1 name "Bob"
HGET user:1 name
HGETALL user:1
```

### 🔹 Lists (queues)
```bash
LPUSH queue task1
RPUSH queue task2
LPOP queue
RPOP queue
```

### 🔹 Sets and sorting
```bash
SADD online_users 123
SISMEMBER online_users 123
ZADD leaderboard 1500 "Alice"
ZRANGE leaderboard 0 -1 WITHSCORES
```

---

## 📦 Redis Application Examples

- API response cache (`GET /user/1 → CACHE`)  
- JWT and access token storage
- Rate limiting (`INCR user:ip`) + `EXPIRE`
- Task Queue for background tasks (Celery, Sidekiq)
- Pub/Sub mechanisms (chat, notifications)

---

## 🛠 Redis Persistence

| Method | Description |
|-------|----------|
| `RDB` | Snapshot to disk at specified time intervals |
| `AOF` | Log of each command (can be combined with RDB) |
| `No persistence` | Memory only — data is lost on restart |

Configuration via `redis.conf`:
```bash
appendonly yes
appendfsync everysec
```

---

## 🔁 Replication and Clustering

- **Replica** — copy of master, can be read-only
- **Sentinel** — automatic failover mechanism
- **Redis Cluster** — automatic sharding by key (hash slots)

---

## 🔐 Security

- Authentication via `requirepass`
- IP access restriction (firewall/proxy)
- TLS connections (starting from Redis 6.0)

---

# 💎 KeyDB — Redis Fork with Extensions

**KeyDB** is a Redis-compatible DBMS that includes several improvements and can serve as a **direct replacement for Redis without code changes**.

## 🆚 Differences from Redis

| Feature             | Redis | KeyDB |
|-------------------------|-------|--------|
| Multi-threading         | ❌    | ✅ (up to 4 threads) |
| Active-Active replication| ❌    | ✅ |
| Built-in authentication (JWT) | ❌ | ✅ |
| Enhanced persistence | partially | ✅ |
| Redis API compatibility | ✅ | ✅ |

## 🚀 KeyDB Advantages:

- Multi-threading = more throughput
- Support for Active-Active (multi-master) replication
- Works "out of the box" with Redis clients
- Better performance in clouds and on CPUs with many cores

## 📥 KeyDB Installation (Linux/macOS):

### Via Docker:
```bash
docker run -d -p 6379:6379 eqalpha/keydb
```

### Manual build from source:
```bash
git clone https://github.com/Snapchat/KeyDB.git
cd KeyDB
make -j$(nproc)
sudo make install
```

### Configuration:
```bash
keydb-server /etc/keydb/keydb.conf
```

---

## ✅ When to Use Redis or KeyDB?

| Scenario | Recommendation |
|----------|--------------|
| High load and cache | Redis ✅ / KeyDB ✅ |
| Want more threads and CPU | KeyDB ✅ |
| Need Active-Active | KeyDB only ✅ |
| Minimal features, simplicity | Redis ✅ |

---
