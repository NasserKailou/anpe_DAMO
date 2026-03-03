--
-- PostgreSQL database dump
--

-- Dumped from database version 15.10
-- Dumped by pg_dump version 15.10

-- Started on 2026-03-03 11:22:53

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
-- TOC entry 3 (class 3079 OID 264736)
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;


--
-- TOC entry 3755 (class 0 OID 0)
-- Dependencies: 3
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


--
-- TOC entry 2 (class 3079 OID 264725)
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- TOC entry 3756 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- TOC entry 304 (class 1255 OID 265239)
-- Name: update_updated_at_column(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_updated_at_column() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_updated_at_column() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 223 (class 1259 OID 264816)
-- Name: branches_activite; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.branches_activite (
    id integer NOT NULL,
    code character varying(10) NOT NULL,
    libelle character varying(200) NOT NULL,
    description text,
    actif boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.branches_activite OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 264815)
-- Name: branches_activite_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.branches_activite_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.branches_activite_id_seq OWNER TO postgres;

--
-- TOC entry 3757 (class 0 OID 0)
-- Dependencies: 222
-- Name: branches_activite_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.branches_activite_id_seq OWNED BY public.branches_activite.id;


--
-- TOC entry 231 (class 1259 OID 264920)
-- Name: campagnes_damo; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.campagnes_damo (
    id integer NOT NULL,
    annee integer NOT NULL,
    libelle character varying(100) NOT NULL,
    date_debut date NOT NULL,
    date_fin date NOT NULL,
    actif boolean DEFAULT true,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer
);


ALTER TABLE public.campagnes_damo OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 264919)
-- Name: campagnes_damo_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.campagnes_damo_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.campagnes_damo_id_seq OWNER TO postgres;

--
-- TOC entry 3758 (class 0 OID 0)
-- Dependencies: 230
-- Name: campagnes_damo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.campagnes_damo_id_seq OWNED BY public.campagnes_damo.id;


--
-- TOC entry 221 (class 1259 OID 264801)
-- Name: communes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.communes (
    id integer NOT NULL,
    departement_id integer NOT NULL,
    nom character varying(100) NOT NULL,
    actif boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.communes OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 264800)
-- Name: communes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.communes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.communes_id_seq OWNER TO postgres;

--
-- TOC entry 3759 (class 0 OID 0)
-- Dependencies: 220
-- Name: communes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.communes_id_seq OWNED BY public.communes.id;


--
-- TOC entry 237 (class 1259 OID 265003)
-- Name: declaration_categories_effectifs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.declaration_categories_effectifs (
    id integer NOT NULL,
    declaration_id integer NOT NULL,
    categorie character varying(50) NOT NULL,
    nigeriens_h integer DEFAULT 0,
    nigeriens_f integer DEFAULT 0,
    africains_h integer DEFAULT 0,
    africains_f integer DEFAULT 0,
    autres_nat_h integer DEFAULT 0,
    autres_nat_f integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT declaration_categories_effectifs_africains_f_check CHECK ((africains_f >= 0)),
    CONSTRAINT declaration_categories_effectifs_africains_h_check CHECK ((africains_h >= 0)),
    CONSTRAINT declaration_categories_effectifs_autres_nat_f_check CHECK ((autres_nat_f >= 0)),
    CONSTRAINT declaration_categories_effectifs_autres_nat_h_check CHECK ((autres_nat_h >= 0)),
    CONSTRAINT declaration_categories_effectifs_categorie_check CHECK (((categorie)::text = ANY ((ARRAY['cadres_superieurs'::character varying, 'agents_maitrise'::character varying, 'employes_bureau'::character varying, 'ouvriers_qualifies'::character varying, 'ouvriers_specialises'::character varying, 'manœuvres'::character varying, 'apprentis_stagiaires'::character varying])::text[]))),
    CONSTRAINT declaration_categories_effectifs_nigeriens_f_check CHECK ((nigeriens_f >= 0)),
    CONSTRAINT declaration_categories_effectifs_nigeriens_h_check CHECK ((nigeriens_h >= 0))
);


ALTER TABLE public.declaration_categories_effectifs OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 265002)
-- Name: declaration_categories_effectifs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.declaration_categories_effectifs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.declaration_categories_effectifs_id_seq OWNER TO postgres;

--
-- TOC entry 3760 (class 0 OID 0)
-- Dependencies: 236
-- Name: declaration_categories_effectifs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.declaration_categories_effectifs_id_seq OWNED BY public.declaration_categories_effectifs.id;


--
-- TOC entry 247 (class 1259 OID 265115)
-- Name: declaration_effectifs_etrangers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.declaration_effectifs_etrangers (
    id integer NOT NULL,
    declaration_id integer NOT NULL,
    pays character varying(100) NOT NULL,
    qualification character varying(100),
    fonction character varying(200),
    sexe character(1),
    nombre integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT declaration_effectifs_etrangers_nombre_check CHECK ((nombre >= 0)),
    CONSTRAINT declaration_effectifs_etrangers_sexe_check CHECK ((sexe = ANY (ARRAY['H'::bpchar, 'F'::bpchar, 'M'::bpchar])))
);


ALTER TABLE public.declaration_effectifs_etrangers OWNER TO postgres;

--
-- TOC entry 246 (class 1259 OID 265114)
-- Name: declaration_effectifs_etrangers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.declaration_effectifs_etrangers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.declaration_effectifs_etrangers_id_seq OWNER TO postgres;

--
-- TOC entry 3761 (class 0 OID 0)
-- Dependencies: 246
-- Name: declaration_effectifs_etrangers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.declaration_effectifs_etrangers_id_seq OWNED BY public.declaration_effectifs_etrangers.id;


--
-- TOC entry 235 (class 1259 OID 264984)
-- Name: declaration_effectifs_mensuels; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.declaration_effectifs_mensuels (
    id integer NOT NULL,
    declaration_id integer NOT NULL,
    mois integer NOT NULL,
    effectif integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT declaration_effectifs_mensuels_effectif_check CHECK ((effectif >= 0)),
    CONSTRAINT declaration_effectifs_mensuels_mois_check CHECK (((mois >= 1) AND (mois <= 12)))
);


ALTER TABLE public.declaration_effectifs_mensuels OWNER TO postgres;

--
-- TOC entry 234 (class 1259 OID 264983)
-- Name: declaration_effectifs_mensuels_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.declaration_effectifs_mensuels_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.declaration_effectifs_mensuels_id_seq OWNER TO postgres;

--
-- TOC entry 3762 (class 0 OID 0)
-- Dependencies: 234
-- Name: declaration_effectifs_mensuels_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.declaration_effectifs_mensuels_id_seq OWNED BY public.declaration_effectifs_mensuels.id;


--
-- TOC entry 241 (class 1259 OID 265054)
-- Name: declaration_formations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.declaration_formations (
    id integer NOT NULL,
    declaration_id integer NOT NULL,
    a_eu_formation boolean DEFAULT false,
    qualification character varying(100),
    nature_formation text,
    duree_formation character varying(100),
    effectif_h integer DEFAULT 0,
    effectif_f integer DEFAULT 0,
    observations text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    ligne_ordre integer DEFAULT 1,
    CONSTRAINT declaration_formations_effectif_f_check CHECK ((effectif_f >= 0)),
    CONSTRAINT declaration_formations_effectif_h_check CHECK ((effectif_h >= 0))
);


ALTER TABLE public.declaration_formations OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 265053)
-- Name: declaration_formations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.declaration_formations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.declaration_formations_id_seq OWNER TO postgres;

--
-- TOC entry 3763 (class 0 OID 0)
-- Dependencies: 240
-- Name: declaration_formations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.declaration_formations_id_seq OWNED BY public.declaration_formations.id;


--
-- TOC entry 239 (class 1259 OID 265032)
-- Name: declaration_niveaux_instruction; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.declaration_niveaux_instruction (
    id integer NOT NULL,
    declaration_id integer NOT NULL,
    categorie character varying(50) NOT NULL,
    niveau character varying(50) NOT NULL,
    effectif_h integer DEFAULT 0,
    effectif_f integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT declaration_niveaux_instruction_categorie_check CHECK (((categorie)::text = ANY ((ARRAY['cadres_superieurs'::character varying, 'agents_maitrise'::character varying, 'employes_bureau'::character varying, 'ouvriers_qualifies'::character varying, 'ouvriers_specialises'::character varying, 'manœuvres'::character varying, 'apprentis_stagiaires'::character varying])::text[]))),
    CONSTRAINT declaration_niveaux_instruction_effectif_f_check CHECK ((effectif_f >= 0)),
    CONSTRAINT declaration_niveaux_instruction_effectif_h_check CHECK ((effectif_h >= 0)),
    CONSTRAINT declaration_niveaux_instruction_niveau_check CHECK (((niveau)::text = ANY ((ARRAY['non_scolarise'::character varying, 'primaire'::character varying, 'secondaire_1er'::character varying, 'secondaire_2eme'::character varying, 'moyen_prof'::character varying, 'superieur_prof'::character varying, 'superieur_1'::character varying, 'superieur_2'::character varying, 'superieur_3'::character varying])::text[])))
);


ALTER TABLE public.declaration_niveaux_instruction OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 265031)
-- Name: declaration_niveaux_instruction_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.declaration_niveaux_instruction_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.declaration_niveaux_instruction_id_seq OWNER TO postgres;

--
-- TOC entry 3764 (class 0 OID 0)
-- Dependencies: 238
-- Name: declaration_niveaux_instruction_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.declaration_niveaux_instruction_id_seq OWNED BY public.declaration_niveaux_instruction.id;


