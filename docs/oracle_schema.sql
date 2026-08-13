-- ====================================================================
-- Oracle Database DDL Script for Clothing Sales Management System (SS-MIS)
-- Standard: Oracle Database 19c / 21c / 23c Compatible
-- ====================================================================

-- --------------------------------------------------------------------
-- 1. DROP TABLES (Reverse Dependency Order)
-- --------------------------------------------------------------------
BEGIN
    EXECUTE IMMEDIATE 'DROP TABLE audit_logs CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE stock_movements CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE payment CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE sale_detail CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE sale_header CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE purchase_detail CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE purchase_header CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE product_variant CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE product CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE customer CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE employee CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE supplier CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE color CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE clothing_size CASCADE CONSTRAINTS';
    EXECUTE IMMEDIATE 'DROP TABLE category CASCADE CONSTRAINTS';
EXCEPTION
    WHEN OTHERS THEN NULL;
END;
/

-- --------------------------------------------------------------------
-- 2. CREATE TABLES (Parent Tables First)
-- --------------------------------------------------------------------

-- CATEGORY
CREATE TABLE category (
    category_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_category PRIMARY KEY,
    category_name VARCHAR2(100) NOT NULL,
    description VARCHAR2(255),
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    CONSTRAINT uq_category_name UNIQUE (category_name)
);

-- CLOTHING_SIZE
CREATE TABLE clothing_size (
    size_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_clothing_size PRIMARY KEY,
    size_name VARCHAR2(50) NOT NULL,
    description VARCHAR2(255),
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    CONSTRAINT uq_size_name UNIQUE (size_name)
);

-- COLOR
CREATE TABLE color (
    color_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_color PRIMARY KEY,
    color_name VARCHAR2(50) NOT NULL,
    description VARCHAR2(255),
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    CONSTRAINT uq_color_name UNIQUE (color_name)
);

-- SUPPLIER
CREATE TABLE supplier (
    supplier_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_supplier PRIMARY KEY,
    supplier_name VARCHAR2(150) NOT NULL,
    phone VARCHAR2(20),
    email VARCHAR2(100),
    address VARCHAR2(255),
    status VARCHAR2(20) DEFAULT 'ACTIVE' NOT NULL,
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    deleted_at TIMESTAMP,
    CONSTRAINT ck_supplier_status CHECK (status IN ('ACTIVE', 'INACTIVE'))
);

-- EMPLOYEE (RBAC)
CREATE TABLE employee (
    employee_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_employee PRIMARY KEY,
    employee_name VARCHAR2(150) NOT NULL,
    gender VARCHAR2(10),
    phone VARCHAR2(20),
    email VARCHAR2(100) NOT NULL,
    position VARCHAR2(50) DEFAULT 'STAFF' NOT NULL,
    username VARCHAR2(50) NOT NULL,
    password_hash VARCHAR2(255) NOT NULL,
    role VARCHAR2(20) DEFAULT 'STAFF' NOT NULL,
    status VARCHAR2(20) DEFAULT 'ACTIVE' NOT NULL,
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    deleted_at TIMESTAMP,
    CONSTRAINT uq_employee_email UNIQUE (email),
    CONSTRAINT uq_employee_username UNIQUE (username),
    CONSTRAINT ck_employee_role CHECK (role IN ('ADMIN', 'MANAGER', 'CASHIER', 'STAFF')),
    CONSTRAINT ck_employee_status CHECK (status IN ('ACTIVE', 'INACTIVE'))
);

-- CUSTOMER
CREATE TABLE customer (
    customer_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_customer PRIMARY KEY,
    customer_name VARCHAR2(150) NOT NULL,
    gender VARCHAR2(10),
    phone VARCHAR2(20),
    email VARCHAR2(100),
    address VARCHAR2(255),
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    deleted_at TIMESTAMP
);

-- PRODUCT
CREATE TABLE product (
    product_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_product PRIMARY KEY,
    category_id NUMBER(10) NOT NULL,
    product_name VARCHAR2(150) NOT NULL,
    brand VARCHAR2(100),
    description VARCHAR2(255),
    status VARCHAR2(20) DEFAULT 'ACTIVE' NOT NULL,
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    deleted_at TIMESTAMP,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES category(category_id),
    CONSTRAINT ck_product_status CHECK (status IN ('ACTIVE', 'INACTIVE'))
);

