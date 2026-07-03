SELECT 'CREATE DATABASE platform'
WHERE NOT EXISTS (
    SELECT FROM pg_database WHERE datname = 'platform'
)\gexec
