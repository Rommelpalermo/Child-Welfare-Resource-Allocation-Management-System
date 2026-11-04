-- ============================================================
-- Bahay Pag-asa - Child Welfare & Resource Allocation System
-- Supabase (PostgreSQL) schema. Run in Supabase > SQL Editor.
-- ============================================================

create or replace function set_updated_at() returns trigger as $$
begin
  new.updated_at = now();
  return new;
end;
$$ language plpgsql;

create table users (
  id          bigint generated always as identity primary key,
  full_name   varchar(150) not null,
  username    varchar(80)  not null unique,
  password    varchar(255) not null,
  role        text not null default 'staff' check (role in ('admin','staff')),
  email       varchar(150),
  contact     varchar(30),
  status      text not null default 'active' check (status in ('active','inactive')),
  created_at  timestamptz default now(),
  updated_at  timestamptz default now()
);

-- Default accounts (password for both: "password")
insert into users (full_name, username, password, role) values
('System Administrator', 'admin', '$2y$10$/Aio6d/LJCahNwf.4MeYYO60qfOx/FKXOLCPSpJW.ZA0Kqf3aWk2C', 'admin'),
('Staff User',           'staff', '$2y$10$/Aio6d/LJCahNwf.4MeYYO60qfOx/FKXOLCPSpJW.ZA0Kqf3aWk2C', 'staff');

create table children (
  id               bigint generated always as identity primary key,
  case_number      varchar(50) unique,
  first_name       varchar(80) not null,
  last_name        varchar(80) not null,
  middle_name      varchar(80),
  birth_date       date,
  gender           text check (gender in ('Male','Female','Other')),
  address          text,
  guardian_name    varchar(150),
  guardian_contact varchar(30),
  admission_date   date,
  case_type        varchar(100),
  case_status      text default 'Active' check (case_status in ('Active','Closed','Referred','Reunified')),
  notes            text,
  photo            varchar(255),
  created_by       bigint references users(id) on delete set null,
  created_at       timestamptz default now(),
  updated_at       timestamptz default now()
);

create table case_status_history (
  id          bigint generated always as identity primary key,
  child_id    bigint not null references children(id) on delete cascade,
  old_status  varchar(50),
  new_status  varchar(50),
  remarks     text,
  changed_by  bigint references users(id) on delete set null,
  changed_at  timestamptz default now()
);

create table partners (
  id               bigint generated always as identity primary key,
  org_name         varchar(200) not null,
  contact_person   varchar(150),
  contact_email    varchar(150),
  contact_phone    varchar(30),
  address          text,
  partnership_type varchar(100),
  status           text default 'Active' check (status in ('Active','Inactive')),
  notes            text,
  created_at       timestamptz default now(),
  updated_at       timestamptz default now()
);

create table resources (
  id               bigint generated always as identity primary key,
  item_name        varchar(150) not null,
  category         varchar(100),
  description      text,
  quantity         integer not null default 0,
  unit             varchar(50),
  condition_status text default 'Good' check (condition_status in ('Good','Fair','Poor','For Disposal')),
  location         varchar(150),
  acquired_date    date,
  notes            text,
  created_at       timestamptz default now(),
  updated_at       timestamptz default now()
);

create table resource_allocations (
  id              bigint generated always as identity primary key,
  resource_id     bigint not null references resources(id) on delete cascade,
  child_id        bigint references children(id) on delete set null,
  allocated_to    varchar(150),
  quantity        integer not null default 1,
  allocated_by    bigint references users(id) on delete set null,
  allocation_date date,
  return_date     date,
  status          text default 'Allocated' check (status in ('Allocated','Returned','Lost')),
  notes           text,
  created_at      timestamptz default now()
);

create table donations (
  id                 bigint generated always as identity primary key,
  donor_name         varchar(200) not null,
  donor_type         text default 'Individual' check (donor_type in ('Individual','Organization','Government')),
  partner_id         bigint references partners(id) on delete set null,
  donation_type      text default 'Cash' check (donation_type in ('Cash','In-Kind','Food','Clothing','Medical','Other')),
  amount             numeric(12,2) default 0.00,
  items_desc         text,
  donation_date      date not null,
  received_by        bigint references users(id) on delete set null,
  status             text default 'Received' check (status in ('Received','Pending','Acknowledged')),
  acknowledgement_no varchar(100),
  notes              text,
  created_at         timestamptz default now(),
  updated_at         timestamptz default now()
);

create table donation_monitoring (
  id                bigint generated always as identity primary key,
  donation_id       bigint not null references donations(id) on delete cascade,
  child_id          bigint references children(id) on delete set null,
  distributed_to    varchar(200),
  quantity          integer default 1,
  amount            numeric(12,2) default 0.00,
  distributed_by    bigint references users(id) on delete set null,
  distribution_date date,
  remarks           text,
  created_at        timestamptz default now()
);

create table activity_logs (
  id          bigint generated always as identity primary key,
  user_id     bigint references users(id) on delete set null,
  action      varchar(255),
  module      varchar(100),
  ip_address  varchar(45),
  logged_at   timestamptz default now()
);

create trigger trg_users_updated     before update on users     for each row execute function set_updated_at();
create trigger trg_children_updated  before update on children  for each row execute function set_updated_at();
create trigger trg_partners_updated  before update on partners  for each row execute function set_updated_at();
create trigger trg_resources_updated before update on resources for each row execute function set_updated_at();
create trigger trg_donations_updated before update on donations for each row execute function set_updated_at();

-- The app talks to Supabase only from the server with the service-role key.
-- Enabling RLS with no policies blocks all access through the public anon key.
alter table users                enable row level security;
alter table children             enable row level security;
alter table case_status_history  enable row level security;
alter table partners             enable row level security;
alter table resources            enable row level security;
alter table resource_allocations enable row level security;
alter table donations            enable row level security;
alter table donation_monitoring  enable row level security;
alter table activity_logs        enable row level security;
