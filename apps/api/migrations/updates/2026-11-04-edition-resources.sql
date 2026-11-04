-- Edition resources: any attachment of an edition, stored in S3 (SeaweedFS).
-- For databases created before this change (migrations/library.sql already
-- includes it). Apply once: make api-migrate f=migrations/updates/2026-11-04-edition-resources.sql
-- The old columns were never used by the code.
BEGIN;

CREATE TABLE IF NOT EXISTS public.edition_resources (
    id uuid NOT NULL,
    edition_id uuid NOT NULL,
    name character varying(255) NOT NULL,
    file_name character varying(255) NOT NULL,
    media_type character varying NOT NULL,
    size bigint NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT edition_resources_pkey PRIMARY KEY (id),
    CONSTRAINT edition_resources_foreign_editions FOREIGN KEY (edition_id) REFERENCES public.editions(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS edition_resources_edition_id_idx ON public.edition_resources USING btree (edition_id);

ALTER TABLE public.editions DROP COLUMN IF EXISTS resource;
ALTER TABLE public.editions DROP COLUMN IF EXISTS resource_type;

COMMIT;
