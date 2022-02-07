--
-- PostgreSQL database dump
--

-- Dumped from database version 14.1 (Debian 14.1-1.pgdg110+1)
-- Dumped by pg_dump version 14.1 (Debian 14.1-1.pgdg110+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: authors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.authors (
    id uuid NOT NULL,
    first_name text NOT NULL,
    last_name text,
    is_pseudonym_of uuid,
    country_id uuid NOT NULL,
    born_year integer NOT NULL,
    death_year integer,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp with time zone
);


ALTER TABLE public.authors OWNER TO postgres;

--
-- Name: books; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.books (
    id uuid NOT NULL,
    title character varying NOT NULL,
    authors_ids uuid[] NOT NULL,
    editions_ids uuid[] NOT NULL,
    image character varying,
    publish_year integer,
    is_estimated_publish_year boolean DEFAULT false NOT NULL,
    is_pseudo_author boolean DEFAULT false NOT NULL,
    is_on_wish_list boolean DEFAULT false NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp with time zone
);


ALTER TABLE public.books OWNER TO postgres;

--
-- Name: books_authors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.books_authors (
    book_id uuid NOT NULL,
    author_id uuid NOT NULL
);


ALTER TABLE public.books_authors OWNER TO postgres;

--
-- Name: books_edtions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.books_edtions (
    book_id uuid NOT NULL,
    edition_id uuid NOT NULL
);


ALTER TABLE public.books_edtions OWNER TO postgres;

--
-- Name: countries; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.countries (
    id uuid DEFAULT uuid_in((md5(((random())::text || (clock_timestamp())::text)))::cstring) NOT NULL,
    num_code integer NOT NULL,
    alpha_2_code character varying(2) DEFAULT NULL::character varying,
    alpha_3_code character varying(3) DEFAULT NULL::character varying,
    en_short_name character varying(52) DEFAULT NULL::character varying,
    nationality character varying(39) DEFAULT NULL::character varying
);


ALTER TABLE public.countries OWNER TO postgres;

--
-- Name: countries_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.countries_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.countries_seq OWNER TO postgres;

--
-- Name: editions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.editions (
    id uuid NOT NULL,
    year integer NOT NULL,
    publisher_id uuid NOT NULL,
    physical_book boolean DEFAULT false NOT NULL,
    condition character varying,
    resource character varying,
    resource_types character varying[],
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp with time zone
);


ALTER TABLE public.editions OWNER TO postgres;

--
-- Name: journals; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.journals (
    id uuid NOT NULL,
    name text NOT NULL,
    image character varying,
    publisher_id uuid,
    foundation_year integer,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp with time zone
);


ALTER TABLE public.journals OWNER TO postgres;

--
-- Name: papers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.papers (
    id uuid NOT NULL,
    title text NOT NULL,
    authors_ids uuid[] NOT NULL,
    journal_id uuid NOT NULL,
    journal_volume integer,
    initial_page integer,
    end_page integer,
    publish_year integer,
    resource character varying,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp with time zone
);


ALTER TABLE public.papers OWNER TO postgres;

--
-- Name: papers_authors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.papers_authors (
    paper_id uuid NOT NULL,
    author_id uuid NOT NULL
);


ALTER TABLE public.papers_authors OWNER TO postgres;

--
-- Name: publishers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.publishers (
    id uuid NOT NULL,
    name character varying NOT NULL,
    city character varying,
    country_id uuid NOT NULL,
    foundation_year integer,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp with time zone
);


ALTER TABLE public.publishers OWNER TO postgres;

--
-- Data for Name: authors; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.authors (id, first_name, last_name, is_pseudonym_of, country_id, born_year, death_year, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: books; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.books (id, title, authors_ids, editions_ids, image, publish_year, is_estimated_publish_year, is_pseudo_author, is_on_wish_list, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: books_authors; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.books_authors (book_id, author_id) FROM stdin;
\.


--
-- Data for Name: books_edtions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.books_edtions (book_id, edition_id) FROM stdin;
\.


--
-- Data for Name: countries; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.countries (id, num_code, alpha_2_code, alpha_3_code, en_short_name, nationality) FROM stdin;
f55325e3-3b17-de4e-49f7-b3e071177644	4	AF	AFG	Afghanistan	Afghan
b6877be3-7cb1-dd88-6fda-100b013970e8	248	AX	ALA	Åland Islands	Åland Island
c3df4af2-e6b6-e3ab-fcbe-4be874163173	8	AL	ALB	Albania	Albanian
d4fff356-ad67-9347-f167-f904adcd8953	12	DZ	DZA	Algeria	Algerian
0dfb1a86-90a2-aec8-ea00-c45750e3fdcc	16	AS	ASM	American Samoa	American Samoan
73e5450b-d0ee-70ce-d76b-b372f2d68594	20	AD	AND	Andorra	Andorran
cfc2f77e-7e95-9955-b4b4-f929dcd0efea	24	AO	AGO	Angola	Angolan
2d36604b-8370-cca0-de47-3a5aad0f21c6	660	AI	AIA	Anguilla	Anguillan
df53337b-9c7d-2d76-4640-505c8cd5cd08	10	AQ	ATA	Antarctica	Antarctic
1fcbddca-7f6e-d599-1983-7c88a1524850	28	AG	ATG	Antigua and Barbuda	Antiguan or Barbudan
64a0791d-f994-5e44-c9e9-8066656f8b36	32	AR	ARG	Argentina	Argentine
daf735bb-dd4b-b7fe-a286-b9b6718c8b2a	51	AM	ARM	Armenia	Armenian
6c45c7c6-b76c-79fc-c3ed-a50bca1fc31a	533	AW	ABW	Aruba	Aruban
7099df41-cb25-eb00-5bd7-2a5d1232f410	36	AU	AUS	Australia	Australian
f2d6937c-4e23-a1ea-3e93-0ddee00d8eb1	40	AT	AUT	Austria	Austrian
570bce7e-7125-f404-811d-98c9bfe51ac3	31	AZ	AZE	Azerbaijan	Azerbaijani, Azeri
9722448e-feeb-2528-5860-7d27a07cef42	44	BS	BHS	Bahamas	Bahamian
af852a89-21a6-763c-95e1-1add7ae78aac	48	BH	BHR	Bahrain	Bahraini
7c8c58d9-1e6d-ef78-e0a9-b76bdfdd3347	50	BD	BGD	Bangladesh	Bangladeshi
b3875e72-26d3-dbaf-c18b-ea2f46e48abb	52	BB	BRB	Barbados	Barbadian
0e7f9139-045a-16f2-3471-3da0825c2f44	112	BY	BLR	Belarus	Belarusian
4e971bef-5e80-a79e-c2de-c7e807e5c28f	56	BE	BEL	Belgium	Belgian
5b7bc55c-cea5-b9a4-1137-2dfd55f81bf1	84	BZ	BLZ	Belize	Belizean
b380632f-4cc5-a3c2-23b1-7909642c095b	204	BJ	BEN	Benin	Beninese, Beninois
744efad3-9b64-859e-f69e-13eea00514c8	60	BM	BMU	Bermuda	Bermudian, Bermudan
5595058d-92da-065c-d1ee-c72ec266e28c	64	BT	BTN	Bhutan	Bhutanese
81ca073d-846a-11a4-f0ff-7fc9678dc0b7	68	BO	BOL	Bolivia (Plurinational State of)	Bolivian
67016713-041f-7627-7391-32832c790dff	535	BQ	BES	Bonaire, Sint Eustatius and Saba	Bonaire
0fc67daa-cb34-f68b-b41f-a7ecb5623144	70	BA	BIH	Bosnia and Herzegovina	Bosnian or Herzegovinian
968b3328-1262-430e-d56e-6467107cd0ae	72	BW	BWA	Botswana	Motswana, Botswanan
bdfee185-23a9-ec4e-2556-d88a3f91f8b8	74	BV	BVT	Bouvet Island	Bouvet Island
3c8d929a-adc9-8fbb-d10d-eb7663dc45a3	76	BR	BRA	Brazil	Brazilian
e0dfe10e-54b7-97ea-9796-1c2a481460b4	86	IO	IOT	British Indian Ocean Territory	BIOT
07dc5268-3f1f-e4f5-3034-5659edc93688	96	BN	BRN	Brunei Darussalam	Bruneian
16a34861-a085-df38-4784-c381fdc64a4e	100	BG	BGR	Bulgaria	Bulgarian
5acdc846-6aa9-2a62-b744-c7fc86853891	854	BF	BFA	Burkina Faso	Burkinabé
03130a10-be8d-fe60-93ce-b2540a738f8d	108	BI	BDI	Burundi	Burundian
ae649e63-aee5-01c5-9b8c-e8f5e78e8500	132	CV	CPV	Cabo Verde	Cabo Verdean
2ab96e05-4e16-da52-7dcb-0c46d48db007	116	KH	KHM	Cambodia	Cambodian
c6cfdbca-a622-4754-8ef6-eec07b77a319	120	CM	CMR	Cameroon	Cameroonian
a94ef34c-9a06-623b-e418-33bb2c3396af	124	CA	CAN	Canada	Canadian
503c25af-993b-4167-a43c-07f2b39d6edd	136	KY	CYM	Cayman Islands	Caymanian
f976bb1a-8d47-df82-4a7b-ae169dd3710e	140	CF	CAF	Central African Republic	Central African
2aafb83d-9066-f888-0a12-587f11516477	148	TD	TCD	Chad	Chadian
368fe3af-d257-5f91-cc9b-ba06dcb77108	152	CL	CHL	Chile	Chilean
118dcfaf-016a-75ba-44a3-977edf533a91	156	CN	CHN	China	Chinese
c9af348d-e8a3-bf56-2d7e-a4f9a797bd99	162	CX	CXR	Christmas Island	Christmas Island
e4208d07-2447-0f21-c370-090a14ae4b8a	166	CC	CCK	Cocos (Keeling) Islands	Cocos Island
4abd2d4b-b025-9df1-acd7-6a3de33f54f0	170	CO	COL	Colombia	Colombian
d1aae583-8b52-7b6f-dea6-e083a09afab6	174	KM	COM	Comoros	Comoran, Comorian
00215e1d-8835-150e-9892-e5182ee79aee	178	CG	COG	Congo (Republic of the)	Congolese
621e3fef-10ce-0a5a-a358-1682a47f826f	180	CD	COD	Congo (Democratic Republic of the)	Congolese
a0f596c6-7336-66ac-a5e0-0ac59cc764af	184	CK	COK	Cook Islands	Cook Island
f484644c-4b62-73cf-26de-45a4868d3ffb	188	CR	CRI	Costa Rica	Costa Rican
bed57d53-3379-b2b0-c5d8-7ba6c7f0dd70	384	CI	CIV	Côte d'Ivoire	Ivorian
5baa28f0-54c9-8f47-0e46-6a392b1f2b8c	191	HR	HRV	Croatia	Croatian
e54b5508-8491-46b9-6aff-167165ea6fa2	192	CU	CUB	Cuba	Cuban
dc153415-9e98-b406-088c-50df5ac14a27	531	CW	CUW	Curaçao	Curaçaoan
34cb5edb-b95a-b607-e6fd-bae2cee2a604	196	CY	CYP	Cyprus	Cypriot
3f1f2e9a-d4c1-14f2-05b3-18fd34821e98	203	CZ	CZE	Czech Republic	Czech
10df4dd5-d7fb-3ba0-ad03-71381280dec7	208	DK	DNK	Denmark	Danish
2355846f-5187-8487-8aff-0aed5b2aee66	262	DJ	DJI	Djibouti	Djiboutian
c7e98f35-41c0-ed3a-184d-22bb4366887e	212	DM	DMA	Dominica	Dominican
7c88ff03-e38c-2e6b-774c-3a21e2f3d3c3	214	DO	DOM	Dominican Republic	Dominican
b53c7731-bf20-354f-f57e-11e36146bf87	218	EC	ECU	Ecuador	Ecuadorian
abbdca52-9ba5-51c4-5d7e-2c7ba5e33a30	818	EG	EGY	Egypt	Egyptian
cd172844-57ec-1a38-45b7-baf916e021fb	222	SV	SLV	El Salvador	Salvadoran
0429c821-f58f-61ab-25c9-76998cf9ec66	226	GQ	GNQ	Equatorial Guinea	Equatorial Guinean, Equatoguinean
571044d1-326e-5df9-47b1-57d6e8eaa5e3	232	ER	ERI	Eritrea	Eritrean
c77bc19b-54f7-34df-dd70-96321e48cd55	233	EE	EST	Estonia	Estonian
0e8e28d6-e486-06cb-476a-715b05f34c73	231	ET	ETH	Ethiopia	Ethiopian
a497b7bf-0902-5dcc-d784-eb58d03de617	238	FK	FLK	Falkland Islands (Malvinas)	Falkland Island
c87e0515-fd38-40e9-f57c-39fa8d6ebdc6	234	FO	FRO	Faroe Islands	Faroese
fc0a2e1a-fca0-fa7c-7811-c0985949004d	242	FJ	FJI	Fiji	Fijian
262519b4-4e8d-4933-15af-efccd1aacbee	246	FI	FIN	Finland	Finnish
c310c88a-c751-167e-006d-8fb1cc36e136	250	FR	FRA	France	French
b4ba2be9-f117-248b-6191-09e9f6f2091f	254	GF	GUF	French Guiana	French Guianese
ed45d21a-352b-357b-f63f-462d003c30fa	258	PF	PYF	French Polynesia	French Polynesian
7e671943-906e-350f-8e0b-25855e7200e3	260	TF	ATF	French Southern Territories	French Southern Territories
9d088ea2-bdb1-e9e3-1b4b-5662efe1475b	266	GA	GAB	Gabon	Gabonese
0db429af-18b1-df41-2a95-86709fcaae7b	270	GM	GMB	Gambia	Gambian
f50501fa-90c3-3b64-abc7-34b0f7c705b1	268	GE	GEO	Georgia	Georgian
f71d65dc-81e3-9aa4-4ebd-97fdec0abe01	276	DE	DEU	Germany	German
245a7c02-c70d-ef91-9542-7b5ec621dc8a	288	GH	GHA	Ghana	Ghanaian
436bbb27-93f7-9d91-c926-39e91823dcf5	292	GI	GIB	Gibraltar	Gibraltar
32e52bf2-4647-ddc2-aa16-23cff71c78be	300	GR	GRC	Greece	Greek, Hellenic
8ecc44d8-605a-3b77-b8ba-f249dc906c5e	304	GL	GRL	Greenland	Greenlandic
96c44911-83ad-96b2-fdea-79c61c80f4a3	308	GD	GRD	Grenada	Grenadian
127d47a9-4c1d-8237-57ed-3254508dc386	312	GP	GLP	Guadeloupe	Guadeloupe
9864addd-d18e-80cb-8d3d-5bc5cceca151	316	GU	GUM	Guam	Guamanian, Guambat
f3dee6e4-9f14-1d20-5320-0dec6ba863b2	320	GT	GTM	Guatemala	Guatemalan
1e3be56e-6cb0-4821-76b0-daabbb72d617	831	GG	GGY	Guernsey	Channel Island
5d18fd53-ccc4-0667-2b9c-6f8da85a44a2	324	GN	GIN	Guinea	Guinean
3c07be37-af27-43fb-1e29-a1f4352ac73d	624	GW	GNB	Guinea-Bissau	Bissau-Guinean
e9159d51-1ca0-02d1-8305-1baab9e19a77	328	GY	GUY	Guyana	Guyanese
a7aff4f7-dc12-dd32-2496-8100f53f83f6	332	HT	HTI	Haiti	Haitian
d0a408ec-14d7-58a5-1580-58e5dec0e568	334	HM	HMD	Heard Island and McDonald Islands	Heard Island or McDonald Islands
8f226726-2779-a77d-a169-97d9ee669f75	336	VA	VAT	Vatican City State	Vatican
6113c729-73c6-5acd-311c-acad041ec0ed	340	HN	HND	Honduras	Honduran
ed3f78fc-d498-090d-83ba-357bc990329d	344	HK	HKG	Hong Kong	Hong Kong, Hong Kongese
23c115a9-f2e6-b38e-1d37-7e42812f7918	348	HU	HUN	Hungary	Hungarian, Magyar
1a31c088-0225-8f0d-ee88-4527a8da4a99	352	IS	ISL	Iceland	Icelandic
b00625cd-a7bd-3e70-cc4c-d6361fb3e584	356	IN	IND	India	Indian
b45a1777-cf5d-c886-27f5-8bed8e723a2c	360	ID	IDN	Indonesia	Indonesian
3c07331d-affa-c67d-f448-95ab41f6efb7	364	IR	IRN	Iran	Iranian, Persian
b64e7dea-19e0-6609-f199-134648505d99	368	IQ	IRQ	Iraq	Iraqi
6034bced-1646-d740-9844-35124e0b84a7	372	IE	IRL	Ireland	Irish
baf1faf2-2072-0b75-17b3-0a5ff79eca1b	833	IM	IMN	Isle of Man	Manx
f1d4c8dd-9634-59d2-312d-ba9eb9441aff	376	IL	ISR	Israel	Israeli
14562cbc-080a-ea6e-43b3-b5da9fe36da2	380	IT	ITA	Italy	Italian
77ad21e6-3bef-1feb-19be-0a7ffb98fa64	388	JM	JAM	Jamaica	Jamaican
db4be814-d581-5c27-7c23-f077c6f52e04	392	JP	JPN	Japan	Japanese
1c8578ce-c0e7-77ce-604a-e580d10c63db	832	JE	JEY	Jersey	Channel Island
704b2428-9409-3c8c-567c-4bd2d1c140b4	400	JO	JOR	Jordan	Jordanian
189d7d8c-efcd-b06d-c0e2-920c617953d3	398	KZ	KAZ	Kazakhstan	Kazakhstani, Kazakh
0afd70a8-9429-76d6-8ce9-e03452d91dd1	404	KE	KEN	Kenya	Kenyan
353ea4dd-2723-7156-5fe1-df0ad8b3265d	296	KI	KIR	Kiribati	I-Kiribati
0db357a7-c361-0a5f-1dfe-56d290ffcaa7	408	KP	PRK	Korea (Democratic People's Republic of)	North Korean
4ccdab1a-14de-b5cf-3520-bc6fabef9623	410	KR	KOR	Korea (Republic of)	South Korean
bc415e95-393e-8a89-af22-8c51bcfd562b	414	KW	KWT	Kuwait	Kuwaiti
d2be6d74-8e3a-53c8-42c7-de02f14014d2	417	KG	KGZ	Kyrgyzstan	Kyrgyzstani, Kyrgyz, Kirgiz, Kirghiz
f397961c-419b-2328-cdfa-6e02659c370b	418	LA	LAO	Lao People's Democratic Republic	Lao, Laotian
582c0b1f-6671-7140-26a3-3bdb8a78f52b	428	LV	LVA	Latvia	Latvian
c610efb5-e785-e8f6-a820-46da2704ca12	422	LB	LBN	Lebanon	Lebanese
61fe520d-4d35-2071-5fb1-a3ed01d147fc	426	LS	LSO	Lesotho	Basotho
a926ec87-065f-a679-6d77-1894123c0e29	430	LR	LBR	Liberia	Liberian
a499da75-3708-e87d-4c9a-232f983060e6	434	LY	LBY	Libya	Libyan
d37af15f-3bbd-5e5f-f185-45c89d79868c	438	LI	LIE	Liechtenstein	Liechtenstein
bd9e6447-d371-e3dc-75b8-0183c0566955	440	LT	LTU	Lithuania	Lithuanian
dcc86222-c03a-3fce-db07-f70b5248cc55	442	LU	LUX	Luxembourg	Luxembourg, Luxembourgish
4c38f2a8-be21-67da-b368-31f72cf01278	446	MO	MAC	Macao	Macanese, Chinese
782e71e6-c592-ee98-012b-88b6930a993c	807	MK	MKD	Macedonia (the former Yugoslav Republic of)	Macedonian
8db1fb10-835b-403d-a256-d79e9017d34c	450	MG	MDG	Madagascar	Malagasy
5175157e-9a59-6e61-13bc-f092144ee04c	454	MW	MWI	Malawi	Malawian
83d90991-c4cd-af29-a96e-e47950ec40b4	458	MY	MYS	Malaysia	Malaysian
7182a8cf-c4d8-0c8b-a5da-d9a14378bcb1	462	MV	MDV	Maldives	Maldivian
1415b679-7d45-0189-ddfb-652cca70dc58	466	ML	MLI	Mali	Malian, Malinese
156d5cbb-1875-5e31-c798-0d3a16142a4d	470	MT	MLT	Malta	Maltese
13a5b31d-346f-a8d3-601b-734920672176	584	MH	MHL	Marshall Islands	Marshallese
bc4cd9de-95ab-1e49-0796-9cfba9a49ac0	474	MQ	MTQ	Martinique	Martiniquais, Martinican
459069b1-db08-a882-f86c-6cfcc1816a04	478	MR	MRT	Mauritania	Mauritanian
8ac93f89-7fb4-197e-ef35-06daee456490	480	MU	MUS	Mauritius	Mauritian
d61b3e16-33cb-c754-307e-0c6ccdd016c2	175	YT	MYT	Mayotte	Mahoran
f1e35566-926b-2925-75a5-31c4a78a6f86	484	MX	MEX	Mexico	Mexican
06ebd2cb-9ef8-bcea-47e3-34f1bae74b3f	583	FM	FSM	Micronesia (Federated States of)	Micronesian
7c80dac1-b348-33f1-445b-1f43096bdb19	498	MD	MDA	Moldova (Republic of)	Moldovan
5670c0af-41a8-49ce-3a7b-1264206693b4	492	MC	MCO	Monaco	Monégasque, Monacan
b4ab1548-c8e6-fa9a-f7c5-8818181f0d18	496	MN	MNG	Mongolia	Mongolian
6cd59dfa-c848-9517-e257-a6acc58a1da9	499	ME	MNE	Montenegro	Montenegrin
18bfd73a-284e-427d-41ef-fc1ec352ad3f	500	MS	MSR	Montserrat	Montserratian
d10055a2-b4f3-8ab8-873d-b3e809fb5f22	504	MA	MAR	Morocco	Moroccan
e53de567-28b0-dd01-e090-9402f6a79551	508	MZ	MOZ	Mozambique	Mozambican
5aacdbc5-65dc-ac83-0688-ff857c4f3881	104	MM	MMR	Myanmar	Burmese
ae99420b-aab4-b221-2633-c4bdd556e4d7	516	NA	NAM	Namibia	Namibian
0323b9b5-10c0-1b47-e7b5-0254b813940e	520	NR	NRU	Nauru	Nauruan
181f7017-4b99-435a-1ecf-6ebad692904a	524	NP	NPL	Nepal	Nepali, Nepalese
50502f1d-0ced-ce12-e003-63ee2aa1a897	528	NL	NLD	Netherlands	Dutch, Netherlandic
fc458576-201a-d9e0-96ef-538f4012dc8b	540	NC	NCL	New Caledonia	New Caledonian
8f68d6ea-ee08-bdfc-24eb-82ce57c7fe28	554	NZ	NZL	New Zealand	New Zealand, NZ
0d17ab10-89d7-c4ea-3ec9-abc625f7939d	558	NI	NIC	Nicaragua	Nicaraguan
3454bafb-cd4e-2426-b927-7479980f3acb	562	NE	NER	Niger	Nigerien
337704c3-8156-10ad-8068-1b134524c9d2	566	NG	NGA	Nigeria	Nigerian
baea1a8b-d972-140d-0e0c-dd2923877267	570	NU	NIU	Niue	Niuean
7b50af7d-1bbe-fd37-d0c7-5bf0ef742eda	574	NF	NFK	Norfolk Island	Norfolk Island
61ae8dea-729f-a979-a290-27b6e4d6e288	580	MP	MNP	Northern Mariana Islands	Northern Marianan
d1896213-8caf-8fcf-2587-d003d93e4588	578	NO	NOR	Norway	Norwegian
facb3374-d881-12f7-2a80-8c4a01bebc43	512	OM	OMN	Oman	Omani
149c1386-0e22-a57a-adca-08574f457130	586	PK	PAK	Pakistan	Pakistani
50ba17e9-a2b6-100a-5408-3926f55dc92b	585	PW	PLW	Palau	Palauan
87a9c651-c306-ecdf-8035-4d2a13694bf4	275	PS	PSE	Palestine, State of	Palestinian
5a1bd1e6-ef40-22c4-4d04-1b527f827a71	591	PA	PAN	Panama	Panamanian
43fc8a1c-d54d-c648-1968-66d4fa2a0a70	598	PG	PNG	Papua New Guinea	Papua New Guinean, Papuan
6aea2e63-30ea-6f5f-8afd-7076469da831	600	PY	PRY	Paraguay	Paraguayan
13059053-a741-255d-2b67-d0b4133df690	604	PE	PER	Peru	Peruvian
705b554d-174d-2542-9a68-57722dca455f	608	PH	PHL	Philippines	Philippine, Filipino
ea148202-27be-f948-a714-dd762b65747d	612	PN	PCN	Pitcairn	Pitcairn Island
bd0e1317-a2c4-4536-74c7-f0d9713f74c4	616	PL	POL	Poland	Polish
de0bf641-d2cb-90b6-bbd4-68808d8b63df	620	PT	PRT	Portugal	Portuguese
c493551b-99cf-3f40-4132-6aa19918f632	630	PR	PRI	Puerto Rico	Puerto Rican
6a8f765a-fb97-27dd-dcdf-96d050f9a600	634	QA	QAT	Qatar	Qatari
19bfd9f2-a4c9-a701-6427-8d3c9250b723	638	RE	REU	Réunion	Réunionese, Réunionnais
0677d919-62d8-8432-3902-984ebc17efeb	642	RO	ROU	Romania	Romanian
6498bf37-3e55-9594-d651-a4c1338dcdde	643	RU	RUS	Russian Federation	Russian
5fa2dc18-08f3-29ac-9d54-389d50550643	646	RW	RWA	Rwanda	Rwandan
b3d517da-5017-1d17-0cc7-90ddf50498b9	652	BL	BLM	Saint Barthélemy	Barthélemois
f45fb10f-c3d3-30ae-0576-d23e39fe3688	654	SH	SHN	Saint Helena, Ascension and Tristan da Cunha	Saint Helenian
2bc17896-28b0-a34c-b82c-115cf4a8f75e	659	KN	KNA	Saint Kitts and Nevis	Kittitian or Nevisian
d66ac4b7-bf34-b64f-f020-a6abdaa19a75	662	LC	LCA	Saint Lucia	Saint Lucian
b8b50627-9873-51ab-468b-8d23ab7b81c0	663	MF	MAF	Saint Martin (French part)	Saint-Martinoise
3f7fc854-a39c-6e37-793a-a7fd6510e159	666	PM	SPM	Saint Pierre and Miquelon	Saint-Pierrais or Miquelonnais
6da33b0a-1057-415e-71bf-3f4282af3c09	670	VC	VCT	Saint Vincent and the Grenadines	Saint Vincentian, Vincentian
42bec47e-3307-8c58-e28d-9da58f22e560	882	WS	WSM	Samoa	Samoan
81192fb3-f167-0949-b090-5bf953350c01	674	SM	SMR	San Marino	Sammarinese
c0b93d1e-12cd-cde6-3417-64c321469e17	678	ST	STP	Sao Tome and Principe	São Toméan
3eb89eb7-aa71-4b11-7e8c-8dd46e6651d6	682	SA	SAU	Saudi Arabia	Saudi, Saudi Arabian
0a33d5e6-28c0-d2bc-6f1f-9367ac1c131f	686	SN	SEN	Senegal	Senegalese
5f86a762-d7be-e35b-8137-4251c4c93e39	688	RS	SRB	Serbia	Serbian
657b2b22-5298-ec50-bf4e-a37e0bc6664c	690	SC	SYC	Seychelles	Seychellois
9ac8ffe4-3f8a-7561-c836-0192c80c6413	694	SL	SLE	Sierra Leone	Sierra Leonean
ec0d49a1-716f-af61-2938-44b5605b6028	702	SG	SGP	Singapore	Singaporean
3d085d12-0634-972d-3b62-fc0029b598e6	534	SX	SXM	Sint Maarten (Dutch part)	Sint Maarten
d2e564e3-7444-ef14-bfe5-8ce154ee5bea	703	SK	SVK	Slovakia	Slovak
343506c1-64e2-21a6-e4d2-1d741de00f99	705	SI	SVN	Slovenia	Slovenian, Slovene
ecbdfa3a-f0ea-10fb-d01c-75831614dcd0	90	SB	SLB	Solomon Islands	Solomon Island
9e179cb1-28a3-0f10-d0e8-ebf2e9728fee	706	SO	SOM	Somalia	Somali, Somalian
5509158e-231c-5f81-3b5b-652ddec61a07	710	ZA	ZAF	South Africa	South African
9b10e9f5-b72c-a8e6-01ba-23d975a38b27	239	GS	SGS	South Georgia and the South Sandwich Islands	South Georgia or South Sandwich Islands
f119347c-d1de-a667-a90a-1aeec6df2679	728	SS	SSD	South Sudan	South Sudanese
34d41de4-d197-8f63-5014-7c5e5e8f71e3	724	ES	ESP	Spain	Spanish
675c429b-156c-e7bd-2428-e984c369a4c2	144	LK	LKA	Sri Lanka	Sri Lankan
626dfa66-e54b-a088-0d50-499ed9216a22	729	SD	SDN	Sudan	Sudanese
58a3cc31-3252-d834-346e-70050f78410f	740	SR	SUR	Suriname	Surinamese
d5440e02-3c69-3b43-f3d0-54bc44e1b320	744	SJ	SJM	Svalbard and Jan Mayen	Svalbard
21ee3bfe-1d39-6493-5193-9bf5db55535d	748	SZ	SWZ	Swaziland	Swazi
892ec1e4-d278-4d0e-82cb-2d073763be26	752	SE	SWE	Sweden	Swedish
5796c6ae-b565-397e-464f-0dd14d547b3d	756	CH	CHE	Switzerland	Swiss
51f91904-087f-0d18-f175-fac43684910a	760	SY	SYR	Syrian Arab Republic	Syrian
da985708-f5e6-b843-e065-f4369daaa43e	158	TW	TWN	Taiwan, Province of China	Chinese, Taiwanese
3bf008bd-ade3-350f-fff4-c66e7e6ac048	762	TJ	TJK	Tajikistan	Tajikistani
6aff5df0-f984-6b7f-10a8-3981cd431d99	834	TZ	TZA	Tanzania, United Republic of	Tanzanian
cbd7b69a-28bf-3e9d-20c5-c17a88ba1623	764	TH	THA	Thailand	Thai
1415473d-2f7f-a644-fb3c-7c45674e5239	626	TL	TLS	Timor-Leste	Timorese
a720bf13-c88b-2847-1fb6-4b2b0159552a	768	TG	TGO	Togo	Togolese
c584d09a-603d-1b29-07a0-77f3d83e207f	772	TK	TKL	Tokelau	Tokelauan
61344ac1-1119-dff4-92c3-bdd5ad578c12	776	TO	TON	Tonga	Tongan
b3c2deaa-bd95-d631-4c6a-8e2d7dd0d45c	780	TT	TTO	Trinidad and Tobago	Trinidadian or Tobagonian
55b965dc-0443-569c-719a-51d872b36557	788	TN	TUN	Tunisia	Tunisian
18587a56-14dd-69d1-e050-85bad5283d39	792	TR	TUR	Turkey	Turkish
965ececf-6343-2478-d3f5-f58cb5d57ab2	795	TM	TKM	Turkmenistan	Turkmen
71832642-04b1-1145-e1b3-3e78667dd7ac	796	TC	TCA	Turks and Caicos Islands	Turks and Caicos Island
72913331-e51b-d12b-bd8e-6b8c99a841e8	798	TV	TUV	Tuvalu	Tuvaluan
adee69d1-cd06-6cd7-e0ec-6a3962700fc3	800	UG	UGA	Uganda	Ugandan
35123364-ea7e-4abe-1a47-a3e3a60ae568	804	UA	UKR	Ukraine	Ukrainian
ac38713d-3051-8ee6-c75a-500d0e98e629	784	AE	ARE	United Arab Emirates	Emirati, Emirian, Emiri
c967a830-a216-bb8f-d27d-1712ed1c107b	826	GB	GBR	United Kingdom of Great Britain and Northern Ireland	British, UK
3b6e6753-2d5a-a6f6-3958-0f159a6f51f6	581	UM	UMI	United States Minor Outlying Islands	American
47d15f59-7661-8f32-b1c0-c737e85b2ef9	840	US	USA	United States of America	American
e6f39f66-af03-658d-8bc1-4e9cc0ea97aa	858	UY	URY	Uruguay	Uruguayan
2b39c5a6-7271-838e-d82b-51e1ca49ed62	860	UZ	UZB	Uzbekistan	Uzbekistani, Uzbek
4f78a199-fb7e-7b57-fd0e-6f521b43a0ee	548	VU	VUT	Vanuatu	Ni-Vanuatu, Vanuatuan
e71ae1bd-8ec8-5d88-8180-a2e3fde6e876	862	VE	VEN	Venezuela (Bolivarian Republic of)	Venezuelan
b6506841-ad01-f479-e398-bca5c154e657	704	VN	VNM	Vietnam	Vietnamese
5117ce0f-537b-11ef-8d4b-02331c67aa60	92	VG	VGB	Virgin Islands (British)	British Virgin Island
701d57b3-81ca-0c21-8465-a6f31faca5ff	850	VI	VIR	Virgin Islands (U.S.)	U.S. Virgin Island
5b10efc8-d905-b2ac-22c0-8574ac411729	876	WF	WLF	Wallis and Futuna	Wallis and Futuna, Wallisian or Futunan
a017682e-9770-50f8-7cf6-709a09b2c381	732	EH	ESH	Western Sahara	Sahrawi, Sahrawian, Sahraouian
87056cab-0bfb-e8c3-34a9-e5abc09e3e99	887	YE	YEM	Yemen	Yemeni
bb5d2496-992f-5c3e-1ab9-cba2c11ae31c	894	ZM	ZMB	Zambia	Zambian
36a9d547-1a59-77b5-0359-f0173d3c794d	716	ZW	ZWE	Zimbabwe	Zimbabwean
\.


--
-- Data for Name: editions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.editions (id, year, publisher_id, physical_book, condition, resource, resource_types, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: journals; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.journals (id, name, image, publisher_id, foundation_year, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: papers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.papers (id, title, authors_ids, journal_id, journal_volume, initial_page, end_page, publish_year, resource, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: papers_authors; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.papers_authors (paper_id, author_id) FROM stdin;
\.


--
-- Data for Name: publishers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.publishers (id, name, city, country_id, foundation_year, created_at, updated_at) FROM stdin;
\.


--
-- Name: countries_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.countries_seq', 1, false);


--
-- Name: authors authors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.authors
    ADD CONSTRAINT authors_pkey PRIMARY KEY (id);


--
-- Name: books_authors books_authors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.books_authors
    ADD CONSTRAINT books_authors_pkey PRIMARY KEY (book_id, author_id);


--
-- Name: books_edtions books_edtions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.books_edtions
    ADD CONSTRAINT books_edtions_pkey PRIMARY KEY (book_id, edition_id);


--
-- Name: books books_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.books
    ADD CONSTRAINT books_pkey PRIMARY KEY (id);


--
-- Name: countries countries_alpha_2_code_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.countries
    ADD CONSTRAINT countries_alpha_2_code_key UNIQUE (alpha_2_code);


--
-- Name: countries countries_alpha_3_code_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.countries
    ADD CONSTRAINT countries_alpha_3_code_key UNIQUE (alpha_3_code);


--
-- Name: countries countries_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.countries
    ADD CONSTRAINT countries_pkey PRIMARY KEY (id);


--
-- Name: editions editions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.editions
    ADD CONSTRAINT editions_pkey PRIMARY KEY (id);


--
-- Name: journals journals_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journals
    ADD CONSTRAINT journals_pkey PRIMARY KEY (id);


--
-- Name: papers_authors paper_authors_primary; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.papers_authors
    ADD CONSTRAINT paper_authors_primary PRIMARY KEY (paper_id, author_id);


--
-- Name: papers papers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.papers
    ADD CONSTRAINT papers_pkey PRIMARY KEY (id);


--
-- Name: publishers publishers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publishers
    ADD CONSTRAINT publishers_pkey PRIMARY KEY (id);


--
-- Name: books_authors authors_foreign_books; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.books_authors
    ADD CONSTRAINT authors_foreign_books FOREIGN KEY (book_id) REFERENCES public.books(id);


--
-- Name: papers_authors authors_foreign_papers; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.papers_authors
    ADD CONSTRAINT authors_foreign_papers FOREIGN KEY (paper_id) REFERENCES public.papers(id);


--
-- Name: authors authors_foreing_countries; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.authors
    ADD CONSTRAINT authors_foreing_countries FOREIGN KEY (country_id) REFERENCES public.countries(id) NOT VALID;


--
-- Name: books_authors books_foreign_authors; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.books_authors
    ADD CONSTRAINT books_foreign_authors FOREIGN KEY (author_id) REFERENCES public.authors(id);


--
-- Name: books_edtions books_foreign_editions; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.books_edtions
    ADD CONSTRAINT books_foreign_editions FOREIGN KEY (edition_id) REFERENCES public.editions(id);


--
-- Name: books_edtions editions_foreign_books; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.books_edtions
    ADD CONSTRAINT editions_foreign_books FOREIGN KEY (book_id) REFERENCES public.books(id);


--
-- Name: editions editons_foreign_publishers; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.editions
    ADD CONSTRAINT editons_foreign_publishers FOREIGN KEY (publisher_id) REFERENCES public.publishers(id);


--
-- Name: journals journlas_foreing_publishers; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journals
    ADD CONSTRAINT journlas_foreing_publishers FOREIGN KEY (publisher_id) REFERENCES public.publishers(id) NOT VALID;


--
-- Name: papers_authors papers_foreign_authors; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.papers_authors
    ADD CONSTRAINT papers_foreign_authors FOREIGN KEY (author_id) REFERENCES public.authors(id);


--
-- Name: publishers publishers_foreign_countries; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publishers
    ADD CONSTRAINT publishers_foreign_countries FOREIGN KEY (country_id) REFERENCES public.countries(id) NOT VALID;


--
-- PostgreSQL database dump complete
--

