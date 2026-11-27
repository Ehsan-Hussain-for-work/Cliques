ALTER TABLE messages
  ADD COLUMN original_body TEXT NULL,
  ADD COLUMN censored_body TEXT NULL,
  ADD COLUMN moderation_status VARCHAR(20) NOT NULL DEFAULT 'bypassed';
