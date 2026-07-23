-- Manga chapter storage tables
CREATE TABLE IF NOT EXISTS chapters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    chapter_number VARCHAR(50) NOT NULL,
    title VARCHAR(255) DEFAULT NULL,
    external_id VARCHAR(255) DEFAULT NULL,
    status ENUM('draft','ready','error') DEFAULT 'draft',
    prefetch_progress INT DEFAULT 0,
    total_pages INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_book (book_id)
);

CREATE TABLE IF NOT EXISTS chapter_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id INT NOT NULL,
    page_number INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_chapter (chapter_id),
    INDEX idx_chapter_page (chapter_id, page_number)
);