--
-- TOC entry 245 (class 1259 OID 265096)
-- Name: declaration_perspectives; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.declaration_perspectives (
    id integer NOT NULL,
    declaration_id integer NOT NULL,
    perspective character varying(20),
    justification text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT declaration_perspectives_perspective_check CHECK (((perspective)::text = ANY ((ARRAY['hausse'::character varying, 'stabilite'::character varying, 'baisse'::character varying])::text[])))
);


ALTER TABLE public.declaration_perspectives OWNER TO postgres;

--
-- TOC entry 244 (class 1259 OID 265095)
-- Name: declaration_perspectives_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.declaration_perspectives_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.declaration_perspectives_id_seq OWNER TO postgres;

--
-- TOC entry 3765 (class 0 OID 0)
-- Dependencies: 244
-- Name: declaration_perspectives_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.declaration_perspectives_id_seq OWNED BY public.declaration_perspectives.id;


--
-- TOC entry 243 (class 1259 OID 265075)
-- Name: declaration_pertes_emploi; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.declaration_pertes_emploi (
    id integer NOT NULL,
    declaration_id integer NOT NULL,
    motif character varying(50) NOT NULL,
    motif_autre character varying(200),
    effectif_h integer DEFAULT 0,
    effectif_f integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT declaration_pertes_emploi_effectif_f_check CHECK ((effectif_f >= 0)),
    CONSTRAINT declaration_pertes_emploi_effectif_h_check CHECK ((effectif_h >= 0)),
    CONSTRAINT declaration_pertes_emploi_motif_check CHECK (((motif)::text = ANY ((ARRAY['licenciement'::character varying, 'demission'::character varying, 'fin_contrat'::character varying, 'retraite'::character varying, 'deces'::character varying, 'autres'::character varying])::text[])))
);


ALTER TABLE public.declaration_pertes_emploi OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 265074)
-- Name: declaration_pertes_emploi_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.declaration_pertes_emploi_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.declaration_pertes_emploi_id_seq OWNER TO postgres;

--
-- TOC entry 3766 (class 0 OID 0)
-- Dependencies: 242
-- Name: declaration_pertes_emploi_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.declaration_pertes_emploi_id_seq OWNED BY public.declaration_pertes_emploi.id;


--
-- TOC entry 233 (class 1259 OID 264939)
-- Name: declarations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.declarations (
    id integer NOT NULL,
    uuid uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    code_questionnaire character varying(50),
    campagne_id integer NOT NULL,
    entreprise_id integer NOT NULL,
    agent_id integer NOT NULL,
    region_id integer NOT NULL,
    nom_enqueteur character varying(200),
    statut character varying(20) DEFAULT 'brouillon'::character varying,
    date_soumission timestamp without time zone,
    date_validation timestamp without time zone,
    date_rejet timestamp without time zone,
    motif_rejet text,
    observations text,
    masse_salariale numeric(20,2),
    validateur_id integer,
    ip_saisie character varying(45),
    etape_courante integer DEFAULT 1,
    pourcentage_completion integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    submitted_at timestamp without time zone,
    total_nigeriens integer DEFAULT 0,
    total_etrangers integer DEFAULT 0,
    CONSTRAINT declarations_statut_check CHECK (((statut)::text = ANY ((ARRAY['brouillon'::character varying, 'soumise'::character varying, 'validee'::character varying, 'rejetee'::character varying, 'corrigee'::character varying])::text[])))
);


ALTER TABLE public.declarations OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 264938)
-- Name: declarations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.declarations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.declarations_id_seq OWNER TO postgres;

--
-- TOC entry 3767 (class 0 OID 0)
-- Dependencies: 232
-- Name: declarations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.declarations_id_seq OWNED BY public.declarations.id;


--
-- TOC entry 219 (class 1259 OID 264786)
-- Name: departements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.departements (
    id integer NOT NULL,
    region_id integer NOT NULL,
    nom character varying(100) NOT NULL,
    actif boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.departements OWNER TO postgres;

--
-- TOC entry 218 (class 1259 OID 264785)
-- Name: departements_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.departements_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.departements_id_seq OWNER TO postgres;

--
-- TOC entry 3768 (class 0 OID 0)
-- Dependencies: 218
-- Name: departements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.departements_id_seq OWNED BY public.departements.id;


--
-- TOC entry 229 (class 1259 OID 264875)
-- Name: entreprises; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.entreprises (
    id integer NOT NULL,
    uuid uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    raison_sociale character varying(255) NOT NULL,
    nationalite character varying(100),
    activite_principale text,
    activites_secondaires text,
    branche_id integer,
    region_id integer NOT NULL,
    departement_id integer,
    commune_id integer,
    localite character varying(200),
    quartier character varying(200),
    adresse text,
    boite_postale character varying(50),
    telephone character varying(20),
    fax character varying(20),
    email character varying(255),
    numero_cnss character varying(50),
    agent_id integer,
    actif boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer
);


ALTER TABLE public.entreprises OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 264874)
-- Name: entreprises_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.entreprises_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.entreprises_id_seq OWNER TO postgres;

--
-- TOC entry 3769 (class 0 OID 0)
-- Dependencies: 228
-- Name: entreprises_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.entreprises_id_seq OWNED BY public.entreprises.id;


--
-- TOC entry 255 (class 1259 OID 265190)
-- Name: guides_documents; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.guides_documents (
    id integer NOT NULL,
    titre character varying(255) NOT NULL,
    description text,
    fichier_nom character varying(255) NOT NULL,
    fichier_path character varying(500) NOT NULL,
    fichier_taille integer,
    fichier_type character varying(100),
    annee integer,
    actif boolean DEFAULT true,
    ordre integer DEFAULT 0,
    telechargements integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer
);


ALTER TABLE public.guides_documents OWNER TO postgres;

--
-- TOC entry 254 (class 1259 OID 265189)
-- Name: guides_documents_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.guides_documents_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.guides_documents_id_seq OWNER TO postgres;

--
-- TOC entry 3770 (class 0 OID 0)
-- Dependencies: 254
-- Name: guides_documents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.guides_documents_id_seq OWNED BY public.guides_documents.id;


--
-- TOC entry 249 (class 1259 OID 265132)
-- Name: historique_declarations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.historique_declarations (
    id integer NOT NULL,
    declaration_id integer NOT NULL,
    utilisateur_id integer,
    action character varying(50) NOT NULL,
    ancien_statut character varying(20),
    nouveau_statut character varying(20),
    details jsonb,
    ip_address character varying(45),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.historique_declarations OWNER TO postgres;

--
-- TOC entry 248 (class 1259 OID 265131)
-- Name: historique_declarations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.historique_declarations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.historique_declarations_id_seq OWNER TO postgres;

--
-- TOC entry 3771 (class 0 OID 0)
-- Dependencies: 248
-- Name: historique_declarations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.historique_declarations_id_seq OWNED BY public.historique_declarations.id;


--
-- TOC entry 257 (class 1259 OID 265209)
-- Name: logs_activite; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.logs_activite (
    id bigint NOT NULL,
    utilisateur_id integer,
    action character varying(100) NOT NULL,
    ressource character varying(100),
    ressource_id integer,
    details jsonb,
    ip_address character varying(45),
    user_agent text,
    statut character varying(20) DEFAULT 'success'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT logs_activite_statut_check CHECK (((statut)::text = ANY ((ARRAY['success'::character varying, 'failure'::character varying, 'warning'::character varying])::text[])))
);


ALTER TABLE public.logs_activite OWNER TO postgres;

--
-- TOC entry 256 (class 1259 OID 265208)
-- Name: logs_activite_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.logs_activite_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.logs_activite_id_seq OWNER TO postgres;

--
-- TOC entry 3772 (class 0 OID 0)
-- Dependencies: 256
-- Name: logs_activite_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.logs_activite_id_seq OWNED BY public.logs_activite.id;


--
-- TOC entry 251 (class 1259 OID 265152)
-- Name: notifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.notifications (
    id integer NOT NULL,
    utilisateur_id integer NOT NULL,
    titre character varying(255) NOT NULL,
    message text NOT NULL,
    type character varying(50) DEFAULT 'info'::character varying,
    lu boolean DEFAULT false,
    lu_at timestamp without time zone,
    lien character varying(500),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT notifications_type_check CHECK (((type)::text = ANY ((ARRAY['info'::character varying, 'success'::character varying, 'warning'::character varying, 'error'::character varying])::text[])))
);


ALTER TABLE public.notifications OWNER TO postgres;

--
-- TOC entry 250 (class 1259 OID 265151)
-- Name: notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.notifications_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.notifications_id_seq OWNER TO postgres;

--
-- TOC entry 3773 (class 0 OID 0)
-- Dependencies: 250
-- Name: notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.notifications_id_seq OWNED BY public.notifications.id;


--
-- TOC entry 253 (class 1259 OID 265170)
-- Name: parametres; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.parametres (
    id integer NOT NULL,
    cle character varying(100) NOT NULL,
    valeur text,
    description text,
    type character varying(20) DEFAULT 'string'::character varying,
    modifiable boolean DEFAULT true,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by integer,
    CONSTRAINT parametres_type_check CHECK (((type)::text = ANY ((ARRAY['string'::character varying, 'integer'::character varying, 'boolean'::character varying, 'json'::character varying])::text[])))
);


ALTER TABLE public.parametres OWNER TO postgres;

--
-- TOC entry 252 (class 1259 OID 265169)
-- Name: parametres_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.parametres_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.parametres_id_seq OWNER TO postgres;

--
-- TOC entry 3774 (class 0 OID 0)
-- Dependencies: 252
-- Name: parametres_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.parametres_id_seq OWNED BY public.parametres.id;


--
-- TOC entry 217 (class 1259 OID 264774)
-- Name: regions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.regions (
    id integer NOT NULL,
    code character varying(5) NOT NULL,
    nom character varying(100) NOT NULL,
    actif boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.regions OWNER TO postgres;

--
-- TOC entry 216 (class 1259 OID 264773)
-- Name: regions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.regions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.regions_id_seq OWNER TO postgres;

--
-- TOC entry 3775 (class 0 OID 0)
-- Dependencies: 216
-- Name: regions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.regions_id_seq OWNED BY public.regions.id;


--
-- TOC entry 227 (class 1259 OID 264859)
-- Name: sessions_utilisateurs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions_utilisateurs (
    id integer NOT NULL,
    utilisateur_id integer NOT NULL,
    session_id character varying(255) NOT NULL,
    ip_address character varying(45),
    user_agent text,
    debut timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    fin timestamp without time zone,
    actif boolean DEFAULT true
);


ALTER TABLE public.sessions_utilisateurs OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 264858)
-- Name: sessions_utilisateurs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sessions_utilisateurs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sessions_utilisateurs_id_seq OWNER TO postgres;

--
-- TOC entry 3776 (class 0 OID 0)
-- Dependencies: 226
-- Name: sessions_utilisateurs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sessions_utilisateurs_id_seq OWNED BY public.sessions_utilisateurs.id;


--
-- TOC entry 225 (class 1259 OID 264829)
-- Name: utilisateurs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.utilisateurs (
    id integer NOT NULL,
    uuid uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    nom character varying(100) NOT NULL,
    prenom character varying(100),
    email character varying(255) NOT NULL,
    telephone character varying(20),
    mot_de_passe character varying(255) NOT NULL,
    role character varying(20) NOT NULL,
    region_id integer,
    actif boolean DEFAULT true,
    email_verifie boolean DEFAULT false,
    token_verification character varying(100),
    token_reset character varying(100),
    token_reset_expiry timestamp without time zone,
    derniere_connexion timestamp without time zone,
    tentatives_connexion integer DEFAULT 0,
    bloque_jusqu_a timestamp without time zone,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by integer,
    CONSTRAINT utilisateurs_role_check CHECK (((role)::text = ANY ((ARRAY['super_admin'::character varying, 'admin'::character varying, 'agent'::character varying])::text[])))
);


ALTER TABLE public.utilisateurs OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 264828)
-- Name: utilisateurs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.utilisateurs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.utilisateurs_id_seq OWNER TO postgres;

