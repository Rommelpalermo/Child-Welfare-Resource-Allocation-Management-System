-- Adds archive support for existing Supabase databases. Run once in the SQL Editor.
alter table children  add column if not exists is_archived boolean not null default false;
alter table resources add column if not exists is_archived boolean not null default false;
alter table donations add column if not exists is_archived boolean not null default false;
