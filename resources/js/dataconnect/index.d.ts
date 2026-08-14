import { ConnectorConfig, DataConnect, QueryRef, QueryPromise, ExecuteQueryOptions, MutationRef, MutationPromise } from 'firebase/data-connect';

export const connectorConfig: ConnectorConfig;

export type TimestampString = string;
export type UUIDString = string;
export type Int64String = string;
export type DateString = string;




export interface AuditLog_Key {
  id: UUIDString;
  __typename?: 'AuditLog_Key';
}

export interface Category_Key {
  id: UUIDString;
  __typename?: 'Category_Key';
}

export interface ClothingSize_Key {
  id: UUIDString;
  __typename?: 'ClothingSize_Key';
}

export interface Color_Key {
  id: UUIDString;
  __typename?: 'Color_Key';
}

export interface CreateAuditLogData {
  auditLog_insert: AuditLog_Key;
}

export interface CreateAuditLogVariables {
  action: string;
  entityType: string;
  entityId?: string | null;
  userId?: string | null;
}

export interface CreateCategoryData {
  category_insert: Category_Key;
}

export interface CreateCategoryVariables {
  categoryName: string;
  slug: string;
  description?: string | null;
}

export interface CreateClothingSizeData {
  clothingSize_insert: ClothingSize_Key;
}

export interface CreateClothingSizeVariables {
  sizeCode: string;
  sizeName: string;
  description?: string | null;
}

export interface CreateColorData {
  color_insert: Color_Key;
}

export interface CreateColorVariables {
  colorCode: string;
  colorName: string;
  hexCode?: string | null;
}

export interface CreateCustomerData {
  customer_insert: Customer_Key;
}

export interface CreateCustomerVariables {
  customerName: string;
  phone?: string | null;
  email?: string | null;
}

export interface CreateEmployeeData {
  employee_insert: Employee_Key;
}

export interface CreateEmployeeVariables {
  storeId: UUIDString;
  employeeName: string;
  username: string;
  position?: string | null;
}

export interface CreatePaymentData {
  payment_insert: Payment_Key;
}

export interface CreatePaymentVariables {
  saleId: UUIDString;
  amount: number;
  amountTendered: number;
  changeDue: number;
  paymentMethod: string;
}

export interface CreateProductData {
  product_insert: Product_Key;
}

export interface CreateProductVariables {
  productName: string;
  categoryId: UUIDString;
  brand?: string | null;
  description?: string | null;
}

export interface CreateProductVariantData {
  productVariant_insert: ProductVariant_Key;
}

export interface CreateProductVariantVariables {
  productId: UUIDString;
  sku: string;
  costPrice: number;
  salePrice: number;
  stockQuantity: number;
}

export interface CreatePurchaseDetailData {
  purchaseDetail_insert: PurchaseDetail_Key;
}

export interface CreatePurchaseDetailVariables {
  purchaseId: UUIDString;
  variantId: UUIDString;
  quantity: number;
  unitCost: number;
  subTotal: number;
}

export interface CreatePurchaseHeaderData {
  purchaseHeader_insert: PurchaseHeader_Key;
}

export interface CreatePurchaseHeaderVariables {
  referenceNo: string;
  supplierId: UUIDString;
  employeeId: UUIDString;
  storeId: UUIDString;
  totalAmount: number;
  grandTotal: number;
}

export interface CreateSaleDetailData {
  saleDetail_insert: SaleDetail_Key;
}

export interface CreateSaleDetailVariables {
  saleId: UUIDString;
  variantId: UUIDString;
  quantity: number;
  unitPrice: number;
  subTotal: number;
}

export interface CreateSaleHeaderData {
  saleHeader_insert: SaleHeader_Key;
}

export interface CreateSaleHeaderVariables {
  invoiceNo: string;
  storeId: UUIDString;
  employeeId: UUIDString;
  subTotal: number;
  grandTotal: number;
}

export interface CreateStockMovementData {
  stockMovement_insert: StockMovement_Key;
}

export interface CreateStockMovementVariables {
  storeId: UUIDString;
  variantId: UUIDString;
  movementType: string;
  quantity: number;
  stockBefore: number;
  stockAfter: number;
}

