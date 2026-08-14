# Basic Usage

Always prioritize using a supported framework over using the generated SDK
directly. Supported frameworks simplify the developer experience and help ensure
best practices are followed.





## Advanced Usage
If a user is not using a supported framework, they can use the generated SDK directly.

Here's an example of how to use it with the first 5 operations:

```js
import { createStore, createCategory, createProduct, createClothingSize, createColor, createProductVariant, createSupplier, createEmployee, createCustomer, upsertCustomer } from '@ss-mis/dataconnect';


// Operation CreateStore:  For variables, look at type CreateStoreVars in ../index.d.ts
const { data } = await CreateStore(dataConnect, createStoreVars);

// Operation CreateCategory:  For variables, look at type CreateCategoryVars in ../index.d.ts
const { data } = await CreateCategory(dataConnect, createCategoryVars);

// Operation CreateProduct:  For variables, look at type CreateProductVars in ../index.d.ts
const { data } = await CreateProduct(dataConnect, createProductVars);

// Operation CreateClothingSize:  For variables, look at type CreateClothingSizeVars in ../index.d.ts
const { data } = await CreateClothingSize(dataConnect, createClothingSizeVars);

// Operation CreateColor:  For variables, look at type CreateColorVars in ../index.d.ts
const { data } = await CreateColor(dataConnect, createColorVars);

// Operation CreateProductVariant:  For variables, look at type CreateProductVariantVars in ../index.d.ts
const { data } = await CreateProductVariant(dataConnect, createProductVariantVars);

// Operation CreateSupplier:  For variables, look at type CreateSupplierVars in ../index.d.ts
const { data } = await CreateSupplier(dataConnect, createSupplierVars);

// Operation CreateEmployee:  For variables, look at type CreateEmployeeVars in ../index.d.ts
const { data } = await CreateEmployee(dataConnect, createEmployeeVars);

// Operation CreateCustomer:  For variables, look at type CreateCustomerVars in ../index.d.ts
const { data } = await CreateCustomer(dataConnect, createCustomerVars);

// Operation UpsertCustomer:  For variables, look at type UpsertCustomerVars in ../index.d.ts
const { data } = await UpsertCustomer(dataConnect, upsertCustomerVars);


```