-- Prudential Bank Database Setup
-- Run this SQL script in phpMyAdmin or MySQL command line

-- Add Prudential Bank to payment methods
INSERT INTO payment_methods (name, active, created_at, updated_at) 
VALUES ('prudential_bank', 1, NOW(), NOW()) 
ON DUPLICATE KEY UPDATE active = 1;

-- Add Prudential Bank sandbox setting
INSERT INTO business_settings (type, value, created_at, updated_at) 
VALUES ('prudential_sandbox_mode', '0', NOW(), NOW()) 
ON DUPLICATE KEY UPDATE value = '0';

-- Add Prudential Bank API URL setting
INSERT INTO business_settings (type, value, created_at, updated_at) 
VALUES ('prudential_api_url', 'https://3dss2.quipu.de:8443/order', NOW(), NOW()) 
ON DUPLICATE KEY UPDATE value = 'https://3dss2.quipu.de:8443/order';

-- Verify the entries were created
SELECT 'Payment Methods:' as Table_Name;
SELECT * FROM payment_methods WHERE name = 'prudential_bank';

SELECT 'Business Settings:' as Table_Name;
SELECT * FROM business_settings WHERE type LIKE 'prudential%';

-- Success message
SELECT 'Prudential Bank setup completed successfully!' as Status;