export interface CreateStoreData {
  store_insert: Store_Key;
}

export interface CreateStoreVariables {
  storeName: string;
  code: string;
  phone?: string | null;
  email?: string | null;
  address?: string | null;
}

export interface CreateSupplierData {
  supplier_insert: Supplier_Key;
}

export interface CreateSupplierVariables {
  supplierName: string;
  contactName?: string | null;
  phone?: string | null;
  email?: string | null;
}

export interface Customer_Key {
  id: UUIDString;
  __typename?: 'Customer_Key';
}

export interface Employee_Key {
  id: UUIDString;
  __typename?: 'Employee_Key';
}

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

export interface GetRecentSalesVariables {
  limit?: number | null;
}

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

export interface ListCategoriesData {
  categories: ({
    id: UUIDString;
    categoryName: string;
    slug: string;
    description?: string | null;
    isActive: boolean;
  } & Category_Key)[];
}

export interface ListClothingSizesData {
  clothingSizes: ({
    id: UUIDString;
    sizeCode: string;
    sizeName: string;
    description?: string | null;
  } & ClothingSize_Key)[];
}

export interface ListColorsData {
  colors: ({
    id: UUIDString;
    colorCode: string;
    colorName: string;
    hexCode?: string | null;
  } & Color_Key)[];
}

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

export interface Payment_Key {
  id: UUIDString;
  __typename?: 'Payment_Key';
}

export interface ProductVariant_Key {
  id: UUIDString;
  __typename?: 'ProductVariant_Key';
}

export interface Product_Key {
  id: UUIDString;
  __typename?: 'Product_Key';
}

export interface PurchaseDetail_Key {
  id: UUIDString;
  __typename?: 'PurchaseDetail_Key';
}

export interface PurchaseHeader_Key {
  id: UUIDString;
  __typename?: 'PurchaseHeader_Key';
}

export interface SaleDetail_Key {
  id: UUIDString;
  __typename?: 'SaleDetail_Key';
}

export interface SaleHeader_Key {
  id: UUIDString;
  __typename?: 'SaleHeader_Key';
}

export interface SearchCustomerByPhoneData {
  customers: ({
    id: UUIDString;
    customerName: string;
    phone?: string | null;
    email?: string | null;
    loyaltyPoints: number;
  } & Customer_Key)[];
}

export interface SearchCustomerByPhoneVariables {
  phone: string;
}

export interface StockMovement_Key {
  id: UUIDString;
  __typename?: 'StockMovement_Key';
}

export interface Store_Key {
  id: UUIDString;
  __typename?: 'Store_Key';
}

export interface Supplier_Key {
  id: UUIDString;
  __typename?: 'Supplier_Key';
}

export interface UpsertCustomerData {
  customer_upsert: Customer_Key;
}

export interface UpsertCustomerVariables {
  id: UUIDString;
  customerName: string;
  phone?: string | null;
  email?: string | null;
  loyaltyPoints?: number | null;
}

interface CreateStoreRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateStoreVariables): MutationRef<CreateStoreData, CreateStoreVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateStoreVariables): MutationRef<CreateStoreData, CreateStoreVariables>;
  operationName: string;
}
export const createStoreRef: CreateStoreRef;

export function createStore(vars: CreateStoreVariables): MutationPromise<CreateStoreData, CreateStoreVariables>;
export function createStore(dc: DataConnect, vars: CreateStoreVariables): MutationPromise<CreateStoreData, CreateStoreVariables>;

interface CreateCategoryRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateCategoryVariables): MutationRef<CreateCategoryData, CreateCategoryVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateCategoryVariables): MutationRef<CreateCategoryData, CreateCategoryVariables>;
  operationName: string;
}
export const createCategoryRef: CreateCategoryRef;

export function createCategory(vars: CreateCategoryVariables): MutationPromise<CreateCategoryData, CreateCategoryVariables>;
export function createCategory(dc: DataConnect, vars: CreateCategoryVariables): MutationPromise<CreateCategoryData, CreateCategoryVariables>;

