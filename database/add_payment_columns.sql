-- Script para agregar columnas de pago a la tabla orders
-- Ejecuta este script si tu tabla orders no tiene las columnas de método de pago

USE adeshop;

-- Agregar columna payment_method si no existe
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NULL 
COMMENT 'Método de pago: credit_card, debit_card, paypal, bank_transfer, cash';

-- Agregar columna payment_status si no existe
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS payment_status VARCHAR(50) DEFAULT 'pending' 
COMMENT 'Estado del pago: pending, completed, failed';

-- Agregar columna payment_details si no existe
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS payment_details TEXT NULL 
COMMENT 'Detalles adicionales del pago en formato JSON';

-- Verificar las columnas
DESCRIBE orders;
