USE online_store_db;

INSERT INTO users (name, email, password_hash, role)
VALUES ('System Admin', 'admin@novacart.test', '$2y$10$wKlp/6wfYKoL4F2QqWYLse56TYRXSmmWzPt9F3/rPcWrk8YATDxwu', 'admin')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password_hash = VALUES(password_hash),
    role = VALUES(role);