interface CreateProductRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateProductVariables): MutationRef<CreateProductData, CreateProductVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateProductVariables): MutationRef<CreateProductData, CreateProductVariables>;
  operationName: string;
}
export const createProductRef: CreateProductRef;

export function createProduct(vars: CreateProductVariables): MutationPromise<CreateProductData, CreateProductVariables>;
export function createProduct(dc: DataConnect, vars: CreateProductVariables): MutationPromise<CreateProductData, CreateProductVariables>;

interface CreateClothingSizeRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateClothingSizeVariables): MutationRef<CreateClothingSizeData, CreateClothingSizeVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateClothingSizeVariables): MutationRef<CreateClothingSizeData, CreateClothingSizeVariables>;
  operationName: string;
}
export const createClothingSizeRef: CreateClothingSizeRef;

export function createClothingSize(vars: CreateClothingSizeVariables): MutationPromise<CreateClothingSizeData, CreateClothingSizeVariables>;
export function createClothingSize(dc: DataConnect, vars: CreateClothingSizeVariables): MutationPromise<CreateClothingSizeData, CreateClothingSizeVariables>;

interface CreateColorRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateColorVariables): MutationRef<CreateColorData, CreateColorVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateColorVariables): MutationRef<CreateColorData, CreateColorVariables>;
  operationName: string;
}
export const createColorRef: CreateColorRef;

export function createColor(vars: CreateColorVariables): MutationPromise<CreateColorData, CreateColorVariables>;
export function createColor(dc: DataConnect, vars: CreateColorVariables): MutationPromise<CreateColorData, CreateColorVariables>;

interface CreateProductVariantRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateProductVariantVariables): MutationRef<CreateProductVariantData, CreateProductVariantVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateProductVariantVariables): MutationRef<CreateProductVariantData, CreateProductVariantVariables>;
  operationName: string;
}
export const createProductVariantRef: CreateProductVariantRef;

export function createProductVariant(vars: CreateProductVariantVariables): MutationPromise<CreateProductVariantData, CreateProductVariantVariables>;
export function createProductVariant(dc: DataConnect, vars: CreateProductVariantVariables): MutationPromise<CreateProductVariantData, CreateProductVariantVariables>;

interface CreateSupplierRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateSupplierVariables): MutationRef<CreateSupplierData, CreateSupplierVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateSupplierVariables): MutationRef<CreateSupplierData, CreateSupplierVariables>;
  operationName: string;
}
export const createSupplierRef: CreateSupplierRef;

export function createSupplier(vars: CreateSupplierVariables): MutationPromise<CreateSupplierData, CreateSupplierVariables>;
export function createSupplier(dc: DataConnect, vars: CreateSupplierVariables): MutationPromise<CreateSupplierData, CreateSupplierVariables>;

interface CreateEmployeeRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateEmployeeVariables): MutationRef<CreateEmployeeData, CreateEmployeeVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateEmployeeVariables): MutationRef<CreateEmployeeData, CreateEmployeeVariables>;
  operationName: string;
}
export const createEmployeeRef: CreateEmployeeRef;

export function createEmployee(vars: CreateEmployeeVariables): MutationPromise<CreateEmployeeData, CreateEmployeeVariables>;
export function createEmployee(dc: DataConnect, vars: CreateEmployeeVariables): MutationPromise<CreateEmployeeData, CreateEmployeeVariables>;

interface CreateCustomerRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateCustomerVariables): MutationRef<CreateCustomerData, CreateCustomerVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateCustomerVariables): MutationRef<CreateCustomerData, CreateCustomerVariables>;
  operationName: string;
}
export const createCustomerRef: CreateCustomerRef;

export function createCustomer(vars: CreateCustomerVariables): MutationPromise<CreateCustomerData, CreateCustomerVariables>;
export function createCustomer(dc: DataConnect, vars: CreateCustomerVariables): MutationPromise<CreateCustomerData, CreateCustomerVariables>;

interface UpsertCustomerRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: UpsertCustomerVariables): MutationRef<UpsertCustomerData, UpsertCustomerVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: UpsertCustomerVariables): MutationRef<UpsertCustomerData, UpsertCustomerVariables>;
  operationName: string;
}
export const upsertCustomerRef: UpsertCustomerRef;

