
CREATE INDEX Regn_Post_Type_coll_post ON regn_post_type(coll_post);
CREATE INDEX Regn_Post_Type_detail_post ON regn_post_type(detail_post);

create INDEX Regn_Post_line ON regn_post(line);
create INDEX Regn_Line_year ON regn_line(year);
create INDEX Regn_Line_year_month ON regn_line(year,month);

