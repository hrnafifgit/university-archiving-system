--
-- PostgreSQL database dump
--

\restrict 6iJHcQmtan6rrCECg9Z27SaETQKNyxad2DqxDRMQrDmJxRT3pSbKBGlJaDNnon5

-- Dumped from database version 18.7
-- Dumped by pg_dump version 18.7

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;


--
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: academic_ranks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.academic_ranks (
    academic_rank_id uuid DEFAULT gen_random_uuid() NOT NULL,
    rank_code character varying(50) NOT NULL,
    rank_name_ar character varying(120) NOT NULL,
    rank_name_en character varying(120),
    rank_order smallint
);


--
-- Name: app_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.app_permissions (
    permission_id uuid DEFAULT gen_random_uuid() NOT NULL,
    permission_code character varying(120) NOT NULL,
    description text
);


--
-- Name: app_roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.app_roles (
    role_id uuid DEFAULT gen_random_uuid() NOT NULL,
    role_name character varying(100) NOT NULL,
    description text
);


--
-- Name: app_users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.app_users (
    user_id uuid DEFAULT gen_random_uuid() NOT NULL,
    employee_id uuid,
    username character varying(100) NOT NULL,
    password_hash text NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    last_login_at timestamp(0) with time zone,
    created_at timestamp(0) with time zone DEFAULT now() NOT NULL
);


--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.audit_logs (
    audit_log_id bigint NOT NULL,
    user_id uuid,
    action_code character varying(80) NOT NULL,
    entity_name character varying(120) NOT NULL,
    entity_id uuid,
    occurred_at timestamp(0) with time zone DEFAULT now() NOT NULL,
    ip_address inet,
    old_values jsonb,
    new_values jsonb
);


--
-- Name: audit_logs_audit_log_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.audit_logs_audit_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: audit_logs_audit_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.audit_logs_audit_log_id_seq OWNED BY public.audit_logs.audit_log_id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


--
-- Name: countries; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.countries (
    country_id bigint NOT NULL,
    country_name_ar character varying(150) NOT NULL,
    country_name_en character varying(150),
    iso_code character(2)
);


--
-- Name: countries_country_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.countries_country_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: countries_country_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.countries_country_id_seq OWNED BY public.countries.country_id;


--
-- Name: data_sources; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.data_sources (
    data_source_id uuid DEFAULT gen_random_uuid() NOT NULL,
    source_name character varying(150) NOT NULL,
    description text,
    created_at timestamp(0) with time zone DEFAULT now() NOT NULL
);


--
-- Name: decision_documents; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.decision_documents (
    decision_id uuid NOT NULL,
    document_id uuid NOT NULL
);


--
-- Name: decision_statuses; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.decision_statuses (
    decision_status_id smallint NOT NULL,
    status_code character varying(40) NOT NULL,
    status_name_ar character varying(100) NOT NULL,
    status_name_en character varying(100)
);


--
-- Name: decision_statuses_decision_status_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.decision_statuses_decision_status_id_seq
    AS smallint
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: decision_statuses_decision_status_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.decision_statuses_decision_status_id_seq OWNED BY public.decision_statuses.decision_status_id;


--
-- Name: decision_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.decision_types (
    decision_type_id smallint NOT NULL,
    type_code character varying(50) NOT NULL,
    type_name_ar character varying(150) NOT NULL,
    type_name_en character varying(150),
    requires_end_date boolean DEFAULT false NOT NULL
);


--
-- Name: decision_types_decision_type_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.decision_types_decision_type_id_seq
    AS smallint
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: decision_types_decision_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.decision_types_decision_type_id_seq OWNED BY public.decision_types.decision_type_id;


--
-- Name: document_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.document_types (
    document_type_id uuid DEFAULT gen_random_uuid() NOT NULL,
    type_code character varying(60) NOT NULL,
    type_name_ar character varying(160) NOT NULL,
    type_name_en character varying(160),
    applies_to character varying(30) DEFAULT 'employee'::character varying NOT NULL,
    is_required_by_default boolean DEFAULT false NOT NULL,
    is_active boolean DEFAULT true NOT NULL
);


--
-- Name: document_versions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.document_versions (
    document_version_id uuid DEFAULT gen_random_uuid() NOT NULL,
    document_id uuid NOT NULL,
    version_number integer NOT NULL,
    storage_key text NOT NULL,
    original_file_name character varying(255) NOT NULL,
    mime_type character varying(120),
    file_size_bytes bigint,
    checksum_sha256 character(64),
    uploaded_at timestamp(0) with time zone DEFAULT now() NOT NULL,
    is_current boolean DEFAULT false NOT NULL
);


--
-- Name: documents; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.documents (
    document_id uuid DEFAULT gen_random_uuid() NOT NULL,
    employee_id uuid NOT NULL,
    document_type_id uuid NOT NULL,
    document_title character varying(250) NOT NULL,
    document_number character varying(100),
    document_date date,
    issuing_authority character varying(200),
    description text,
    is_confidential boolean DEFAULT true NOT NULL,
    retention_until date,
    uploaded_by_user_id uuid,
    created_at timestamp(0) with time zone DEFAULT now() NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT now() NOT NULL
);