export function upsertCustomer(vars: UpsertCustomerVariables): MutationPromise<UpsertCustomerData, UpsertCustomerVariables>;
export function upsertCustomer(dc: DataConnect, vars: UpsertCustomerVariables): MutationPromise<UpsertCustomerData, UpsertCustomerVariables>;

interface CreatePurchaseHeaderRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreatePurchaseHeaderVariables): MutationRef<CreatePurchaseHeaderData, CreatePurchaseHeaderVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreatePurchaseHeaderVariables): MutationRef<CreatePurchaseHeaderData, CreatePurchaseHeaderVariables>;
  operationName: string;
}
export const createPurchaseHeaderRef: CreatePurchaseHeaderRef;

export function createPurchaseHeader(vars: CreatePurchaseHeaderVariables): MutationPromise<CreatePurchaseHeaderData, CreatePurchaseHeaderVariables>;
export function createPurchaseHeader(dc: DataConnect, vars: CreatePurchaseHeaderVariables): MutationPromise<CreatePurchaseHeaderData, CreatePurchaseHeaderVariables>;

interface CreatePurchaseDetailRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreatePurchaseDetailVariables): MutationRef<CreatePurchaseDetailData, CreatePurchaseDetailVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreatePurchaseDetailVariables): MutationRef<CreatePurchaseDetailData, CreatePurchaseDetailVariables>;
  operationName: string;
}
export const createPurchaseDetailRef: CreatePurchaseDetailRef;

export function createPurchaseDetail(vars: CreatePurchaseDetailVariables): MutationPromise<CreatePurchaseDetailData, CreatePurchaseDetailVariables>;
export function createPurchaseDetail(dc: DataConnect, vars: CreatePurchaseDetailVariables): MutationPromise<CreatePurchaseDetailData, CreatePurchaseDetailVariables>;

interface CreateSaleHeaderRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateSaleHeaderVariables): MutationRef<CreateSaleHeaderData, CreateSaleHeaderVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateSaleHeaderVariables): MutationRef<CreateSaleHeaderData, CreateSaleHeaderVariables>;
  operationName: string;
}
export const createSaleHeaderRef: CreateSaleHeaderRef;

export function createSaleHeader(vars: CreateSaleHeaderVariables): MutationPromise<CreateSaleHeaderData, CreateSaleHeaderVariables>;
export function createSaleHeader(dc: DataConnect, vars: CreateSaleHeaderVariables): MutationPromise<CreateSaleHeaderData, CreateSaleHeaderVariables>;

interface CreateSaleDetailRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateSaleDetailVariables): MutationRef<CreateSaleDetailData, CreateSaleDetailVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateSaleDetailVariables): MutationRef<CreateSaleDetailData, CreateSaleDetailVariables>;
  operationName: string;
}
export const createSaleDetailRef: CreateSaleDetailRef;

export function createSaleDetail(vars: CreateSaleDetailVariables): MutationPromise<CreateSaleDetailData, CreateSaleDetailVariables>;
export function createSaleDetail(dc: DataConnect, vars: CreateSaleDetailVariables): MutationPromise<CreateSaleDetailData, CreateSaleDetailVariables>;

interface CreatePaymentRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreatePaymentVariables): MutationRef<CreatePaymentData, CreatePaymentVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreatePaymentVariables): MutationRef<CreatePaymentData, CreatePaymentVariables>;
  operationName: string;
}
export const createPaymentRef: CreatePaymentRef;

export function createPayment(vars: CreatePaymentVariables): MutationPromise<CreatePaymentData, CreatePaymentVariables>;
export function createPayment(dc: DataConnect, vars: CreatePaymentVariables): MutationPromise<CreatePaymentData, CreatePaymentVariables>;

interface CreateStockMovementRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateStockMovementVariables): MutationRef<CreateStockMovementData, CreateStockMovementVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateStockMovementVariables): MutationRef<CreateStockMovementData, CreateStockMovementVariables>;
  operationName: string;
}
export const createStockMovementRef: CreateStockMovementRef;

