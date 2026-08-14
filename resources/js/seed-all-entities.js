import { dataConnect, db } from './firebase.js';
import { 
  createStore, 
  createCategory, 
  createProduct, 
  createClothingSize, 
  createColor, 
  createProductVariant, 
  createSupplier, 
  createEmployee, 
  createCustomer, 
  createPurchaseHeader, 
  createPurchaseDetail, 
  createSaleHeader, 
  createSaleDetail, 
  createPayment, 
  createStockMovement, 
  createAuditLog,
  listStores,
  listCategories,
  listProducts,
  listClothingSizes,
  listColors,
  getProductVariants,
  listSuppliers,
  listEmployees,
  listCustomers,
  listPurchaseHeaders,
  getRecentSales
} from './dataconnect/esm/index.esm.js';
import { doc, setDoc } from 'firebase/firestore';

async function seedAllEntities() {
  console.log('-------------------------------------------------------------');
  console.log('🚀 SEEDING SIMPLE EXAMPLE DATA FOR ALL 16 RELATIONAL ENTITIES');
  console.log('-------------------------------------------------------------');

  try {
    // 1. Store Entity
    console.log('\n[1/16] Seeding STORE entity...');
    let storesList = await listStores(dataConnect);
    let storeId;
    if (storesList.data.stores.length > 0) {
      storeId = storesList.data.stores[0].id;
      console.log('   ℹ️ STORE already exists:', storeId);
    } else {
      const storeRes = await createStore(dataConnect, {
        storeName: 'Phnom Penh Flagship Store',
        code: 'STORE-PP-001',
        phone: '+855 23 123 456',
        email: 'phnompenh@ssmis.com',
        address: 'Monivong Blvd, Phnom Penh, Cambodia'
      });
      storeId = storeRes.data.store_insert.id;
      console.log('   ✅ STORE created:', storeId);
    }

    // 2. Category Entity
    console.log('\n[2/16] Seeding CATEGORY entity...');
    let categoriesList = await listCategories(dataConnect);
    let categoryId;
    if (categoriesList.data.categories.length > 0) {
      categoryId = categoriesList.data.categories[0].id;
      console.log('   ℹ️ CATEGORY already exists:', categoryId);
    } else {
      const catRes = await createCategory(dataConnect, {
        categoryName: 'Apparel & T-Shirts',
        slug: 'apparel-tshirts',
        description: 'Men and Women Casual Fashion Apparel'
      });
      categoryId = catRes.data.category_insert.id;
      console.log('   ✅ CATEGORY created:', categoryId);
    }

    // 3. Product Entity
    console.log('\n[3/16] Seeding PRODUCT entity...');
    let productsList = await listProducts(dataConnect);
    let productId;
    if (productsList.data.products.length > 0) {
      productId = productsList.data.products[0].id;
      console.log('   ℹ️ PRODUCT already exists:', productId);
    } else {
      const prodRes = await createProduct(dataConnect, {
        productName: 'Classic Premium Polo Shirt',
        categoryId: categoryId,
        brand: 'SS Fashion',
        description: '100% Breathable Cotton Casual Polo Shirt'
      });
      productId = prodRes.data.product_insert.id;
      console.log('   ✅ PRODUCT created:', productId);
    }

    // 4. ClothingSize Entity
    console.log('\n[4/16] Seeding CLOTHING_SIZE entity...');
    let sizesList = await listClothingSizes(dataConnect);
    let sizeId;
    if (sizesList.data.clothingSizes.length > 0) {
      sizeId = sizesList.data.clothingSizes[0].id;
      console.log('   ℹ️ CLOTHING_SIZE already exists:', sizeId);
    } else {
      const sizeRes = await createClothingSize(dataConnect, {
        sizeCode: 'M',
        sizeName: 'Medium Size',
        description: 'Standard Adult Medium (M)'
      });
      sizeId = sizeRes.data.clothingSize_insert.id;
      console.log('   ✅ CLOTHING_SIZE created:', sizeId);
    }

    // 5. Color Entity
    console.log('\n[5/16] Seeding COLOR entity...');
    let colorsList = await listColors(dataConnect);
    let colorId;
    if (colorsList.data.colors.length > 0) {
      colorId = colorsList.data.colors[0].id;
      console.log('   ℹ️ COLOR already exists:', colorId);
    } else {
      const colorRes = await createColor(dataConnect, {
        colorCode: 'NAVY',
        colorName: 'Navy Blue',
        hexCode: '#000080'
      });
      colorId = colorRes.data.color_insert.id;
      console.log('   ✅ COLOR created:', colorId);
    }

    // 6. ProductVariant Entity
    console.log('\n[6/16] Seeding PRODUCT_VARIANT entity...');
    let variantsList = await getProductVariants(dataConnect);
    let variantId;
    if (variantsList.data.productVariants.length > 0) {
      variantId = variantsList.data.productVariants[0].id;
      console.log('   ℹ️ PRODUCT_VARIANT already exists:', variantId);
    } else {
      const variantRes = await createProductVariant(dataConnect, {
        productId: productId,
        sku: 'SKU-POLO-NAVY-M',
        costPrice: 8.50,
        salePrice: 15.00,
        stockQuantity: 100
      });
      variantId = variantRes.data.productVariant_insert.id;
      console.log('   ✅ PRODUCT_VARIANT created:', variantId);
    }

    // 7. Supplier Entity
    console.log('\n[7/16] Seeding SUPPLIER entity...');
    let suppliersList = await listSuppliers(dataConnect);
    let supplierId;
    if (suppliersList.data.suppliers.length > 0) {
      supplierId = suppliersList.data.suppliers[0].id;
      console.log('   ℹ️ SUPPLIER already exists:', supplierId);
    } else {
      const suppRes = await createSupplier(dataConnect, {
        supplierName: 'Khmer Garments Co., Ltd.',
        contactName: 'Sokha Heng',
        phone: '+855 12 345 678',
        email: 'orders@khmergarments.com'
      });
      supplierId = suppRes.data.supplier_insert.id;
      console.log('   ✅ SUPPLIER created:', supplierId);
    }

    // 8. Employee Entity
    console.log('\n[8/16] Seeding EMPLOYEE entity...');
    let employeesList = await listEmployees(dataConnect);
    let employeeId;
    if (employeesList.data.employees.length > 0) {
      employeeId = employeesList.data.employees[0].id;
      console.log('   ℹ️ EMPLOYEE already exists:', employeeId);
    } else {
      const empRes = await createEmployee(dataConnect, {
        storeId: storeId,
        employeeName: 'Vannak Chan',
        username: 'vannak_mgr',
        position: 'Store Manager'
      });
      employeeId = empRes.data.employee_insert.id;
      console.log('   ✅ EMPLOYEE created:', employeeId);
    }

    // 9. Customer Entity
    console.log('\n[9/16] Seeding CUSTOMER entity...');
    let customersList = await listCustomers(dataConnect);
    let customerId;
    if (customersList.data.customers.length > 0) {
      customerId = customersList.data.customers[0].id;
      console.log('   ℹ️ CUSTOMER already exists:', customerId);
    } else {
      const custRes = await createCustomer(dataConnect, {
        customerName: 'Bopha Chea',
        phone: '+855 98 765 432',
        email: 'bopha@example.com'
      });
      customerId = custRes.data.customer_insert.id;
      console.log('   ✅ CUSTOMER created:', customerId);
    }

    // 10. PurchaseHeader Entity
    console.log('\n[10/16] Seeding PURCHASE_HEADER entity...');
    let poList = await listPurchaseHeaders(dataConnect);
    let purchaseId;
    if (poList.data.purchaseHeaders.length > 0) {
      purchaseId = poList.data.purchaseHeaders[0].id;
      console.log('   ℹ️ PURCHASE_HEADER already exists:', purchaseId);
    } else {
      const poRes = await createPurchaseHeader(dataConnect, {
        referenceNo: 'PO-2026-0001',
        supplierId: supplierId,
        employeeId: employeeId,
        storeId: storeId,
        totalAmount: 850.00,
        grandTotal: 850.00
      });
      purchaseId = poRes.data.purchaseHeader_insert.id;
      console.log('   ✅ PURCHASE_HEADER created:', purchaseId);
    }

    // 11. PurchaseDetail Entity
    console.log('\n[11/16] Seeding PURCHASE_DETAIL entity...');
    const poDetailRes = await createPurchaseDetail(dataConnect, {
      purchaseId: purchaseId,
      variantId: variantId,
      quantity: 100,
      unitCost: 8.50,
      subTotal: 850.00
    });
    console.log('   ✅ PURCHASE_DETAIL inserted');

    // 12. SaleHeader Entity
    console.log('\n[12/16] Seeding SALE_HEADER entity...');
    let salesList = await getRecentSales(dataConnect, { limit: 1 });
    let saleId;
    if (salesList.data.saleHeaders.length > 0) {
      saleId = salesList.data.saleHeaders[0].id;
      console.log('   ℹ️ SALE_HEADER already exists:', saleId);
    } else {
      const saleRes = await createSaleHeader(dataConnect, {
        invoiceNo: 'INV-2026-1001',
        storeId: storeId,
        employeeId: employeeId,
        subTotal: 30.00,
        grandTotal: 30.00
      });
      saleId = saleRes.data.saleHeader_insert.id;
      console.log('   ✅ SALE_HEADER created:', saleId);
    }

    // 13. SaleDetail Entity
    console.log('\n[13/16] Seeding SALE_DETAIL entity...');
    await createSaleDetail(dataConnect, {
      saleId: saleId,
      variantId: variantId,
      quantity: 2,
      unitPrice: 15.00,
      subTotal: 30.00
    });
    console.log('   ✅ SALE_DETAIL inserted');

    // 14. Payment Entity
    console.log('\n[14/16] Seeding PAYMENT entity...');
    await createPayment(dataConnect, {
      saleId: saleId,
      amount: 30.00,
      amountTendered: 30.00,
      changeDue: 0.00,
      paymentMethod: 'ABA PAY / QR'
    });
    console.log('   ✅ PAYMENT inserted');

    // 15. StockMovement Entity
    console.log('\n[15/16] Seeding STOCK_MOVEMENT entity...');
    await createStockMovement(dataConnect, {
      storeId: storeId,
      variantId: variantId,
      movementType: 'PURCHASE_RECEIPT',
      quantity: 100,
      stockBefore: 0,
      stockAfter: 100
    });
    console.log('   ✅ STOCK_MOVEMENT inserted');

    // 16. AuditLog Entity
    console.log('\n[16/16] Seeding AUDIT_LOG entity...');
    await createAuditLog(dataConnect, {
      action: 'SEED_INITIAL_DATA',
      entityType: 'ALL_16_TABLES',
      entityId: storeId,
      userId: employeeId
    });
    console.log('   ✅ AUDIT_LOG inserted');

    // Sync user to Firestore as well
    await setDoc(doc(db, 'users', customerId), {
      uid: customerId,
      displayName: 'Bopha Chea',
      email: 'bopha@example.com',
      phone: '+855 98 765 432',
      role: 'Customer',
      loyaltyPoints: 150
    });
    console.log('\n✅ Firestore "users" collection document synced!');

    console.log('-------------------------------------------------------------');
    console.log('🎉 SEEDER EXECUTION COMPLETED (IDEMPOTENT & SAFE)!');
    console.log('-------------------------------------------------------------');

  } catch (err) {
    console.error('❌ SEEDING ERROR:', err.message);
  }
}

seedAllEntities();