--
-- Name: employee_absences; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.employee_absences (
    absence_id uuid DEFAULT gen_random_uuid() NOT NULL,
    employee_id uuid NOT NULL,
    absence_type character varying(40) NOT NULL,
    start_date date NOT NULL,
    end_date date,
    destination_or_host character varying(250),
    status character varying(30) DEFAULT 'active'::character varying NOT NULL,
    decision_id uuid,
    notes text
);


--
-- Name: employee_categories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.employee_categories (
    employee_category_id smallint NOT NULL,
    category_code character varying(40) NOT NULL,
    category_name_ar character varying(120) NOT NULL,
    category_name_en character varying(120),
    description text
);


--
-- Name: employee_categories_employee_category_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.employee_categories_employee_category_id_seq
    AS smallint
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: employee_categories_employee_category_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.employee_categories_employee_category_id_seq OWNED BY public.employee_categories.employee_category_id;


--
-- Name: employee_decisions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.employee_decisions (
    decision_id uuid DEFAULT gen_random_uuid() NOT NULL,
    employee_id uuid NOT NULL,
    decision_type_id smallint NOT NULL,
    decision_number character varying(100),
    issuing_authority character varying(200),
    issue_date date,
    effective_start_date date,
    effective_end_date date,
    decision_status_id smallint,
    summary text,
    notes text,
    data_source_id uuid,
    created_at timestamp(0) with time zone DEFAULT now() NOT NULL
);


--
-- Name: employee_qualifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.employee_qualifications (
    qualification_id uuid DEFAULT gen_random_uuid() NOT NULL,
    employee_id uuid NOT NULL,
    qualification_level_id smallint NOT NULL,
    specialization_id uuid,
    university_id uuid,
    country_id bigint,
    degree_title character varying(200),
    grade_or_gpa character varying(100),
    graduation_date date,
    award_date date,
    is_highest_qualification boolean DEFAULT false NOT NULL,
    verification_status character varying(30) DEFAULT 'unknown'::character varying NOT NULL,
    verification_date date,
    data_source_id uuid,
    notes text,
    created_at timestamp(0) with time zone DEFAULT now() NOT NULL
);


--
-- Name: employee_source_snapshots; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.employee_source_snapshots (
    employee_source_snapshot_id uuid DEFAULT gen_random_uuid() NOT NULL,
    employee_id uuid NOT NULL,
    data_source_id uuid NOT NULL,
    snapshot_as_of_date date NOT NULL,
    reported_employment_status character varying(150),
    reported_job_title character varying(180),
    reported_job_grade character varying(120),
    reported_organizational_unit character varying(220),
    reported_first_appointment_date date,
    reported_academic_appointment_date date,
    reported_qualification_at_appointment character varying(180),
    raw_source_data jsonb,
    imported_at timestamp(0) with time zone DEFAULT now() NOT NULL,
    notes text
);


--
-- Name: employees; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.employees (
    employee_id uuid DEFAULT gen_random_uuid() NOT NULL,
    employee_number character varying(50),
    national_id character varying(50),
    full_name_ar character varying(250) NOT NULL,
    full_name_en character varying(250),
    gender character varying(20),
    date_of_birth date,
    place_of_birth character varying(180),
    phone_number character varying(40),
    email character varying(254),
    address text,
    photo_document_id uuid,
    notes text,
    is_active_record boolean DEFAULT true NOT NULL,
    created_at timestamp(0) with time zone DEFAULT now() NOT NULL,
    updated_at timestamp(0) with time zone DEFAULT now() NOT NULL,
    created_from_source_id uuid
);


--
-- Name: employment_documents; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.employment_documents (
    employment_record_id uuid NOT NULL,
    document_id uuid NOT NULL
);


--
-- Name: employment_records; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.employment_records (
    employment_record_id uuid DEFAULT gen_random_uuid() NOT NULL,
    employee_id uuid NOT NULL,
    employee_category_id smallint NOT NULL,
    organizational_unit_id uuid,
    job_title_id uuid,
    job_grade_id uuid,
    academic_rank_id uuid,
    employment_status_id smallint,
    start_date date NOT NULL,
    end_date date,
    is_primary boolean DEFAULT true NOT NULL,
    appointment_basis character varying(100),
    decision_id uuid,
    data_source_id uuid,
    notes text,
    created_at timestamp(0) with time zone DEFAULT now() NOT NULL,
    payroll_status character varying(100),
    payroll_job_title character varying(150),
    payroll_job_grade character varying(100),
    qualification_at_appointment character varying(150),
    first_appointment_date date,
    academic_appointment_date date,
    initial_rank character varying(100)
);


--
-- Name: COLUMN employment_records.payroll_status; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.employment_records.payroll_status IS 'الحالة بحسب كشف الراتب';


--
-- Name: COLUMN employment_records.payroll_job_title; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.employment_records.payroll_job_title IS 'الوظيفة الحالية بكشف الراتب';