export function createStockMovement(vars: CreateStockMovementVariables): MutationPromise<CreateStockMovementData, CreateStockMovementVariables>;
export function createStockMovement(dc: DataConnect, vars: CreateStockMovementVariables): MutationPromise<CreateStockMovementData, CreateStockMovementVariables>;

interface CreateAuditLogRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: CreateAuditLogVariables): MutationRef<CreateAuditLogData, CreateAuditLogVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: CreateAuditLogVariables): MutationRef<CreateAuditLogData, CreateAuditLogVariables>;
  operationName: string;
}
export const createAuditLogRef: CreateAuditLogRef;

export function createAuditLog(vars: CreateAuditLogVariables): MutationPromise<CreateAuditLogData, CreateAuditLogVariables>;
export function createAuditLog(dc: DataConnect, vars: CreateAuditLogVariables): MutationPromise<CreateAuditLogData, CreateAuditLogVariables>;

interface ListStoresRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListStoresData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListStoresData, undefined>;
  operationName: string;
}
export const listStoresRef: ListStoresRef;

export function listStores(options?: ExecuteQueryOptions): QueryPromise<ListStoresData, undefined>;
export function listStores(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListStoresData, undefined>;

interface ListCategoriesRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListCategoriesData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListCategoriesData, undefined>;
  operationName: string;
}
export const listCategoriesRef: ListCategoriesRef;

export function listCategories(options?: ExecuteQueryOptions): QueryPromise<ListCategoriesData, undefined>;
export function listCategories(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListCategoriesData, undefined>;

interface ListProductsRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListProductsData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListProductsData, undefined>;
  operationName: string;
}
export const listProductsRef: ListProductsRef;

export function listProducts(options?: ExecuteQueryOptions): QueryPromise<ListProductsData, undefined>;
export function listProducts(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListProductsData, undefined>;

interface ListClothingSizesRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListClothingSizesData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListClothingSizesData, undefined>;
  operationName: string;
}
export const listClothingSizesRef: ListClothingSizesRef;

export function listClothingSizes(options?: ExecuteQueryOptions): QueryPromise<ListClothingSizesData, undefined>;
export function listClothingSizes(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListClothingSizesData, undefined>;

interface ListColorsRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListColorsData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListColorsData, undefined>;
  operationName: string;
}
export const listColorsRef: ListColorsRef;

export function listColors(options?: ExecuteQueryOptions): QueryPromise<ListColorsData, undefined>;
export function listColors(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListColorsData, undefined>;

interface GetProductVariantsRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<GetProductVariantsData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<GetProductVariantsData, undefined>;
  operationName: string;
}
export const getProductVariantsRef: GetProductVariantsRef;

export function getProductVariants(options?: ExecuteQueryOptions): QueryPromise<GetProductVariantsData, undefined>;
export function getProductVariants(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<GetProductVariantsData, undefined>;

interface ListSuppliersRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListSuppliersData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListSuppliersData, undefined>;
  operationName: string;
}
export const listSuppliersRef: ListSuppliersRef;

export function listSuppliers(options?: ExecuteQueryOptions): QueryPromise<ListSuppliersData, undefined>;
export function listSuppliers(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListSuppliersData, undefined>;

interface ListEmployeesRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListEmployeesData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListEmployeesData, undefined>;
  operationName: string;
}
export const listEmployeesRef: ListEmployeesRef;

export function listEmployees(options?: ExecuteQueryOptions): QueryPromise<ListEmployeesData, undefined>;
export function listEmployees(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListEmployeesData, undefined>;

interface ListCustomersRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListCustomersData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListCustomersData, undefined>;
  operationName: string;
}
export const listCustomersRef: ListCustomersRef;

export function listCustomers(options?: ExecuteQueryOptions): QueryPromise<ListCustomersData, undefined>;
export function listCustomers(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListCustomersData, undefined>;

interface SearchCustomerByPhoneRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars: SearchCustomerByPhoneVariables): QueryRef<SearchCustomerByPhoneData, SearchCustomerByPhoneVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars: SearchCustomerByPhoneVariables): QueryRef<SearchCustomerByPhoneData, SearchCustomerByPhoneVariables>;
  operationName: string;
}
export const searchCustomerByPhoneRef: SearchCustomerByPhoneRef;

