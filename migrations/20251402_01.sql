CREATE TABLE recipient (
    id SERIAL PRIMARY KEY,
    insee VARCHAR(15) NOT NULL,
    telephone VARCHAR(10) NOT NULL,
    UNIQUE (insee, telephone)
);