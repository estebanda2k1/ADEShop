-- Script para probar el Setup Wizard
-- Este script elimina todos los administradores para que el setup wizard se active

-- Ver administradores actuales
SELECT * FROM users WHERE is_admin = 1;

-- Eliminar todos los administradores (CUIDADO: esto activará el setup wizard)
-- Descomenta la siguiente línea para ejecutar
-- DELETE FROM users WHERE is_admin = 1;

-- Verificar que no hay administradores
-- SELECT COUNT(*) as admin_count FROM users WHERE is_admin = 1;

-- Después de ejecutar este script, visita cualquier página de ADESHOP
-- Serás redirigido automáticamente a setup.php
