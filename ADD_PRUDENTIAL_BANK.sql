-- Add Prudential Bank Payment Method to Database
-- Run this SQL in phpMyAdmin or MySQL command line

-- Add Prudential Bank to payment_methods table
INSERT INTO payment_methods (name, active, created_at, updated_at) 
VALUES ('prudential_bank', 0, NOW(), NOW());

-- Add Prudential Bank sandbox setting
INSERT INTO business_settings (type, value, created_at, updated_at) 
VALUES ('prudential_sandbox_mode', '0', NOW(), NOW());

-- Verify the entries were created
SELECT 'Payment Methods:' as Table_Name;
SELECT * FROM payment_methods WHERE name = 'prudential_bank';

SELECT 'Business Settings:' as Table_Name;
SELECT * FROM business_settings WHERE type = 'prudential_sandbox_mode';
