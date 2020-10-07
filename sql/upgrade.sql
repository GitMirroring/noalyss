ALTER TABLE public.document_option ADD do_option varchar NULL;
COMMENT ON COLUMN public.document_option.do_option IS 'Option for the detail';
