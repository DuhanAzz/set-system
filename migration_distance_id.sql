-- Jalankan script SQL ini di phpMyAdmin pada database hosting Anda (Production)
-- Script ini akan menambahkan distance_id, menyinkronkan data, dan menghapus race_distance

-- 1. Tambahkan kolom distance_id
ALTER TABLE roll_entries ADD COLUMN distance_id INT(11) NULL AFTER race_class_id;

-- 2. Sinkronkan (backfill) data dari race_distance teks lama ke ID yang benar
UPDATE roll_entries e
JOIN roll_ref_distances d ON e.race_distance = d.distance_name
SET e.distance_id = d.id;

-- 3. Hapus kolom race_distance yang lama agar database lebih ringan
ALTER TABLE roll_entries DROP COLUMN race_distance;
