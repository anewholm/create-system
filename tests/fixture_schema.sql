-- Test fixture schema for acorn-create-system CI
-- Uses 'add-missing-columns: false' table comments so check() skips
-- interactive schema-fixup (designed for production schemas, not CI fixtures).
-- The view-building section of check() is handled by Table::find() returning
-- NULL for absent ecosystem tables (location, calendar).

CREATE TABLE acorn_test_categories (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);
COMMENT ON TABLE acorn_test_categories IS 'add-missing-columns: false';

CREATE TABLE acorn_test_events (
    id              SERIAL PRIMARY KEY,
    title           VARCHAR(255) NOT NULL,
    description     TEXT,
    category_id     INTEGER REFERENCES acorn_test_categories(id) ON DELETE SET NULL,
    start_at        TIMESTAMP NOT NULL,
    end_at          TIMESTAMP,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);
COMMENT ON TABLE acorn_test_events IS 'add-missing-columns: false';

CREATE TABLE acorn_test_attendees (
    id          SERIAL PRIMARY KEY,
    event_id    INTEGER NOT NULL REFERENCES acorn_test_events(id) ON DELETE CASCADE,
    user_id     INTEGER NOT NULL,
    status      VARCHAR(50) DEFAULT 'pending',
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP,
    UNIQUE(event_id, user_id)
);
COMMENT ON TABLE acorn_test_attendees IS 'add-missing-columns: false';
