--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: RECORDS; Type: TABLE; Schema: public; Owner: admin; Tablespace: 
--

CREATE TABLE "RECORDS" (
    id integer NOT NULL,
    user_id integer,
    user_ip character(40),
    date_r timestamp with time zone,
    content text NOT NULL
);


ALTER TABLE public."RECORDS" OWNER TO admin;

--
-- Name: RECORDS_ID_seq; Type: SEQUENCE; Schema: public; Owner: admin
--

CREATE SEQUENCE "RECORDS_ID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."RECORDS_ID_seq" OWNER TO admin;

--
-- Name: RECORDS_ID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: admin
--

ALTER SEQUENCE "RECORDS_ID_seq" OWNED BY "RECORDS".id;


--
-- Name: USERS; Type: TABLE; Schema: public; Owner: admin; Tablespace: 
--

CREATE TABLE "USERS" (
    id integer NOT NULL,
    user_login character(100) NOT NULL,
    user_pass character(32) NOT NULL
);


ALTER TABLE public."USERS" OWNER TO admin;

--
-- Name: USERS_ID_seq; Type: SEQUENCE; Schema: public; Owner: admin
--

CREATE SEQUENCE "USERS_ID_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."USERS_ID_seq" OWNER TO admin;

--
-- Name: USERS_ID_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: admin
--

ALTER SEQUENCE "USERS_ID_seq" OWNED BY "USERS".id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: admin
--

ALTER TABLE ONLY "RECORDS" ALTER COLUMN id SET DEFAULT nextval('"RECORDS_ID_seq"'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: admin
--

ALTER TABLE ONLY "USERS" ALTER COLUMN id SET DEFAULT nextval('"USERS_ID_seq"'::regclass);


--
-- Data for Name: RECORDS; Type: TABLE DATA; Schema: public; Owner: admin
--

COPY "RECORDS" (id, user_id, user_ip, date_r, content) FROM stdin;
3	-1	10.0.2.2                                	2016-11-15 15:48:36+03	hello!
4	-1	10.0.2.2                                	2016-11-15 15:56:41+03	bla bla bla!
5	-1	10.0.2.2                                	2016-11-15 16:37:32+03	Happy New Year!
8	4	10.0.2.2                                	2016-11-22 18:12:21+03	Mouse. I amCat
10	2	10.0.2.2                                	2016-11-24 16:52:22+03	Привет, geg!
9	2	10.0.2.2                                	2016-11-24 16:40:47+03	Привет, хомяки!tratratra
11	-1	10.0.2.2                                	2016-11-24 17:08:58+03	WOWOWOWOWOWOWOWOW
7	3	10.0.2.2                                	2016-11-22 18:09:08+03	Big Mouse!!!!!Eat Cats
6	3	10.0.2.2                                	2016-11-22 18:07:05+03	Hi! I am Mouse!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
\.


--
-- Name: RECORDS_ID_seq; Type: SEQUENCE SET; Schema: public; Owner: admin
--

SELECT pg_catalog.setval('"RECORDS_ID_seq"', 11, true);


--
-- Data for Name: USERS; Type: TABLE DATA; Schema: public; Owner: admin
--

COPY "USERS" (id, user_login, user_pass) FROM stdin;
2	anika                                                                                               	81dc9bdb52d04dc20036dbd8313ed055
3	mouse                                                                                               	827ccb0eea8a706c4c34a16891f84e7b
4	cat                                                                                                 	b59c67bf196a4758191e42f76670ceba
\.


--
-- Name: USERS_ID_seq; Type: SEQUENCE SET; Schema: public; Owner: admin
--

SELECT pg_catalog.setval('"USERS_ID_seq"', 4, true);


--
-- Name: RECORDS_pkey; Type: CONSTRAINT; Schema: public; Owner: admin; Tablespace: 
--

ALTER TABLE ONLY "RECORDS"
    ADD CONSTRAINT "RECORDS_pkey" PRIMARY KEY (id);


--
-- Name: USERS_pkey; Type: CONSTRAINT; Schema: public; Owner: admin; Tablespace: 
--

ALTER TABLE ONLY "USERS"
    ADD CONSTRAINT "USERS_pkey" PRIMARY KEY (id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

