-- Test fixture schema for acorn-create-system CI
-- Exercises: FK detection, pivot tables, WinterCMS column conventions

CREATE TABLE acorn_test_categories (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP,
    deleted_at  TIMESTAMP,
    created_by  INTEGER,
    updated_by  INTEGER
);

CREATE TABLE acorn_test_events (
    id              SERIAL PRIMARY KEY,
    title           VARCHAR(255) NOT NULL,
    description     TEXT,
    category_id     INTEGER REFERENCES acorn_test_categories(id) ON DELETE SET NULL,
    start_at        TIMESTAMP NOT NULL,
    end_at          TIMESTAMP,
    is_public       BOOLEAN DEFAULT TRUE,
    max_attendees   INTEGER,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    deleted_at      TIMESTAMP,
    created_by      INTEGER,
    updated_by      INTEGER
);

CREATE TABLE acorn_test_attendees (
    id          SERIAL PRIMARY KEY,
    event_id    INTEGER NOT NULL REFERENCES acorn_test_events(id) ON DELETE CASCADE,
    user_id     INTEGER NOT NULL,
    status      VARCHAR(50) DEFAULT 'pending',
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP,
    UNIQUE(event_id, user_id)
);
