---
name: sql-server-cheatsheet
description: T-SQL / SQL Server reference covering DDL & DML basics, filtering operators, aggregation (COUNT/SUM/AVG/HAVING), all JOIN types (inner, left, right, full outer, self, cross), UNION/UNION ALL/INTERSECT/EXCEPT, window functions (ROW_NUMBER, RANK, DENSE_RANK, NTILE, PARTITION BY), CASE statements, subqueries (SELECT/FROM/WHERE), CAST/CONVERT, string functions (TRIM, REPLACE, SUBSTRING, CHARINDEX, UPPER/LOWER), stored procedures, temp tables, CTEs, views, and data-cleaning patterns (ISNULL joins, splitting delimited strings). Use this skill whenever the user asks to write, fix, explain, or teach a SQL Server / T-SQL query, is unsure which JOIN or window function to use, needs syntax for GROUP BY/HAVING/CASE/subqueries, or is doing data-cleaning tasks like the Nashville Housing style ISNULL/SUBSTRING patterns. Trigger even if they just say "how do I join these tables" or "rank rows by salary" without mentioning SQL explicitly.
---

# SQL Server (T-SQL) Cheatsheet

A condensed reference distilled from hand-written study notes covering core T-SQL syntax through
window functions, subqueries, temp objects, and data-cleaning idioms. Use it to write correct,
idiomatic T-SQL quickly, and to explain *why* a construct works the way it does when teaching.

When answering a query-writing request: pick the smallest correct construct from below (don't
reach for a CTE when a WHERE clause will do), match the exact keyword order T-SQL expects, and
prefer the explicit `JOIN ... ON` syntax over comma-joins in old-style implicit joins unless the
user's existing code already uses that style.

## SQL command families

| Family | Purpose | Commands |
|---|---|---|
| DDL — Data Definition | define schema | CREATE, DROP, ALTER, TRUNCATE |
| DML — Data Manipulation | manipulate data | INSERT, UPDATE, DELETE |
| DCL — Data Control | access rights | GRANT, REVOKE |
| TCL — Transaction Control | transactions | COMMIT, ROLLBACK |
| DQL — Data Query | retrieve data | SELECT |

## Database & table basics

```sql
create database SaleOrder
use SaleOrder

create table dbo.customer (
  CustomerID int NOT null primary key,
  CustomerFirstName varchar(50) NOT null,
  CustomerLastName varchar(50) NOT null,
  CustomerAddress varchar(50) NOT null,
  CustomerSuburb varchar(50) null,
  CustomerCity varchar(50) NOT null,
  CustomerPostCode char(4) null,
  CustomerPhoneNumber char(12) null
);

create table dbo.sale (
  SaleID tinyint not null primary key,
  CustomerID int not null references customer(CustomerID),
  InventoryID tinyint not null references Inventory(InventoryID),
  EmployeeID tinyint not null references Employee(EmployeeID),
  SaleDate date not null,
  SaleQuantity int not null,
  SaleUnitPrice smallmoney not null
);
```

- `identity(1,1)` = auto-increment primary key. `unique check (col>0)` combines a unique
  constraint with a check constraint. `default 'value'` sets a column default.
- `select * from information_schema.tables` — list tables in the current database.
- `select top 2 * from customer` — first N rows. `select top 40 percent * from customer` — first N% of rows.
- `order by customerlastname desc` — sort; default is ascending. You can order by column position:
  `order by 4, 2, 3 desc`.
- `select distinct customerlastname from customer` — unique values only.

### Alter / insert / update / delete

```sql
alter table customer add phonenumber varchar(20)
alter table customer drop column phonenumber
alter table customer alter column phonenumber varchar(10)

insert into customer values
(100,'Fang Ying','Sham','418999','sdadasfdfd',default),
(200,'Mei Mei','Tan',default,'adssdsasd','Thailand')

update customer set phonenumber='12345455' where customerid=1

delete from customer where country='Thailand'   -- no WHERE = deletes every row
drop table customer
```

## Filtering operators

```sql
-- LIKE: _ matches exactly one char, % matches zero/one/many chars
select * from customer where customerlastname like '_r%'

-- IN: match against a list
select * from customer where customerlastname in ('Brown','Michael','Jim')

select * from customer where customerlastname > 'Brown'   -- comparison operators work on strings too
select * from customer where customerlastname <> 'Brown'  -- not equal
select * from customer where customerlastname IS NULL
select * from customer where customerlastname IS NOT NULL
select * from sale where saleunitprice between 5 and 10    -- inclusive
```

## Aggregation, GROUP BY, HAVING

