alter table regn_fond add accountline INTEGER(8) UNSIGNED;

insert into regn_happeningv2 set description='Dansekveld', linedesc='Dansekveld',debetpost=1904 ,kredpost=3980, count_req=0;

insert into regn_happeningv2 set description='Gebyrer bank', linedesc='Gebyrer bank', debetpost=1920 ,kredpost=8170 ,count_req = 0;

insert into regn_fond_action (description, fond, defaultdesc, actionclub, actionfond, debetpost, creditpost) 
  values ('Justering fond positivt','BSC','Justering fond positivt',0,1, null, null);

insert into regn_fond_action (description, fond, defaultdesc, actionclub, actionfond, debetpost, creditpost) 
  values ('Justering fond negativt','BSC','Justering fond negativt',0,-1, null, null);