--
-- TOC entry 3777 (class 0 OID 0)
-- Dependencies: 224
-- Name: utilisateurs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.utilisateurs_id_seq OWNED BY public.utilisateurs.id;


--
-- TOC entry 3334 (class 2604 OID 264819)
-- Name: branches_activite id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.branches_activite ALTER COLUMN id SET DEFAULT nextval('public.branches_activite_id_seq'::regclass);


--
-- TOC entry 3352 (class 2604 OID 264923)
-- Name: campagnes_damo id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.campagnes_damo ALTER COLUMN id SET DEFAULT nextval('public.campagnes_damo_id_seq'::regclass);


--
-- TOC entry 3330 (class 2604 OID 264804)
-- Name: communes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.communes ALTER COLUMN id SET DEFAULT nextval('public.communes_id_seq'::regclass);


--
-- TOC entry 3369 (class 2604 OID 265006)
-- Name: declaration_categories_effectifs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_categories_effectifs ALTER COLUMN id SET DEFAULT nextval('public.declaration_categories_effectifs_id_seq'::regclass);


--
-- TOC entry 3398 (class 2604 OID 265118)
-- Name: declaration_effectifs_etrangers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_effectifs_etrangers ALTER COLUMN id SET DEFAULT nextval('public.declaration_effectifs_etrangers_id_seq'::regclass);


--
-- TOC entry 3365 (class 2604 OID 264987)
-- Name: declaration_effectifs_mensuels id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_effectifs_mensuels ALTER COLUMN id SET DEFAULT nextval('public.declaration_effectifs_mensuels_id_seq'::regclass);


--
-- TOC entry 3383 (class 2604 OID 265057)
-- Name: declaration_formations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_formations ALTER COLUMN id SET DEFAULT nextval('public.declaration_formations_id_seq'::regclass);


--
-- TOC entry 3378 (class 2604 OID 265035)
-- Name: declaration_niveaux_instruction id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_niveaux_instruction ALTER COLUMN id SET DEFAULT nextval('public.declaration_niveaux_instruction_id_seq'::regclass);


--
-- TOC entry 3395 (class 2604 OID 265099)
-- Name: declaration_perspectives id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_perspectives ALTER COLUMN id SET DEFAULT nextval('public.declaration_perspectives_id_seq'::regclass);


--
-- TOC entry 3390 (class 2604 OID 265078)
-- Name: declaration_pertes_emploi id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_pertes_emploi ALTER COLUMN id SET DEFAULT nextval('public.declaration_pertes_emploi_id_seq'::regclass);


--
-- TOC entry 3356 (class 2604 OID 264942)
-- Name: declarations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declarations ALTER COLUMN id SET DEFAULT nextval('public.declarations_id_seq'::regclass);


--
-- TOC entry 3326 (class 2604 OID 264789)
-- Name: departements id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departements ALTER COLUMN id SET DEFAULT nextval('public.departements_id_seq'::regclass);


--
-- TOC entry 3347 (class 2604 OID 264878)
-- Name: entreprises id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entreprises ALTER COLUMN id SET DEFAULT nextval('public.entreprises_id_seq'::regclass);


--
-- TOC entry 3412 (class 2604 OID 265193)
-- Name: guides_documents id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.guides_documents ALTER COLUMN id SET DEFAULT nextval('public.guides_documents_id_seq'::regclass);


--
-- TOC entry 3402 (class 2604 OID 265135)
-- Name: historique_declarations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historique_declarations ALTER COLUMN id SET DEFAULT nextval('public.historique_declarations_id_seq'::regclass);


--
-- TOC entry 3418 (class 2604 OID 265212)
-- Name: logs_activite id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.logs_activite ALTER COLUMN id SET DEFAULT nextval('public.logs_activite_id_seq'::regclass);


--
-- TOC entry 3404 (class 2604 OID 265155)
-- Name: notifications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications ALTER COLUMN id SET DEFAULT nextval('public.notifications_id_seq'::regclass);


--
-- TOC entry 3408 (class 2604 OID 265173)
-- Name: parametres id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parametres ALTER COLUMN id SET DEFAULT nextval('public.parametres_id_seq'::regclass);


--
-- TOC entry 3322 (class 2604 OID 264777)
-- Name: regions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.regions ALTER COLUMN id SET DEFAULT nextval('public.regions_id_seq'::regclass);


--
-- TOC entry 3344 (class 2604 OID 264862)
-- Name: sessions_utilisateurs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions_utilisateurs ALTER COLUMN id SET DEFAULT nextval('public.sessions_utilisateurs_id_seq'::regclass);


--
-- TOC entry 3337 (class 2604 OID 264832)
-- Name: utilisateurs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.utilisateurs ALTER COLUMN id SET DEFAULT nextval('public.utilisateurs_id_seq'::regclass);


--
-- TOC entry 3715 (class 0 OID 264816)
-- Dependencies: 223
-- Data for Name: branches_activite; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.branches_activite (id, code, libelle, description, actif, created_at) FROM stdin;
1	B1	Agriculture, Élevage, Chasse et Sylviculture	Activités agricoles et d'élevage	t	2026-03-02 16:50:28.576914
2	B2	Pêche	Activités de pêche et pisciculture	t	2026-03-02 16:50:28.576914
3	B3	Industries extractives et mines	Extraction minière, pétrole, gaz	t	2026-03-02 16:50:28.576914
4	B4	Industries manufacturières	Transformation industrielle	t	2026-03-02 16:50:28.576914
5	B5	Electricité, Gaz et Eau	Production et distribution d'énergie et d'eau	t	2026-03-02 16:50:28.576914
6	B6	Construction et BTP	Travaux de construction et bâtiment	t	2026-03-02 16:50:28.576914
7	B7	Commerce, Restauration et Hôtellerie	Commerce et tourisme	t	2026-03-02 16:50:28.576914
8	B8	Transport, Entreposage et Communications	Logistique et télécommunications	t	2026-03-02 16:50:28.576914
9	B9	Services	Services financiers, éducation, santé, administration	t	2026-03-02 16:50:28.576914
\.


--
-- TOC entry 3723 (class 0 OID 264920)
-- Dependencies: 231
-- Data for Name: campagnes_damo; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.campagnes_damo (id, annee, libelle, date_debut, date_fin, actif, description, created_at, updated_at, created_by) FROM stdin;
1	2025	Déclaration Annuelle 2025	2025-01-01	2025-03-31	t	Campagne de collecte de la Déclaration Annuelle de la Main d'Œuvre pour l'année 2025	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914	\N
\.