```sql
select count(*) as [Number of Records] from customer where customerfirstname like 'B%'

select sale.employeeid, EmployeeFirstName, EmployeeLastName,
       count(*) as [Number of order], sum(salequantity) as [Total Quantity]
from sale, employee
where sale.employeeid = employee.employeeid
group by sale.employeeid, EmployeeFirstName, EmployeeLastName

select month(saledate) as [Month], count(*) as [Number of sale],
       sum(salequantity*saleunitprice) as [Total Amount]
from sale
group by month(saledate)

SELECT MAX(Salary) FROM EmployeeSalary
SELECT MIN(Salary) FROM EmployeeSalary
SELECT AVG(Salary) FROM EmployeeSalary

-- HAVING filters on the aggregated group (WHERE can't reference aggregates)
SELECT JobTitle, COUNT(JobTitle)
FROM EmployeeDemographics ED JOIN EmployeeSalary ES ON ED.EmployeeID = ES.EmployeeID
GROUP BY JobTitle HAVING COUNT(JobTitle) > 1

SELECT JobTitle, AVG(Salary)
FROM EmployeeDemographics ED JOIN EmployeeSalary ES ON ED.EmployeeID = ES.EmployeeID
GROUP BY JobTitle HAVING AVG(Salary) > 45000 ORDER BY AVG(Salary)
```

## Joins

```
              SQL JOINS
   Inner | Self | Outer | Cross
                  |
     Left Outer / Right Outer / Full Outer
```

- **Inner join**: only rows with matches in both tables.
- **Left outer join**: all rows from the left table, NULLs where no right match.
- **Right outer join**: all rows from the right table, NULLs where no left match.
- **Full outer join**: all rows from both, NULLs where no match on either side.
- **Self join**: a table joined to itself, common for hierarchies (employee → manager).
- **Cross join**: Cartesian product — every combination of rows (no ON clause).

```sql
-- implicit join (old style, still common in legacy code)
select * from inventory, sale where sale.inventoryid = inventory.inventoryid

-- explicit inner join (preferred)
select inventoryname, saledate, saleunitprice, salequantity,
       saleunitprice*salequantity as [Total Amount]
from inventory inner join sale on sale.inventoryid = inventory.inventoryid
order by inventoryname

-- left join: keep every inventory row even with no matching sale
select inventory.inventoryid, inventoryname
from inventory left join sale on sale.inventoryid = inventory.inventoryid

-- left join restricted to inventory items that never sold (anti-join)
select inventory.inventoryid, inventoryname
from inventory left join sale on sale.inventoryid = inventory.inventoryid
where sale.inventoryid is NULL
-- equivalent without a join, using a subquery:
select inventoryid, inventoryname from inventory
where inventoryid not in (select inventoryid from sale)

-- right join
select sale.inventoryid, inventoryname
from inventory right join sale on sale.inventoryid = inventory.inventoryid

-- full outer join, restricted to non-matching rows only
select sale.inventoryid, inventoryname
from inventory full outer join sale on sale.inventoryid = inventory.inventoryid
where sale.inventoryid is NULL

-- self join (staff → manager hierarchy)
select E.employeeID, E.employeefirstname+' '+E.employeelastname as [Full Name],
       E.managerID, M.employeefirstname+' '+M.employeelastname as [Manager Name]
from staff E
inner join staff M on E.managerID = M.employeeID
-- change to `left outer join staff M` to also list employees who have no manager

-- cross join: every combination of rows (Cartesian product)
select * from inventory1 cross join inventory2
```

## UNION family

| Operation | Behaviour |
|---|---|
| `UNION` | combine two result sets, dedupe. Columns/types must match; no common key required. |
| `UNION ALL` | combine and keep duplicates. |
| `INTERSECT` | keep only rows present in both queries, dedupe. |
| `EXCEPT` | keep rows unique to the first query only. |

```sql
select cust_lname, cust_fname from customer
union
select cust_lname, cust_fname from customer_2

select cust_lname, cust_fname from customer
intersect
select cust_lname, cust_fname from customer_2

-- INTERSECT via explicit join (equivalent)
select c.cust_lname, c.cust_fname from customer c, customer_2 c2
where c.cust_lname = c2.cust_lname and c.cust_fname = c2.cust_fname

select cust_lname, cust_fname from customer
except
select cust_lname, cust_fname from customer_2

-- EXCEPT via subquery (equivalent)
select cust_lname, cust_fname from customer
where (cust_lname) not in (select cust_lname from customer_2)
  and (cust_fname) not in (select cust_fname from customer_2)
```

## Window / ranking functions

All use `OVER (...)`. Add `PARTITION BY <col>` to restart the calculation within each group;
without it, the function runs across the whole result set.

