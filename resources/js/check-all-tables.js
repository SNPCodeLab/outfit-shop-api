import { dataConnect, db } from './firebase.js';
import { 
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
  listPurchaseDetails,
  getRecentSales,
  listSaleDetails,
  listPayments,
  listStockMovements,
  listAuditLogs
} from './dataconnect/esm/index.esm.js';
import { collection, getDocs } from 'firebase/firestore';

async function checkAllDataTables() {
  console.log('================================================================');
  console.log('📊 DATABASE TABLE INSPECTOR — ALL 16 ENTITIES + FIRESTORE');
  console.log('================================================================\n');

  try {
    // 1. STORE
    const storesRes = await listStores(dataConnect);
    console.log(`1. STORE Table (${storesRes.data.stores.length} records):`);
    console.table(storesRes.data.stores);

    // 2. CATEGORY
    const catRes = await listCategories(dataConnect);
    console.log(`\n2. CATEGORY Table (${catRes.data.categories.length} records):`);
    console.table(catRes.data.categories);

    // 3. PRODUCT
    const prodRes = await listProducts(dataConnect);
    console.log(`\n3. PRODUCT Table (${prodRes.data.products.length} records):`);
    console.table(prodRes.data.products.map(p => ({
      id: p.id,
      productName: p.productName,
      brand: p.brand,
      status: p.status,
      category: p.category?.categoryName
    })));

    // 4. CLOTHING_SIZE
    const sizeRes = await listClothingSizes(dataConnect);
    console.log(`\n4. CLOTHING_SIZE Table (${sizeRes.data.clothingSizes.length} records):`);
    console.table(sizeRes.data.clothingSizes);

    // 5. COLOR
    const colorRes = await listColors(dataConnect);
    console.log(`\n5. COLOR Table (${colorRes.data.colors.length} records):`);
    console.table(colorRes.data.colors);

    // 6. PRODUCT_VARIANT
    const variantRes = await getProductVariants(dataConnect);
    console.log(`\n6. PRODUCT_VARIANT Table (${variantRes.data.productVariants.length} records):`);
    console.table(variantRes.data.productVariants.map(v => ({
      id: v.id,
      sku: v.sku,
      costPrice: v.costPrice,
      salePrice: v.salePrice,
      stockQuantity: v.stockQuantity,
      product: v.product?.productName,
      size: v.size?.sizeCode,
      color: v.color?.colorName
    })));

    // 7. SUPPLIER
    const suppRes = await listSuppliers(dataConnect);
    console.log(`\n7. SUPPLIER Table (${suppRes.data.suppliers.length} records):`);
    console.table(suppRes.data.suppliers);

    // 8. EMPLOYEE
    const empRes = await listEmployees(dataConnect);
    console.log(`\n8. EMPLOYEE Table (${empRes.data.employees.length} records):`);
    console.table(empRes.data.employees.map(e => ({
      id: e.id,
      employeeName: e.employeeName,
      username: e.username,
      position: e.position,
      store: e.store?.storeName
    })));

    // 9. CUSTOMER
    const custRes = await listCustomers(dataConnect);
    console.log(`\n9. CUSTOMER Table (${custRes.data.customers.length} records):`);
    console.table(custRes.data.customers);

    // 10. PURCHASE_HEADER
    const poRes = await listPurchaseHeaders(dataConnect);
    console.log(`\n10. PURCHASE_HEADER Table (${poRes.data.purchaseHeaders.length} records):`);
    console.table(poRes.data.purchaseHeaders.map(po => ({
      id: po.id,
      referenceNo: po.referenceNo,
      grandTotal: po.grandTotal,
      status: po.status,
      supplier: po.supplier?.supplierName,
      employee: po.employee?.employeeName
    })));

    // 11. PURCHASE_DETAIL
    const poDetailRes = await listPurchaseDetails(dataConnect);
    console.log(`\n11. PURCHASE_DETAIL Table (${poDetailRes.data.purchaseDetails.length} records):`);
    console.table(poDetailRes.data.purchaseDetails.map(pd => ({
      id: pd.id,
      quantity: pd.quantity,
      unitCost: pd.unitCost,
      subTotal: pd.subTotal,
      purchase: pd.purchase?.referenceNo,
      variant: pd.variant?.sku
    })));

    // 12. SALE_HEADER
    const salesRes = await getRecentSales(dataConnect, { limit: 50 });
    console.log(`\n12. SALE_HEADER Table (${salesRes.data.saleHeaders.length} records):`);
    console.table(salesRes.data.saleHeaders.map(s => ({
      id: s.id,
      invoiceNo: s.invoiceNo,
      grandTotal: s.grandTotal,
      paymentStatus: s.paymentStatus,
      customer: s.customer?.customerName,
      employee: s.employee?.employeeName
    })));

    // 13. SALE_DETAIL
    const saleDetailRes = await listSaleDetails(dataConnect);
    console.log(`\n13. SALE_DETAIL Table (${saleDetailRes.data.saleDetails.length} records):`);
    console.table(saleDetailRes.data.saleDetails.map(sd => ({
      id: sd.id,
      quantity: sd.quantity,
      unitPrice: sd.unitPrice,
      subTotal: sd.subTotal,
      sale: sd.sale?.invoiceNo,
      variant: sd.variant?.sku
    })));

    // 14. PAYMENT
    const payRes = await listPayments(dataConnect);
    console.log(`\n14. PAYMENT Table (${payRes.data.payments.length} records):`);
    console.table(payRes.data.payments.map(p => ({
      id: p.id,
      amount: p.amount,
      paymentMethod: p.paymentMethod,
      paymentStatus: p.paymentStatus,
      sale: p.sale?.invoiceNo
    })));

    // 15. STOCK_MOVEMENT
    const stockRes = await listStockMovements(dataConnect);
    console.log(`\n15. STOCK_MOVEMENT Table (${stockRes.data.stockMovements.length} records):`);
    console.table(stockRes.data.stockMovements.map(sm => ({
      id: sm.id,
      movementType: sm.movementType,
      quantity: sm.quantity,
      stockBefore: sm.stockBefore,
      stockAfter: sm.stockAfter,
      store: sm.store?.storeName,
      variant: sm.variant?.sku
    })));

    // 16. AUDIT_LOG
    const auditRes = await listAuditLogs(dataConnect);
    console.log(`\n16. AUDIT_LOG Table (${auditRes.data.auditLogs.length} records):`);
    console.table(auditRes.data.auditLogs);

    // FIRESTORE
    console.log('\n----------------------------------------------------------------');
    console.log('🔥 CLOUD FIRESTORE COLLECTIONS');
    console.log('----------------------------------------------------------------');
    const userSnap = await getDocs(collection(db, 'users'));
    console.log(`Firestore "users" Collection (${userSnap.size} documents):`);
    userSnap.forEach(d => console.log('  Doc:', d.id, d.data()));

    console.log('\n================================================================');
    console.log('✅ ALL TABLES INSPECTED SUCCESSFULLY!');
    console.log('================================================================');

  } catch (err) {
    console.error('❌ CHECK TABLES ERROR:', err.message);
  }
}

checkAllDataTables();