--
-- Name: COLUMN employment_records.payroll_job_grade; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.employment_records.payroll_job_grade IS 'الدرجة بحسب كشف الراتب';


--
-- Name: COLUMN employment_records.qualification_at_appointment; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.employment_records.qualification_at_appointment IS 'المؤهل عند التعيين بالجامعة أكاديمي';


--
-- Name: COLUMN employment_records.first_appointment_date; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.employment_records.first_appointment_date IS 'تاريخ التعيين بالوظيفة لأول مرة';


--
-- Name: COLUMN employment_records.academic_appointment_date; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.employment_records.academic_appointment_date IS 'تاريخ التعيين بالجامعة أكاديمي';


--
-- Name: COLUMN employment_records.initial_rank; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.employment_records.initial_rank IS 'الدرجة المعين فيها أول مرة';


--
-- Name: employment_statuses; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.employment_statuses (
    employment_status_id smallint NOT NULL,
    status_code character varying(40) NOT NULL,
    status_name_ar character varying(120) NOT NULL,
    status_name_en character varying(120),
    is_active_status boolean DEFAULT true NOT NULL
);


--
-- Name: employment_statuses_employment_status_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.employment_statuses_employment_status_id_seq
    AS smallint
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: employment_statuses_employment_status_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.employment_statuses_employment_status_id_seq OWNED BY public.employment_statuses.employment_status_id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection character varying(255) NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: import_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.import_batches (
    import_batch_id uuid DEFAULT gen_random_uuid() NOT NULL,
    source_file_name character varying(255) NOT NULL,
    data_source_id uuid,
    imported_by_user_id uuid,
    imported_at timestamp(0) with time zone DEFAULT now() NOT NULL,
    total_rows integer DEFAULT 0 NOT NULL,
    successful_rows integer DEFAULT 0 NOT NULL,
    rejected_rows integer DEFAULT 0 NOT NULL,
    notes text
);


--
-- Name: import_row_errors; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.import_row_errors (
    import_row_error_id bigint NOT NULL,
    import_batch_id uuid NOT NULL,
    source_row_number integer NOT NULL,
    row_data jsonb,
    error_details text NOT NULL
);


--
-- Name: import_row_errors_import_row_error_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.import_row_errors_import_row_error_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: import_row_errors_import_row_error_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.import_row_errors_import_row_error_id_seq OWNED BY public.import_row_errors.import_row_error_id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: job_grades; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_grades (
    job_grade_id uuid DEFAULT gen_random_uuid() NOT NULL,
    grade_code character varying(50) NOT NULL,
    grade_name_ar character varying(120) NOT NULL,
    grade_name_en character varying(120),
    description text
);


--
-- Name: job_titles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_titles (
    job_title_id uuid DEFAULT gen_random_uuid() NOT NULL,
    title_name_ar character varying(180) NOT NULL,
    title_name_en character varying(180),
    employee_category_id smallint,
    is_active boolean DEFAULT true NOT NULL
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: organizational_units; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.organizational_units (
    organizational_unit_id uuid DEFAULT gen_random_uuid() NOT NULL,
    parent_unit_id uuid,
    unit_code character varying(50),
    unit_name_ar character varying(200) NOT NULL,
    unit_name_en character varying(200),
    unit_type character varying(30) NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) with time zone DEFAULT now() NOT NULL,
    CONSTRAINT chk_parent_not_self CHECK (((parent_unit_id IS NULL) OR (parent_unit_id <> organizational_unit_id)))
);


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: qualification_documents; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.qualification_documents (
    qualification_id uuid NOT NULL,
    document_id uuid NOT NULL
);


--
-- Name: qualification_levels; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.qualification_levels (
    qualification_level_id smallint NOT NULL,
    level_code character varying(40) NOT NULL,
    level_name_ar character varying(120) NOT NULL,
    level_name_en character varying(120),
    level_order smallint
);


--
-- Name: qualification_levels_qualification_level_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.qualification_levels_qualification_level_id_seq
    AS smallint
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: qualification_levels_qualification_level_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.qualification_levels_qualification_level_id_seq OWNED BY public.qualification_levels.qualification_level_id;


--
-- Name: role_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_permissions (
    role_id uuid NOT NULL,
    permission_id uuid NOT NULL
);


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: specializations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.specializations (
    specialization_id uuid DEFAULT gen_random_uuid() NOT NULL,
    parent_specialization_id uuid,
    specialization_name_ar character varying(200) NOT NULL,
    specialization_name_en character varying(200),
    specialization_type character varying(20) NOT NULL
);


--
-- Name: universities; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.universities (
    university_id uuid DEFAULT gen_random_uuid() NOT NULL,
    university_name_ar character varying(220) NOT NULL,
    university_name_en character varying(220),
    country_id bigint
);