| Function | Behaviour on ties |
|---|---|
| `ROW_NUMBER()` | always unique, sequential — breaks ties arbitrarily |
| `RANK()` | ties get the same rank, then **skips** the next rank number(s) |
| `DENSE_RANK()` | ties get the same rank, **no gap** in the following numbers |
| `NTILE(n)` | splits rows into `n` roughly-equal ranked buckets |

```sql
SELECT *, ROW_NUMBER() OVER (ORDER BY Salary DESC) SalaryRank FROM EmployeeSalary

SELECT *, RANK() OVER (PARTITION BY JobTitle ORDER BY Salary DESC) SalaryRank
FROM EmployeeSalary ORDER BY JobTitle, SalaryRank

SELECT *, DENSE_RANK() OVER (ORDER BY Salary DESC) SalaryRank
FROM EmployeeSalary ORDER BY SalaryRank

-- NTILE(3): splits into 3 groups; PARTITION BY restarts bucketing per JobTitle
SELECT *, NTILE(3) OVER (PARTITION BY JobTitle ORDER BY Salary DESC) SalaryRank
FROM EmployeeSalary ORDER BY JobTitle, SalaryRank

-- PARTITION BY without ranking: a running aggregate per row
SELECT FirstName, LastName, Gender, Salary,
       COUNT(Gender) OVER (PARTITION BY Gender) AS TotalGender
FROM EmployeeDemographics ED JOIN EmployeeSalary ES ON ED.EmployeeID = ES.EmployeeID
```

## CASE statement

```sql
SELECT FirstName, LastName, Age,
  CASE
    WHEN Age > 30 THEN 'Old'
    WHEN Age BETWEEN 27 AND 30 THEN 'Young'
    ELSE 'Baby'
  END
FROM EmployeeDemographics WHERE Age IS NOT NULL ORDER BY Age

SELECT FirstName, LastName, JobTitle, Salary,
  CASE
    WHEN JobTitle = 'Salesman' THEN Salary + (Salary * .10)
    WHEN JobTitle = 'Accountant' THEN Salary + (Salary * .05)
    WHEN JobTitle = 'HR' THEN Salary + (Salary * .000001)
    ELSE Salary + (Salary * .03)
  END AS SalaryAfterRaise
FROM EmployeeDemographics ED JOIN EmployeeSalary ES ON ED.EmployeeID = ES.EmployeeID
```

## Subqueries

```sql
-- in SELECT: a scalar value repeated on every row
SELECT EmployeeID, Salary, (SELECT AVG(Salary) FROM EmployeeSalary) AS AllAvgSalary
FROM EmployeeSalary
-- equivalent using a window function instead of a correlated scalar subquery
SELECT EmployeeID, Salary, AVG(Salary) OVER () AS AllAvgSalary FROM EmployeeSalary

-- in FROM: query an inline derived table
SELECT a.EmployeeID, AllAvgSalary
FROM (SELECT EmployeeID, Salary, AVG(Salary) OVER () AS AllAvgSalary FROM EmployeeSalary) a
ORDER BY a.EmployeeID

-- in WHERE
SELECT EmployeeID, JobTitle, Salary FROM EmployeeSalary
WHERE EmployeeID IN (SELECT EmployeeID FROM EmployeeDemographics WHERE Age > 30)

SELECT EmployeeID, JobTitle, Salary FROM EmployeeSalary
WHERE Salary IN (SELECT MAX(Salary) FROM EmployeeSalary)
```

## CAST / CONVERT

```sql
-- CAST(expression AS datatype(length))
SELECT CAST('2017-08-25 00:00:00.000' AS date)

-- CONVERT(data_type(length), expression, style)
SELECT CONVERT(date, '2017-08-25 00:00:00.000')
```

## String functions

```sql
Select EmployeeID, TRIM(EmployeeID) AS IDTRIM FROM EmployeeErrors   -- strips leading+trailing space
Select EmployeeID, RTRIM(EmployeeID) as IDRTRIM FROM EmployeeErrors -- trailing only
Select EmployeeID, LTRIM(EmployeeID) as IDLTRIM FROM EmployeeErrors -- leading only

Select LastName, REPLACE(LastName, '- Fired', '') as LastNameFixed FROM EmployeeErrors

Select Substring(err.FirstName,1,3), Substring(dem.FirstName,1,3)
FROM EmployeeErrors err
JOIN EmployeeDemographics dem
  on Substring(err.FirstName,1,3) = Substring(dem.FirstName,1,3)
 and Substring(err.LastName,1,3)  = Substring(dem.LastName,1,3)

Select firstname, LOWER(firstname) from EmployeeErrors
Select Firstname, UPPER(FirstName) from EmployeeErrors
```

## Stored procedures