export function searchCustomerByPhone(vars: SearchCustomerByPhoneVariables, options?: ExecuteQueryOptions): QueryPromise<SearchCustomerByPhoneData, SearchCustomerByPhoneVariables>;
export function searchCustomerByPhone(dc: DataConnect, vars: SearchCustomerByPhoneVariables, options?: ExecuteQueryOptions): QueryPromise<SearchCustomerByPhoneData, SearchCustomerByPhoneVariables>;

interface ListPurchaseHeadersRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListPurchaseHeadersData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListPurchaseHeadersData, undefined>;
  operationName: string;
}
export const listPurchaseHeadersRef: ListPurchaseHeadersRef;

export function listPurchaseHeaders(options?: ExecuteQueryOptions): QueryPromise<ListPurchaseHeadersData, undefined>;
export function listPurchaseHeaders(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListPurchaseHeadersData, undefined>;

interface ListPurchaseDetailsRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListPurchaseDetailsData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListPurchaseDetailsData, undefined>;
  operationName: string;
}
export const listPurchaseDetailsRef: ListPurchaseDetailsRef;

export function listPurchaseDetails(options?: ExecuteQueryOptions): QueryPromise<ListPurchaseDetailsData, undefined>;
export function listPurchaseDetails(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListPurchaseDetailsData, undefined>;

interface GetRecentSalesRef {
  /* Allow users to create refs without passing in DataConnect */
  (vars?: GetRecentSalesVariables): QueryRef<GetRecentSalesData, GetRecentSalesVariables>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect, vars?: GetRecentSalesVariables): QueryRef<GetRecentSalesData, GetRecentSalesVariables>;
  operationName: string;
}
export const getRecentSalesRef: GetRecentSalesRef;

export function getRecentSales(vars?: GetRecentSalesVariables, options?: ExecuteQueryOptions): QueryPromise<GetRecentSalesData, GetRecentSalesVariables>;
export function getRecentSales(dc: DataConnect, vars?: GetRecentSalesVariables, options?: ExecuteQueryOptions): QueryPromise<GetRecentSalesData, GetRecentSalesVariables>;

interface ListSaleDetailsRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListSaleDetailsData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListSaleDetailsData, undefined>;
  operationName: string;
}
export const listSaleDetailsRef: ListSaleDetailsRef;

export function listSaleDetails(options?: ExecuteQueryOptions): QueryPromise<ListSaleDetailsData, undefined>;
export function listSaleDetails(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListSaleDetailsData, undefined>;

interface ListPaymentsRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListPaymentsData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListPaymentsData, undefined>;
  operationName: string;
}
export const listPaymentsRef: ListPaymentsRef;

export function listPayments(options?: ExecuteQueryOptions): QueryPromise<ListPaymentsData, undefined>;
export function listPayments(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListPaymentsData, undefined>;

interface ListStockMovementsRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListStockMovementsData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListStockMovementsData, undefined>;
  operationName: string;
}
export const listStockMovementsRef: ListStockMovementsRef;

export function listStockMovements(options?: ExecuteQueryOptions): QueryPromise<ListStockMovementsData, undefined>;
export function listStockMovements(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListStockMovementsData, undefined>;

interface ListAuditLogsRef {
  /* Allow users to create refs without passing in DataConnect */
  (): QueryRef<ListAuditLogsData, undefined>;
  /* Allow users to pass in custom DataConnect instances */
  (dc: DataConnect): QueryRef<ListAuditLogsData, undefined>;
  operationName: string;
}
export const listAuditLogsRef: ListAuditLogsRef;

export function listAuditLogs(options?: ExecuteQueryOptions): QueryPromise<ListAuditLogsData, undefined>;
export function listAuditLogs(dc: DataConnect, options?: ExecuteQueryOptions): QueryPromise<ListAuditLogsData, undefined>;

