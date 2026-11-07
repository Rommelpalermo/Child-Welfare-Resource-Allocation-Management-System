-- Adds archive support for existing databases
USE bahay_pagasa;
ALTER TABLE children  ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE resources ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE donations ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0;