-- PRODUCT_VARIANT
CREATE TABLE product_variant (
    variant_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_product_variant PRIMARY KEY,
    product_id NUMBER(10) NOT NULL,
    size_id NUMBER(10) NOT NULL,
    color_id NUMBER(10) NOT NULL,
    sku VARCHAR2(50) NOT NULL,
    barcode VARCHAR2(50),
    cost_price NUMBER(12,2) DEFAULT 0 NOT NULL,
    sale_price NUMBER(12,2) DEFAULT 0 NOT NULL,
    quantity NUMBER(10) DEFAULT 0 NOT NULL,
    reorder_level NUMBER(10) DEFAULT 5 NOT NULL,
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    deleted_at TIMESTAMP,
    CONSTRAINT fk_variant_product FOREIGN KEY (product_id) REFERENCES product(product_id),
    CONSTRAINT fk_variant_size FOREIGN KEY (size_id) REFERENCES clothing_size(size_id),
    CONSTRAINT fk_variant_color FOREIGN KEY (color_id) REFERENCES color(color_id),
    CONSTRAINT uq_variant_sku UNIQUE (sku),
    CONSTRAINT uq_variant_barcode UNIQUE (barcode),
    CONSTRAINT uq_product_size_color UNIQUE (product_id, size_id, color_id),
    CONSTRAINT ck_variant_cost CHECK (cost_price >= 0),
    CONSTRAINT ck_variant_sale CHECK (sale_price >= 0),
    CONSTRAINT ck_variant_quantity CHECK (quantity >= 0),
    CONSTRAINT ck_variant_reorder CHECK (reorder_level >= 0)
);

-- PURCHASE_HEADER
CREATE TABLE purchase_header (
    purchase_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_purchase_header PRIMARY KEY,
    supplier_id NUMBER(10) NOT NULL,
    employee_id NUMBER(10) NOT NULL,
    purchase_date TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    total_amount NUMBER(12,2) DEFAULT 0 NOT NULL,
    status VARCHAR2(20) DEFAULT 'COMPLETED' NOT NULL,
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    CONSTRAINT fk_purchase_supplier FOREIGN KEY (supplier_id) REFERENCES supplier(supplier_id),
    CONSTRAINT fk_purchase_employee FOREIGN KEY (employee_id) REFERENCES employee(employee_id),
    CONSTRAINT ck_purchase_status CHECK (status IN ('COMPLETED', 'CANCELLED'))
);

