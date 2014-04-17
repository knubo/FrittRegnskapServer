CREATE INDEX XXX_Post_Type_coll_post on XXX_post_type(coll_post);
CREATE INDEX XXX_Post_Type_detail_post on XXX_post_type(detail_post);

create INDEX XXX_Post_line on XXX_post(line);

create INDEX XXX_Line_year on XXX_line(year);
create INDEX XXX_Line_year_month on XXX_line(year,month);

create INDEX XXX_Post_debet on XXX_post(debet);

create INDEX XXX_Person_RequiredYear on XXX_person(year_membership_required);
create INDEX XXX_Person_RequiredSemester on XXX_person(semester_membership_required);

create INDEX XXX_Invoice_Receivers on XXX_invoice_recipient (invoice_id);