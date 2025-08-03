ALTER TABLE public.ac_users ADD use_auth_method int2 DEFAULT 0 NULL;
COMMENT ON COLUMN public.ac_users.use_auth_method IS '0 = plain text, 1 = OTP , digit sent by email, 2=freeOTP';
ALTER TABLE public.ac_users ADD use_otp_secret text  NULL;
COMMENT ON COLUMN public.ac_users.use_otp_secret IS 'string base32 generated for OTP';
ALTER TABLE public.ac_users ADD CONSTRAINT ac_users_unique UNIQUE (use_otp_secret);
update public.ac_users set use_auth_method=0;
ALTER TABLE public.ac_users ALTER COLUMN use_auth_method SET NOT NULL;



CREATE TABLE public.otp_send_secret (
	os_id int8 GENERATED ALWAYS AS IDENTITY( INCREMENT BY 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1 NO CYCLE) NOT NULL, -- PK
	os_timestamp timestamptz DEFAULT now() NOT NULL, -- Timestamp of the email sent
	os_request text  NULL, -- Unique identifier when request to scan qrcode
	use_id int4 NOT NULL, -- FK to ac_users
	os_code varchar(8) NULL,
	os_valid_time timestamp NOT NULL,
	CONSTRAINT otp_send_secret_pk PRIMARY KEY (os_id),
	CONSTRAINT otp_send_secret_unique UNIQUE (use_id)
);
COMMENT ON TABLE public.otp_send_secret IS 'sent to user for scanning a QRCODE for  FreeOTP
or digit to connect, depends of ac_users use_auth_method.';

-- Column comments

COMMENT ON COLUMN public.otp_send_secret.os_id IS 'PK';
COMMENT ON COLUMN public.otp_send_secret.os_timestamp IS 'Timestamp of the email sent';
COMMENT ON COLUMN public.otp_send_secret.os_request IS 'Unique identifier when request to scan qrcode';
COMMENT ON COLUMN public.otp_send_secret.os_code IS 'contains  code sent by email';
COMMENT ON COLUMN public.otp_send_secret.use_id IS 'FK to ac_users';