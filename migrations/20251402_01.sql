CREATE TABLE recipient (
    id SERIAL PRIMARY KEY,
    insee VARCHAR(5) NOT NULL,
    telephone VARCHAR(12) NOT NULL,
    UNIQUE (insee, telephone)
);