--
-- Name: user_roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_roles (
    user_id uuid NOT NULL,
    role_id uuid NOT NULL
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: v_employee_current_primary_employment; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW public.v_employee_current_primary_employment AS
 SELECT e.employee_id,
    e.employee_number,
    e.full_name_ar,
    er.employment_record_id,
    er.start_date,
    er.organizational_unit_id,
    ou.unit_name_ar,
    ec.category_name_ar,
    jt.title_name_ar,
    jg.grade_name_ar,
    ar.rank_name_ar,
    es.status_name_ar
   FROM (((((((public.employees e
     LEFT JOIN public.employment_records er ON (((er.employee_id = e.employee_id) AND (er.is_primary = true) AND (er.end_date IS NULL))))
     LEFT JOIN public.organizational_units ou ON ((ou.organizational_unit_id = er.organizational_unit_id)))
     LEFT JOIN public.employee_categories ec ON ((ec.employee_category_id = er.employee_category_id)))
     LEFT JOIN public.job_titles jt ON ((jt.job_title_id = er.job_title_id)))
     LEFT JOIN public.job_grades jg ON ((jg.job_grade_id = er.job_grade_id)))
     LEFT JOIN public.academic_ranks ar ON ((ar.academic_rank_id = er.academic_rank_id)))
     LEFT JOIN public.employment_statuses es ON ((es.employment_status_id = er.employment_status_id)));


--
-- Name: audit_logs audit_log_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN audit_log_id SET DEFAULT nextval('public.audit_logs_audit_log_id_seq'::regclass);


--
-- Name: countries country_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.countries ALTER COLUMN country_id SET DEFAULT nextval('public.countries_country_id_seq'::regclass);


--
-- Name: decision_statuses decision_status_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.decision_statuses ALTER COLUMN decision_status_id SET DEFAULT nextval('public.decision_statuses_decision_status_id_seq'::regclass);


--
-- Name: decision_types decision_type_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.decision_types ALTER COLUMN decision_type_id SET DEFAULT nextval('public.decision_types_decision_type_id_seq'::regclass);


--
-- Name: employee_categories employee_category_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_categories ALTER COLUMN employee_category_id SET DEFAULT nextval('public.employee_categories_employee_category_id_seq'::regclass);


--
-- Name: employment_statuses employment_status_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_statuses ALTER COLUMN employment_status_id SET DEFAULT nextval('public.employment_statuses_employment_status_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: import_row_errors import_row_error_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_row_errors ALTER COLUMN import_row_error_id SET DEFAULT nextval('public.import_row_errors_import_row_error_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: qualification_levels qualification_level_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.qualification_levels ALTER COLUMN qualification_level_id SET DEFAULT nextval('public.qualification_levels_qualification_level_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: academic_ranks academic_ranks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_ranks
    ADD CONSTRAINT academic_ranks_pkey PRIMARY KEY (academic_rank_id);


--
-- Name: academic_ranks academic_ranks_rank_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_ranks
    ADD CONSTRAINT academic_ranks_rank_code_unique UNIQUE (rank_code);


--
-- Name: academic_ranks academic_ranks_rank_name_ar_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.academic_ranks
    ADD CONSTRAINT academic_ranks_rank_name_ar_unique UNIQUE (rank_name_ar);


--
-- Name: app_permissions app_permissions_permission_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.app_permissions
    ADD CONSTRAINT app_permissions_permission_code_unique UNIQUE (permission_code);


--
-- Name: app_permissions app_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.app_permissions
    ADD CONSTRAINT app_permissions_pkey PRIMARY KEY (permission_id);


--
-- Name: app_roles app_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.app_roles
    ADD CONSTRAINT app_roles_pkey PRIMARY KEY (role_id);


--
-- Name: app_roles app_roles_role_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.app_roles
    ADD CONSTRAINT app_roles_role_name_unique UNIQUE (role_name);


--
-- Name: app_users app_users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.app_users
    ADD CONSTRAINT app_users_pkey PRIMARY KEY (user_id);


--
-- Name: app_users app_users_username_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.app_users
    ADD CONSTRAINT app_users_username_unique UNIQUE (username);


--
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (audit_log_id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: countries countries_iso_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.countries
    ADD CONSTRAINT countries_iso_code_unique UNIQUE (iso_code);


--
-- Name: countries countries_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.countries
    ADD CONSTRAINT countries_pkey PRIMARY KEY (country_id);


--
-- Name: data_sources data_sources_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.data_sources
    ADD CONSTRAINT data_sources_pkey PRIMARY KEY (data_source_id);


--
-- Name: data_sources data_sources_source_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.data_sources
    ADD CONSTRAINT data_sources_source_name_unique UNIQUE (source_name);


--
-- Name: decision_documents decision_documents_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.decision_documents
    ADD CONSTRAINT decision_documents_pkey PRIMARY KEY (decision_id, document_id);


--
-- Name: decision_statuses decision_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.decision_statuses
    ADD CONSTRAINT decision_statuses_pkey PRIMARY KEY (decision_status_id);


--
-- Name: decision_statuses decision_statuses_status_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.decision_statuses
    ADD CONSTRAINT decision_statuses_status_code_unique UNIQUE (status_code);


--
-- Name: decision_statuses decision_statuses_status_name_ar_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.decision_statuses
    ADD CONSTRAINT decision_statuses_status_name_ar_unique UNIQUE (status_name_ar);


--
-- Name: decision_types decision_types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.decision_types
    ADD CONSTRAINT decision_types_pkey PRIMARY KEY (decision_type_id);


--
-- Name: decision_types decision_types_type_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.decision_types
    ADD CONSTRAINT decision_types_type_code_unique UNIQUE (type_code);


--
-- Name: decision_types decision_types_type_name_ar_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.decision_types
    ADD CONSTRAINT decision_types_type_name_ar_unique UNIQUE (type_name_ar);


--
-- Name: document_types document_types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_types
    ADD CONSTRAINT document_types_pkey PRIMARY KEY (document_type_id);


--
-- Name: document_types document_types_type_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_types
    ADD CONSTRAINT document_types_type_code_unique UNIQUE (type_code);


--
-- Name: document_types document_types_type_name_ar_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_types
    ADD CONSTRAINT document_types_type_name_ar_unique UNIQUE (type_name_ar);


--
-- Name: document_versions document_versions_document_id_version_number_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_versions
    ADD CONSTRAINT document_versions_document_id_version_number_unique UNIQUE (document_id, version_number);


--
-- Name: document_versions document_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_versions
    ADD CONSTRAINT document_versions_pkey PRIMARY KEY (document_version_id);


--
-- Name: document_versions document_versions_storage_key_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_versions
    ADD CONSTRAINT document_versions_storage_key_unique UNIQUE (storage_key);


--
-- Name: documents documents_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_pkey PRIMARY KEY (document_id);


--
-- Name: employee_absences employee_absences_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_absences
    ADD CONSTRAINT employee_absences_pkey PRIMARY KEY (absence_id);


--
-- Name: employee_categories employee_categories_category_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_categories
    ADD CONSTRAINT employee_categories_category_code_unique UNIQUE (category_code);


--
-- Name: employee_categories employee_categories_category_name_ar_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_categories
    ADD CONSTRAINT employee_categories_category_name_ar_unique UNIQUE (category_name_ar);


--
-- Name: employee_categories employee_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_categories
    ADD CONSTRAINT employee_categories_pkey PRIMARY KEY (employee_category_id);


--
-- Name: employee_decisions employee_decisions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_decisions
    ADD CONSTRAINT employee_decisions_pkey PRIMARY KEY (decision_id);


--
-- Name: employee_qualifications employee_qualifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_qualifications
    ADD CONSTRAINT employee_qualifications_pkey PRIMARY KEY (qualification_id);


--
-- Name: employee_source_snapshots employee_source_snapshots_employee_id_data_source_id_snapshot_a; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_source_snapshots
    ADD CONSTRAINT employee_source_snapshots_employee_id_data_source_id_snapshot_a UNIQUE (employee_id, data_source_id, snapshot_as_of_date);


--
-- Name: employee_source_snapshots employee_source_snapshots_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_source_snapshots
    ADD CONSTRAINT employee_source_snapshots_pkey PRIMARY KEY (employee_source_snapshot_id);


--
-- Name: employees employees_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employees
    ADD CONSTRAINT employees_pkey PRIMARY KEY (employee_id);


--
-- Name: employment_documents employment_documents_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_documents
    ADD CONSTRAINT employment_documents_pkey PRIMARY KEY (employment_record_id, document_id);


--
-- Name: employment_records employment_records_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_records
    ADD CONSTRAINT employment_records_pkey PRIMARY KEY (employment_record_id);


--
-- Name: employment_statuses employment_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_statuses
    ADD CONSTRAINT employment_statuses_pkey PRIMARY KEY (employment_status_id);


--
-- Name: employment_statuses employment_statuses_status_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_statuses
    ADD CONSTRAINT employment_statuses_status_code_unique UNIQUE (status_code);


--
-- Name: employment_statuses employment_statuses_status_name_ar_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_statuses
    ADD CONSTRAINT employment_statuses_status_name_ar_unique UNIQUE (status_name_ar);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: import_batches import_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_batches
    ADD CONSTRAINT import_batches_pkey PRIMARY KEY (import_batch_id);


--
-- Name: import_row_errors import_row_errors_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_row_errors
    ADD CONSTRAINT import_row_errors_pkey PRIMARY KEY (import_row_error_id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: job_grades job_grades_grade_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_grades
    ADD CONSTRAINT job_grades_grade_code_unique UNIQUE (grade_code);


--
-- Name: job_grades job_grades_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_grades
    ADD CONSTRAINT job_grades_pkey PRIMARY KEY (job_grade_id);


--
-- Name: job_titles job_titles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_titles
    ADD CONSTRAINT job_titles_pkey PRIMARY KEY (job_title_id);


--
-- Name: job_titles job_titles_title_name_ar_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_titles
    ADD CONSTRAINT job_titles_title_name_ar_unique UNIQUE (title_name_ar);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: organizational_units organizational_units_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizational_units
    ADD CONSTRAINT organizational_units_pkey PRIMARY KEY (organizational_unit_id);


--
-- Name: organizational_units organizational_units_unit_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizational_units
    ADD CONSTRAINT organizational_units_unit_code_unique UNIQUE (unit_code);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: qualification_documents qualification_documents_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.qualification_documents
    ADD CONSTRAINT qualification_documents_pkey PRIMARY KEY (qualification_id, document_id);


--
-- Name: qualification_levels qualification_levels_level_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.qualification_levels
    ADD CONSTRAINT qualification_levels_level_code_unique UNIQUE (level_code);


--
-- Name: qualification_levels qualification_levels_level_name_ar_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.qualification_levels
    ADD CONSTRAINT qualification_levels_level_name_ar_unique UNIQUE (level_name_ar);


--
-- Name: qualification_levels qualification_levels_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.qualification_levels
    ADD CONSTRAINT qualification_levels_pkey PRIMARY KEY (qualification_level_id);


--
-- Name: role_permissions role_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_pkey PRIMARY KEY (role_id, permission_id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: specializations specializations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.specializations
    ADD CONSTRAINT specializations_pkey PRIMARY KEY (specialization_id);


--
-- Name: specializations specializations_specialization_name_ar_specialization_type_uniq; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.specializations
    ADD CONSTRAINT specializations_specialization_name_ar_specialization_type_uniq UNIQUE (specialization_name_ar, specialization_type);


--
-- Name: universities universities_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.universities
    ADD CONSTRAINT universities_pkey PRIMARY KEY (university_id);


--
-- Name: universities universities_university_name_ar_country_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.universities
    ADD CONSTRAINT universities_university_name_ar_country_id_unique UNIQUE (university_name_ar, country_id);


--
-- Name: user_roles user_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_pkey PRIMARY KEY (user_id, role_id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: failed_jobs_connection_queue_failed_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX failed_jobs_connection_queue_failed_at_index ON public.failed_jobs USING btree (connection, queue, failed_at);


--
-- Name: ix_absences_employee_dates; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_absences_employee_dates ON public.employee_absences USING btree (employee_id, start_date DESC);


--
-- Name: ix_audit_logs_date; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_audit_logs_date ON public.audit_logs USING btree (occurred_at DESC);


--
-- Name: ix_audit_logs_entity; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_audit_logs_entity ON public.audit_logs USING btree (entity_name, entity_id);


--
-- Name: ix_decisions_employee; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_decisions_employee ON public.employee_decisions USING btree (employee_id);


--
-- Name: ix_decisions_number; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_decisions_number ON public.employee_decisions USING btree (decision_number);


--
-- Name: ix_documents_employee; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_documents_employee ON public.documents USING btree (employee_id);


--
-- Name: ix_documents_type; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_documents_type ON public.documents USING btree (document_type_id);


--
-- Name: ix_employee_source_snapshots_employee_date; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_employee_source_snapshots_employee_date ON public.employee_source_snapshots USING btree (employee_id, snapshot_as_of_date DESC);


--
-- Name: ix_employees_name_ar; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_employees_name_ar ON public.employees USING btree (full_name_ar);


--
-- Name: ix_employees_phone; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_employees_phone ON public.employees USING btree (phone_number);


--
-- Name: ix_employment_employee_dates; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_employment_employee_dates ON public.employment_records USING btree (employee_id, start_date DESC);


--
-- Name: ix_employment_unit; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_employment_unit ON public.employment_records USING btree (organizational_unit_id);


--
-- Name: ix_qualifications_employee; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_qualifications_employee ON public.employee_qualifications USING btree (employee_id);


--
-- Name: ix_qualifications_level; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ix_qualifications_level ON public.employee_qualifications USING btree (qualification_level_id);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: uq_document_one_current_version; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_document_one_current_version ON public.document_versions USING btree (document_id) WHERE (is_current = true);


--
-- Name: uq_employees_employee_number; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_employees_employee_number ON public.employees USING btree (employee_number) WHERE ((employee_number IS NOT NULL) AND (btrim((employee_number)::text) <> ''::text));


--
-- Name: uq_employees_national_id; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_employees_national_id ON public.employees USING btree (national_id) WHERE ((national_id IS NOT NULL) AND (btrim((national_id)::text) <> ''::text));


--
-- Name: uq_one_current_primary_employment; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uq_one_current_primary_employment ON public.employment_records USING btree (employee_id) WHERE ((is_primary = true) AND (end_date IS NULL));


--
-- Name: app_users app_users_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.app_users
    ADD CONSTRAINT app_users_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(employee_id) ON DELETE SET NULL;


--
-- Name: audit_logs audit_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.app_users(user_id) ON DELETE SET NULL;


--
-- Name: decision_documents decision_documents_decision_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.decision_documents
    ADD CONSTRAINT decision_documents_decision_id_foreign FOREIGN KEY (decision_id) REFERENCES public.employee_decisions(decision_id) ON DELETE CASCADE;


--
-- Name: decision_documents decision_documents_document_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.decision_documents
    ADD CONSTRAINT decision_documents_document_id_foreign FOREIGN KEY (document_id) REFERENCES public.documents(document_id) ON DELETE RESTRICT;


--
-- Name: document_versions document_versions_document_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_versions
    ADD CONSTRAINT document_versions_document_id_foreign FOREIGN KEY (document_id) REFERENCES public.documents(document_id) ON DELETE RESTRICT;


--
-- Name: documents documents_document_type_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_document_type_id_foreign FOREIGN KEY (document_type_id) REFERENCES public.document_types(document_type_id);


--
-- Name: documents documents_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(employee_id) ON DELETE RESTRICT;


--
-- Name: employee_absences employee_absences_decision_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_absences
    ADD CONSTRAINT employee_absences_decision_id_foreign FOREIGN KEY (decision_id) REFERENCES public.employee_decisions(decision_id) ON DELETE SET NULL;


--
-- Name: employee_absences employee_absences_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_absences
    ADD CONSTRAINT employee_absences_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(employee_id) ON DELETE RESTRICT;


--
-- Name: employee_decisions employee_decisions_data_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_decisions
    ADD CONSTRAINT employee_decisions_data_source_id_foreign FOREIGN KEY (data_source_id) REFERENCES public.data_sources(data_source_id) ON DELETE SET NULL;


--
-- Name: employee_decisions employee_decisions_decision_status_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_decisions
    ADD CONSTRAINT employee_decisions_decision_status_id_foreign FOREIGN KEY (decision_status_id) REFERENCES public.decision_statuses(decision_status_id) ON DELETE SET NULL;


--
-- Name: employee_decisions employee_decisions_decision_type_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_decisions
    ADD CONSTRAINT employee_decisions_decision_type_id_foreign FOREIGN KEY (decision_type_id) REFERENCES public.decision_types(decision_type_id);


--
-- Name: employee_decisions employee_decisions_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_decisions
    ADD CONSTRAINT employee_decisions_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(employee_id) ON DELETE RESTRICT;


--
-- Name: employee_qualifications employee_qualifications_country_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_qualifications
    ADD CONSTRAINT employee_qualifications_country_id_foreign FOREIGN KEY (country_id) REFERENCES public.countries(country_id) ON DELETE SET NULL;


--
-- Name: employee_qualifications employee_qualifications_data_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_qualifications
    ADD CONSTRAINT employee_qualifications_data_source_id_foreign FOREIGN KEY (data_source_id) REFERENCES public.data_sources(data_source_id) ON DELETE SET NULL;


--
-- Name: employee_qualifications employee_qualifications_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_qualifications
    ADD CONSTRAINT employee_qualifications_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(employee_id) ON DELETE RESTRICT;


--
-- Name: employee_qualifications employee_qualifications_qualification_level_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_qualifications
    ADD CONSTRAINT employee_qualifications_qualification_level_id_foreign FOREIGN KEY (qualification_level_id) REFERENCES public.qualification_levels(qualification_level_id);


--
-- Name: employee_qualifications employee_qualifications_specialization_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_qualifications
    ADD CONSTRAINT employee_qualifications_specialization_id_foreign FOREIGN KEY (specialization_id) REFERENCES public.specializations(specialization_id) ON DELETE SET NULL;


--
-- Name: employee_qualifications employee_qualifications_university_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_qualifications
    ADD CONSTRAINT employee_qualifications_university_id_foreign FOREIGN KEY (university_id) REFERENCES public.universities(university_id) ON DELETE SET NULL;


--
-- Name: employee_source_snapshots employee_source_snapshots_data_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_source_snapshots
    ADD CONSTRAINT employee_source_snapshots_data_source_id_foreign FOREIGN KEY (data_source_id) REFERENCES public.data_sources(data_source_id) ON DELETE RESTRICT;


--
-- Name: employee_source_snapshots employee_source_snapshots_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employee_source_snapshots
    ADD CONSTRAINT employee_source_snapshots_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(employee_id) ON DELETE RESTRICT;


--
-- Name: employees employees_created_from_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employees
    ADD CONSTRAINT employees_created_from_source_id_foreign FOREIGN KEY (created_from_source_id) REFERENCES public.data_sources(data_source_id) ON DELETE SET NULL;


--
-- Name: employees employees_photo_document_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employees
    ADD CONSTRAINT employees_photo_document_id_foreign FOREIGN KEY (photo_document_id) REFERENCES public.documents(document_id) ON DELETE SET NULL;


--
-- Name: employment_documents employment_documents_document_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_documents
    ADD CONSTRAINT employment_documents_document_id_foreign FOREIGN KEY (document_id) REFERENCES public.documents(document_id) ON DELETE RESTRICT;


--
-- Name: employment_documents employment_documents_employment_record_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_documents
    ADD CONSTRAINT employment_documents_employment_record_id_foreign FOREIGN KEY (employment_record_id) REFERENCES public.employment_records(employment_record_id) ON DELETE CASCADE;


--
-- Name: employment_records employment_records_academic_rank_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_records
    ADD CONSTRAINT employment_records_academic_rank_id_foreign FOREIGN KEY (academic_rank_id) REFERENCES public.academic_ranks(academic_rank_id) ON DELETE SET NULL;


--
-- Name: employment_records employment_records_data_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_records
    ADD CONSTRAINT employment_records_data_source_id_foreign FOREIGN KEY (data_source_id) REFERENCES public.data_sources(data_source_id) ON DELETE SET NULL;


--
-- Name: employment_records employment_records_decision_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_records
    ADD CONSTRAINT employment_records_decision_id_foreign FOREIGN KEY (decision_id) REFERENCES public.employee_decisions(decision_id) ON DELETE SET NULL;


--
-- Name: employment_records employment_records_employee_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_records
    ADD CONSTRAINT employment_records_employee_category_id_foreign FOREIGN KEY (employee_category_id) REFERENCES public.employee_categories(employee_category_id);


--
-- Name: employment_records employment_records_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_records
    ADD CONSTRAINT employment_records_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(employee_id) ON DELETE RESTRICT;


--
-- Name: employment_records employment_records_employment_status_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_records
    ADD CONSTRAINT employment_records_employment_status_id_foreign FOREIGN KEY (employment_status_id) REFERENCES public.employment_statuses(employment_status_id) ON DELETE SET NULL;


--
-- Name: employment_records employment_records_job_grade_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_records
    ADD CONSTRAINT employment_records_job_grade_id_foreign FOREIGN KEY (job_grade_id) REFERENCES public.job_grades(job_grade_id) ON DELETE SET NULL;


--
-- Name: employment_records employment_records_job_title_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_records
    ADD CONSTRAINT employment_records_job_title_id_foreign FOREIGN KEY (job_title_id) REFERENCES public.job_titles(job_title_id) ON DELETE SET NULL;


--
-- Name: employment_records employment_records_organizational_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.employment_records
    ADD CONSTRAINT employment_records_organizational_unit_id_foreign FOREIGN KEY (organizational_unit_id) REFERENCES public.organizational_units(organizational_unit_id) ON DELETE SET NULL;


--
-- Name: specializations fk_parent_specialization; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.specializations
    ADD CONSTRAINT fk_parent_specialization FOREIGN KEY (parent_specialization_id) REFERENCES public.specializations(specialization_id) ON DELETE SET NULL;


--
-- Name: organizational_units fk_parent_unit; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizational_units
    ADD CONSTRAINT fk_parent_unit FOREIGN KEY (parent_unit_id) REFERENCES public.organizational_units(organizational_unit_id) ON DELETE SET NULL;


--
-- Name: import_batches import_batches_data_source_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_batches
    ADD CONSTRAINT import_batches_data_source_id_foreign FOREIGN KEY (data_source_id) REFERENCES public.data_sources(data_source_id) ON DELETE SET NULL;


--
-- Name: import_batches import_batches_imported_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_batches
    ADD CONSTRAINT import_batches_imported_by_user_id_foreign FOREIGN KEY (imported_by_user_id) REFERENCES public.app_users(user_id) ON DELETE SET NULL;


--
-- Name: import_row_errors import_row_errors_import_batch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.import_row_errors
    ADD CONSTRAINT import_row_errors_import_batch_id_foreign FOREIGN KEY (import_batch_id) REFERENCES public.import_batches(import_batch_id) ON DELETE CASCADE;


--
-- Name: job_titles job_titles_employee_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_titles
    ADD CONSTRAINT job_titles_employee_category_id_foreign FOREIGN KEY (employee_category_id) REFERENCES public.employee_categories(employee_category_id) ON DELETE SET NULL;


--
-- Name: qualification_documents qualification_documents_document_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.qualification_documents
    ADD CONSTRAINT qualification_documents_document_id_foreign FOREIGN KEY (document_id) REFERENCES public.documents(document_id) ON DELETE RESTRICT;


--
-- Name: qualification_documents qualification_documents_qualification_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.qualification_documents
    ADD CONSTRAINT qualification_documents_qualification_id_foreign FOREIGN KEY (qualification_id) REFERENCES public.employee_qualifications(qualification_id) ON DELETE CASCADE;


--
-- Name: role_permissions role_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.app_permissions(permission_id) ON DELETE CASCADE;


--
-- Name: role_permissions role_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.app_roles(role_id) ON DELETE CASCADE;


--
-- Name: universities universities_country_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.universities
    ADD CONSTRAINT universities_country_id_foreign FOREIGN KEY (country_id) REFERENCES public.countries(country_id) ON DELETE SET NULL;


--
-- Name: user_roles user_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.app_roles(role_id) ON DELETE CASCADE;


--
-- Name: user_roles user_roles_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.app_users(user_id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict 6iJHcQmtan6rrCECg9Z27SaETQKNyxad2DqxDRMQrDmJxRT3pSbKBGlJaDNnon5