--
-- TOC entry 3713 (class 0 OID 264801)
-- Dependencies: 221
-- Data for Name: communes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.communes (id, departement_id, nom, actif, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3729 (class 0 OID 265003)
-- Dependencies: 237
-- Data for Name: declaration_categories_effectifs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.declaration_categories_effectifs (id, declaration_id, categorie, nigeriens_h, nigeriens_f, africains_h, africains_f, autres_nat_h, autres_nat_f, created_at, updated_at) FROM stdin;
1	1	cadres_superieurs	0	0	0	0	0	0	2026-03-02 19:24:02.733711	2026-03-02 19:24:02.733711
2	1	agents_maitrise	0	0	0	0	0	0	2026-03-02 19:24:02.742181	2026-03-02 19:24:02.742181
3	1	employes_bureau	0	0	0	0	0	0	2026-03-02 19:24:02.745728	2026-03-02 19:24:02.745728
4	1	ouvriers_qualifies	0	0	0	0	0	0	2026-03-02 19:24:02.748872	2026-03-02 19:24:02.748872
5	1	ouvriers_specialises	0	0	0	0	0	0	2026-03-02 19:24:02.752894	2026-03-02 19:24:02.752894
6	1	manœuvres	0	0	0	0	0	0	2026-03-02 19:24:02.756023	2026-03-02 19:24:02.756023
7	1	apprentis_stagiaires	0	0	0	0	0	0	2026-03-02 19:24:02.759553	2026-03-02 19:24:02.759553
\.


--
-- TOC entry 3739 (class 0 OID 265115)
-- Dependencies: 247
-- Data for Name: declaration_effectifs_etrangers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.declaration_effectifs_etrangers (id, declaration_id, pays, qualification, fonction, sexe, nombre, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3727 (class 0 OID 264984)
-- Dependencies: 235
-- Data for Name: declaration_effectifs_mensuels; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.declaration_effectifs_mensuels (id, declaration_id, mois, effectif, created_at, updated_at) FROM stdin;
1	1	1	0	2026-03-02 19:24:02.727891	2026-03-02 19:24:02.727891
2	1	2	0	2026-03-02 19:24:02.730035	2026-03-02 19:24:02.730035
3	1	3	0	2026-03-02 19:24:02.730412	2026-03-02 19:24:02.730412
4	1	4	0	2026-03-02 19:24:02.730737	2026-03-02 19:24:02.730737
5	1	5	0	2026-03-02 19:24:02.731068	2026-03-02 19:24:02.731068
6	1	6	0	2026-03-02 19:24:02.731465	2026-03-02 19:24:02.731465
7	1	7	0	2026-03-02 19:24:02.731835	2026-03-02 19:24:02.731835
8	1	8	0	2026-03-02 19:24:02.732479	2026-03-02 19:24:02.732479
9	1	9	0	2026-03-02 19:24:02.732745	2026-03-02 19:24:02.732745
10	1	10	0	2026-03-02 19:24:02.73299	2026-03-02 19:24:02.73299
11	1	11	0	2026-03-02 19:24:02.733205	2026-03-02 19:24:02.733205
12	1	12	0	2026-03-02 19:24:02.733429	2026-03-02 19:24:02.733429
\.


--
-- TOC entry 3733 (class 0 OID 265054)
-- Dependencies: 241
-- Data for Name: declaration_formations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.declaration_formations (id, declaration_id, a_eu_formation, qualification, nature_formation, duree_formation, effectif_h, effectif_f, observations, created_at, updated_at, ligne_ordre) FROM stdin;
1	1	f	\N	\N	\N	0	0	\N	2026-03-02 19:24:02.771772	2026-03-02 19:24:02.771772	1
\.


--
-- TOC entry 3731 (class 0 OID 265032)
-- Dependencies: 239
-- Data for Name: declaration_niveaux_instruction; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.declaration_niveaux_instruction (id, declaration_id, categorie, niveau, effectif_h, effectif_f, created_at, updated_at) FROM stdin;
1	1	cadres_superieurs	non_scolarise	0	0	2026-03-02 19:24:02.736174	2026-03-02 19:24:02.736174
2	1	cadres_superieurs	primaire	0	0	2026-03-02 19:24:02.739048	2026-03-02 19:24:02.739048
3	1	cadres_superieurs	secondaire_1er	0	0	2026-03-02 19:24:02.739661	2026-03-02 19:24:02.739661
4	1	cadres_superieurs	secondaire_2eme	0	0	2026-03-02 19:24:02.740065	2026-03-02 19:24:02.740065
5	1	cadres_superieurs	moyen_prof	0	0	2026-03-02 19:24:02.740521	2026-03-02 19:24:02.740521
6	1	cadres_superieurs	superieur_prof	0	0	2026-03-02 19:24:02.740986	2026-03-02 19:24:02.740986
7	1	cadres_superieurs	superieur_1	0	0	2026-03-02 19:24:02.74134	2026-03-02 19:24:02.74134
8	1	cadres_superieurs	superieur_2	0	0	2026-03-02 19:24:02.741645	2026-03-02 19:24:02.741645
9	1	cadres_superieurs	superieur_3	0	0	2026-03-02 19:24:02.741921	2026-03-02 19:24:02.741921
10	1	agents_maitrise	non_scolarise	0	0	2026-03-02 19:24:02.742584	2026-03-02 19:24:02.742584
11	1	agents_maitrise	primaire	0	0	2026-03-02 19:24:02.742853	2026-03-02 19:24:02.742853
12	1	agents_maitrise	secondaire_1er	0	0	2026-03-02 19:24:02.74311	2026-03-02 19:24:02.74311
13	1	agents_maitrise	secondaire_2eme	0	0	2026-03-02 19:24:02.743483	2026-03-02 19:24:02.743483
14	1	agents_maitrise	moyen_prof	0	0	2026-03-02 19:24:02.743838	2026-03-02 19:24:02.743838
15	1	agents_maitrise	superieur_prof	0	0	2026-03-02 19:24:02.744143	2026-03-02 19:24:02.744143
16	1	agents_maitrise	superieur_1	0	0	2026-03-02 19:24:02.744502	2026-03-02 19:24:02.744502
17	1	agents_maitrise	superieur_2	0	0	2026-03-02 19:24:02.744872	2026-03-02 19:24:02.744872
18	1	agents_maitrise	superieur_3	0	0	2026-03-02 19:24:02.745246	2026-03-02 19:24:02.745246
19	1	employes_bureau	non_scolarise	0	0	2026-03-02 19:24:02.746262	2026-03-02 19:24:02.746262
20	1	employes_bureau	primaire	0	0	2026-03-02 19:24:02.746628	2026-03-02 19:24:02.746628
21	1	employes_bureau	secondaire_1er	0	0	2026-03-02 19:24:02.746906	2026-03-02 19:24:02.746906
22	1	employes_bureau	secondaire_2eme	0	0	2026-03-02 19:24:02.7472	2026-03-02 19:24:02.7472
23	1	employes_bureau	moyen_prof	0	0	2026-03-02 19:24:02.747463	2026-03-02 19:24:02.747463
24	1	employes_bureau	superieur_prof	0	0	2026-03-02 19:24:02.747722	2026-03-02 19:24:02.747722
25	1	employes_bureau	superieur_1	0	0	2026-03-02 19:24:02.748002	2026-03-02 19:24:02.748002
26	1	employes_bureau	superieur_2	0	0	2026-03-02 19:24:02.748325	2026-03-02 19:24:02.748325
27	1	employes_bureau	superieur_3	0	0	2026-03-02 19:24:02.7486	2026-03-02 19:24:02.7486
28	1	ouvriers_qualifies	non_scolarise	0	0	2026-03-02 19:24:02.749236	2026-03-02 19:24:02.749236
29	1	ouvriers_qualifies	primaire	0	0	2026-03-02 19:24:02.749632	2026-03-02 19:24:02.749632
30	1	ouvriers_qualifies	secondaire_1er	0	0	2026-03-02 19:24:02.749989	2026-03-02 19:24:02.749989
31	1	ouvriers_qualifies	secondaire_2eme	0	0	2026-03-02 19:24:02.750814	2026-03-02 19:24:02.750814
32	1	ouvriers_qualifies	moyen_prof	0	0	2026-03-02 19:24:02.751286	2026-03-02 19:24:02.751286
33	1	ouvriers_qualifies	superieur_prof	0	0	2026-03-02 19:24:02.751666	2026-03-02 19:24:02.751666
34	1	ouvriers_qualifies	superieur_1	0	0	2026-03-02 19:24:02.752063	2026-03-02 19:24:02.752063
35	1	ouvriers_qualifies	superieur_2	0	0	2026-03-02 19:24:02.752344	2026-03-02 19:24:02.752344
36	1	ouvriers_qualifies	superieur_3	0	0	2026-03-02 19:24:02.752602	2026-03-02 19:24:02.752602
37	1	ouvriers_specialises	non_scolarise	0	0	2026-03-02 19:24:02.75326	2026-03-02 19:24:02.75326
38	1	ouvriers_specialises	primaire	0	0	2026-03-02 19:24:02.753547	2026-03-02 19:24:02.753547
39	1	ouvriers_specialises	secondaire_1er	0	0	2026-03-02 19:24:02.753808	2026-03-02 19:24:02.753808
40	1	ouvriers_specialises	secondaire_2eme	0	0	2026-03-02 19:24:02.754096	2026-03-02 19:24:02.754096
41	1	ouvriers_specialises	moyen_prof	0	0	2026-03-02 19:24:02.754383	2026-03-02 19:24:02.754383
42	1	ouvriers_specialises	superieur_prof	0	0	2026-03-02 19:24:02.754656	2026-03-02 19:24:02.754656
43	1	ouvriers_specialises	superieur_1	0	0	2026-03-02 19:24:02.75493	2026-03-02 19:24:02.75493
44	1	ouvriers_specialises	superieur_2	0	0	2026-03-02 19:24:02.755213	2026-03-02 19:24:02.755213
45	1	ouvriers_specialises	superieur_3	0	0	2026-03-02 19:24:02.755608	2026-03-02 19:24:02.755608
46	1	manœuvres	non_scolarise	0	0	2026-03-02 19:24:02.756588	2026-03-02 19:24:02.756588
47	1	manœuvres	primaire	0	0	2026-03-02 19:24:02.756975	2026-03-02 19:24:02.756975
48	1	manœuvres	secondaire_1er	0	0	2026-03-02 19:24:02.757423	2026-03-02 19:24:02.757423
49	1	manœuvres	secondaire_2eme	0	0	2026-03-02 19:24:02.757847	2026-03-02 19:24:02.757847
50	1	manœuvres	moyen_prof	0	0	2026-03-02 19:24:02.758145	2026-03-02 19:24:02.758145
51	1	manœuvres	superieur_prof	0	0	2026-03-02 19:24:02.758417	2026-03-02 19:24:02.758417
52	1	manœuvres	superieur_1	0	0	2026-03-02 19:24:02.758693	2026-03-02 19:24:02.758693
53	1	manœuvres	superieur_2	0	0	2026-03-02 19:24:02.758997	2026-03-02 19:24:02.758997
54	1	manœuvres	superieur_3	0	0	2026-03-02 19:24:02.759276	2026-03-02 19:24:02.759276
55	1	apprentis_stagiaires	non_scolarise	0	0	2026-03-02 19:24:02.759863	2026-03-02 19:24:02.759863
56	1	apprentis_stagiaires	primaire	0	0	2026-03-02 19:24:02.760171	2026-03-02 19:24:02.760171
57	1	apprentis_stagiaires	secondaire_1er	0	0	2026-03-02 19:24:02.76047	2026-03-02 19:24:02.76047
58	1	apprentis_stagiaires	secondaire_2eme	0	0	2026-03-02 19:24:02.760818	2026-03-02 19:24:02.760818
59	1	apprentis_stagiaires	moyen_prof	0	0	2026-03-02 19:24:02.761174	2026-03-02 19:24:02.761174
60	1	apprentis_stagiaires	superieur_prof	0	0	2026-03-02 19:24:02.761572	2026-03-02 19:24:02.761572
61	1	apprentis_stagiaires	superieur_1	0	0	2026-03-02 19:24:02.762039	2026-03-02 19:24:02.762039
62	1	apprentis_stagiaires	superieur_2	0	0	2026-03-02 19:24:02.762454	2026-03-02 19:24:02.762454
63	1	apprentis_stagiaires	superieur_3	0	0	2026-03-02 19:24:02.762842	2026-03-02 19:24:02.762842
\.


--
-- TOC entry 3737 (class 0 OID 265096)
-- Dependencies: 245
-- Data for Name: declaration_perspectives; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.declaration_perspectives (id, declaration_id, perspective, justification, created_at, updated_at) FROM stdin;
1	1	\N	\N	2026-03-02 19:24:02.768927	2026-03-02 19:24:02.768927
\.


--
-- TOC entry 3735 (class 0 OID 265075)
-- Dependencies: 243
-- Data for Name: declaration_pertes_emploi; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.declaration_pertes_emploi (id, declaration_id, motif, motif_autre, effectif_h, effectif_f, created_at, updated_at) FROM stdin;
1	1	licenciement	\N	0	0	2026-03-02 19:24:02.763207	2026-03-02 19:24:02.763207
2	1	demission	\N	0	0	2026-03-02 19:24:02.766217	2026-03-02 19:24:02.766217
3	1	fin_contrat	\N	0	0	2026-03-02 19:24:02.767079	2026-03-02 19:24:02.767079
4	1	retraite	\N	0	0	2026-03-02 19:24:02.767452	2026-03-02 19:24:02.767452
5	1	deces	\N	0	0	2026-03-02 19:24:02.768119	2026-03-02 19:24:02.768119
6	1	autres	\N	0	0	2026-03-02 19:24:02.768594	2026-03-02 19:24:02.768594
\.


--
-- TOC entry 3725 (class 0 OID 264939)
-- Dependencies: 233
-- Data for Name: declarations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.declarations (id, uuid, code_questionnaire, campagne_id, entreprise_id, agent_id, region_id, nom_enqueteur, statut, date_soumission, date_validation, date_rejet, motif_rejet, observations, masse_salariale, validateur_id, ip_saisie, etape_courante, pourcentage_completion, created_at, updated_at, submitted_at, total_nigeriens, total_etrangers) FROM stdin;
1	879000fa-5926-4ffd-bdd4-31d5b915ecc3	4/01/001	1	1	5	4	Fati ISSAKA	brouillon	\N	\N	\N	\N	\N	\N	\N	127.0.0.1	1	0	2026-03-02 19:24:02.712831	2026-03-02 19:24:02.712831	\N	0	0
\.


--
-- TOC entry 3711 (class 0 OID 264786)
-- Dependencies: 219
-- Data for Name: departements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.departements (id, region_id, nom, actif, created_at, updated_at) FROM stdin;
1	1	Agadez	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
2	1	Arlit	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
3	1	Bilma	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
4	1	Tchirozerine	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
5	2	Diffa	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
6	2	Maine-Soroa	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
7	2	Nguigmi	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
8	3	Boboye	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
9	3	Dosso	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
10	3	Gaya	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
11	3	Loga	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
12	3	Doutchi	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
13	4	Aguié	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
14	4	Dakoro	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
15	4	Guidan-Roumdji	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
16	4	Madarounfa	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
17	4	Maradi	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
18	4	Mayahi	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
19	4	Tessaoua	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
20	5	Abalak	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
21	5	Birni-N'Konni	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
22	5	Bouza	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
23	5	Illéla	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
24	5	Keïta	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
25	5	Madaoua	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
26	5	Tahoua	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
27	5	Tchintabaraden	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
28	6	Filingué	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
29	6	Kollo	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
30	6	Ouallam	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
31	6	Say	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
32	6	Tera	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
33	6	Tillabéri	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
34	6	Torodi	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
35	7	Gouré	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
36	7	Magaria	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
37	7	Matameye	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
38	7	Mirriah	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
39	7	Tanout	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
40	7	Zinder	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
41	8	Niamey I	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
42	8	Niamey II	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
43	8	Niamey III	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
44	8	Niamey IV	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
45	8	Niamey V	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
\.


--
-- TOC entry 3721 (class 0 OID 264875)
-- Dependencies: 229
-- Data for Name: entreprises; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.entreprises (id, uuid, raison_sociale, nationalite, activite_principale, activites_secondaires, branche_id, region_id, departement_id, commune_id, localite, quartier, adresse, boite_postale, telephone, fax, email, numero_cnss, agent_id, actif, created_at, updated_at, created_by) FROM stdin;
1	fe8738e8-0df7-4933-a3df-5f980fdbb83b	ZFSFSFSFS	Nigérienne			1	4	13	\N			\N						5	t	2026-03-02 19:22:57.137783	2026-03-02 19:23:33.254836	5
\.


--
-- TOC entry 3747 (class 0 OID 265190)
-- Dependencies: 255
-- Data for Name: guides_documents; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.guides_documents (id, titre, description, fichier_nom, fichier_path, fichier_taille, fichier_type, annee, actif, ordre, telechargements, created_at, updated_at, created_by) FROM stdin;
1	Guide de remplissage du formulaire DAMO 2026	Guide officiel de remplissage du Formulaire de Déclaration Annuelle de la Main d'Œuvre (DAMO). Ce document explique étape par étape comment compléter correctement chaque section du formulaire RAMO.	guide_damo_2026.pdf	uploads/guides/guide_damo_2026.pdf	864167	application/pdf	2026	t	1	2	2026-03-02 17:49:47.786679	2026-03-02 17:49:47.786679	\N
\.


--
-- TOC entry 3741 (class 0 OID 265132)
-- Dependencies: 249
-- Data for Name: historique_declarations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.historique_declarations (id, declaration_id, utilisateur_id, action, ancien_statut, nouveau_statut, details, ip_address, created_at) FROM stdin;
\.


--
-- TOC entry 3749 (class 0 OID 265209)
-- Dependencies: 257
-- Data for Name: logs_activite; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.logs_activite (id, utilisateur_id, action, ressource, ressource_id, details, ip_address, user_agent, statut, created_at) FROM stdin;
1	\N	login_failed	auth	\N	{"email": "admin@edamo.ne", "reason": "user_not_found"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	failure	2026-03-02 16:35:48.15884
2	\N	login_failed	auth	1	{"email": "admin@anpe-niger.ne", "reason": "wrong_password"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	failure	2026-03-02 16:36:50.17468
3	\N	login_failed	auth	1	{"email": "admin@anpe-niger.ne", "reason": "wrong_password"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	failure	2026-03-02 16:50:45.847772
4	\N	login_failed	auth	1	{"email": "admin@anpe-niger.ne", "reason": "wrong_password"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	failure	2026-03-02 16:51:08.288562
5	\N	login_failed	auth	2	{"email": "admin@anpe-niger.ne", "reason": "wrong_password"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	failure	2026-03-02 16:52:36.952188
6	3	login_success	auth	3	{"email": "super@anpe-niger.ne"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 16:58:44.155474
7	3	logout	auth	3	[]	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 17:14:10.217884
8	4	login_success	auth	4	{"email": "admin@anpe-niger.ne"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 17:14:14.265389
9	4	logout	auth	4	[]	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 17:43:18.269136
10	4	login_success	auth	4	{"email": "admin@anpe-niger.ne"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 17:56:24.981353
11	4	logout	auth	4	[]	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 18:27:18.144048
12	3	login_success	auth	3	{"email": "super@anpe-niger.ne"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 18:27:31.668608
13	3	logout	auth	3	[]	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 18:46:14.740351
14	\N	login_failed	auth	5	{"email": "agent@anpe-niger.ne", "reason": "wrong_password"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	failure	2026-03-02 18:46:31.139351
15	\N	login_failed	auth	5	{"email": "agent@anpe-niger.ne", "reason": "wrong_password"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	failure	2026-03-02 18:46:43.545981
16	5	login_success	auth	5	{"email": "agent@anpe-niger.ne"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 18:47:17.315293
17	5	logout	auth	5	[]	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 18:48:58.176491
18	4	login_success	auth	4	{"email": "admin@anpe-niger.ne"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 18:49:02.418557
19	4	user_updated	utilisateurs	5	[]	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 19:03:18.341786
20	4	logout	auth	4	[]	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 19:03:33.319495
21	5	login_success	auth	5	{"email": "agent@anpe-niger.ne"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 19:03:39.271249
22	5	logout	auth	5	[]	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 19:04:36.377739
23	5	login_success	auth	5	{"email": "agent@anpe-niger.ne"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 19:04:45.229092
24	5	enterprise_created	entreprises	\N	{"raison_sociale": "ZFSFSFSFS"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 19:22:57.155462
25	5	enterprise_updated	entreprises	1	[]	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 19:23:33.268208
26	5	declaration_created	declarations	1	{"code": "4/01/001"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-02 19:24:02.773461
27	4	login_success	auth	4	{"email": "admin@anpe-niger.ne"}	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	success	2026-03-03 09:28:51.929994
\.


--
-- TOC entry 3743 (class 0 OID 265152)
-- Dependencies: 251
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.notifications (id, utilisateur_id, titre, message, type, lu, lu_at, lien, created_at) FROM stdin;
\.


--
-- TOC entry 3745 (class 0 OID 265170)
-- Dependencies: 253
-- Data for Name: parametres; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.parametres (id, cle, valeur, description, type, modifiable, updated_at, updated_by) FROM stdin;
1	app_nom	e-DAMO	Nom de l'application	string	t	2026-03-02 16:50:28.576914	\N
2	app_slogan	Déclaration Annuelle de la Main d'Œuvre en ligne	Slogan de l'application	string	t	2026-03-02 16:50:28.576914	\N
3	annee_courante	2025	Année de déclaration courante	integer	t	2026-03-02 16:50:28.576914	\N
4	deadline_declaration	2025-03-31	Date limite de déclaration	string	t	2026-03-02 16:50:28.576914	\N
5	email_contact	anpe-niger16@gmail.com	Email de contact	string	t	2026-03-02 16:50:28.576914	\N
6	tel_contact	20 73 33 84	Téléphone de contact	string	t	2026-03-02 16:50:28.576914	\N
7	site_web	https://www.anpe-niger.ne	Site web officiel	string	t	2026-03-02 16:50:28.576914	\N
8	adresse	BP 13 222 NIAMEY – NIGER	Adresse postale	string	t	2026-03-02 16:50:28.576914	\N
9	maintenance_mode	false	Mode maintenance activé	boolean	t	2026-03-02 16:50:28.576914	\N
10	inscriptions_ouvertes	true	Inscriptions ouvertes	boolean	t	2026-03-02 16:50:28.576914	\N
11	items_par_page	20	Nombre d'éléments par page	integer	t	2026-03-02 16:50:28.576914	\N
12	logo_path	/assets/img/logo-anpe.png	Chemin du logo	string	t	2026-03-02 16:50:28.576914	\N
\.


--
-- TOC entry 3709 (class 0 OID 264774)
-- Dependencies: 217
-- Data for Name: regions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.regions (id, code, nom, actif, created_at, updated_at) FROM stdin;
1	1	Agadez	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
2	2	Diffa	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
3	3	Dosso	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
4	4	Maradi	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
5	5	Tahoua	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
6	6	Tillabéri	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
7	7	Zinder	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
8	8	Niamey	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
9	11	Arlit	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
10	51	Konni	t	2026-03-02 16:50:28.576914	2026-03-02 16:50:28.576914
\.


--
-- TOC entry 3719 (class 0 OID 264859)
-- Dependencies: 227
-- Data for Name: sessions_utilisateurs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions_utilisateurs (id, utilisateur_id, session_id, ip_address, user_agent, debut, fin, actif) FROM stdin;
1	3	nod5kdru090h7j1rgqja5706qj	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	2026-03-02 16:58:44.153309	2026-03-02 17:14:10.222468	f
2	4	tvu59djr4oc0qrd70nil0ja08m	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	2026-03-02 17:14:14.263782	2026-03-02 17:43:18.281803	f
3	4	5gmns49quhrbsn9kmh1fh1blit	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	2026-03-02 17:56:24.979511	\N	t
4	3	c1f23ga04fbc1sngmmudj05u0t	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	2026-03-02 18:27:31.66748	2026-03-02 18:46:14.753926	f
5	5	s0rt4raf5u4es2576brsv6qd4m	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	2026-03-02 18:47:17.313309	2026-03-02 18:48:58.182089	f
6	4	uaad24vurqmi9pn75gl988mj7v	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	2026-03-02 18:49:02.416602	2026-03-02 19:03:33.325536	f
7	5	2pm76s5tc7esjm8jhsueuv3eu8	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	2026-03-02 19:03:39.269515	2026-03-02 19:04:36.392231	f
8	5	7mkkf1kuqh4t5eupbom559ai9s	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	2026-03-02 19:04:45.227235	\N	t
9	4	cqh18o54o297pr4l29bjhjmb4e	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0	2026-03-03 09:28:51.924532	\N	t
\.


--
-- TOC entry 3717 (class 0 OID 264829)
-- Dependencies: 225
-- Data for Name: utilisateurs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.utilisateurs (id, uuid, nom, prenom, email, telephone, mot_de_passe, role, region_id, actif, email_verifie, token_verification, token_reset, token_reset_expiry, derniere_connexion, tentatives_connexion, bloque_jusqu_a, created_at, updated_at, created_by) FROM stdin;
3	0a49400c-de08-4e5f-8c67-0438e4185cea	TRAORE	Moussa	super@anpe-niger.ne	\N	$2y$12$1VIsVrvXwxtX4wcoy04ITuXWO1aIofWEJ3BNOqR4SGEaF1HIqw5fe	super_admin	\N	t	t	\N	\N	\N	2026-03-02 18:27:31.664374	0	\N	2026-03-02 17:58:09.408075	2026-03-02 18:27:31.664374	\N
5	373ccfac-01ae-4b12-88cf-60d06df01a8c	ISSAKA	Fati	agent@anpe-niger.ne		$2y$12$1VIsVrvXwxtX4wcoy04ITuXWO1aIofWEJ3BNOqR4SGEaF1HIqw5fe	agent	4	t	t	\N	\N	\N	2026-03-02 19:04:45.216319	0	\N	2026-03-02 17:58:09.408075	2026-03-02 19:04:45.216319	\N
4	90d26e20-ff8d-4096-8e9f-73a347b0fd3a	MAHAMADOU	Ibrahim	admin@anpe-niger.ne	\N	$2y$12$.hHy/6URdxjLix5OIe9nYO/jA2InGO6OsXIbWDwnGa2DoBMD0Tg2C	admin	\N	t	t	\N	\N	\N	2026-03-03 09:28:51.886341	0	\N	2026-03-02 17:58:09.408075	2026-03-03 09:28:51.886341	\N
\.


--
-- TOC entry 3778 (class 0 OID 0)
-- Dependencies: 222
-- Name: branches_activite_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.branches_activite_id_seq', 9, true);


--
-- TOC entry 3779 (class 0 OID 0)
-- Dependencies: 230
-- Name: campagnes_damo_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.campagnes_damo_id_seq', 1, true);


--
-- TOC entry 3780 (class 0 OID 0)
-- Dependencies: 220
-- Name: communes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.communes_id_seq', 1, false);


--
-- TOC entry 3781 (class 0 OID 0)
-- Dependencies: 236
-- Name: declaration_categories_effectifs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.declaration_categories_effectifs_id_seq', 7, true);


--
-- TOC entry 3782 (class 0 OID 0)
-- Dependencies: 246
-- Name: declaration_effectifs_etrangers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.declaration_effectifs_etrangers_id_seq', 1, false);


--
-- TOC entry 3783 (class 0 OID 0)
-- Dependencies: 234
-- Name: declaration_effectifs_mensuels_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.declaration_effectifs_mensuels_id_seq', 12, true);


--
-- TOC entry 3784 (class 0 OID 0)
-- Dependencies: 240
-- Name: declaration_formations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.declaration_formations_id_seq', 1, true);


--
-- TOC entry 3785 (class 0 OID 0)
-- Dependencies: 238
-- Name: declaration_niveaux_instruction_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.declaration_niveaux_instruction_id_seq', 63, true);


--
-- TOC entry 3786 (class 0 OID 0)
-- Dependencies: 244
-- Name: declaration_perspectives_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.declaration_perspectives_id_seq', 1, true);


--
-- TOC entry 3787 (class 0 OID 0)
-- Dependencies: 242
-- Name: declaration_pertes_emploi_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.declaration_pertes_emploi_id_seq', 6, true);


--
-- TOC entry 3788 (class 0 OID 0)
-- Dependencies: 232
-- Name: declarations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.declarations_id_seq', 1, true);


--
-- TOC entry 3789 (class 0 OID 0)
-- Dependencies: 218
-- Name: departements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.departements_id_seq', 45, true);


--
-- TOC entry 3790 (class 0 OID 0)
-- Dependencies: 228
-- Name: entreprises_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.entreprises_id_seq', 1, true);


--
-- TOC entry 3791 (class 0 OID 0)
-- Dependencies: 254
-- Name: guides_documents_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.guides_documents_id_seq', 1, true);


--
-- TOC entry 3792 (class 0 OID 0)
-- Dependencies: 248
-- Name: historique_declarations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.historique_declarations_id_seq', 1, false);


--
-- TOC entry 3793 (class 0 OID 0)
-- Dependencies: 256
-- Name: logs_activite_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.logs_activite_id_seq', 27, true);


--
-- TOC entry 3794 (class 0 OID 0)
-- Dependencies: 250
-- Name: notifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.notifications_id_seq', 1, false);


--
-- TOC entry 3795 (class 0 OID 0)
-- Dependencies: 252
-- Name: parametres_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.parametres_id_seq', 12, true);


--
-- TOC entry 3796 (class 0 OID 0)
-- Dependencies: 216
-- Name: regions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.regions_id_seq', 10, true);


--
-- TOC entry 3797 (class 0 OID 0)
-- Dependencies: 226
-- Name: sessions_utilisateurs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sessions_utilisateurs_id_seq', 9, true);


--
-- TOC entry 3798 (class 0 OID 0)
-- Dependencies: 224
-- Name: utilisateurs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.utilisateurs_id_seq', 5, true);


--
-- TOC entry 3456 (class 2606 OID 264827)
-- Name: branches_activite branches_activite_code_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.branches_activite
    ADD CONSTRAINT branches_activite_code_key UNIQUE (code);


--
-- TOC entry 3458 (class 2606 OID 264825)
-- Name: branches_activite branches_activite_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.branches_activite
    ADD CONSTRAINT branches_activite_pkey PRIMARY KEY (id);


--
-- TOC entry 3477 (class 2606 OID 264932)
-- Name: campagnes_damo campagnes_damo_annee_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.campagnes_damo
    ADD CONSTRAINT campagnes_damo_annee_key UNIQUE (annee);


--
-- TOC entry 3479 (class 2606 OID 264930)
-- Name: campagnes_damo campagnes_damo_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.campagnes_damo
    ADD CONSTRAINT campagnes_damo_pkey PRIMARY KEY (id);


--
-- TOC entry 3454 (class 2606 OID 264809)
-- Name: communes communes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.communes
    ADD CONSTRAINT communes_pkey PRIMARY KEY (id);


--
-- TOC entry 3496 (class 2606 OID 265025)
-- Name: declaration_categories_effectifs declaration_categories_effectifs_declaration_id_categorie_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_categories_effectifs
    ADD CONSTRAINT declaration_categories_effectifs_declaration_id_categorie_key UNIQUE (declaration_id, categorie);


--
-- TOC entry 3498 (class 2606 OID 265023)
-- Name: declaration_categories_effectifs declaration_categories_effectifs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_categories_effectifs
    ADD CONSTRAINT declaration_categories_effectifs_pkey PRIMARY KEY (id);


--
-- TOC entry 3514 (class 2606 OID 265125)
-- Name: declaration_effectifs_etrangers declaration_effectifs_etrangers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_effectifs_etrangers
    ADD CONSTRAINT declaration_effectifs_etrangers_pkey PRIMARY KEY (id);


--
-- TOC entry 3492 (class 2606 OID 264996)
-- Name: declaration_effectifs_mensuels declaration_effectifs_mensuels_declaration_id_mois_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_effectifs_mensuels
    ADD CONSTRAINT declaration_effectifs_mensuels_declaration_id_mois_key UNIQUE (declaration_id, mois);


--
-- TOC entry 3494 (class 2606 OID 264994)
-- Name: declaration_effectifs_mensuels declaration_effectifs_mensuels_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_effectifs_mensuels
    ADD CONSTRAINT declaration_effectifs_mensuels_pkey PRIMARY KEY (id);


--
-- TOC entry 3504 (class 2606 OID 265068)
-- Name: declaration_formations declaration_formations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_formations
    ADD CONSTRAINT declaration_formations_pkey PRIMARY KEY (id);


--
-- TOC entry 3500 (class 2606 OID 265047)
-- Name: declaration_niveaux_instruction declaration_niveaux_instructi_declaration_id_categorie_nive_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_niveaux_instruction
    ADD CONSTRAINT declaration_niveaux_instructi_declaration_id_categorie_nive_key UNIQUE (declaration_id, categorie, niveau);


--
-- TOC entry 3502 (class 2606 OID 265045)
-- Name: declaration_niveaux_instruction declaration_niveaux_instruction_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_niveaux_instruction
    ADD CONSTRAINT declaration_niveaux_instruction_pkey PRIMARY KEY (id);


--
-- TOC entry 3510 (class 2606 OID 265108)
-- Name: declaration_perspectives declaration_perspectives_declaration_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_perspectives
    ADD CONSTRAINT declaration_perspectives_declaration_id_key UNIQUE (declaration_id);


--
-- TOC entry 3512 (class 2606 OID 265106)
-- Name: declaration_perspectives declaration_perspectives_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_perspectives
    ADD CONSTRAINT declaration_perspectives_pkey PRIMARY KEY (id);


--
-- TOC entry 3506 (class 2606 OID 265089)
-- Name: declaration_pertes_emploi declaration_pertes_emploi_declaration_id_motif_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_pertes_emploi
    ADD CONSTRAINT declaration_pertes_emploi_declaration_id_motif_key UNIQUE (declaration_id, motif);


--
-- TOC entry 3508 (class 2606 OID 265087)
-- Name: declaration_pertes_emploi declaration_pertes_emploi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_pertes_emploi
    ADD CONSTRAINT declaration_pertes_emploi_pkey PRIMARY KEY (id);


--
-- TOC entry 3481 (class 2606 OID 264957)
-- Name: declarations declarations_campagne_id_entreprise_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declarations
    ADD CONSTRAINT declarations_campagne_id_entreprise_id_key UNIQUE (campagne_id, entreprise_id);


--
-- TOC entry 3483 (class 2606 OID 264953)
-- Name: declarations declarations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declarations
    ADD CONSTRAINT declarations_pkey PRIMARY KEY (id);


--
-- TOC entry 3485 (class 2606 OID 264955)
-- Name: declarations declarations_uuid_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declarations
    ADD CONSTRAINT declarations_uuid_key UNIQUE (uuid);


--
-- TOC entry 3452 (class 2606 OID 264794)
-- Name: departements departements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departements
    ADD CONSTRAINT departements_pkey PRIMARY KEY (id);


--
-- TOC entry 3471 (class 2606 OID 264886)
-- Name: entreprises entreprises_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entreprises
    ADD CONSTRAINT entreprises_pkey PRIMARY KEY (id);


--
-- TOC entry 3473 (class 2606 OID 264888)
-- Name: entreprises entreprises_uuid_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entreprises
    ADD CONSTRAINT entreprises_uuid_key UNIQUE (uuid);


--
-- TOC entry 3526 (class 2606 OID 265202)
-- Name: guides_documents guides_documents_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.guides_documents
    ADD CONSTRAINT guides_documents_pkey PRIMARY KEY (id);


--
-- TOC entry 3516 (class 2606 OID 265140)
-- Name: historique_declarations historique_declarations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historique_declarations
    ADD CONSTRAINT historique_declarations_pkey PRIMARY KEY (id);


--
-- TOC entry 3530 (class 2606 OID 265219)
-- Name: logs_activite logs_activite_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.logs_activite
    ADD CONSTRAINT logs_activite_pkey PRIMARY KEY (id);


--
-- TOC entry 3520 (class 2606 OID 265163)
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- TOC entry 3522 (class 2606 OID 265183)
-- Name: parametres parametres_cle_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parametres
    ADD CONSTRAINT parametres_cle_key UNIQUE (cle);


--
-- TOC entry 3524 (class 2606 OID 265181)
-- Name: parametres parametres_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parametres
    ADD CONSTRAINT parametres_pkey PRIMARY KEY (id);


--
-- TOC entry 3448 (class 2606 OID 264784)
-- Name: regions regions_code_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.regions
    ADD CONSTRAINT regions_code_key UNIQUE (code);


--
-- TOC entry 3450 (class 2606 OID 264782)
-- Name: regions regions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.regions
    ADD CONSTRAINT regions_pkey PRIMARY KEY (id);


--
-- TOC entry 3469 (class 2606 OID 264868)
-- Name: sessions_utilisateurs sessions_utilisateurs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions_utilisateurs
    ADD CONSTRAINT sessions_utilisateurs_pkey PRIMARY KEY (id);


--
-- TOC entry 3463 (class 2606 OID 264847)
-- Name: utilisateurs utilisateurs_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.utilisateurs
    ADD CONSTRAINT utilisateurs_email_key UNIQUE (email);


--
-- TOC entry 3465 (class 2606 OID 264843)
-- Name: utilisateurs utilisateurs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.utilisateurs
    ADD CONSTRAINT utilisateurs_pkey PRIMARY KEY (id);


--
-- TOC entry 3467 (class 2606 OID 264845)
-- Name: utilisateurs utilisateurs_uuid_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.utilisateurs
    ADD CONSTRAINT utilisateurs_uuid_key UNIQUE (uuid);


--
-- TOC entry 3486 (class 1259 OID 265232)
-- Name: idx_declarations_agent; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_declarations_agent ON public.declarations USING btree (agent_id);


--
-- TOC entry 3487 (class 1259 OID 265230)
-- Name: idx_declarations_campagne; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_declarations_campagne ON public.declarations USING btree (campagne_id);


--
-- TOC entry 3488 (class 1259 OID 265231)
-- Name: idx_declarations_entreprise; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_declarations_entreprise ON public.declarations USING btree (entreprise_id);


--
-- TOC entry 3489 (class 1259 OID 265234)
-- Name: idx_declarations_region; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_declarations_region ON public.declarations USING btree (region_id);


--
-- TOC entry 3490 (class 1259 OID 265233)
-- Name: idx_declarations_statut; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_declarations_statut ON public.declarations USING btree (statut);


--
-- TOC entry 3474 (class 1259 OID 265228)
-- Name: idx_entreprises_cnss; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_entreprises_cnss ON public.entreprises USING btree (numero_cnss);


--
-- TOC entry 3475 (class 1259 OID 265229)
-- Name: idx_entreprises_region; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_entreprises_region ON public.entreprises USING btree (region_id);


--
-- TOC entry 3517 (class 1259 OID 265235)
-- Name: idx_historique_declaration; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_historique_declaration ON public.historique_declarations USING btree (declaration_id);


--
-- TOC entry 3527 (class 1259 OID 265237)
-- Name: idx_logs_created; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_logs_created ON public.logs_activite USING btree (created_at);


--
-- TOC entry 3528 (class 1259 OID 265236)
-- Name: idx_logs_utilisateur; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_logs_utilisateur ON public.logs_activite USING btree (utilisateur_id);


--
-- TOC entry 3518 (class 1259 OID 265238)
-- Name: idx_notifications_user; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_notifications_user ON public.notifications USING btree (utilisateur_id, lu);


--
-- TOC entry 3459 (class 1259 OID 265225)
-- Name: idx_utilisateurs_email; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_utilisateurs_email ON public.utilisateurs USING btree (email);


--
-- TOC entry 3460 (class 1259 OID 265227)
-- Name: idx_utilisateurs_region; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_utilisateurs_region ON public.utilisateurs USING btree (region_id);


--
-- TOC entry 3461 (class 1259 OID 265226)
-- Name: idx_utilisateurs_role; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_utilisateurs_role ON public.utilisateurs USING btree (role);


--
-- TOC entry 3564 (class 2620 OID 265244)
-- Name: campagnes_damo update_campagnes_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_campagnes_updated_at BEFORE UPDATE ON public.campagnes_damo FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- TOC entry 3565 (class 2620 OID 265242)
-- Name: declarations update_declarations_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_declarations_updated_at BEFORE UPDATE ON public.declarations FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- TOC entry 3563 (class 2620 OID 265241)
-- Name: entreprises update_entreprises_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_entreprises_updated_at BEFORE UPDATE ON public.entreprises FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- TOC entry 3561 (class 2620 OID 265240)
-- Name: regions update_regions_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_regions_updated_at BEFORE UPDATE ON public.regions FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- TOC entry 3562 (class 2620 OID 265243)
-- Name: utilisateurs update_utilisateurs_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_utilisateurs_updated_at BEFORE UPDATE ON public.utilisateurs FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- TOC entry 3542 (class 2606 OID 264933)
-- Name: campagnes_damo campagnes_damo_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.campagnes_damo
    ADD CONSTRAINT campagnes_damo_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- TOC entry 3532 (class 2606 OID 264810)
-- Name: communes communes_departement_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.communes
    ADD CONSTRAINT communes_departement_id_fkey FOREIGN KEY (departement_id) REFERENCES public.departements(id) ON DELETE CASCADE;


--
-- TOC entry 3549 (class 2606 OID 265026)
-- Name: declaration_categories_effectifs declaration_categories_effectifs_declaration_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_categories_effectifs
    ADD CONSTRAINT declaration_categories_effectifs_declaration_id_fkey FOREIGN KEY (declaration_id) REFERENCES public.declarations(id) ON DELETE CASCADE;


--
-- TOC entry 3554 (class 2606 OID 265126)
-- Name: declaration_effectifs_etrangers declaration_effectifs_etrangers_declaration_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_effectifs_etrangers
    ADD CONSTRAINT declaration_effectifs_etrangers_declaration_id_fkey FOREIGN KEY (declaration_id) REFERENCES public.declarations(id) ON DELETE CASCADE;


--
-- TOC entry 3548 (class 2606 OID 264997)
-- Name: declaration_effectifs_mensuels declaration_effectifs_mensuels_declaration_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_effectifs_mensuels
    ADD CONSTRAINT declaration_effectifs_mensuels_declaration_id_fkey FOREIGN KEY (declaration_id) REFERENCES public.declarations(id) ON DELETE CASCADE;


--
-- TOC entry 3551 (class 2606 OID 265069)
-- Name: declaration_formations declaration_formations_declaration_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_formations
    ADD CONSTRAINT declaration_formations_declaration_id_fkey FOREIGN KEY (declaration_id) REFERENCES public.declarations(id) ON DELETE CASCADE;


--
-- TOC entry 3550 (class 2606 OID 265048)
-- Name: declaration_niveaux_instruction declaration_niveaux_instruction_declaration_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_niveaux_instruction
    ADD CONSTRAINT declaration_niveaux_instruction_declaration_id_fkey FOREIGN KEY (declaration_id) REFERENCES public.declarations(id) ON DELETE CASCADE;


--
-- TOC entry 3553 (class 2606 OID 265109)
-- Name: declaration_perspectives declaration_perspectives_declaration_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_perspectives
    ADD CONSTRAINT declaration_perspectives_declaration_id_fkey FOREIGN KEY (declaration_id) REFERENCES public.declarations(id) ON DELETE CASCADE;


--
-- TOC entry 3552 (class 2606 OID 265090)
-- Name: declaration_pertes_emploi declaration_pertes_emploi_declaration_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declaration_pertes_emploi
    ADD CONSTRAINT declaration_pertes_emploi_declaration_id_fkey FOREIGN KEY (declaration_id) REFERENCES public.declarations(id) ON DELETE CASCADE;


--
-- TOC entry 3543 (class 2606 OID 264968)
-- Name: declarations declarations_agent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declarations
    ADD CONSTRAINT declarations_agent_id_fkey FOREIGN KEY (agent_id) REFERENCES public.utilisateurs(id) ON DELETE RESTRICT;


--
-- TOC entry 3544 (class 2606 OID 264958)
-- Name: declarations declarations_campagne_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declarations
    ADD CONSTRAINT declarations_campagne_id_fkey FOREIGN KEY (campagne_id) REFERENCES public.campagnes_damo(id) ON DELETE RESTRICT;


--
-- TOC entry 3545 (class 2606 OID 264963)
-- Name: declarations declarations_entreprise_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declarations
    ADD CONSTRAINT declarations_entreprise_id_fkey FOREIGN KEY (entreprise_id) REFERENCES public.entreprises(id) ON DELETE RESTRICT;


--
-- TOC entry 3546 (class 2606 OID 264973)
-- Name: declarations declarations_region_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declarations
    ADD CONSTRAINT declarations_region_id_fkey FOREIGN KEY (region_id) REFERENCES public.regions(id);


--
-- TOC entry 3547 (class 2606 OID 264978)
-- Name: declarations declarations_validateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.declarations
    ADD CONSTRAINT declarations_validateur_id_fkey FOREIGN KEY (validateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- TOC entry 3531 (class 2606 OID 264795)
-- Name: departements departements_region_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departements
    ADD CONSTRAINT departements_region_id_fkey FOREIGN KEY (region_id) REFERENCES public.regions(id) ON DELETE CASCADE;


--
-- TOC entry 3536 (class 2606 OID 264909)
-- Name: entreprises entreprises_agent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entreprises
    ADD CONSTRAINT entreprises_agent_id_fkey FOREIGN KEY (agent_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- TOC entry 3537 (class 2606 OID 264889)
-- Name: entreprises entreprises_branche_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entreprises
    ADD CONSTRAINT entreprises_branche_id_fkey FOREIGN KEY (branche_id) REFERENCES public.branches_activite(id) ON DELETE SET NULL;


--
-- TOC entry 3538 (class 2606 OID 264904)
-- Name: entreprises entreprises_commune_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entreprises
    ADD CONSTRAINT entreprises_commune_id_fkey FOREIGN KEY (commune_id) REFERENCES public.communes(id) ON DELETE SET NULL;


--
-- TOC entry 3539 (class 2606 OID 264914)
-- Name: entreprises entreprises_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entreprises
    ADD CONSTRAINT entreprises_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- TOC entry 3540 (class 2606 OID 264899)
-- Name: entreprises entreprises_departement_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entreprises
    ADD CONSTRAINT entreprises_departement_id_fkey FOREIGN KEY (departement_id) REFERENCES public.departements(id) ON DELETE SET NULL;


--
-- TOC entry 3541 (class 2606 OID 264894)
-- Name: entreprises entreprises_region_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.entreprises
    ADD CONSTRAINT entreprises_region_id_fkey FOREIGN KEY (region_id) REFERENCES public.regions(id);


--
-- TOC entry 3559 (class 2606 OID 265203)
-- Name: guides_documents guides_documents_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.guides_documents
    ADD CONSTRAINT guides_documents_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- TOC entry 3555 (class 2606 OID 265141)
-- Name: historique_declarations historique_declarations_declaration_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historique_declarations
    ADD CONSTRAINT historique_declarations_declaration_id_fkey FOREIGN KEY (declaration_id) REFERENCES public.declarations(id) ON DELETE CASCADE;


--
-- TOC entry 3556 (class 2606 OID 265146)
-- Name: historique_declarations historique_declarations_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historique_declarations
    ADD CONSTRAINT historique_declarations_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- TOC entry 3560 (class 2606 OID 265220)
-- Name: logs_activite logs_activite_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.logs_activite
    ADD CONSTRAINT logs_activite_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- TOC entry 3557 (class 2606 OID 265164)
-- Name: notifications notifications_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE CASCADE;


--
-- TOC entry 3558 (class 2606 OID 265184)
-- Name: parametres parametres_updated_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parametres
    ADD CONSTRAINT parametres_updated_by_fkey FOREIGN KEY (updated_by) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- TOC entry 3535 (class 2606 OID 264869)
-- Name: sessions_utilisateurs sessions_utilisateurs_utilisateur_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions_utilisateurs
    ADD CONSTRAINT sessions_utilisateurs_utilisateur_id_fkey FOREIGN KEY (utilisateur_id) REFERENCES public.utilisateurs(id) ON DELETE CASCADE;


--
-- TOC entry 3533 (class 2606 OID 264853)
-- Name: utilisateurs utilisateurs_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.utilisateurs
    ADD CONSTRAINT utilisateurs_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.utilisateurs(id) ON DELETE SET NULL;


--
-- TOC entry 3534 (class 2606 OID 264848)
-- Name: utilisateurs utilisateurs_region_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.utilisateurs
    ADD CONSTRAINT utilisateurs_region_id_fkey FOREIGN KEY (region_id) REFERENCES public.regions(id) ON DELETE SET NULL;


-- Completed on 2026-03-03 11:22:54

--
-- PostgreSQL database dump complete
--

