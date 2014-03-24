ALTER TABLE llx_company_contacts ADD COLUMN entity integer DEFAULT 1 NOT NULL AFTER tms;
ALTER TABLE llx_company_contacts ADD COLUMN fk_soc_source integer DEFAULT 0 NOT NULL AFTER entity;