```sql
CREATE PROCEDURE Temp_Employee
  @JobTitle nvarchar(100)
AS
DROP TABLE IF EXISTS #temp_employee
Create table #temp_employee (
  JobTitle varchar(100), EmployeesPerJob int, AvgAge int, AvgSalary int
)
Insert into #temp_employee
SELECT JobTitle, Count(JobTitle), Avg(Age), AVG(salary)
FROM EmployeeDemographics emp JOIN EmployeeSalary sal ON emp.EmployeeID = sal.EmployeeID
where JobTitle = @JobTitle   -- must match the parameter name declared above
group by JobTitle
Select * From #temp_employee
GO;

-- run it again later with a different parameter, no need to redefine the procedure
EXEC Temp_Employee @JobTitle = 'Salesman'
```

## Temp tables, CTEs, Views, duplicate tables

| Object | Updates with base table? | Notes |
|---|---|---|
| VIEW | yes — it's a saved query | `create view CustomerView as select ...` |
| Temp table (`#name`) | no — a real physical copy | lives in tempdb; supports CRUD, joins, indexes |
| CTE | no — scoped to one query | lives in memory; **cannot be indexed** |
| `select ... into newtable` | no — a real physical copy | duplicates result set into a new base table |

```sql
create view CustomerView as
select customerfirstname+' '+customerlastname as [Customer Name], customerphonenumber,
       inventoryname, saledate, salequantity, saleunitprice,
       salequantity*saleunitprice as [Total Amount]
from customer inner join sale on customer.customerid = sale.customerid
inner join inventory on sale.inventoryid = inventory.inventoryid

DROP TABLE IF EXISTS #temp_Employee
Create table #temp_Employee (
  JobTitle varchar(100), EmployeesPerJob int, AvgAge int, AvgSalary int
)
Insert INTO #temp_Employee
SELECT JobTitle, Count(JobTitle), Avg(Age), AVG(salary)
FROM EmployeeDemographics emp JOIN EmployeeSalary sal ON emp.EmployeeID = sal.EmployeeID
group by JobTitle
SELECT * FROM #temp_Employee

WITH CTE_Employee AS (
  SELECT FirstName, LastName, Gender, Salary,
         COUNT(Gender) OVER (PARTITION BY Gender) AS TotalGender
  FROM EmployeeDemographics ED JOIN EmployeeSalary ES ON ED.EmployeeID = ES.EmployeeID
  WHERE Salary > '45000'
)
SELECT FirstName, LastName, Gender, TotalGender
FROM CTE_Employee
WHERE TotalGender = (SELECT MIN(TotalGender) FROM CTE_Employee)

-- select ... into: save a query result as a new base table
select distinct customerlastname into temp from customer order by customerlastname
select * from temp   -- data types carry over
```

## Data-cleaning patterns

```sql
-- ISNULL(expression, value): backfill NULLs by matching another row on a shared key
-- (classic "Nashville Housing" pattern: same ParcelID implies same PropertyAddress)
Select a.ParcelID, a.PropertyAddress, b.ParcelID, b.PropertyAddress,
       ISNULL(a.PropertyAddress, b.PropertyAddress)
From NashvilleHousing a JOIN NashvilleHousing b
  on a.ParcelID = b.ParcelID AND a.[UniqueID] <> b.[UniqueID]
Where a.PropertyAddress is null

-- Same join used to actually fix the data:
Update a SET PropertyAddress = ISNULL(a.PropertyAddress, b.PropertyAddress)
From NashvilleHousing a JOIN NashvilleHousing b
  on a.ParcelID = b.ParcelID AND a.[UniqueID] <> b.[UniqueID]
Where a.PropertyAddress is null

-- Split a "street, city" address into two columns
SELECT PropertyAddress,
  SUBSTRING(PropertyAddress, 1, CHARINDEX(',', PropertyAddress) -1) as Address,
  SUBSTRING(PropertyAddress, CHARINDEX(',', PropertyAddress) + 1, LEN(PropertyAddress)) as City
From NashvilleHousing

ALTER TABLE NashvilleHousing ADD PropertySplitAddress Nvarchar(255);
ALTER TABLE NashvilleHousing ADD PropertySplitCity Nvarchar(255);
```
- `SUBSTRING(string, start, length)` — extract a substring.
- `CHARINDEX(substring, string, [start])` — find the position of a substring.
- `LEN(string)` — string length.

## Worked multi-table example

```sql
-- invoice number, customer number, customer name, invoice date, invoice amount
-- for all customers with a balance of $1,000 or more
select invoice_num, c.cust_num, cust_lname+' '+cust_fname as [Name], inv_date, inv_amount
from customer c join invoice i on c.cust_num = i.cust_num
where cust_balance >= 1000
```
