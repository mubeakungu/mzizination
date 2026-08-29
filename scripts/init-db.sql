-- Mzizination Database Initialization
-- Run on fresh PostgreSQL database

-- Create extensions if not exists
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "citext";

-- Create schemas
CREATE SCHEMA IF NOT EXISTS public;

-- Note: Laravel migrations will handle table creation
-- This file ensures the database is ready for Laravel

GRANT ALL PRIVILEGES ON SCHEMA public TO avnadmin;

-- Comment: Laravel artisan migrate will create all tables
-- based on files in database/migrations/ directory
