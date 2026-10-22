create table
	if not exists users (
		id integer primary key,
		`role` text not null default 'User',
		`status` text not null default 'New',
		username text unique not null,
		passhash blob not null,
		display_name text not null,
		created_at integer not null,
		created_by integer not null,
		updated_at integer,
		updated_by integer
	);

create table
	if not exists tickets (
		id text primary key,
		summary text not null,
		details text,
		`state` text not null default 'New',
		reason text,
		urgency integer not null default 3,
		assignee_id integer,
		category_id integer not null,
		location_id integer not null,
		created_at integer not null,
		created_by integer not null,
		updated_at integer,
		updated_by integer
	);

create table
	if not exists ticket_notes (
		id integer primary key,
		ticket_id text not null,
		content text not null,
		created_by integer not null,
		created_at integer not null,
		foreign key (ticket_id) references tickets (id) on delete cascade,
		foreign key (created_by) references users (id) on delete cascade
	);

create table
	if not exists categories (
		id integer primary key,
		`name` text unique not null,
		created_at integer not null,
		created_by integer not null,
		updated_at integer,
		updated_by integer
	);

create table
	if not exists locations (
		id integer primary key,
		`name` text unique not null,
		created_at integer not null,
		created_by integer not null,
		updated_at integer,
		updated_by integer
	);

create table
	if not exists notification_subs (
		id integer primary key,
		endpoint text not null,
		p256dh_key text not null,
		auth_key text not null,
		created_by integer not null,
		created_at integer not null,
		updated_by integer,
		updated_at integer,
		foreign key (created_by) references users (id) on delete cascade,
		foreign key (updated_by) references users (id) on delete cascade
	);

drop view if exists v_users;

create view
	if not exists v_users as
select
	u.id,
	u.role,
	u.status,
	u.username,
	u.display_name,
	u.created_at,
	u.created_by,
	u.updated_at,
	u.updated_by,
	(
		case
			when u.created_by = 0 then '[system]'
			else cu.display_name
		end
	) as created_by_display_name,
	(
		case
			when u.updated_by = 0 then '[system]'
			else uu.display_name
		end
	) as updated_by_display_name
from
	users u
	left join users cu on u.created_by = cu.id
	left join users uu on u.updated_by = uu.id;

drop view if exists v_tickets;

create view
	if not exists v_tickets as
select
	t.*,
	case
		when t.created_by = 0 then '[system]'
		else cu.display_name
	end as created_by_display_name,
	case
		when t.updated_by = 0 then '[system]'
		else uu.display_name
	end as updated_by_display_name,
	au.display_name as assignee_display_name,
	l.name as location_name,
	c.name as category_name
from
	tickets t
	join users cu on t.created_by = cu.id
	left join users uu on t.updated_by = uu.id
	left join users au on t.assignee_id = au.id
	left join locations l on t.location_id = l.id
	left join categories c on t.category_id = c.id;

drop view if exists v_ticket_notes;

create view
	if not exists v_ticket_notes as
select
	r.*,
	u.display_name as created_by_display_name
from
	ticket_notes r
	join users u on r.created_by = u.id;