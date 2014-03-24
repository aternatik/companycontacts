-- <one line to give the program's name and a brief idea of what it does.>
-- Copyright (C) <year>  <name of author>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.
ALTER TABLE llx_company_contacts ADD INDEX idx_company_contacts_fk_soc (fk_soc);
ALTER TABLE llx_company_contacts ADD INDEX idx_company_contacts_fk_contact (fk_contact);

ALTER TABLE llx_company_contacts ADD CONSTRAINT fk_company_contacts_fk_soc    FOREIGN KEY (fk_soc)    REFERENCES llx_societe (rowid);
ALTER TABLE llx_company_contacts ADD CONSTRAINT fk_company_contacts_fk_user    FOREIGN KEY (fk_contact)    REFERENCES llx_socpeople (rowid);
