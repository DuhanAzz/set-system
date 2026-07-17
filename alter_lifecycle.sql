ALTER TABLE roll_entries MODIFY COLUMN status ENUM('Unpaid', 'Pending', 'Paid', 'Seeded', 'Qualified', 'Finished', 'Eliminated') DEFAULT 'Unpaid';
ALTER TABLE roll_event_details ADD COLUMN result_status ENUM('Draft', 'Published') DEFAULT 'Draft';
