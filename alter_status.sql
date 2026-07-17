ALTER TABLE roll_entries MODIFY COLUMN status ENUM('Unpaid', 'Pending', 'Paid', 'Seeded', 'Qualified', 'Finished', 'Eliminated') DEFAULT 'Unpaid';
