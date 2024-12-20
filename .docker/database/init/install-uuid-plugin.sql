CREATE OR REPLACE FUNCTION uuid_generate_v4 RETURNS string SONAME 'libudf_uuid.so';
CREATE OR REPLACE FUNCTION uuid_generate_v7 RETURNS string SONAME 'libudf_uuid.so';
CREATE OR REPLACE FUNCTION uuid_is_valid RETURNS integer SONAME 'libudf_uuid.so';
CREATE OR REPLACE FUNCTION uuid_to_bin RETURNS string SONAME 'libudf_uuid.so';
CREATE OR REPLACE FUNCTION uuid_from_bin RETURNS string SONAME 'libudf_uuid.so';
