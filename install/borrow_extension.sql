-- Add extension tracking and queue position support for borrows
ALTER TABLE borrows ADD COLUMN extension_used TINYINT(1) NOT NULL DEFAULT 0 AFTER fine_amount;
ALTER TABLE borrows ADD INDEX idx_extension (extension_used);
