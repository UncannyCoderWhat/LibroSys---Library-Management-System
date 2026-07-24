-- Add chapter context to reading progress for manga support
-- Run this if reading_progress table already exists
ALTER TABLE reading_progress ADD COLUMN chapter_id INT NULL DEFAULT NULL AFTER book_id;
ALTER TABLE reading_progress ADD INDEX idx_user_book_chapter (user_id, book_id, chapter_id);
