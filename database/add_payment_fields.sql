-- Script para agregar campos de pago a la tabla orders en una base de datos existente
-- Este script es seguro de ejecutar múltiples veces

USE adeshop;

-- Agregar columna payment_method si no existe
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'adeshop'
    AND TABLE_NAME = 'orders'
    AND COLUMN_NAME = 'payment_method'
);

SET @query = IF(@column_exists = 0,
    'ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) NULL COMMENT "Método de pago: credit_card, debit_card, paypal, bank_transfer, cash" AFTER status',
    'SELECT "Column payment_method already exists" AS message'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar columna payment_status si no existe
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'adeshop'
    AND TABLE_NAME = 'orders'
    AND COLUMN_NAME = 'payment_status'
);

SET @query = IF(@column_exists = 0,
    'ALTER TABLE orders ADD COLUMN payment_status VARCHAR(50) DEFAULT "pending" COMMENT "Estado del pago: pending, completed, failed" AFTER payment_method',
    'SELECT "Column payment_status already exists" AS message'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar columna payment_details si no existe
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'adeshop'
    AND TABLE_NAME = 'orders'
    AND COLUMN_NAME = 'payment_details'
);

SET @query = IF(@column_exists = 0,
    'ALTER TABLE orders ADD COLUMN payment_details TEXT NULL COMMENT "Detalles adicionales del pago en formato JSON" AFTER payment_status',
    'SELECT "Column payment_details already exists" AS message'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Campos de pago agregados correctamente a la tabla orders' AS resultado;