-- PURCHASE_DETAIL
CREATE TABLE purchase_detail (
    purchase_detail_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_purchase_detail PRIMARY KEY,
    purchase_id NUMBER(10) NOT NULL,
    variant_id NUMBER(10) NOT NULL,
    quantity NUMBER(10) NOT NULL,
    cost_price NUMBER(12,2) NOT NULL,
    sub_total NUMBER(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    CONSTRAINT fk_pdetail_header FOREIGN KEY (purchase_id) REFERENCES purchase_header(purchase_id),
    CONSTRAINT fk_pdetail_variant FOREIGN KEY (variant_id) REFERENCES product_variant(variant_id),
    CONSTRAINT ck_pdetail_quantity CHECK (quantity > 0)
);

-- SALE_HEADER
CREATE TABLE sale_header (
    sale_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_sale_header PRIMARY KEY,
    customer_id NUMBER(10),
    employee_id NUMBER(10) NOT NULL,
    sale_date TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    total_amount NUMBER(12,2) DEFAULT 0 NOT NULL,
    discount NUMBER(12,2) DEFAULT 0 NOT NULL,
    grand_total NUMBER(12,2) DEFAULT 0 NOT NULL,
    status VARCHAR2(20) DEFAULT 'COMPLETED' NOT NULL,
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    CONSTRAINT fk_sale_customer FOREIGN KEY (customer_id) REFERENCES customer(customer_id),
    CONSTRAINT fk_sale_employee FOREIGN KEY (employee_id) REFERENCES employee(employee_id),
    CONSTRAINT ck_sale_status CHECK (status IN ('COMPLETED', 'VOIDED', 'REFUNDED'))
);

-- SALE_DETAIL
CREATE TABLE sale_detail (
    sale_detail_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_sale_detail PRIMARY KEY,
    sale_id NUMBER(10) NOT NULL,
    variant_id NUMBER(10) NOT NULL,
    quantity NUMBER(10) NOT NULL,
    unit_price NUMBER(12,2) NOT NULL,
    discount NUMBER(12,2) DEFAULT 0 NOT NULL,
    sub_total NUMBER(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    CONSTRAINT fk_sdetail_header FOREIGN KEY (sale_id) REFERENCES sale_header(sale_id),
    CONSTRAINT fk_sdetail_variant FOREIGN KEY (variant_id) REFERENCES product_variant(variant_id),
    CONSTRAINT ck_sdetail_quantity CHECK (quantity > 0)
);

-- PAYMENT
CREATE TABLE payment (
    payment_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_payment PRIMARY KEY,
    sale_id NUMBER(10) NOT NULL,
    payment_date TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    amount NUMBER(12,2) NOT NULL,
    payment_method VARCHAR2(20) DEFAULT 'CASH' NOT NULL,
    payment_status VARCHAR2(20) DEFAULT 'PAID' NOT NULL,
    reference_number VARCHAR2(100),
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    CONSTRAINT fk_payment_sale FOREIGN KEY (sale_id) REFERENCES sale_header(sale_id),
    CONSTRAINT ck_payment_method CHECK (payment_method IN ('CASH', 'CARD', 'QR', 'ABA')),
    CONSTRAINT ck_payment_status CHECK (payment_status IN ('PAID', 'PENDING', 'REFUNDED'))
);

-- STOCK_MOVEMENT
CREATE TABLE stock_movement (
    movement_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_stock_movement PRIMARY KEY,
    variant_id NUMBER(10) NOT NULL,
    movement_type VARCHAR2(30) NOT NULL,
    quantity NUMBER(10) NOT NULL,
    movement_date TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    reference_type VARCHAR2(50),
    reference_id NUMBER(10),
    note VARCHAR2(255),
    employee_id NUMBER(10),
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    CONSTRAINT fk_smovement_variant FOREIGN KEY (variant_id) REFERENCES product_variant(variant_id),
    CONSTRAINT fk_smovement_employee FOREIGN KEY (employee_id) REFERENCES employee(employee_id),
    CONSTRAINT ck_smovement_type CHECK (movement_type IN ('PURCHASE', 'SALE', 'RETURN_IN', 'RETURN_OUT', 'ADJUSTMENT'))
);

-- AUDIT_LOGS
CREATE TABLE audit_logs (
    audit_id NUMBER(10) GENERATED ALWAYS AS IDENTITY CONSTRAINT pk_audit_logs PRIMARY KEY,
    user_id NUMBER(10),
    user_type VARCHAR2(100),
    action VARCHAR2(50) NOT NULL,
    entity VARCHAR2(100) NOT NULL,
    entity_id VARCHAR2(100),
    ip_address VARCHAR2(45),
    user_agent VARCHAR2(255),
    old_values CLOB,
    new_values CLOB,
    created_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT SYSTIMESTAMP NOT NULL
);

-- --------------------------------------------------------------------
-- 3. INDEXES FOR PERFORMANCE
-- --------------------------------------------------------------------
CREATE INDEX idx_product_cat ON product(category_id);
CREATE INDEX idx_variant_prod ON product_variant(product_id);
CREATE INDEX idx_variant_size ON product_variant(size_id);
CREATE INDEX idx_variant_color ON product_variant(color_id);
CREATE INDEX idx_pdetail_header ON purchase_detail(purchase_id);
CREATE INDEX idx_sdetail_header ON sale_detail(sale_id);
CREATE INDEX idx_payment_sale ON payment(sale_id);
CREATE INDEX idx_smovement_var ON stock_movement(variant_id);
