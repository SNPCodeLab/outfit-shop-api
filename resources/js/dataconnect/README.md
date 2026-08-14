# Generated TypeScript README
This README will guide you through the process of using the generated JavaScript SDK package for the connector `ss-mis-connector`. It will also provide examples on how to use your generated SDK to call your Data Connect queries and mutations.

***NOTE:** This README is generated alongside the generated SDK. If you make changes to this file, they will be overwritten when the SDK is regenerated.*

# Table of Contents
- [**Overview**](#generated-javascript-readme)
- [**Accessing the connector**](#accessing-the-connector)
  - [*Connecting to the local Emulator*](#connecting-to-the-local-emulator)
- [**Queries**](#queries)
  - [*ListStores*](#liststores)
  - [*ListCategories*](#listcategories)
  - [*ListProducts*](#listproducts)
  - [*ListClothingSizes*](#listclothingsizes)
  - [*ListColors*](#listcolors)
  - [*GetProductVariants*](#getproductvariants)
  - [*ListSuppliers*](#listsuppliers)
  - [*ListEmployees*](#listemployees)
  - [*ListCustomers*](#listcustomers)
  - [*SearchCustomerByPhone*](#searchcustomerbyphone)
  - [*ListPurchaseHeaders*](#listpurchaseheaders)
  - [*ListPurchaseDetails*](#listpurchasedetails)
  - [*GetRecentSales*](#getrecentsales)
  - [*ListSaleDetails*](#listsaledetails)
  - [*ListPayments*](#listpayments)
  - [*ListStockMovements*](#liststockmovements)
  - [*ListAuditLogs*](#listauditlogs)
- [**Mutations**](#mutations)
  - [*CreateStore*](#createstore)
  - [*CreateCategory*](#createcategory)
  - [*CreateProduct*](#createproduct)
  - [*CreateClothingSize*](#createclothingsize)
  - [*CreateColor*](#createcolor)
  - [*CreateProductVariant*](#createproductvariant)
  - [*CreateSupplier*](#createsupplier)
  - [*CreateEmployee*](#createemployee)
  - [*CreateCustomer*](#createcustomer)
  - [*UpsertCustomer*](#upsertcustomer)
  - [*CreatePurchaseHeader*](#createpurchaseheader)
  - [*CreatePurchaseDetail*](#createpurchasedetail)
  - [*CreateSaleHeader*](#createsaleheader)
  - [*CreateSaleDetail*](#createsaledetail)
  - [*CreatePayment*](#createpayment)
  - [*CreateStockMovement*](#createstockmovement)
  - [*CreateAuditLog*](#createauditlog)

# Accessing the connector
A connector is a collection of Queries and Mutations. One SDK is generated for each connector - this SDK is generated for the connector `ss-mis-connector`. You can find more information about connectors in the [Data Connect documentation](https://firebase.google.com/docs/data-connect#how-does).

You can use this generated SDK by importing from the package `@ss-mis/dataconnect` as shown below. Both CommonJS and ESM imports are supported.

You can also follow the instructions from the [Data Connect documentation](https://firebase.google.com/docs/data-connect/web-sdk#set-client).

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig } from '@ss-mis/dataconnect';

const dataConnect = getDataConnect(connectorConfig);
```

## Connecting to the local Emulator
By default, the connector will connect to the production service.

To connect to the emulator, you can use the following code.
You can also follow the emulator instructions from the [Data Connect documentation](https://firebase.google.com/docs/data-connect/web-sdk#instrument-clients).

```typescript
import { connectDataConnectEmulator, getDataConnect } from 'firebase/data-connect';
import { connectorConfig } from '@ss-mis/dataconnect';

const dataConnect = getDataConnect(connectorConfig);
connectDataConnectEmulator(dataConnect, 'localhost', 9399);
```

After it's initialized, you can call your Data Connect [queries](#queries) and [mutations](#mutations) from your generated SDK.

# Queries

There are two ways to execute a Data Connect Query using the generated Web SDK:
- Using a Query Reference function, which returns a `QueryRef`
  - The `QueryRef` can be used as an argument to `executeQuery()`, which will execute the Query and return a `QueryPromise`
- Using an action shortcut function, which returns a `QueryPromise`
  - Calling the action shortcut function will execute the Query and return a `QueryPromise`

The following is true for both the action shortcut function and the `QueryRef` function:
- The `QueryPromise` returned will resolve to the result of the Query once it has finished executing
- If the Query accepts arguments, both the action shortcut function and the `QueryRef` function accept a single argument: an object that contains all the required variables (and the optional variables) for the Query
- Both functions can be called with or without passing in a `DataConnect` instance as an argument. If no `DataConnect` argument is passed in, then the generated SDK will call `getDataConnect(connectorConfig)` behind the scenes for you.

Below are examples of how to use the `ss-mis-connector` connector's generated functions to execute each query. You can also follow the examples from the [Data Connect documentation](https://firebase.google.com/docs/data-connect/web-sdk#using-queries).

## ListStores
You can execute the `ListStores` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
listStores(options?: ExecuteQueryOptions): QueryPromise<ListStoresData, undefined>;

interface ListStoresRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListStoresData, undefined>;
}
export const listStoresRef: ListStoresRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listStores(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListStoresData, undefined>;

interface ListStoresRef {
  ...
  (dc: DataConnect): QueryRef<ListStoresData, undefined>;
}
export const listStoresRef: ListStoresRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listStoresRef:
```typescript
const name = listStoresRef.operationName;
console.log(name);
```

### Variables
The `ListStores` query has no variables.
### Return Type
Recall that executing the `ListStores` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListStoresData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListStoresData {
  stores: ({
    id: UUIDString;
    storeName: string;
    code: string;
    taxId?: string | null;
    phone?: string | null;
    email?: string | null;
    address?: string | null;
    isActive: boolean;
  } & Store_Key)[];
}
```
### Using `ListStores`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listStores } from '@ss-mis/dataconnect';


// Call the `listStores()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listStores();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listStores(dataConnect);

console.log(data.stores);

// Or, you can use the `Promise` API.
listStores().then((response) => {
  const data = response.data;
  console.log(data.stores);
});
```

### Using `ListStores`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listStoresRef } from '@ss-mis/dataconnect';


// Call the `listStoresRef()` function to get a reference to the query.
const ref = listStoresRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listStoresRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.stores);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.stores);
});
```

## ListCategories
You can execute the `ListCategories` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
listCategories(options?: ExecuteQueryOptions): QueryPromise<ListCategoriesData, undefined>;

interface ListCategoriesRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListCategoriesData, undefined>;
}
export const listCategoriesRef: ListCategoriesRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listCategories(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListCategoriesData, undefined>;

interface ListCategoriesRef {
  ...
  (dc: DataConnect): QueryRef<ListCategoriesData, undefined>;
}
export const listCategoriesRef: ListCategoriesRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listCategoriesRef:
```typescript
const name = listCategoriesRef.operationName;
console.log(name);
```

### Variables
The `ListCategories` query has no variables.
### Return Type
Recall that executing the `ListCategories` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListCategoriesData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListCategoriesData {
  categories: ({
    id: UUIDString;
    categoryName: string;
    slug: string;
    description?: string | null;
    isActive: boolean;
  } & Category_Key)[];
}
```
### Using `ListCategories`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listCategories } from '@ss-mis/dataconnect';


// Call the `listCategories()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listCategories();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listCategories(dataConnect);

console.log(data.categories);

// Or, you can use the `Promise` API.
listCategories().then((response) => {
  const data = response.data;
  console.log(data.categories);
});
```

### Using `ListCategories`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listCategoriesRef } from '@ss-mis/dataconnect';


// Call the `listCategoriesRef()` function to get a reference to the query.
const ref = listCategoriesRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listCategoriesRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.categories);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.categories);
});
```

## ListProducts
You can execute the `ListProducts` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
listProducts(options?: ExecuteQueryOptions): QueryPromise<ListProductsData, undefined>;

interface ListProductsRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListProductsData, undefined>;
}
export const listProductsRef: ListProductsRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listProducts(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListProductsData, undefined>;

interface ListProductsRef {
  ...
  (dc: DataConnect): QueryRef<ListProductsData, undefined>;
}
export const listProductsRef: ListProductsRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listProductsRef:
```typescript
const name = listProductsRef.operationName;
console.log(name);
```

### Variables
The `ListProducts` query has no variables.
### Return Type
Recall that executing the `ListProducts` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListProductsData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListProductsData {
  products: ({
    id: UUIDString;
    productName: string;
    brand?: string | null;
    description?: string | null;
    status: string;
    category: {
      id: UUIDString;
      categoryName: string;
    } & Category_Key;
  } & Product_Key)[];
}
```
### Using `ListProducts`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listProducts } from '@ss-mis/dataconnect';


// Call the `listProducts()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listProducts();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listProducts(dataConnect);

console.log(data.products);

// Or, you can use the `Promise` API.
listProducts().then((response) => {
  const data = response.data;
  console.log(data.products);
});
```

### Using `ListProducts`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listProductsRef } from '@ss-mis/dataconnect';


// Call the `listProductsRef()` function to get a reference to the query.
const ref = listProductsRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listProductsRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.products);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.products);
});
```

## ListClothingSizes
You can execute the `ListClothingSizes` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
listClothingSizes(options?: ExecuteQueryOptions): QueryPromise<ListClothingSizesData, undefined>;

interface ListClothingSizesRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListClothingSizesData, undefined>;
}
export const listClothingSizesRef: ListClothingSizesRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listClothingSizes(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListClothingSizesData, undefined>;

interface ListClothingSizesRef {
  ...
  (dc: DataConnect): QueryRef<ListClothingSizesData, undefined>;
}
export const listClothingSizesRef: ListClothingSizesRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listClothingSizesRef:
```typescript
const name = listClothingSizesRef.operationName;
console.log(name);
```

### Variables
The `ListClothingSizes` query has no variables.
### Return Type
Recall that executing the `ListClothingSizes` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListClothingSizesData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListClothingSizesData {
  clothingSizes: ({
    id: UUIDString;
    sizeCode: string;
    sizeName: string;
    description?: string | null;
  } & ClothingSize_Key)[];
}
```
### Using `ListClothingSizes`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listClothingSizes } from '@ss-mis/dataconnect';


// Call the `listClothingSizes()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listClothingSizes();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listClothingSizes(dataConnect);

console.log(data.clothingSizes);

// Or, you can use the `Promise` API.
listClothingSizes().then((response) => {
  const data = response.data;
  console.log(data.clothingSizes);
});
```

### Using `ListClothingSizes`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listClothingSizesRef } from '@ss-mis/dataconnect';


// Call the `listClothingSizesRef()` function to get a reference to the query.
const ref = listClothingSizesRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listClothingSizesRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.clothingSizes);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.clothingSizes);
});
```

## ListColors
You can execute the `ListColors` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
listColors(options?: ExecuteQueryOptions): QueryPromise<ListColorsData, undefined>;

interface ListColorsRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListColorsData, undefined>;
}
export const listColorsRef: ListColorsRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listColors(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListColorsData, undefined>;

interface ListColorsRef {
  ...
  (dc: DataConnect): QueryRef<ListColorsData, undefined>;
}
export const listColorsRef: ListColorsRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listColorsRef:
```typescript
const name = listColorsRef.operationName;
console.log(name);
```

### Variables
The `ListColors` query has no variables.
### Return Type
Recall that executing the `ListColors` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListColorsData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListColorsData {
  colors: ({
    id: UUIDString;
    colorCode: string;
    colorName: string;
    hexCode?: string | null;
  } & Color_Key)[];
}
```
### Using `ListColors`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listColors } from '@ss-mis/dataconnect';


// Call the `listColors()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listColors();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listColors(dataConnect);

console.log(data.colors);

// Or, you can use the `Promise` API.
listColors().then((response) => {
  const data = response.data;
  console.log(data.colors);
});
```

### Using `ListColors`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listColorsRef } from '@ss-mis/dataconnect';


// Call the `listColorsRef()` function to get a reference to the query.
const ref = listColorsRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listColorsRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.colors);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.colors);
});
```

## GetProductVariants
You can execute the `GetProductVariants` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
getProductVariants(options?: ExecuteQueryOptions): QueryPromise<GetProductVariantsData, undefined>;

interface GetProductVariantsRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<GetProductVariantsData, undefined>;
}
export const getProductVariantsRef: GetProductVariantsRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getProductVariants(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<GetProductVariantsData, undefined>;

interface GetProductVariantsRef {
  ...
  (dc: DataConnect): QueryRef<GetProductVariantsData, undefined>;
}
export const getProductVariantsRef: GetProductVariantsRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getProductVariantsRef:
```typescript
const name = getProductVariantsRef.operationName;
console.log(name);
```

### Variables
The `GetProductVariants` query has no variables.
### Return Type
Recall that executing the `GetProductVariants` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetProductVariantsData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetProductVariantsData {
  productVariants: ({
    id: UUIDString;
    sku: string;
    barcode?: string | null;
    costPrice: number;
    salePrice: number;
    wholesalePrice?: number | null;
    stockQuantity: number;
    reorderLevel: number;
    isActive: boolean;
    product: {
      id: UUIDString;
      productName: string;
      brand?: string | null;
    } & Product_Key;
    size?: {
      id: UUIDString;
      sizeCode: string;
      sizeName: string;
    } & ClothingSize_Key;
    color?: {
      id: UUIDString;
      colorName: string;
      hexCode?: string | null;
    } & Color_Key;
  } & ProductVariant_Key)[];
}
```
### Using `GetProductVariants`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getProductVariants } from '@ss-mis/dataconnect';


// Call the `getProductVariants()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getProductVariants();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getProductVariants(dataConnect);

console.log(data.productVariants);

// Or, you can use the `Promise` API.
getProductVariants().then((response) => {
  const data = response.data;
  console.log(data.productVariants);
});
```

### Using `GetProductVariants`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getProductVariantsRef } from '@ss-mis/dataconnect';


// Call the `getProductVariantsRef()` function to get a reference to the query.
const ref = getProductVariantsRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getProductVariantsRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.productVariants);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.productVariants);
});
```

## ListSuppliers
You can execute the `ListSuppliers` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
listSuppliers(options?: ExecuteQueryOptions): QueryPromise<ListSuppliersData, undefined>;

interface ListSuppliersRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListSuppliersData, undefined>;
}
export const listSuppliersRef: ListSuppliersRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listSuppliers(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListSuppliersData, undefined>;

interface ListSuppliersRef {
  ...
  (dc: DataConnect): QueryRef<ListSuppliersData, undefined>;
}
export const listSuppliersRef: ListSuppliersRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listSuppliersRef:
```typescript
const name = listSuppliersRef.operationName;
console.log(name);
```

### Variables
The `ListSuppliers` query has no variables.
### Return Type
Recall that executing the `ListSuppliers` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListSuppliersData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListSuppliersData {
  suppliers: ({
    id: UUIDString;
    supplierName: string;
    contactName?: string | null;
    phone?: string | null;
    email?: string | null;
    address?: string | null;
    taxId?: string | null;
    status: string;
  } & Supplier_Key)[];
}
```
### Using `ListSuppliers`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listSuppliers } from '@ss-mis/dataconnect';


// Call the `listSuppliers()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listSuppliers();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listSuppliers(dataConnect);

console.log(data.suppliers);

// Or, you can use the `Promise` API.
listSuppliers().then((response) => {
  const data = response.data;
  console.log(data.suppliers);
});
```

### Using `ListSuppliers`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listSuppliersRef } from '@ss-mis/dataconnect';


// Call the `listSuppliersRef()` function to get a reference to the query.
const ref = listSuppliersRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listSuppliersRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.suppliers);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.suppliers);
});
```

## ListEmployees
You can execute the `ListEmployees` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
listEmployees(options?: ExecuteQueryOptions): QueryPromise<ListEmployeesData, undefined>;

interface ListEmployeesRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListEmployeesData, undefined>;
}
export const listEmployeesRef: ListEmployeesRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listEmployees(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListEmployeesData, undefined>;

interface ListEmployeesRef {
  ...
  (dc: DataConnect): QueryRef<ListEmployeesData, undefined>;
}
export const listEmployeesRef: ListEmployeesRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listEmployeesRef:
```typescript
const name = listEmployeesRef.operationName;
console.log(name);
```

### Variables
The `ListEmployees` query has no variables.
### Return Type
Recall that executing the `ListEmployees` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListEmployeesData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListEmployeesData {
  employees: ({
    id: UUIDString;
    employeeName: string;
    gender?: string | null;
    phone?: string | null;
    email?: string | null;
    position?: string | null;
    username: string;
    status: string;
    store: {
      id: UUIDString;
      storeName: string;
    } & Store_Key;
  } & Employee_Key)[];
}
```
### Using `ListEmployees`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listEmployees } from '@ss-mis/dataconnect';


// Call the `listEmployees()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listEmployees();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listEmployees(dataConnect);

console.log(data.employees);

// Or, you can use the `Promise` API.
listEmployees().then((response) => {
  const data = response.data;
  console.log(data.employees);
});
```

### Using `ListEmployees`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listEmployeesRef } from '@ss-mis/dataconnect';


// Call the `listEmployeesRef()` function to get a reference to the query.
const ref = listEmployeesRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listEmployeesRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.employees);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.employees);
});
```

## ListCustomers
You can execute the `ListCustomers` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
listCustomers(options?: ExecuteQueryOptions): QueryPromise<ListCustomersData, undefined>;

interface ListCustomersRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListCustomersData, undefined>;
}
export const listCustomersRef: ListCustomersRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listCustomers(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListCustomersData, undefined>;

interface ListCustomersRef {
  ...
  (dc: DataConnect): QueryRef<ListCustomersData, undefined>;
}
export const listCustomersRef: ListCustomersRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listCustomersRef:
```typescript
const name = listCustomersRef.operationName;
console.log(name);
```

### Variables
The `ListCustomers` query has no variables.
### Return Type
Recall that executing the `ListCustomers` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListCustomersData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListCustomersData {
  customers: ({
    id: UUIDString;
    customerName: string;
    gender?: string | null;
    phone?: string | null;
    email?: string | null;
    address?: string | null;
    loyaltyPoints: number;
  } & Customer_Key)[];
}
```
### Using `ListCustomers`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listCustomers } from '@ss-mis/dataconnect';


// Call the `listCustomers()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listCustomers();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listCustomers(dataConnect);

console.log(data.customers);

// Or, you can use the `Promise` API.
listCustomers().then((response) => {
  const data = response.data;
  console.log(data.customers);
});
```

### Using `ListCustomers`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listCustomersRef } from '@ss-mis/dataconnect';


// Call the `listCustomersRef()` function to get a reference to the query.
const ref = listCustomersRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listCustomersRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.customers);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.customers);
});
```

## SearchCustomerByPhone
You can execute the `SearchCustomerByPhone` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
searchCustomerByPhone(vars: SearchCustomerByPhoneVariables, options?: ExecuteQueryOptions): QueryPromise<SearchCustomerByPhoneData, SearchCustomerByPhoneVariables>;

interface SearchCustomerByPhoneRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: SearchCustomerByPhoneVariables): QueryRef<SearchCustomerByPhoneData, SearchCustomerByPhoneVariables>;
}
export const searchCustomerByPhoneRef: SearchCustomerByPhoneRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
searchCustomerByPhone(dc: DataConnect, vars: SearchCustomerByPhoneVariables, options?: ExecuteQueryOptions): QueryPromise<SearchCustomerByPhoneData, SearchCustomerByPhoneVariables>;

interface SearchCustomerByPhoneRef {
  ...
  (dc: DataConnect, vars: SearchCustomerByPhoneVariables): QueryRef<SearchCustomerByPhoneData, SearchCustomerByPhoneVariables>;
}
export const searchCustomerByPhoneRef: SearchCustomerByPhoneRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the searchCustomerByPhoneRef:
```typescript
const name = searchCustomerByPhoneRef.operationName;
console.log(name);
```

### Variables
The `SearchCustomerByPhone` query requires an argument of type `SearchCustomerByPhoneVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface SearchCustomerByPhoneVariables {
  phone: string;
}
```
### Return Type
Recall that executing the `SearchCustomerByPhone` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `SearchCustomerByPhoneData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface SearchCustomerByPhoneData {
  customers: ({
    id: UUIDString;
    customerName: string;
    phone?: string | null;
    email?: string | null;
    loyaltyPoints: number;
  } & Customer_Key)[];
}
```
### Using `SearchCustomerByPhone`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, searchCustomerByPhone, SearchCustomerByPhoneVariables } from '@ss-mis/dataconnect';

// The `SearchCustomerByPhone` query requires an argument of type `SearchCustomerByPhoneVariables`:
const searchCustomerByPhoneVars: SearchCustomerByPhoneVariables = {
  phone: ..., 
};

// Call the `searchCustomerByPhone()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await searchCustomerByPhone(searchCustomerByPhoneVars);
// Variables can be defined inline as well.
const { data } = await searchCustomerByPhone({ phone: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await searchCustomerByPhone(dataConnect, searchCustomerByPhoneVars);

console.log(data.customers);

// Or, you can use the `Promise` API.
searchCustomerByPhone(searchCustomerByPhoneVars).then((response) => {
  const data = response.data;
  console.log(data.customers);
});
```

### Using `SearchCustomerByPhone`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, searchCustomerByPhoneRef, SearchCustomerByPhoneVariables } from '@ss-mis/dataconnect';

// The `SearchCustomerByPhone` query requires an argument of type `SearchCustomerByPhoneVariables`:
const searchCustomerByPhoneVars: SearchCustomerByPhoneVariables = {
  phone: ..., 
};

// Call the `searchCustomerByPhoneRef()` function to get a reference to the query.
const ref = searchCustomerByPhoneRef(searchCustomerByPhoneVars);
// Variables can be defined inline as well.
const ref = searchCustomerByPhoneRef({ phone: ..., });

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = searchCustomerByPhoneRef(dataConnect, searchCustomerByPhoneVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.customers);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.customers);
});
```

## ListPurchaseHeaders
You can execute the `ListPurchaseHeaders` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
listPurchaseHeaders(options?: ExecuteQueryOptions): QueryPromise<ListPurchaseHeadersData, undefined>;

interface ListPurchaseHeadersRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListPurchaseHeadersData, undefined>;
}
export const listPurchaseHeadersRef: ListPurchaseHeadersRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listPurchaseHeaders(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListPurchaseHeadersData, undefined>;

interface ListPurchaseHeadersRef {
  ...
  (dc: DataConnect): QueryRef<ListPurchaseHeadersData, undefined>;
}
export const listPurchaseHeadersRef: ListPurchaseHeadersRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listPurchaseHeadersRef:
```typescript
const name = listPurchaseHeadersRef.operationName;
console.log(name);
```

### Variables
The `ListPurchaseHeaders` query has no variables.
### Return Type
Recall that executing the `ListPurchaseHeaders` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListPurchaseHeadersData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListPurchaseHeadersData {
  purchaseHeaders: ({
    id: UUIDString;
    referenceNo: string;
    purchaseDate: TimestampString;
    totalAmount: number;
    taxAmount: number;
    grandTotal: number;
    status: string;
    notes?: string | null;
    supplier: {
      id: UUIDString;
      supplierName: string;
    } & Supplier_Key;
    employee: {
      id: UUIDString;
      employeeName: string;
    } & Employee_Key;
    store: {
      id: UUIDString;
      storeName: string;
    } & Store_Key;
  } & PurchaseHeader_Key)[];
}
```
### Using `ListPurchaseHeaders`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listPurchaseHeaders } from '@ss-mis/dataconnect';


// Call the `listPurchaseHeaders()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listPurchaseHeaders();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listPurchaseHeaders(dataConnect);

console.log(data.purchaseHeaders);

// Or, you can use the `Promise` API.
listPurchaseHeaders().then((response) => {
  const data = response.data;
  console.log(data.purchaseHeaders);
});
```

### Using `ListPurchaseHeaders`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listPurchaseHeadersRef } from '@ss-mis/dataconnect';


// Call the `listPurchaseHeadersRef()` function to get a reference to the query.
const ref = listPurchaseHeadersRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listPurchaseHeadersRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.purchaseHeaders);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.purchaseHeaders);
});
```

## ListPurchaseDetails
You can execute the `ListPurchaseDetails` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
listPurchaseDetails(options?: ExecuteQueryOptions): QueryPromise<ListPurchaseDetailsData, undefined>;

interface ListPurchaseDetailsRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListPurchaseDetailsData, undefined>;
}
export const listPurchaseDetailsRef: ListPurchaseDetailsRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listPurchaseDetails(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListPurchaseDetailsData, undefined>;

interface ListPurchaseDetailsRef {
  ...
  (dc: DataConnect): QueryRef<ListPurchaseDetailsData, undefined>;
}
export const listPurchaseDetailsRef: ListPurchaseDetailsRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listPurchaseDetailsRef:
```typescript
const name = listPurchaseDetailsRef.operationName;
console.log(name);
```

### Variables
The `ListPurchaseDetails` query has no variables.
### Return Type
Recall that executing the `ListPurchaseDetails` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListPurchaseDetailsData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListPurchaseDetailsData {
  purchaseDetails: ({
    id: UUIDString;
    quantity: number;
    unitCost: number;
    subTotal: number;
    purchase: {
      id: UUIDString;
      referenceNo: string;
    } & PurchaseHeader_Key;
    variant: {
      id: UUIDString;
      sku: string;
    } & ProductVariant_Key;
  } & PurchaseDetail_Key)[];
}
```
### Using `ListPurchaseDetails`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listPurchaseDetails } from '@ss-mis/dataconnect';


// Call the `listPurchaseDetails()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listPurchaseDetails();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listPurchaseDetails(dataConnect);

console.log(data.purchaseDetails);

// Or, you can use the `Promise` API.
listPurchaseDetails().then((response) => {
  const data = response.data;
  console.log(data.purchaseDetails);
});
```

### Using `ListPurchaseDetails`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listPurchaseDetailsRef } from '@ss-mis/dataconnect';


// Call the `listPurchaseDetailsRef()` function to get a reference to the query.
const ref = listPurchaseDetailsRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listPurchaseDetailsRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.purchaseDetails);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.purchaseDetails);
});
```

## GetRecentSales
You can execute the `GetRecentSales` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
getRecentSales(vars?: GetRecentSalesVariables, options?: ExecuteQueryOptions): QueryPromise<GetRecentSalesData, GetRecentSalesVariables>;

interface GetRecentSalesRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars?: GetRecentSalesVariables): QueryRef<GetRecentSalesData, GetRecentSalesVariables>;
}
export const getRecentSalesRef: GetRecentSalesRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
getRecentSales(dc: DataConnect, vars?: GetRecentSalesVariables, options?: ExecuteQueryOptions): QueryPromise<GetRecentSalesData, GetRecentSalesVariables>;

interface GetRecentSalesRef {
  ...
  (dc: DataConnect, vars?: GetRecentSalesVariables): QueryRef<GetRecentSalesData, GetRecentSalesVariables>;
}
export const getRecentSalesRef: GetRecentSalesRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the getRecentSalesRef:
```typescript
const name = getRecentSalesRef.operationName;
console.log(name);
```

### Variables
The `GetRecentSales` query has an optional argument of type `GetRecentSalesVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface GetRecentSalesVariables {
  limit?: number | null;
}
```
### Return Type
Recall that executing the `GetRecentSales` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `GetRecentSalesData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface GetRecentSalesData {
  saleHeaders: ({
    id: UUIDString;
    invoiceNo: string;
    saleDate: TimestampString;
    subTotal: number;
    discountAmount: number;
    taxAmount: number;
    grandTotal: number;
    paymentStatus: string;
    status: string;
    notes?: string | null;
    store: {
      id: UUIDString;
      storeName: string;
    } & Store_Key;
    customer?: {
      id: UUIDString;
      customerName: string;
    } & Customer_Key;
    employee: {
      id: UUIDString;
      employeeName: string;
    } & Employee_Key;
  } & SaleHeader_Key)[];
}
```
### Using `GetRecentSales`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, getRecentSales, GetRecentSalesVariables } from '@ss-mis/dataconnect';

// The `GetRecentSales` query has an optional argument of type `GetRecentSalesVariables`:
const getRecentSalesVars: GetRecentSalesVariables = {
  limit: ..., // optional
};

// Call the `getRecentSales()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await getRecentSales(getRecentSalesVars);
// Variables can be defined inline as well.
const { data } = await getRecentSales({ limit: ..., });
// Since all variables are optional for this query, you can omit the `GetRecentSalesVariables` argument.
const { data } = await getRecentSales();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await getRecentSales(dataConnect, getRecentSalesVars);

console.log(data.saleHeaders);

// Or, you can use the `Promise` API.
getRecentSales(getRecentSalesVars).then((response) => {
  const data = response.data;
  console.log(data.saleHeaders);
});
```

### Using `GetRecentSales`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, getRecentSalesRef, GetRecentSalesVariables } from '@ss-mis/dataconnect';

// The `GetRecentSales` query has an optional argument of type `GetRecentSalesVariables`:
const getRecentSalesVars: GetRecentSalesVariables = {
  limit: ..., // optional
};

// Call the `getRecentSalesRef()` function to get a reference to the query.
const ref = getRecentSalesRef(getRecentSalesVars);
// Variables can be defined inline as well.
const ref = getRecentSalesRef({ limit: ..., });
// Since all variables are optional for this query, you can omit the `GetRecentSalesVariables` argument.
const ref = getRecentSalesRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = getRecentSalesRef(dataConnect, getRecentSalesVars);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.saleHeaders);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.saleHeaders);
});
```

## ListSaleDetails
You can execute the `ListSaleDetails` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
listSaleDetails(options?: ExecuteQueryOptions): QueryPromise<ListSaleDetailsData, undefined>;

interface ListSaleDetailsRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListSaleDetailsData, undefined>;
}
export const listSaleDetailsRef: ListSaleDetailsRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listSaleDetails(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListSaleDetailsData, undefined>;

interface ListSaleDetailsRef {
  ...
  (dc: DataConnect): QueryRef<ListSaleDetailsData, undefined>;
}
export const listSaleDetailsRef: ListSaleDetailsRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listSaleDetailsRef:
```typescript
const name = listSaleDetailsRef.operationName;
console.log(name);
```

### Variables
The `ListSaleDetails` query has no variables.
### Return Type
Recall that executing the `ListSaleDetails` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListSaleDetailsData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListSaleDetailsData {
  saleDetails: ({
    id: UUIDString;
    quantity: number;
    unitPrice: number;
    discountAmount: number;
    subTotal: number;
    sale: {
      id: UUIDString;
      invoiceNo: string;
    } & SaleHeader_Key;
    variant: {
      id: UUIDString;
      sku: string;
    } & ProductVariant_Key;
  } & SaleDetail_Key)[];
}
```
### Using `ListSaleDetails`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listSaleDetails } from '@ss-mis/dataconnect';


// Call the `listSaleDetails()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listSaleDetails();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listSaleDetails(dataConnect);

console.log(data.saleDetails);

// Or, you can use the `Promise` API.
listSaleDetails().then((response) => {
  const data = response.data;
  console.log(data.saleDetails);
});
```

### Using `ListSaleDetails`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listSaleDetailsRef } from '@ss-mis/dataconnect';


// Call the `listSaleDetailsRef()` function to get a reference to the query.
const ref = listSaleDetailsRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listSaleDetailsRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.saleDetails);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.saleDetails);
});
```

## ListPayments
You can execute the `ListPayments` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
listPayments(options?: ExecuteQueryOptions): QueryPromise<ListPaymentsData, undefined>;

interface ListPaymentsRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListPaymentsData, undefined>;
}
export const listPaymentsRef: ListPaymentsRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listPayments(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListPaymentsData, undefined>;

interface ListPaymentsRef {
  ...
  (dc: DataConnect): QueryRef<ListPaymentsData, undefined>;
}
export const listPaymentsRef: ListPaymentsRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listPaymentsRef:
```typescript
const name = listPaymentsRef.operationName;
console.log(name);
```

### Variables
The `ListPayments` query has no variables.
### Return Type
Recall that executing the `ListPayments` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListPaymentsData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListPaymentsData {
  payments: ({
    id: UUIDString;
    paymentDate: TimestampString;
    amount: number;
    amountTendered: number;
    changeDue: number;
    paymentMethod: string;
    paymentStatus: string;
    transactionRef?: string | null;
    sale: {
      id: UUIDString;
      invoiceNo: string;
    } & SaleHeader_Key;
  } & Payment_Key)[];
}
```
### Using `ListPayments`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listPayments } from '@ss-mis/dataconnect';


// Call the `listPayments()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listPayments();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listPayments(dataConnect);

console.log(data.payments);

// Or, you can use the `Promise` API.
listPayments().then((response) => {
  const data = response.data;
  console.log(data.payments);
});
```

### Using `ListPayments`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listPaymentsRef } from '@ss-mis/dataconnect';


// Call the `listPaymentsRef()` function to get a reference to the query.
const ref = listPaymentsRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listPaymentsRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.payments);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.payments);
});
```

## ListStockMovements
You can execute the `ListStockMovements` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
listStockMovements(options?: ExecuteQueryOptions): QueryPromise<ListStockMovementsData, undefined>;

interface ListStockMovementsRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListStockMovementsData, undefined>;
}
export const listStockMovementsRef: ListStockMovementsRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listStockMovements(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListStockMovementsData, undefined>;

interface ListStockMovementsRef {
  ...
  (dc: DataConnect): QueryRef<ListStockMovementsData, undefined>;
}
export const listStockMovementsRef: ListStockMovementsRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listStockMovementsRef:
```typescript
const name = listStockMovementsRef.operationName;
console.log(name);
```

### Variables
The `ListStockMovements` query has no variables.
### Return Type
Recall that executing the `ListStockMovements` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListStockMovementsData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListStockMovementsData {
  stockMovements: ({
    id: UUIDString;
    movementType: string;
    quantity: number;
    stockBefore: number;
    stockAfter: number;
    movementDate: TimestampString;
    referenceType?: string | null;
    referenceId?: string | null;
    note?: string | null;
    store: {
      id: UUIDString;
      storeName: string;
    } & Store_Key;
    variant: {
      id: UUIDString;
      sku: string;
    } & ProductVariant_Key;
    createdBy?: {
      id: UUIDString;
      employeeName: string;
    } & Employee_Key;
  } & StockMovement_Key)[];
}
```
### Using `ListStockMovements`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listStockMovements } from '@ss-mis/dataconnect';


// Call the `listStockMovements()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listStockMovements();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listStockMovements(dataConnect);

console.log(data.stockMovements);

// Or, you can use the `Promise` API.
listStockMovements().then((response) => {
  const data = response.data;
  console.log(data.stockMovements);
});
```

### Using `ListStockMovements`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listStockMovementsRef } from '@ss-mis/dataconnect';


// Call the `listStockMovementsRef()` function to get a reference to the query.
const ref = listStockMovementsRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listStockMovementsRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.stockMovements);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.stockMovements);
});
```

## ListAuditLogs
You can execute the `ListAuditLogs` query using the following action shortcut function, or by calling `executeQuery()` after calling the following `QueryRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
listAuditLogs(options?: ExecuteQueryOptions): QueryPromise<ListAuditLogsData, undefined>;

interface ListAuditLogsRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListAuditLogsData, undefined>;
}
export const listAuditLogsRef: ListAuditLogsRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `QueryRef` function.
```typescript
listAuditLogs(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListAuditLogsData, undefined>;

interface ListAuditLogsRef {
  ...
  (dc: DataConnect): QueryRef<ListAuditLogsData, undefined>;
}
export const listAuditLogsRef: ListAuditLogsRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the listAuditLogsRef:
```typescript
const name = listAuditLogsRef.operationName;
console.log(name);
```

### Variables
The `ListAuditLogs` query has no variables.
### Return Type
Recall that executing the `ListAuditLogs` query returns a `QueryPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `ListAuditLogsData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface ListAuditLogsData {
  auditLogs: ({
    id: UUIDString;
    userId?: string | null;
    action: string;
    entityType: string;
    entityId?: string | null;
    oldValues?: string | null;
    newValues?: string | null;
    ipAddress?: string | null;
    userAgent?: string | null;
    createdAt: TimestampString;
  } & AuditLog_Key)[];
}
```
### Using `ListAuditLogs`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, listAuditLogs } from '@ss-mis/dataconnect';


// Call the `listAuditLogs()` function to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await listAuditLogs();

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await listAuditLogs(dataConnect);

console.log(data.auditLogs);

// Or, you can use the `Promise` API.
listAuditLogs().then((response) => {
  const data = response.data;
  console.log(data.auditLogs);
});
```

### Using `ListAuditLogs`'s `QueryRef` function

```typescript
import { getDataConnect, executeQuery } from 'firebase/data-connect';
import { connectorConfig, listAuditLogsRef } from '@ss-mis/dataconnect';


// Call the `listAuditLogsRef()` function to get a reference to the query.
const ref = listAuditLogsRef();

// You can also pass in a `DataConnect` instance to the `QueryRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = listAuditLogsRef(dataConnect);

// Call `executeQuery()` on the reference to execute the query.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeQuery(ref);

console.log(data.auditLogs);

// Or, you can use the `Promise` API.
executeQuery(ref).then((response) => {
  const data = response.data;
  console.log(data.auditLogs);
});
```

# Mutations

There are two ways to execute a Data Connect Mutation using the generated Web SDK:
- Using a Mutation Reference function, which returns a `MutationRef`
  - The `MutationRef` can be used as an argument to `executeMutation()`, which will execute the Mutation and return a `MutationPromise`
- Using an action shortcut function, which returns a `MutationPromise`
  - Calling the action shortcut function will execute the Mutation and return a `MutationPromise`

The following is true for both the action shortcut function and the `MutationRef` function:
- The `MutationPromise` returned will resolve to the result of the Mutation once it has finished executing
- If the Mutation accepts arguments, both the action shortcut function and the `MutationRef` function accept a single argument: an object that contains all the required variables (and the optional variables) for the Mutation
- Both functions can be called with or without passing in a `DataConnect` instance as an argument. If no `DataConnect` argument is passed in, then the generated SDK will call `getDataConnect(connectorConfig)` behind the scenes for you.

Below are examples of how to use the `ss-mis-connector` connector's generated functions to execute each mutation. You can also follow the examples from the [Data Connect documentation](https://firebase.google.com/docs/data-connect/web-sdk#using-mutations).

## CreateStore
You can execute the `CreateStore` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createStore(vars: CreateStoreVariables): MutationPromise<CreateStoreData, CreateStoreVariables>;

interface CreateStoreRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateStoreVariables): MutationRef<CreateStoreData, CreateStoreVariables>;
}
export const createStoreRef: CreateStoreRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createStore(dc: DataConnect, vars: CreateStoreVariables): MutationPromise<CreateStoreData, CreateStoreVariables>;

interface CreateStoreRef {
  ...
  (dc: DataConnect, vars: CreateStoreVariables): MutationRef<CreateStoreData, CreateStoreVariables>;
}
export const createStoreRef: CreateStoreRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createStoreRef:
```typescript
const name = createStoreRef.operationName;
console.log(name);
```

### Variables
The `CreateStore` mutation requires an argument of type `CreateStoreVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateStoreVariables {
  storeName: string;
  code: string;
  phone?: string | null;
  email?: string | null;
  address?: string | null;
}
```
### Return Type
Recall that executing the `CreateStore` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateStoreData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateStoreData {
  store_insert: Store_Key;
}
```
### Using `CreateStore`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createStore, CreateStoreVariables } from '@ss-mis/dataconnect';

// The `CreateStore` mutation requires an argument of type `CreateStoreVariables`:
const createStoreVars: CreateStoreVariables = {
  storeName: ..., 
  code: ..., 
  phone: ..., // optional
  email: ..., // optional
  address: ..., // optional
};

// Call the `createStore()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createStore(createStoreVars);
// Variables can be defined inline as well.
const { data } = await createStore({ storeName: ..., code: ..., phone: ..., email: ..., address: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createStore(dataConnect, createStoreVars);

console.log(data.store_insert);

// Or, you can use the `Promise` API.
createStore(createStoreVars).then((response) => {
  const data = response.data;
  console.log(data.store_insert);
});
```

### Using `CreateStore`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createStoreRef, CreateStoreVariables } from '@ss-mis/dataconnect';

// The `CreateStore` mutation requires an argument of type `CreateStoreVariables`:
const createStoreVars: CreateStoreVariables = {
  storeName: ..., 
  code: ..., 
  phone: ..., // optional
  email: ..., // optional
  address: ..., // optional
};

// Call the `createStoreRef()` function to get a reference to the mutation.
const ref = createStoreRef(createStoreVars);
// Variables can be defined inline as well.
const ref = createStoreRef({ storeName: ..., code: ..., phone: ..., email: ..., address: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createStoreRef(dataConnect, createStoreVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.store_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.store_insert);
});
```

## CreateCategory
You can execute the `CreateCategory` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createCategory(vars: CreateCategoryVariables): MutationPromise<CreateCategoryData, CreateCategoryVariables>;

interface CreateCategoryRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateCategoryVariables): MutationRef<CreateCategoryData, CreateCategoryVariables>;
}
export const createCategoryRef: CreateCategoryRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createCategory(dc: DataConnect, vars: CreateCategoryVariables): MutationPromise<CreateCategoryData, CreateCategoryVariables>;

interface CreateCategoryRef {
  ...
  (dc: DataConnect, vars: CreateCategoryVariables): MutationRef<CreateCategoryData, CreateCategoryVariables>;
}
export const createCategoryRef: CreateCategoryRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createCategoryRef:
```typescript
const name = createCategoryRef.operationName;
console.log(name);
```

### Variables
The `CreateCategory` mutation requires an argument of type `CreateCategoryVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateCategoryVariables {
  categoryName: string;
  slug: string;
  description?: string | null;
}
```
### Return Type
Recall that executing the `CreateCategory` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateCategoryData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateCategoryData {
  category_insert: Category_Key;
}
```
### Using `CreateCategory`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createCategory, CreateCategoryVariables } from '@ss-mis/dataconnect';

// The `CreateCategory` mutation requires an argument of type `CreateCategoryVariables`:
const createCategoryVars: CreateCategoryVariables = {
  categoryName: ..., 
  slug: ..., 
  description: ..., // optional
};

// Call the `createCategory()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createCategory(createCategoryVars);
// Variables can be defined inline as well.
const { data } = await createCategory({ categoryName: ..., slug: ..., description: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createCategory(dataConnect, createCategoryVars);

console.log(data.category_insert);

// Or, you can use the `Promise` API.
createCategory(createCategoryVars).then((response) => {
  const data = response.data;
  console.log(data.category_insert);
});
```

### Using `CreateCategory`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createCategoryRef, CreateCategoryVariables } from '@ss-mis/dataconnect';

// The `CreateCategory` mutation requires an argument of type `CreateCategoryVariables`:
const createCategoryVars: CreateCategoryVariables = {
  categoryName: ..., 
  slug: ..., 
  description: ..., // optional
};

// Call the `createCategoryRef()` function to get a reference to the mutation.
const ref = createCategoryRef(createCategoryVars);
// Variables can be defined inline as well.
const ref = createCategoryRef({ categoryName: ..., slug: ..., description: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createCategoryRef(dataConnect, createCategoryVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.category_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.category_insert);
});
```

## CreateProduct
You can execute the `CreateProduct` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createProduct(vars: CreateProductVariables): MutationPromise<CreateProductData, CreateProductVariables>;

interface CreateProductRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateProductVariables): MutationRef<CreateProductData, CreateProductVariables>;
}
export const createProductRef: CreateProductRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createProduct(dc: DataConnect, vars: CreateProductVariables): MutationPromise<CreateProductData, CreateProductVariables>;

interface CreateProductRef {
  ...
  (dc: DataConnect, vars: CreateProductVariables): MutationRef<CreateProductData, CreateProductVariables>;
}
export const createProductRef: CreateProductRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createProductRef:
```typescript
const name = createProductRef.operationName;
console.log(name);
```

### Variables
The `CreateProduct` mutation requires an argument of type `CreateProductVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateProductVariables {
  productName: string;
  categoryId: UUIDString;
  brand?: string | null;
  description?: string | null;
}
```
### Return Type
Recall that executing the `CreateProduct` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateProductData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateProductData {
  product_insert: Product_Key;
}
```
### Using `CreateProduct`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createProduct, CreateProductVariables } from '@ss-mis/dataconnect';

// The `CreateProduct` mutation requires an argument of type `CreateProductVariables`:
const createProductVars: CreateProductVariables = {
  productName: ..., 
  categoryId: ..., 
  brand: ..., // optional
  description: ..., // optional
};

// Call the `createProduct()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createProduct(createProductVars);
// Variables can be defined inline as well.
const { data } = await createProduct({ productName: ..., categoryId: ..., brand: ..., description: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createProduct(dataConnect, createProductVars);

console.log(data.product_insert);

// Or, you can use the `Promise` API.
createProduct(createProductVars).then((response) => {
  const data = response.data;
  console.log(data.product_insert);
});
```

### Using `CreateProduct`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createProductRef, CreateProductVariables } from '@ss-mis/dataconnect';

// The `CreateProduct` mutation requires an argument of type `CreateProductVariables`:
const createProductVars: CreateProductVariables = {
  productName: ..., 
  categoryId: ..., 
  brand: ..., // optional
  description: ..., // optional
};

// Call the `createProductRef()` function to get a reference to the mutation.
const ref = createProductRef(createProductVars);
// Variables can be defined inline as well.
const ref = createProductRef({ productName: ..., categoryId: ..., brand: ..., description: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createProductRef(dataConnect, createProductVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.product_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.product_insert);
});
```

## CreateClothingSize
You can execute the `CreateClothingSize` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createClothingSize(vars: CreateClothingSizeVariables): MutationPromise<CreateClothingSizeData, CreateClothingSizeVariables>;

interface CreateClothingSizeRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateClothingSizeVariables): MutationRef<CreateClothingSizeData, CreateClothingSizeVariables>;
}
export const createClothingSizeRef: CreateClothingSizeRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createClothingSize(dc: DataConnect, vars: CreateClothingSizeVariables): MutationPromise<CreateClothingSizeData, CreateClothingSizeVariables>;

interface CreateClothingSizeRef {
  ...
  (dc: DataConnect, vars: CreateClothingSizeVariables): MutationRef<CreateClothingSizeData, CreateClothingSizeVariables>;
}
export const createClothingSizeRef: CreateClothingSizeRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createClothingSizeRef:
```typescript
const name = createClothingSizeRef.operationName;
console.log(name);
```

### Variables
The `CreateClothingSize` mutation requires an argument of type `CreateClothingSizeVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateClothingSizeVariables {
  sizeCode: string;
  sizeName: string;
  description?: string | null;
}
```
### Return Type
Recall that executing the `CreateClothingSize` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateClothingSizeData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateClothingSizeData {
  clothingSize_insert: ClothingSize_Key;
}
```
### Using `CreateClothingSize`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createClothingSize, CreateClothingSizeVariables } from '@ss-mis/dataconnect';

// The `CreateClothingSize` mutation requires an argument of type `CreateClothingSizeVariables`:
const createClothingSizeVars: CreateClothingSizeVariables = {
  sizeCode: ..., 
  sizeName: ..., 
  description: ..., // optional
};

// Call the `createClothingSize()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createClothingSize(createClothingSizeVars);
// Variables can be defined inline as well.
const { data } = await createClothingSize({ sizeCode: ..., sizeName: ..., description: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createClothingSize(dataConnect, createClothingSizeVars);

console.log(data.clothingSize_insert);

// Or, you can use the `Promise` API.
createClothingSize(createClothingSizeVars).then((response) => {
  const data = response.data;
  console.log(data.clothingSize_insert);
});
```

### Using `CreateClothingSize`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createClothingSizeRef, CreateClothingSizeVariables } from '@ss-mis/dataconnect';

// The `CreateClothingSize` mutation requires an argument of type `CreateClothingSizeVariables`:
const createClothingSizeVars: CreateClothingSizeVariables = {
  sizeCode: ..., 
  sizeName: ..., 
  description: ..., // optional
};

// Call the `createClothingSizeRef()` function to get a reference to the mutation.
const ref = createClothingSizeRef(createClothingSizeVars);
// Variables can be defined inline as well.
const ref = createClothingSizeRef({ sizeCode: ..., sizeName: ..., description: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createClothingSizeRef(dataConnect, createClothingSizeVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.clothingSize_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.clothingSize_insert);
});
```

## CreateColor
You can execute the `CreateColor` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createColor(vars: CreateColorVariables): MutationPromise<CreateColorData, CreateColorVariables>;

interface CreateColorRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateColorVariables): MutationRef<CreateColorData, CreateColorVariables>;
}
export const createColorRef: CreateColorRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createColor(dc: DataConnect, vars: CreateColorVariables): MutationPromise<CreateColorData, CreateColorVariables>;

interface CreateColorRef {
  ...
  (dc: DataConnect, vars: CreateColorVariables): MutationRef<CreateColorData, CreateColorVariables>;
}
export const createColorRef: CreateColorRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createColorRef:
```typescript
const name = createColorRef.operationName;
console.log(name);
```

### Variables
The `CreateColor` mutation requires an argument of type `CreateColorVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateColorVariables {
  colorCode: string;
  colorName: string;
  hexCode?: string | null;
}
```
### Return Type
Recall that executing the `CreateColor` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateColorData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateColorData {
  color_insert: Color_Key;
}
```
### Using `CreateColor`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createColor, CreateColorVariables } from '@ss-mis/dataconnect';

// The `CreateColor` mutation requires an argument of type `CreateColorVariables`:
const createColorVars: CreateColorVariables = {
  colorCode: ..., 
  colorName: ..., 
  hexCode: ..., // optional
};

// Call the `createColor()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createColor(createColorVars);
// Variables can be defined inline as well.
const { data } = await createColor({ colorCode: ..., colorName: ..., hexCode: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createColor(dataConnect, createColorVars);

console.log(data.color_insert);

// Or, you can use the `Promise` API.
createColor(createColorVars).then((response) => {
  const data = response.data;
  console.log(data.color_insert);
});
```

### Using `CreateColor`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createColorRef, CreateColorVariables } from '@ss-mis/dataconnect';

// The `CreateColor` mutation requires an argument of type `CreateColorVariables`:
const createColorVars: CreateColorVariables = {
  colorCode: ..., 
  colorName: ..., 
  hexCode: ..., // optional
};

// Call the `createColorRef()` function to get a reference to the mutation.
const ref = createColorRef(createColorVars);
// Variables can be defined inline as well.
const ref = createColorRef({ colorCode: ..., colorName: ..., hexCode: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createColorRef(dataConnect, createColorVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.color_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.color_insert);
});
```

## CreateProductVariant
You can execute the `CreateProductVariant` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createProductVariant(vars: CreateProductVariantVariables): MutationPromise<CreateProductVariantData, CreateProductVariantVariables>;

interface CreateProductVariantRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateProductVariantVariables): MutationRef<CreateProductVariantData, CreateProductVariantVariables>;
}
export const createProductVariantRef: CreateProductVariantRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createProductVariant(dc: DataConnect, vars: CreateProductVariantVariables): MutationPromise<CreateProductVariantData, CreateProductVariantVariables>;

interface CreateProductVariantRef {
  ...
  (dc: DataConnect, vars: CreateProductVariantVariables): MutationRef<CreateProductVariantData, CreateProductVariantVariables>;
}
export const createProductVariantRef: CreateProductVariantRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createProductVariantRef:
```typescript
const name = createProductVariantRef.operationName;
console.log(name);
```

### Variables
The `CreateProductVariant` mutation requires an argument of type `CreateProductVariantVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateProductVariantVariables {
  productId: UUIDString;
  sku: string;
  costPrice: number;
  salePrice: number;
  stockQuantity: number;
}
```
### Return Type
Recall that executing the `CreateProductVariant` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateProductVariantData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateProductVariantData {
  productVariant_insert: ProductVariant_Key;
}
```
### Using `CreateProductVariant`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createProductVariant, CreateProductVariantVariables } from '@ss-mis/dataconnect';

// The `CreateProductVariant` mutation requires an argument of type `CreateProductVariantVariables`:
const createProductVariantVars: CreateProductVariantVariables = {
  productId: ..., 
  sku: ..., 
  costPrice: ..., 
  salePrice: ..., 
  stockQuantity: ..., 
};

// Call the `createProductVariant()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createProductVariant(createProductVariantVars);
// Variables can be defined inline as well.
const { data } = await createProductVariant({ productId: ..., sku: ..., costPrice: ..., salePrice: ..., stockQuantity: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createProductVariant(dataConnect, createProductVariantVars);

console.log(data.productVariant_insert);

// Or, you can use the `Promise` API.
createProductVariant(createProductVariantVars).then((response) => {
  const data = response.data;
  console.log(data.productVariant_insert);
});
```

### Using `CreateProductVariant`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createProductVariantRef, CreateProductVariantVariables } from '@ss-mis/dataconnect';

// The `CreateProductVariant` mutation requires an argument of type `CreateProductVariantVariables`:
const createProductVariantVars: CreateProductVariantVariables = {
  productId: ..., 
  sku: ..., 
  costPrice: ..., 
  salePrice: ..., 
  stockQuantity: ..., 
};

// Call the `createProductVariantRef()` function to get a reference to the mutation.
const ref = createProductVariantRef(createProductVariantVars);
// Variables can be defined inline as well.
const ref = createProductVariantRef({ productId: ..., sku: ..., costPrice: ..., salePrice: ..., stockQuantity: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createProductVariantRef(dataConnect, createProductVariantVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.productVariant_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.productVariant_insert);
});
```

## CreateSupplier
You can execute the `CreateSupplier` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createSupplier(vars: CreateSupplierVariables): MutationPromise<CreateSupplierData, CreateSupplierVariables>;

interface CreateSupplierRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateSupplierVariables): MutationRef<CreateSupplierData, CreateSupplierVariables>;
}
export const createSupplierRef: CreateSupplierRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createSupplier(dc: DataConnect, vars: CreateSupplierVariables): MutationPromise<CreateSupplierData, CreateSupplierVariables>;

interface CreateSupplierRef {
  ...
  (dc: DataConnect, vars: CreateSupplierVariables): MutationRef<CreateSupplierData, CreateSupplierVariables>;
}
export const createSupplierRef: CreateSupplierRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createSupplierRef:
```typescript
const name = createSupplierRef.operationName;
console.log(name);
```

### Variables
The `CreateSupplier` mutation requires an argument of type `CreateSupplierVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateSupplierVariables {
  supplierName: string;
  contactName?: string | null;
  phone?: string | null;
  email?: string | null;
}
```
### Return Type
Recall that executing the `CreateSupplier` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateSupplierData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateSupplierData {
  supplier_insert: Supplier_Key;
}
```
### Using `CreateSupplier`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createSupplier, CreateSupplierVariables } from '@ss-mis/dataconnect';

// The `CreateSupplier` mutation requires an argument of type `CreateSupplierVariables`:
const createSupplierVars: CreateSupplierVariables = {
  supplierName: ..., 
  contactName: ..., // optional
  phone: ..., // optional
  email: ..., // optional
};

// Call the `createSupplier()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createSupplier(createSupplierVars);
// Variables can be defined inline as well.
const { data } = await createSupplier({ supplierName: ..., contactName: ..., phone: ..., email: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createSupplier(dataConnect, createSupplierVars);

console.log(data.supplier_insert);

// Or, you can use the `Promise` API.
createSupplier(createSupplierVars).then((response) => {
  const data = response.data;
  console.log(data.supplier_insert);
});
```

### Using `CreateSupplier`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createSupplierRef, CreateSupplierVariables } from '@ss-mis/dataconnect';

// The `CreateSupplier` mutation requires an argument of type `CreateSupplierVariables`:
const createSupplierVars: CreateSupplierVariables = {
  supplierName: ..., 
  contactName: ..., // optional
  phone: ..., // optional
  email: ..., // optional
};

// Call the `createSupplierRef()` function to get a reference to the mutation.
const ref = createSupplierRef(createSupplierVars);
// Variables can be defined inline as well.
const ref = createSupplierRef({ supplierName: ..., contactName: ..., phone: ..., email: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createSupplierRef(dataConnect, createSupplierVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.supplier_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.supplier_insert);
});
```

## CreateEmployee
You can execute the `CreateEmployee` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createEmployee(vars: CreateEmployeeVariables): MutationPromise<CreateEmployeeData, CreateEmployeeVariables>;

interface CreateEmployeeRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateEmployeeVariables): MutationRef<CreateEmployeeData, CreateEmployeeVariables>;
}
export const createEmployeeRef: CreateEmployeeRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createEmployee(dc: DataConnect, vars: CreateEmployeeVariables): MutationPromise<CreateEmployeeData, CreateEmployeeVariables>;

interface CreateEmployeeRef {
  ...
  (dc: DataConnect, vars: CreateEmployeeVariables): MutationRef<CreateEmployeeData, CreateEmployeeVariables>;
}
export const createEmployeeRef: CreateEmployeeRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createEmployeeRef:
```typescript
const name = createEmployeeRef.operationName;
console.log(name);
```

### Variables
The `CreateEmployee` mutation requires an argument of type `CreateEmployeeVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateEmployeeVariables {
  storeId: UUIDString;
  employeeName: string;
  username: string;
  position?: string | null;
}
```
### Return Type
Recall that executing the `CreateEmployee` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateEmployeeData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateEmployeeData {
  employee_insert: Employee_Key;
}
```
### Using `CreateEmployee`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createEmployee, CreateEmployeeVariables } from '@ss-mis/dataconnect';

// The `CreateEmployee` mutation requires an argument of type `CreateEmployeeVariables`:
const createEmployeeVars: CreateEmployeeVariables = {
  storeId: ..., 
  employeeName: ..., 
  username: ..., 
  position: ..., // optional
};

// Call the `createEmployee()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createEmployee(createEmployeeVars);
// Variables can be defined inline as well.
const { data } = await createEmployee({ storeId: ..., employeeName: ..., username: ..., position: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createEmployee(dataConnect, createEmployeeVars);

console.log(data.employee_insert);

// Or, you can use the `Promise` API.
createEmployee(createEmployeeVars).then((response) => {
  const data = response.data;
  console.log(data.employee_insert);
});
```

### Using `CreateEmployee`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createEmployeeRef, CreateEmployeeVariables } from '@ss-mis/dataconnect';

// The `CreateEmployee` mutation requires an argument of type `CreateEmployeeVariables`:
const createEmployeeVars: CreateEmployeeVariables = {
  storeId: ..., 
  employeeName: ..., 
  username: ..., 
  position: ..., // optional
};

// Call the `createEmployeeRef()` function to get a reference to the mutation.
const ref = createEmployeeRef(createEmployeeVars);
// Variables can be defined inline as well.
const ref = createEmployeeRef({ storeId: ..., employeeName: ..., username: ..., position: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createEmployeeRef(dataConnect, createEmployeeVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.employee_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.employee_insert);
});
```

## CreateCustomer
You can execute the `CreateCustomer` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createCustomer(vars: CreateCustomerVariables): MutationPromise<CreateCustomerData, CreateCustomerVariables>;

interface CreateCustomerRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateCustomerVariables): MutationRef<CreateCustomerData, CreateCustomerVariables>;
}
export const createCustomerRef: CreateCustomerRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createCustomer(dc: DataConnect, vars: CreateCustomerVariables): MutationPromise<CreateCustomerData, CreateCustomerVariables>;

interface CreateCustomerRef {
  ...
  (dc: DataConnect, vars: CreateCustomerVariables): MutationRef<CreateCustomerData, CreateCustomerVariables>;
}
export const createCustomerRef: CreateCustomerRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createCustomerRef:
```typescript
const name = createCustomerRef.operationName;
console.log(name);
```

### Variables
The `CreateCustomer` mutation requires an argument of type `CreateCustomerVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateCustomerVariables {
  customerName: string;
  phone?: string | null;
  email?: string | null;
}
```
### Return Type
Recall that executing the `CreateCustomer` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateCustomerData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateCustomerData {
  customer_insert: Customer_Key;
}
```
### Using `CreateCustomer`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createCustomer, CreateCustomerVariables } from '@ss-mis/dataconnect';

// The `CreateCustomer` mutation requires an argument of type `CreateCustomerVariables`:
const createCustomerVars: CreateCustomerVariables = {
  customerName: ..., 
  phone: ..., // optional
  email: ..., // optional
};

// Call the `createCustomer()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createCustomer(createCustomerVars);
// Variables can be defined inline as well.
const { data } = await createCustomer({ customerName: ..., phone: ..., email: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createCustomer(dataConnect, createCustomerVars);

console.log(data.customer_insert);

// Or, you can use the `Promise` API.
createCustomer(createCustomerVars).then((response) => {
  const data = response.data;
  console.log(data.customer_insert);
});
```

### Using `CreateCustomer`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createCustomerRef, CreateCustomerVariables } from '@ss-mis/dataconnect';

// The `CreateCustomer` mutation requires an argument of type `CreateCustomerVariables`:
const createCustomerVars: CreateCustomerVariables = {
  customerName: ..., 
  phone: ..., // optional
  email: ..., // optional
};

// Call the `createCustomerRef()` function to get a reference to the mutation.
const ref = createCustomerRef(createCustomerVars);
// Variables can be defined inline as well.
const ref = createCustomerRef({ customerName: ..., phone: ..., email: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createCustomerRef(dataConnect, createCustomerVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.customer_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.customer_insert);
});
```

## UpsertCustomer
You can execute the `UpsertCustomer` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
upsertCustomer(vars: UpsertCustomerVariables): MutationPromise<UpsertCustomerData, UpsertCustomerVariables>;

interface UpsertCustomerRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: UpsertCustomerVariables): MutationRef<UpsertCustomerData, UpsertCustomerVariables>;
}
export const upsertCustomerRef: UpsertCustomerRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
upsertCustomer(dc: DataConnect, vars: UpsertCustomerVariables): MutationPromise<UpsertCustomerData, UpsertCustomerVariables>;

interface UpsertCustomerRef {
  ...
  (dc: DataConnect, vars: UpsertCustomerVariables): MutationRef<UpsertCustomerData, UpsertCustomerVariables>;
}
export const upsertCustomerRef: UpsertCustomerRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the upsertCustomerRef:
```typescript
const name = upsertCustomerRef.operationName;
console.log(name);
```

### Variables
The `UpsertCustomer` mutation requires an argument of type `UpsertCustomerVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface UpsertCustomerVariables {
  id: UUIDString;
  customerName: string;
  phone?: string | null;
  email?: string | null;
  loyaltyPoints?: number | null;
}
```
### Return Type
Recall that executing the `UpsertCustomer` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `UpsertCustomerData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface UpsertCustomerData {
  customer_upsert: Customer_Key;
}
```
### Using `UpsertCustomer`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, upsertCustomer, UpsertCustomerVariables } from '@ss-mis/dataconnect';

// The `UpsertCustomer` mutation requires an argument of type `UpsertCustomerVariables`:
const upsertCustomerVars: UpsertCustomerVariables = {
  id: ..., 
  customerName: ..., 
  phone: ..., // optional
  email: ..., // optional
  loyaltyPoints: ..., // optional
};

// Call the `upsertCustomer()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await upsertCustomer(upsertCustomerVars);
// Variables can be defined inline as well.
const { data } = await upsertCustomer({ id: ..., customerName: ..., phone: ..., email: ..., loyaltyPoints: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await upsertCustomer(dataConnect, upsertCustomerVars);

console.log(data.customer_upsert);

// Or, you can use the `Promise` API.
upsertCustomer(upsertCustomerVars).then((response) => {
  const data = response.data;
  console.log(data.customer_upsert);
});
```

### Using `UpsertCustomer`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, upsertCustomerRef, UpsertCustomerVariables } from '@ss-mis/dataconnect';

// The `UpsertCustomer` mutation requires an argument of type `UpsertCustomerVariables`:
const upsertCustomerVars: UpsertCustomerVariables = {
  id: ..., 
  customerName: ..., 
  phone: ..., // optional
  email: ..., // optional
  loyaltyPoints: ..., // optional
};

// Call the `upsertCustomerRef()` function to get a reference to the mutation.
const ref = upsertCustomerRef(upsertCustomerVars);
// Variables can be defined inline as well.
const ref = upsertCustomerRef({ id: ..., customerName: ..., phone: ..., email: ..., loyaltyPoints: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = upsertCustomerRef(dataConnect, upsertCustomerVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.customer_upsert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.customer_upsert);
});
```

## CreatePurchaseHeader
You can execute the `CreatePurchaseHeader` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createPurchaseHeader(vars: CreatePurchaseHeaderVariables): MutationPromise<CreatePurchaseHeaderData, CreatePurchaseHeaderVariables>;

interface CreatePurchaseHeaderRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreatePurchaseHeaderVariables): MutationRef<CreatePurchaseHeaderData, CreatePurchaseHeaderVariables>;
}
export const createPurchaseHeaderRef: CreatePurchaseHeaderRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createPurchaseHeader(dc: DataConnect, vars: CreatePurchaseHeaderVariables): MutationPromise<CreatePurchaseHeaderData, CreatePurchaseHeaderVariables>;

interface CreatePurchaseHeaderRef {
  ...
  (dc: DataConnect, vars: CreatePurchaseHeaderVariables): MutationRef<CreatePurchaseHeaderData, CreatePurchaseHeaderVariables>;
}
export const createPurchaseHeaderRef: CreatePurchaseHeaderRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createPurchaseHeaderRef:
```typescript
const name = createPurchaseHeaderRef.operationName;
console.log(name);
```

### Variables
The `CreatePurchaseHeader` mutation requires an argument of type `CreatePurchaseHeaderVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreatePurchaseHeaderVariables {
  referenceNo: string;
  supplierId: UUIDString;
  employeeId: UUIDString;
  storeId: UUIDString;
  totalAmount: number;
  grandTotal: number;
}
```
### Return Type
Recall that executing the `CreatePurchaseHeader` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreatePurchaseHeaderData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreatePurchaseHeaderData {
  purchaseHeader_insert: PurchaseHeader_Key;
}
```
### Using `CreatePurchaseHeader`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createPurchaseHeader, CreatePurchaseHeaderVariables } from '@ss-mis/dataconnect';

// The `CreatePurchaseHeader` mutation requires an argument of type `CreatePurchaseHeaderVariables`:
const createPurchaseHeaderVars: CreatePurchaseHeaderVariables = {
  referenceNo: ..., 
  supplierId: ..., 
  employeeId: ..., 
  storeId: ..., 
  totalAmount: ..., 
  grandTotal: ..., 
};

// Call the `createPurchaseHeader()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createPurchaseHeader(createPurchaseHeaderVars);
// Variables can be defined inline as well.
const { data } = await createPurchaseHeader({ referenceNo: ..., supplierId: ..., employeeId: ..., storeId: ..., totalAmount: ..., grandTotal: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createPurchaseHeader(dataConnect, createPurchaseHeaderVars);

console.log(data.purchaseHeader_insert);

// Or, you can use the `Promise` API.
createPurchaseHeader(createPurchaseHeaderVars).then((response) => {
  const data = response.data;
  console.log(data.purchaseHeader_insert);
});
```

### Using `CreatePurchaseHeader`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createPurchaseHeaderRef, CreatePurchaseHeaderVariables } from '@ss-mis/dataconnect';

// The `CreatePurchaseHeader` mutation requires an argument of type `CreatePurchaseHeaderVariables`:
const createPurchaseHeaderVars: CreatePurchaseHeaderVariables = {
  referenceNo: ..., 
  supplierId: ..., 
  employeeId: ..., 
  storeId: ..., 
  totalAmount: ..., 
  grandTotal: ..., 
};

// Call the `createPurchaseHeaderRef()` function to get a reference to the mutation.
const ref = createPurchaseHeaderRef(createPurchaseHeaderVars);
// Variables can be defined inline as well.
const ref = createPurchaseHeaderRef({ referenceNo: ..., supplierId: ..., employeeId: ..., storeId: ..., totalAmount: ..., grandTotal: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createPurchaseHeaderRef(dataConnect, createPurchaseHeaderVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.purchaseHeader_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.purchaseHeader_insert);
});
```

## CreatePurchaseDetail
You can execute the `CreatePurchaseDetail` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createPurchaseDetail(vars: CreatePurchaseDetailVariables): MutationPromise<CreatePurchaseDetailData, CreatePurchaseDetailVariables>;

interface CreatePurchaseDetailRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreatePurchaseDetailVariables): MutationRef<CreatePurchaseDetailData, CreatePurchaseDetailVariables>;
}
export const createPurchaseDetailRef: CreatePurchaseDetailRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createPurchaseDetail(dc: DataConnect, vars: CreatePurchaseDetailVariables): MutationPromise<CreatePurchaseDetailData, CreatePurchaseDetailVariables>;

interface CreatePurchaseDetailRef {
  ...
  (dc: DataConnect, vars: CreatePurchaseDetailVariables): MutationRef<CreatePurchaseDetailData, CreatePurchaseDetailVariables>;
}
export const createPurchaseDetailRef: CreatePurchaseDetailRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createPurchaseDetailRef:
```typescript
const name = createPurchaseDetailRef.operationName;
console.log(name);
```

### Variables
The `CreatePurchaseDetail` mutation requires an argument of type `CreatePurchaseDetailVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreatePurchaseDetailVariables {
  purchaseId: UUIDString;
  variantId: UUIDString;
  quantity: number;
  unitCost: number;
  subTotal: number;
}
```
### Return Type
Recall that executing the `CreatePurchaseDetail` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreatePurchaseDetailData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreatePurchaseDetailData {
  purchaseDetail_insert: PurchaseDetail_Key;
}
```
### Using `CreatePurchaseDetail`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createPurchaseDetail, CreatePurchaseDetailVariables } from '@ss-mis/dataconnect';

// The `CreatePurchaseDetail` mutation requires an argument of type `CreatePurchaseDetailVariables`:
const createPurchaseDetailVars: CreatePurchaseDetailVariables = {
  purchaseId: ..., 
  variantId: ..., 
  quantity: ..., 
  unitCost: ..., 
  subTotal: ..., 
};

// Call the `createPurchaseDetail()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createPurchaseDetail(createPurchaseDetailVars);
// Variables can be defined inline as well.
const { data } = await createPurchaseDetail({ purchaseId: ..., variantId: ..., quantity: ..., unitCost: ..., subTotal: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createPurchaseDetail(dataConnect, createPurchaseDetailVars);

console.log(data.purchaseDetail_insert);

// Or, you can use the `Promise` API.
createPurchaseDetail(createPurchaseDetailVars).then((response) => {
  const data = response.data;
  console.log(data.purchaseDetail_insert);
});
```

### Using `CreatePurchaseDetail`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createPurchaseDetailRef, CreatePurchaseDetailVariables } from '@ss-mis/dataconnect';

// The `CreatePurchaseDetail` mutation requires an argument of type `CreatePurchaseDetailVariables`:
const createPurchaseDetailVars: CreatePurchaseDetailVariables = {
  purchaseId: ..., 
  variantId: ..., 
  quantity: ..., 
  unitCost: ..., 
  subTotal: ..., 
};

// Call the `createPurchaseDetailRef()` function to get a reference to the mutation.
const ref = createPurchaseDetailRef(createPurchaseDetailVars);
// Variables can be defined inline as well.
const ref = createPurchaseDetailRef({ purchaseId: ..., variantId: ..., quantity: ..., unitCost: ..., subTotal: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createPurchaseDetailRef(dataConnect, createPurchaseDetailVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.purchaseDetail_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.purchaseDetail_insert);
});
```

## CreateSaleHeader
You can execute the `CreateSaleHeader` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createSaleHeader(vars: CreateSaleHeaderVariables): MutationPromise<CreateSaleHeaderData, CreateSaleHeaderVariables>;

interface CreateSaleHeaderRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateSaleHeaderVariables): MutationRef<CreateSaleHeaderData, CreateSaleHeaderVariables>;
}
export const createSaleHeaderRef: CreateSaleHeaderRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createSaleHeader(dc: DataConnect, vars: CreateSaleHeaderVariables): MutationPromise<CreateSaleHeaderData, CreateSaleHeaderVariables>;

interface CreateSaleHeaderRef {
  ...
  (dc: DataConnect, vars: CreateSaleHeaderVariables): MutationRef<CreateSaleHeaderData, CreateSaleHeaderVariables>;
}
export const createSaleHeaderRef: CreateSaleHeaderRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createSaleHeaderRef:
```typescript
const name = createSaleHeaderRef.operationName;
console.log(name);
```

### Variables
The `CreateSaleHeader` mutation requires an argument of type `CreateSaleHeaderVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateSaleHeaderVariables {
  invoiceNo: string;
  storeId: UUIDString;
  employeeId: UUIDString;
  subTotal: number;
  grandTotal: number;
}
```
### Return Type
Recall that executing the `CreateSaleHeader` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateSaleHeaderData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateSaleHeaderData {
  saleHeader_insert: SaleHeader_Key;
}
```
### Using `CreateSaleHeader`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createSaleHeader, CreateSaleHeaderVariables } from '@ss-mis/dataconnect';

// The `CreateSaleHeader` mutation requires an argument of type `CreateSaleHeaderVariables`:
const createSaleHeaderVars: CreateSaleHeaderVariables = {
  invoiceNo: ..., 
  storeId: ..., 
  employeeId: ..., 
  subTotal: ..., 
  grandTotal: ..., 
};

// Call the `createSaleHeader()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createSaleHeader(createSaleHeaderVars);
// Variables can be defined inline as well.
const { data } = await createSaleHeader({ invoiceNo: ..., storeId: ..., employeeId: ..., subTotal: ..., grandTotal: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createSaleHeader(dataConnect, createSaleHeaderVars);

console.log(data.saleHeader_insert);

// Or, you can use the `Promise` API.
createSaleHeader(createSaleHeaderVars).then((response) => {
  const data = response.data;
  console.log(data.saleHeader_insert);
});
```

### Using `CreateSaleHeader`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createSaleHeaderRef, CreateSaleHeaderVariables } from '@ss-mis/dataconnect';

// The `CreateSaleHeader` mutation requires an argument of type `CreateSaleHeaderVariables`:
const createSaleHeaderVars: CreateSaleHeaderVariables = {
  invoiceNo: ..., 
  storeId: ..., 
  employeeId: ..., 
  subTotal: ..., 
  grandTotal: ..., 
};

// Call the `createSaleHeaderRef()` function to get a reference to the mutation.
const ref = createSaleHeaderRef(createSaleHeaderVars);
// Variables can be defined inline as well.
const ref = createSaleHeaderRef({ invoiceNo: ..., storeId: ..., employeeId: ..., subTotal: ..., grandTotal: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createSaleHeaderRef(dataConnect, createSaleHeaderVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.saleHeader_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.saleHeader_insert);
});
```

## CreateSaleDetail
You can execute the `CreateSaleDetail` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createSaleDetail(vars: CreateSaleDetailVariables): MutationPromise<CreateSaleDetailData, CreateSaleDetailVariables>;

interface CreateSaleDetailRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateSaleDetailVariables): MutationRef<CreateSaleDetailData, CreateSaleDetailVariables>;
}
export const createSaleDetailRef: CreateSaleDetailRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createSaleDetail(dc: DataConnect, vars: CreateSaleDetailVariables): MutationPromise<CreateSaleDetailData, CreateSaleDetailVariables>;

interface CreateSaleDetailRef {
  ...
  (dc: DataConnect, vars: CreateSaleDetailVariables): MutationRef<CreateSaleDetailData, CreateSaleDetailVariables>;
}
export const createSaleDetailRef: CreateSaleDetailRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createSaleDetailRef:
```typescript
const name = createSaleDetailRef.operationName;
console.log(name);
```

### Variables
The `CreateSaleDetail` mutation requires an argument of type `CreateSaleDetailVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateSaleDetailVariables {
  saleId: UUIDString;
  variantId: UUIDString;
  quantity: number;
  unitPrice: number;
  subTotal: number;
}
```
### Return Type
Recall that executing the `CreateSaleDetail` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateSaleDetailData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateSaleDetailData {
  saleDetail_insert: SaleDetail_Key;
}
```
### Using `CreateSaleDetail`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createSaleDetail, CreateSaleDetailVariables } from '@ss-mis/dataconnect';

// The `CreateSaleDetail` mutation requires an argument of type `CreateSaleDetailVariables`:
const createSaleDetailVars: CreateSaleDetailVariables = {
  saleId: ..., 
  variantId: ..., 
  quantity: ..., 
  unitPrice: ..., 
  subTotal: ..., 
};

// Call the `createSaleDetail()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createSaleDetail(createSaleDetailVars);
// Variables can be defined inline as well.
const { data } = await createSaleDetail({ saleId: ..., variantId: ..., quantity: ..., unitPrice: ..., subTotal: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createSaleDetail(dataConnect, createSaleDetailVars);

console.log(data.saleDetail_insert);

// Or, you can use the `Promise` API.
createSaleDetail(createSaleDetailVars).then((response) => {
  const data = response.data;
  console.log(data.saleDetail_insert);
});
```

### Using `CreateSaleDetail`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createSaleDetailRef, CreateSaleDetailVariables } from '@ss-mis/dataconnect';

// The `CreateSaleDetail` mutation requires an argument of type `CreateSaleDetailVariables`:
const createSaleDetailVars: CreateSaleDetailVariables = {
  saleId: ..., 
  variantId: ..., 
  quantity: ..., 
  unitPrice: ..., 
  subTotal: ..., 
};

// Call the `createSaleDetailRef()` function to get a reference to the mutation.
const ref = createSaleDetailRef(createSaleDetailVars);
// Variables can be defined inline as well.
const ref = createSaleDetailRef({ saleId: ..., variantId: ..., quantity: ..., unitPrice: ..., subTotal: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createSaleDetailRef(dataConnect, createSaleDetailVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.saleDetail_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.saleDetail_insert);
});
```

## CreatePayment
You can execute the `CreatePayment` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createPayment(vars: CreatePaymentVariables): MutationPromise<CreatePaymentData, CreatePaymentVariables>;

interface CreatePaymentRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreatePaymentVariables): MutationRef<CreatePaymentData, CreatePaymentVariables>;
}
export const createPaymentRef: CreatePaymentRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createPayment(dc: DataConnect, vars: CreatePaymentVariables): MutationPromise<CreatePaymentData, CreatePaymentVariables>;

interface CreatePaymentRef {
  ...
  (dc: DataConnect, vars: CreatePaymentVariables): MutationRef<CreatePaymentData, CreatePaymentVariables>;
}
export const createPaymentRef: CreatePaymentRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createPaymentRef:
```typescript
const name = createPaymentRef.operationName;
console.log(name);
```

### Variables
The `CreatePayment` mutation requires an argument of type `CreatePaymentVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreatePaymentVariables {
  saleId: UUIDString;
  amount: number;
  amountTendered: number;
  changeDue: number;
  paymentMethod: string;
}
```
### Return Type
Recall that executing the `CreatePayment` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreatePaymentData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreatePaymentData {
  payment_insert: Payment_Key;
}
```
### Using `CreatePayment`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createPayment, CreatePaymentVariables } from '@ss-mis/dataconnect';

// The `CreatePayment` mutation requires an argument of type `CreatePaymentVariables`:
const createPaymentVars: CreatePaymentVariables = {
  saleId: ..., 
  amount: ..., 
  amountTendered: ..., 
  changeDue: ..., 
  paymentMethod: ..., 
};

// Call the `createPayment()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createPayment(createPaymentVars);
// Variables can be defined inline as well.
const { data } = await createPayment({ saleId: ..., amount: ..., amountTendered: ..., changeDue: ..., paymentMethod: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createPayment(dataConnect, createPaymentVars);

console.log(data.payment_insert);

// Or, you can use the `Promise` API.
createPayment(createPaymentVars).then((response) => {
  const data = response.data;
  console.log(data.payment_insert);
});
```

### Using `CreatePayment`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createPaymentRef, CreatePaymentVariables } from '@ss-mis/dataconnect';

// The `CreatePayment` mutation requires an argument of type `CreatePaymentVariables`:
const createPaymentVars: CreatePaymentVariables = {
  saleId: ..., 
  amount: ..., 
  amountTendered: ..., 
  changeDue: ..., 
  paymentMethod: ..., 
};

// Call the `createPaymentRef()` function to get a reference to the mutation.
const ref = createPaymentRef(createPaymentVars);
// Variables can be defined inline as well.
const ref = createPaymentRef({ saleId: ..., amount: ..., amountTendered: ..., changeDue: ..., paymentMethod: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createPaymentRef(dataConnect, createPaymentVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.payment_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.payment_insert);
});
```

## CreateStockMovement
You can execute the `CreateStockMovement` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createStockMovement(vars: CreateStockMovementVariables): MutationPromise<CreateStockMovementData, CreateStockMovementVariables>;

interface CreateStockMovementRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateStockMovementVariables): MutationRef<CreateStockMovementData, CreateStockMovementVariables>;
}
export const createStockMovementRef: CreateStockMovementRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createStockMovement(dc: DataConnect, vars: CreateStockMovementVariables): MutationPromise<CreateStockMovementData, CreateStockMovementVariables>;

interface CreateStockMovementRef {
  ...
  (dc: DataConnect, vars: CreateStockMovementVariables): MutationRef<CreateStockMovementData, CreateStockMovementVariables>;
}
export const createStockMovementRef: CreateStockMovementRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createStockMovementRef:
```typescript
const name = createStockMovementRef.operationName;
console.log(name);
```

### Variables
The `CreateStockMovement` mutation requires an argument of type `CreateStockMovementVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateStockMovementVariables {
  storeId: UUIDString;
  variantId: UUIDString;
  movementType: string;
  quantity: number;
  stockBefore: number;
  stockAfter: number;
}
```
### Return Type
Recall that executing the `CreateStockMovement` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateStockMovementData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateStockMovementData {
  stockMovement_insert: StockMovement_Key;
}
```
### Using `CreateStockMovement`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createStockMovement, CreateStockMovementVariables } from '@ss-mis/dataconnect';

// The `CreateStockMovement` mutation requires an argument of type `CreateStockMovementVariables`:
const createStockMovementVars: CreateStockMovementVariables = {
  storeId: ..., 
  variantId: ..., 
  movementType: ..., 
  quantity: ..., 
  stockBefore: ..., 
  stockAfter: ..., 
};

// Call the `createStockMovement()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createStockMovement(createStockMovementVars);
// Variables can be defined inline as well.
const { data } = await createStockMovement({ storeId: ..., variantId: ..., movementType: ..., quantity: ..., stockBefore: ..., stockAfter: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createStockMovement(dataConnect, createStockMovementVars);

console.log(data.stockMovement_insert);

// Or, you can use the `Promise` API.
createStockMovement(createStockMovementVars).then((response) => {
  const data = response.data;
  console.log(data.stockMovement_insert);
});
```

### Using `CreateStockMovement`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createStockMovementRef, CreateStockMovementVariables } from '@ss-mis/dataconnect';

// The `CreateStockMovement` mutation requires an argument of type `CreateStockMovementVariables`:
const createStockMovementVars: CreateStockMovementVariables = {
  storeId: ..., 
  variantId: ..., 
  movementType: ..., 
  quantity: ..., 
  stockBefore: ..., 
  stockAfter: ..., 
};

// Call the `createStockMovementRef()` function to get a reference to the mutation.
const ref = createStockMovementRef(createStockMovementVars);
// Variables can be defined inline as well.
const ref = createStockMovementRef({ storeId: ..., variantId: ..., movementType: ..., quantity: ..., stockBefore: ..., stockAfter: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createStockMovementRef(dataConnect, createStockMovementVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.stockMovement_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.stockMovement_insert);
});
```

## CreateAuditLog
You can execute the `CreateAuditLog` mutation using the following action shortcut function, or by calling `executeMutation()` after calling the following `MutationRef` function, both of which are defined in [dataconnect/index.d.ts](./index.d.ts):
```typescript
createAuditLog(vars: CreateAuditLogVariables): MutationPromise<CreateAuditLogData, CreateAuditLogVariables>;

interface CreateAuditLogRef {
  ...
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateAuditLogVariables): MutationRef<CreateAuditLogData, CreateAuditLogVariables>;
}
export const createAuditLogRef: CreateAuditLogRef;
```
You can also pass in a `DataConnect` instance to the action shortcut function or `MutationRef` function.
```typescript
createAuditLog(dc: DataConnect, vars: CreateAuditLogVariables): MutationPromise<CreateAuditLogData, CreateAuditLogVariables>;

interface CreateAuditLogRef {
  ...
  (dc: DataConnect, vars: CreateAuditLogVariables): MutationRef<CreateAuditLogData, CreateAuditLogVariables>;
}
export const createAuditLogRef: CreateAuditLogRef;
```

If you need the name of the operation without creating a ref, you can retrieve the operation name by calling the `operationName` property on the createAuditLogRef:
```typescript
const name = createAuditLogRef.operationName;
console.log(name);
```

### Variables
The `CreateAuditLog` mutation requires an argument of type `CreateAuditLogVariables`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:

```typescript
export interface CreateAuditLogVariables {
  action: string;
  entityType: string;
  entityId?: string | null;
  userId?: string | null;
}
```
### Return Type
Recall that executing the `CreateAuditLog` mutation returns a `MutationPromise` that resolves to an object with a `data` property.

The `data` property is an object of type `CreateAuditLogData`, which is defined in [dataconnect/index.d.ts](./index.d.ts). It has the following fields:
```typescript
export interface CreateAuditLogData {
  auditLog_insert: AuditLog_Key;
}
```
### Using `CreateAuditLog`'s action shortcut function

```typescript
import { getDataConnect } from 'firebase/data-connect';
import { connectorConfig, createAuditLog, CreateAuditLogVariables } from '@ss-mis/dataconnect';

// The `CreateAuditLog` mutation requires an argument of type `CreateAuditLogVariables`:
const createAuditLogVars: CreateAuditLogVariables = {
  action: ..., 
  entityType: ..., 
  entityId: ..., // optional
  userId: ..., // optional
};

// Call the `createAuditLog()` function to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await createAuditLog(createAuditLogVars);
// Variables can be defined inline as well.
const { data } = await createAuditLog({ action: ..., entityType: ..., entityId: ..., userId: ..., });

// You can also pass in a `DataConnect` instance to the action shortcut function.
const dataConnect = getDataConnect(connectorConfig);
const { data } = await createAuditLog(dataConnect, createAuditLogVars);

console.log(data.auditLog_insert);

// Or, you can use the `Promise` API.
createAuditLog(createAuditLogVars).then((response) => {
  const data = response.data;
  console.log(data.auditLog_insert);
});
```

### Using `CreateAuditLog`'s `MutationRef` function

```typescript
import { getDataConnect, executeMutation } from 'firebase/data-connect';
import { connectorConfig, createAuditLogRef, CreateAuditLogVariables } from '@ss-mis/dataconnect';

// The `CreateAuditLog` mutation requires an argument of type `CreateAuditLogVariables`:
const createAuditLogVars: CreateAuditLogVariables = {
  action: ..., 
  entityType: ..., 
  entityId: ..., // optional
  userId: ..., // optional
};

// Call the `createAuditLogRef()` function to get a reference to the mutation.
const ref = createAuditLogRef(createAuditLogVars);
// Variables can be defined inline as well.
const ref = createAuditLogRef({ action: ..., entityType: ..., entityId: ..., userId: ..., });

// You can also pass in a `DataConnect` instance to the `MutationRef` function.
const dataConnect = getDataConnect(connectorConfig);
const ref = createAuditLogRef(dataConnect, createAuditLogVars);

// Call `executeMutation()` on the reference to execute the mutation.
// You can use the `await` keyword to wait for the promise to resolve.
const { data } = await executeMutation(ref);

console.log(data.auditLog_insert);

// Or, you can use the `Promise` API.
executeMutation(ref).then((response) => {
  const data = response.data;
  console.log(data.auditLog_insert);
});
```

