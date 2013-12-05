-- Allows insertion of simple teams after registration deadline.

START TRANSACTION;
insert into team(id_year, name, password, category, email, address)
values(
	1, -- should be fixed 1
	'NÁZEV',
	sha1('heslo'),
	'open', -- 'high_school','open','abroad','hs_a','hs_b','hs_c'
	'email@example.org',
	'no address'
);

insert into competitor(id_team, id_school, name)
values(
	last_insert_id(),
	null,
	'John Doe'
);

COMMIT;
