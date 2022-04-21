--
-- PostgreSQL database dump
--

-- Dumped from database version 10.19 (Ubuntu 10.19-0ubuntu0.18.04.1)
-- Dumped by pg_dump version 13.3

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
-- Name: audit; Type: SCHEMA; Schema: -; Owner: armasi
--

CREATE SCHEMA audit;


ALTER SCHEMA audit OWNER TO armasi;

--
-- Name: db_maintenance; Type: SCHEMA; Schema: -; Owner: armasi
--

CREATE SCHEMA db_maintenance;


ALTER SCHEMA db_maintenance OWNER TO armasi;

--
-- Name: SCHEMA db_maintenance; Type: COMMENT; Schema: -; Owner: armasi
--

COMMENT ON SCHEMA db_maintenance IS 'Contains all objects related to maintenance of the DB, e.g. material view refresh.';


--
-- Name: gbj_report; Type: SCHEMA; Schema: -; Owner: armasi
--

CREATE SCHEMA gbj_report;


ALTER SCHEMA gbj_report OWNER TO armasi;

--
-- Name: man; Type: SCHEMA; Schema: -; Owner: armasi_man
--

CREATE SCHEMA man;


ALTER SCHEMA man OWNER TO armasi_man;

--
-- Name: SCHEMA man; Type: COMMENT; Schema: -; Owner: armasi_man
--

COMMENT ON SCHEMA man IS 'database terkait maintenance (man) application';


--
-- Name: qc; Type: SCHEMA; Schema: -; Owner: armasi_qc
--

CREATE SCHEMA qc;


ALTER SCHEMA qc OWNER TO armasi_qc;

--
-- Name: SCHEMA qc; Type: COMMENT; Schema: -; Owner: armasi_qc
--

COMMENT ON SCHEMA qc IS 'database terkait QC application';


--
-- Name: taman; Type: SCHEMA; Schema: -; Owner: armasi_taman
--

CREATE SCHEMA taman;


ALTER SCHEMA taman OWNER TO armasi_taman;

--
-- Name: wmm; Type: SCHEMA; Schema: -; Owner: armasi_wmm
--

CREATE SCHEMA wmm;


ALTER SCHEMA wmm OWNER TO armasi_wmm;

--
-- Name: btree_gist; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS btree_gist WITH SCHEMA public;


--
-- Name: EXTENSION btree_gist; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION btree_gist IS 'support for indexing common datatypes in GiST';


--
-- Name: pg_trgm; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pg_trgm WITH SCHEMA public;


--
-- Name: EXTENSION pg_trgm; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pg_trgm IS 'text similarity measurement and index searching based on trigrams';


--
-- Name: dblink_pkey_results; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.dblink_pkey_results AS (
	"position" integer,
	colname text
);


ALTER TYPE public.dblink_pkey_results OWNER TO postgres;

--
-- Name: ghstore; Type: SHELL TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.ghstore;


--
-- Name: ghstore_in(cstring); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.ghstore_in(cstring) RETURNS public.ghstore
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'ghstore_in';


ALTER FUNCTION public.ghstore_in(cstring) OWNER TO postgres;

--
-- Name: ghstore_out(public.ghstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.ghstore_out(public.ghstore) RETURNS cstring
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'ghstore_out';


ALTER FUNCTION public.ghstore_out(public.ghstore) OWNER TO postgres;

--
-- Name: ghstore; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.ghstore (
    INTERNALLENGTH = variable,
    INPUT = public.ghstore_in,
    OUTPUT = public.ghstore_out,
    ALIGNMENT = int4,
    STORAGE = plain
);


ALTER TYPE public.ghstore OWNER TO postgres;

--
-- Name: hstore; Type: SHELL TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.hstore;


--
-- Name: hstore_in(cstring); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_in(cstring) RETURNS public.hstore
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_in';


ALTER FUNCTION public.hstore_in(cstring) OWNER TO postgres;

--
-- Name: hstore_out(public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_out(public.hstore) RETURNS cstring
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_out';


ALTER FUNCTION public.hstore_out(public.hstore) OWNER TO postgres;

--
-- Name: hstore_recv(internal); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_recv(internal) RETURNS public.hstore
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_recv';


ALTER FUNCTION public.hstore_recv(internal) OWNER TO postgres;

--
-- Name: hstore_send(public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_send(public.hstore) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_send';


ALTER FUNCTION public.hstore_send(public.hstore) OWNER TO postgres;

--
-- Name: hstore; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.hstore (
    INTERNALLENGTH = variable,
    INPUT = public.hstore_in,
    OUTPUT = public.hstore_out,
    RECEIVE = public.hstore_recv,
    SEND = public.hstore_send,
    ALIGNMENT = int4,
    STORAGE = extended
);


ALTER TYPE public.hstore OWNER TO postgres;

--
-- Name: log_inactive_armasi_users(); Type: FUNCTION; Schema: audit; Owner: armasi
--

CREATE FUNCTION audit.log_inactive_armasi_users() RETURNS integer
    LANGUAGE sql
    AS $$
  WITH inactive_users_for_the_month AS (
    SELECT user_name, last_activity
    FROM tbl_user
    WHERE monthly_login_count = 0
    AND last_activity < CURRENT_DATE - INTERVAL '1 month'
    AND is_active IS TRUE
    ORDER BY user_name
  ), inserted_rows AS (
    INSERT INTO audit.inactive_armasi_users(year, month, username, last_login)
      SELECT date_part('year', CURRENT_DATE), date_part('month', CURRENT_DATE), user_name, last_activity
      FROM inactive_users_for_the_month
    RETURNING 1
  )
  SELECT COUNT(*)::INT FROM inserted_rows;
$$;


ALTER FUNCTION audit.log_inactive_armasi_users() OWNER TO armasi;

--
-- Name: max_user_inactivity_interval(); Type: FUNCTION; Schema: audit; Owner: armasi
--

CREATE FUNCTION audit.max_user_inactivity_interval() RETURNS interval
    LANGUAGE sql IMMUTABLE
    AS $$ SELECT INTERVAL '60 days' $$;


ALTER FUNCTION audit.max_user_inactivity_interval() OWNER TO armasi;

--
-- Name: add_pallets_to_warehouse(character varying[], character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.add_pallets_to_warehouse(pallet_nos character varying[], userid_val character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
DECLARE
  pallet_record        RECORD;
  pallet_event_id      INTEGER;
  can_move_all_pallets BOOLEAN;

  mbj_no               VARCHAR;
BEGIN
  -- check the pallet_nos first.
  CREATE TEMP TABLE temp_pallet_move_status_result ON COMMIT DROP AS
    SELECT pallet_no, status_code
    FROM check_pallets_move_to_warehouse_status(pallet_nos) AS
             f (pallet_no VARCHAR, subplant VARCHAR, status_code INT);

  SELECT * INTO pallet_record FROM temp_pallet_move_status_result WHERE status_code <> 0;

  can_move_all_pallets := NOT FOUND;
  IF NOT can_move_all_pallets
  THEN
    RAISE EXCEPTION 'Invalid pallet number(s) requested! (%)', (SELECT array_to_string(array_agg(pallet_no), ',')
                                                                FROM pallet_record);
  END IF;

  IF NOT can_move_all_pallets
  THEN
    RAISE EXCEPTION 'There is an invalid pallet number!';
  -- the user then reuses check_pallets_move_to_warehouse_status(VARCHAR) function to check the pallets. preferably before.
  END IF;

  -- step 1: initialize the values
  mbj_no := 'MBJ/' || get_plant_code() || '/' || to_char(CURRENT_DATE, 'YY') || '/' ||
            to_char(get_next_mbj_number(), 'fm00000');
  SELECT id
      INTO pallet_event_id FROM pallet_event_types WHERE event_name = 'log_received';
  IF NOT FOUND
  THEN
    RAISE EXCEPTION 'Event name ''%'' not found in "pallet_event_types"!', 'log_received';
  END IF;

  -- step 2: insert to mutation table
  INSERT INTO tbl_sp_mutasi_pallet (plan_kode, no_mutasi, qty, tanggal, pallet_no, create_date, create_user, status_mut)
  SELECT get_plant_code(), mbj_no, qty, CURRENT_DATE, pallet_no, CURRENT_TIMESTAMP, userid_val, 'O'
  FROM tbl_sp_hasilbj
  WHERE pallet_no = ANY(pallet_nos);

  -- step 3: insert into pallet events
  INSERT INTO pallet_events (event_id, pallet_no, userid, event_time, old_values, new_values, plant_id)
  SELECT pallet_event_id,
         t2.pallet_no,
         userid_val                                                            AS userid,
         CURRENT_TIMESTAMP                                                     AS event_time,
         '{"terima_no":null,"tanggal_terima":null,"terima_user":null}' :: TEXT as old_val,
         CONCAT('{"terima_no":"', mbj_no, '","tanggal_terima":"', CURRENT_DATE :: TEXT,
                '","terima_user":"', userid_val, '"}')                         as new_val,
         (CASE
            WHEN t2.plant_id = '4' THEN '4A'
            WHEN t2.plant_id = '5' THEN '5A'
            ELSE t2.plant_id
             END)                                                              AS plant_id
  FROM (SELECT pallet_no AS pallet_no, SUBSTRING(pallet_no, subplant_regex()) AS plant_id
        FROM unnest(pallet_nos) AS pallet_no) t2;

  -- step 4: update the values
  UPDATE tbl_sp_hasilbj
  SET terima_no        = mbj_no,
      terima_user      = userid_val,
      tanggal_terima   = CURRENT_DATE,
      status_plt       = 'R', -- received by logistics
      update_tran      = CURRENT_TIMESTAMP,
      update_tran_user = userid_val
  WHERE pallet_no = ANY (pallet_nos);

  RETURN mbj_no;
  DROP TABLE temp_pallet_move_status_result;
END;
$$;


ALTER FUNCTION public.add_pallets_to_warehouse(pallet_nos character varying[], userid_val character varying) OWNER TO armasi;

--
-- Name: FUNCTION add_pallets_to_warehouse(pallet_nos character varying[], userid_val character varying); Type: COMMENT; Schema: public; Owner: armasi
--

COMMENT ON FUNCTION public.add_pallets_to_warehouse(pallet_nos character varying[], userid_val character varying) IS 'This function will add the pallets to warehouse, designated by new MBJ number in "terima_no" column.
The result of this function is the newly generated MBJ number.
This function assumes that the userid supplied is valid. Since no validation is performed, user should ensure that the supplied userid is authorized to perform the move (using check_user_can_move_pallet_to_warehouse).';


--
-- Name: akeys(public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.akeys(public.hstore) RETURNS text[]
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_akeys';


ALTER FUNCTION public.akeys(public.hstore) OWNER TO postgres;

--
-- Name: approve_downgrade(character varying, boolean, character varying, character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.approve_downgrade(downgrade_id character varying, is_approved boolean, userid_requester character varying, reason character varying DEFAULT NULL::character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    downgrade_rec    RECORD;
    affected_pallets INT := 0;
BEGIN
    SELECT * INTO downgrade_rec FROM tbl_sp_downgrade_pallet WHERE no_downgrade = downgrade_id FOR UPDATE;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Downgrade record with ID % is not found!', downgrade_id;
    END IF;
    -- check status
    IF downgrade_rec.status = 'R' THEN
        RAISE EXCEPTION 'Downgrade record with ID % has been rejected!', downgrade_id;
    ELSEIF downgrade_rec.status = 'A' THEN
        RAISE EXCEPTION 'Downgrade record with ID % has been approved!', downgrade_id;
    ELSEIF downgrade_rec.status = 'C' THEN
        RAISE EXCEPTION 'Downgrade record with ID % has been cancelled!', downgrade_id;
    END IF;

    IF NOT is_approved THEN
        IF COALESCE(TRIM(reason), '') = '' THEN
            RAISE EXCEPTION 'Rejection reason cannot be empty!';
        END IF;
        affected_pallets :=
                (SELECT unblock_pallets((SELECT array_agg(pallet_no)
                                         FROM tbl_sp_downgrade_pallet
                                         WHERE no_downgrade = downgrade_id), userid_requester, 'Batal Downgrade',
                                        TRUE));
        UPDATE tbl_sp_downgrade_pallet
        SET status          = 'R',
            keterangan      = keterangan || format(' [Ditolak: %s]', reason),
            last_updated_at = now(),
            last_updated_by = userid_requester
        WHERE no_downgrade = downgrade_rec.no_downgrade;
        RETURN affected_pallets;
    END IF;

    CREATE TEMP TABLE pallets_to_downgrade ON COMMIT DROP AS
    SELECT hasilbj.pallet_no AS pallet_no,
           t1.item_kode      AS current_motif_id,
           t2.item_kode      AS new_motif_id,
           (CASE
                WHEN downgrade_rec.jenis_downgrade = const_downgrade_type_exp_to_eco() THEN 'EKONOMI'
                WHEN downgrade_rec.jenis_downgrade IN
                     (const_downgrade_type_exp_to_kw4(), const_downgrade_type_eco_to_kw4()) THEN 'KW4'
               END)          AS new_quality,
           hasilbj.last_qty  AS current_quantity
    FROM tbl_sp_hasilbj hasilbj
             JOIN tbl_sp_downgrade_pallet dwg ON hasilbj.plan_kode = dwg.plan_kode AND hasilbj.pallet_no = dwg.pallet_no
             JOIN item t1 ON hasilbj.item_kode = t1.item_kode
             LEFT JOIN (
        SELECT *,
               ROW_NUMBER() OVER (PARTITION BY category_kode, color, quality, plant_kode ORDER BY category_kode) row_num
        FROM item
    ) t2 ON (
        CASE
            WHEN dwg.item_kode_baru IS NOT NULL THEN (dwg.item_kode_baru = t2.item_kode AND row_num = 1)
            ELSE (
                        t2.quality = (
                        CASE
                            WHEN dwg.jenis_downgrade = const_downgrade_type_exp_to_eco() THEN 'EKONOMI'
                            WHEN dwg.jenis_downgrade IN
                                 (const_downgrade_type_exp_to_kw4(), const_downgrade_type_eco_to_kw4()) THEN 'KW4'
                            END)
                    AND t1.category_kode = t2.category_kode
                    AND t1.color = t2.color
                    AND dwg.plan_kode = t2.plant_kode::VARCHAR
                    AND row_num = 1)
            END
        )
    WHERE no_downgrade = downgrade_rec.no_downgrade;
    IF EXISTS(SELECT * FROM pallets_to_downgrade WHERE new_motif_id IS NULL) THEN
        RAISE EXCEPTION 'Cannot downgrade some pallet(s) in %! Please check for any missing item records!', downgrade_rec.no_downgrade;
    END IF;

    IF conf_downgrade_kw4_treat_as_broken() AND
       downgrade_rec.jenis_downgrade IN (const_downgrade_type_eco_to_kw4(), const_downgrade_type_exp_to_kw4())
    THEN
        -- record the pallets as broken
        WITH txn_id AS (
            -- track these transactions as 'BRR'
            INSERT INTO txn_counters (plant_id, txn_id, period, count, last_period)
                VALUES (SUBSTRING(downgrade_rec.no_downgrade, subplant_regex()), 'BRR', 'month', 1,
                        date_trunc('month', now()))
                ON CONFLICT (plant_id, txn_id) DO UPDATE
                    SET count = (CASE
                                     WHEN txn_counters.last_period = excluded.last_period THEN txn_counters.count + 1
                                     ELSE 1 END),
                        last_period = excluded.last_period,
                        last_updated_at = now()
                RETURNING format('%s/%s/%s/%s/%s', txn_id, plant_id, to_char(last_period, 'MM'),
                                 to_char(last_period, 'YY'), to_char(count, 'fm00000')) id
        ),
             updated_pallets AS (
                 UPDATE tbl_sp_hasilbj hasilbj
                     SET last_qty = 0,
                         keterangan = format('Pecah dalam Gudang [%s]', (SELECT id FROM txn_id)),
                         status_plt = 'R',
                         block_ref_id = NULL,
                         update_tran_user = userid_requester,
                         update_tran = now()
                     FROM pallets_to_downgrade t1
                         JOIN tbl_sp_hasilbj t2
                         ON t1.pallet_no = t2.pallet_no
                     WHERE hasilbj.pallet_no = t1.pallet_no
                     RETURNING hasilbj.plan_kode, hasilbj.pallet_no, hasilbj.subplant, hasilbj.update_tran_user,
                         hasilbj.item_kode, hasilbj.quality,
                         hasilbj.status_plt AS new_status_plt, t2.status_plt AS old_status_plt,
                         hasilbj.last_qty AS new_last_qty, t2.last_qty AS old_last_qty,
                         hasilbj.keterangan AS new_keterangan, t2.keterangan AS old_keterangan
             ),
             broken_ret_records AS (
                 INSERT INTO tbl_retur_produksi (retur_kode, tanggal, jenis_bahan, create_by, approve_by)
                     VALUES ((SELECT id FROM txn_id), CURRENT_DATE, 'Downgrade ke KW4', userid_requester,
                             userid_requester)
             ),
             broken_pallet_records AS (
                 INSERT INTO item_retur_produksi (retur_kode, item_kode, export, keterangan, ekonomi, pallet_no)
                     SELECT id,
                            item_kode,
                            (CASE WHEN quality = 'EXPORT' THEN old_last_qty ELSE NULL END),
                            'Downgrade ke KW4',
                            (CASE WHEN quality = 'EKONOMI' THEN old_last_qty ELSE NULL END),
                            pallet_no
                     FROM updated_pallets
                              CROSS JOIN txn_id
             ),
             mutation_table AS (
                 INSERT INTO tbl_sp_mutasi_pallet(plan_kode, no_mutasi, tanggal, pallet_no, qty, create_date, create_user, status_mut, keterangan, update_tran, update_tran_user)
                 SELECT plan_kode, id, CURRENT_DATE, pallet_no, new_last_qty - old_last_qty, now(), update_tran_user, 'O', new_keterangan, now(), update_tran_user
                 FROM updated_pallets
                 CROSS JOIN txn_id
             )
        INSERT
        INTO pallet_events (event_id, pallet_no, userid, plant_id, old_values, new_values)
        SELECT (SELECT id FROM pallet_event_types WHERE event_name = 'qa_broken'),
               pallet_no,
               update_tran_user,
               subplant,
               jsonb_build_object(
                       'last_qty', old_last_qty,
                       'keterangan', old_keterangan,
                       'status_plt', old_status_plt
                   ),
               jsonb_build_object(
                       'last_qty', new_last_qty,
                       'keterangan', new_keterangan,
                       'status_plt', new_status_plt
                   )
        FROM updated_pallets;

    ELSE -- change the quality of the pallets.
        WITH updated_pallets AS (
            UPDATE tbl_sp_hasilbj hasilbj
                SET item_kode = t1.new_motif_id, quality = t1.new_quality,
                    keterangan = '[DOWNGRADE] ' || downgrade_rec.keterangan,
                    status_plt = 'R', update_tran = now(), update_tran_user = userid_requester
                FROM pallets_to_downgrade t1
                    JOIN tbl_sp_hasilbj t2 ON t1.pallet_no = t2.pallet_no
                WHERE hasilbj.pallet_no = t1.pallet_no
                RETURNING t1.pallet_no, hasilbj.subplant, hasilbj.update_tran_user,
                    t2.status_plt AS old_status, hasilbj.status_plt AS new_status,
                    t2.quality AS old_quality, hasilbj.quality AS new_quality,
                    t2.item_kode AS old_item_kode, hasilbj.item_kode AS new_item_kode,
                    t2.keterangan AS old_keterangan, hasilbj.keterangan AS new_keterangan
        )
        INSERT
        INTO pallet_events (event_id, pallet_no, userid, plant_id, old_values, new_values)
        SELECT (SELECT id FROM pallet_event_types WHERE event_name = 'qa_downgrade'),
               pallet_no,
               update_tran_user,
               (CASE WHEN subplant IN ('4', '5') THEN subplant || 'A' ELSE subplant END),
               jsonb_build_object(
                       'item_kode', old_item_kode,
                       'quality', old_quality,
                       'keterangan', old_keterangan,
                       'status_plt', old_status
                   ),
               jsonb_build_object(
                       'item_kode', new_item_kode,
                       'quality', new_quality,
                       'keterangan', new_keterangan,
                       'status_plt', new_status
                   )
        FROM updated_pallets;
    END IF;
    UPDATE tbl_sp_downgrade_pallet
    SET approval        = TRUE,
        status          = 'A',
        approval_user   = userid_requester,
        date_approval   = now(),
        item_kode_baru  = new_motif_id,
        last_updated_at = now(),
        last_updated_by = userid_requester
    FROM pallets_to_downgrade t1
    WHERE tbl_sp_downgrade_pallet.pallet_no = t1.pallet_no
      AND no_downgrade = downgrade_rec.no_downgrade;
    GET DIAGNOSTICS affected_pallets := ROW_COUNT;
    DROP TABLE pallets_to_downgrade;
    RETURN affected_pallets;
END;
$$;


ALTER FUNCTION public.approve_downgrade(downgrade_id character varying, is_approved boolean, userid_requester character varying, reason character varying) OWNER TO armasi;

--
-- Name: armor(bytea); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.armor(bytea) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_armor';


ALTER FUNCTION public.armor(bytea) OWNER TO postgres;

--
-- Name: array_subtract(anyarray, anyarray); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.array_subtract(anyarray, anyarray) RETURNS anyarray
    LANGUAGE sql IMMUTABLE
    AS $_$
	SELECT ARRAY(
	    SELECT unnest($1)
	    EXCEPT
	    SELECT unnest($2)
	);
$_$;


ALTER FUNCTION public.array_subtract(anyarray, anyarray) OWNER TO armasi;

--
-- Name: auto_remove_location(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.auto_remove_location() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    remove_location BOOLEAN := FALSE;
BEGIN
    IF TG_OP = 'UPDATE'
    THEN
        remove_location := (NEW.last_qty <= 0);
    END IF;

    IF remove_location THEN
        WITH updated_pallet AS (
            DELETE FROM inv_opname
                WHERE io_no_pallet = NEW.pallet_no
                RETURNING io_plan_kode, io_no_pallet, io_kd_lok
        )
        INSERT
        INTO inv_opname_hist(ioh_plan_kode, ioh_kd_lok, ioh_no_pallet, ioh_qty_pallet, ioh_tgl, ioh_txn, ioh_userid,
                             ioh_kd_lok_old)
        SELECT io_plan_kode,
               '0',
               io_no_pallet,
               0,
               now(),
               format('[System] Dihapus dari lokasi %s karena last_qty = 0.', io_kd_lok),
               'admin',
               io_kd_lok
        FROM updated_pallet;
    END IF;
    RETURN NULL; -- after trigger, result is ignored.
END;
$$;


ALTER FUNCTION public.auto_remove_location() OWNER TO armasi;

--
-- Name: auto_transit_hasilbj(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.auto_transit_hasilbj() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    auto_insert     BOOLEAN := FALSE;
    subplant        VARCHAR;
    userid_handover VARCHAR;
    pallet_no       VARCHAR;
BEGIN
    IF TG_OP = 'INSERT'
    THEN
        auto_insert := LEFT(NEW.no_mutasi, 3) IN ('SEQ', 'MBJ');
        subplant := SUBSTRING(NEW.pallet_no, subplant_regex());
        userid_handover := NEW.create_user;
        pallet_no := NEW.pallet_no;
    END IF;

    IF auto_insert
    THEN
        WITH allocated_pallet AS (
            INSERT INTO inv_opname (io_plan_kode, io_kd_lok, io_no_pallet, io_qty_pallet, io_tgl)
                VALUES (RIGHT(subplant, 1),
                        CASE
                            WHEN subplant = '2A' THEN 'A0801001'
                            WHEN subplant = '2B' THEN 'B2201001'
                            WHEN subplant = '2C' THEN 'C2201001'
                            END,
                        pallet_no,
                        0,
                        now())
                ON CONFLICT (io_no_pallet) DO NOTHING
                RETURNING io_plan_kode, io_kd_lok, io_no_pallet, io_qty_pallet, io_tgl
        )
        INSERT
        INTO inv_opname_hist(ioh_plan_kode, ioh_kd_lok, ioh_no_pallet, ioh_qty_pallet, ioh_tgl, ioh_txn,
                             ioh_userid)
        SELECT io_plan_kode,
               io_kd_lok,
               io_no_pallet,
               io_qty_pallet,
               io_tgl,
               '[System] Auto Transit',
               userid_handover
        FROM allocated_pallet;
    END IF;
    RETURN NULL; -- after trigger, result is ignored.
END;
$$;


ALTER FUNCTION public.auto_transit_hasilbj() OWNER TO armasi;

--
-- Name: avals(public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.avals(public.hstore) RETURNS text[]
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_avals';


ALTER FUNCTION public.avals(public.hstore) OWNER TO postgres;

--
-- Name: batch_update_pallet_location(character varying[], character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.batch_update_pallet_location(pallet_nos character varying[], new_location_no character varying, userid character varying, approval_message character varying DEFAULT ''::character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
  pallets_affected           INTEGER;

  nonexistent_pallet_nos     VARCHAR [];
  pallet_nos_not_handed_over VARCHAR [];
  invalid_pallet_nos         VARCHAR [];
  pallet_nos_with_location   VARCHAR [];

  new_mbj_no_str             VARCHAR;
  location_record            RECORD;

BEGIN
  pallets_affected := 0;

  -- check pallets
  nonexistent_pallet_nos := array_subtract(pallet_nos, (SELECT array_agg(pallet_no)
                                                        FROM tbl_sp_hasilbj
                                                        WHERE pallet_no = ANY(pallet_nos)));
  IF nonexistent_pallet_nos <> '{}'
  THEN
    RAISE EXCEPTION 'Nonexistent pallet nos detected! (%)', array_to_string(nonexistent_pallet_nos, ',');
  END IF;

  -- check location
  SELECT inv_master_lok_pallet.*, TRUE AS is_active INTO location_record
  FROM inv_master_lok_pallet
         INNER JOIN inv_master_area ima
           ON inv_master_lok_pallet.iml_plan_kode = ima.plan_kode AND inv_master_lok_pallet.iml_kd_area = ima.kd_area
  WHERE iml_kd_lok = new_location_no;
  IF NOT FOUND
  THEN
    RAISE EXCEPTION 'Location (%) not found in DB!', new_location_no;
  END IF;
  IF location_record.is_active IS FALSE
  THEN
    RAISE EXCEPTION 'Location (%) is not available for insertion!', new_location_no;
  END IF;

  -- check for invalid pallets (empty/not validated by QA)
  SELECT array_agg(pallet_no)
      INTO invalid_pallet_nos
  FROM tbl_sp_hasilbj
  WHERE pallet_no = ANY(pallet_nos)
    AND ((qa_approved IS FALSE AND terima_no IS NULL AND pallet_no LIKE 'PLT%')
           OR last_qty = 0);

  IF invalid_pallet_nos <> '{}'
  THEN
    RAISE EXCEPTION 'Invalid pallets! (%)', array_to_string(invalid_pallet_nos, ',');
  END IF;

  -- step 1: handover pallets not handed over.
  SELECT array_agg(pallet_no)
      INTO pallet_nos_not_handed_over
  FROM tbl_sp_hasilbj
  WHERE pallet_no = ANY(pallet_nos)
    AND terima_no IS NULL
      --AND qa_approved IS TRUE
    AND pallet_no LIKE 'PLT%';
  IF pallet_nos_not_handed_over <> '{}'
  THEN
    -- do handover first
    new_mbj_no_str := (SELECT add_pallets_to_warehouse(pallet_nos_not_handed_over, userid));
    RAISE NOTICE '% pallets handed over with ref_no: %', array_length(pallet_nos_not_handed_over, 1), new_mbj_no_str;
  END IF;

  -- step 2: insert to location history table
  INSERT INTO inv_opname_hist (ioh_plan_kode,
                               ioh_kd_lok,
                               ioh_no_pallet,
                               ioh_qty_pallet,
                               ioh_tgl,
                               ioh_txn,
                               ioh_userid,
                               ioh_kd_lok_old)
  SELECT location_record.iml_plan_kode,
         location_record.iml_kd_lok,
         hasilbj.pallet_no,
         COALESCE(io.io_qty_pallet, 0),
         CURRENT_TIMESTAMP,
         (CASE
            WHEN COALESCE(approval_message, '') <> '' THEN approval_message
            WHEN io.io_kd_lok IS NULL THEN 'Masuk ke ' || location_record.iml_kd_lok
            ELSE 'Pindah dari ' || io.io_kd_lok || ' ke ' || location_record.iml_kd_lok
             END),
         userid,
         COALESCE(io.io_kd_lok, '0')
  FROM tbl_sp_hasilbj hasilbj
         LEFT JOIN inv_opname io ON hasilbj.pallet_no = io.io_no_pallet
  WHERE pallet_no = ANY(pallet_nos);

  -- set return value
  GET DIAGNOSTICS pallets_affected := ROW_COUNT;

  -- step 3: update to location
  SELECT array_agg(io_no_pallet) INTO pallet_nos_with_location FROM inv_opname WHERE io_no_pallet = ANY(pallet_nos);
  IF COALESCE(array_length(pallet_nos_with_location, 1), 0) > 0
  THEN
    UPDATE inv_opname
    SET io_kd_lok    = location_record.iml_kd_lok,
        io_plan_kode = location_record.iml_plan_kode,
        io_tgl       = CURRENT_TIMESTAMP
    WHERE io_no_pallet = ANY(pallet_nos_with_location);
  ELSE pallet_nos_with_location := '{}';
  END IF;

  -- step 4: insert to location.
  INSERT INTO inv_opname (io_plan_kode, io_kd_lok, io_no_pallet, io_qty_pallet, io_tgl)
  SELECT location_record.iml_plan_kode, location_record.iml_kd_lok, pallet_no, 0, CURRENT_TIMESTAMP
  FROM unnest(array_subtract(pallet_nos, pallet_nos_with_location)) AS pallet_no;

  RETURN pallets_affected;
END;
$$;


ALTER FUNCTION public.batch_update_pallet_location(pallet_nos character varying[], new_location_no character varying, userid character varying, approval_message character varying) OWNER TO armasi;

--
-- Name: block_pallets(character varying[], character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.block_pallets(pallet_nos character varying[], reason character varying, userid_requester character varying, block_ref_txn character varying DEFAULT NULL::character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    affected_pallets INT;
BEGIN
    IF COALESCE(array_length(pallet_nos, 1), 0) = 0 THEN
        RAISE EXCEPTION 'No requested pallet!';
    END IF;
    IF COALESCE(TRIM(userid_requester), '') = '' THEN
        RAISE EXCEPTION 'userid_requester is empty!';
    END IF;

    CREATE TEMP TABLE pallets_to_block ON COMMIT DROP AS
    SELECT requested_pallet_no, status_plt, last_qty
    FROM unnest(pallet_nos) requested_pallet_no
             LEFT JOIN tbl_sp_hasilbj ON requested_pallet_no = pallet_no;

    IF EXISTS(SELECT * FROM pallets_to_block WHERE status_plt IS NULL) THEN
        RAISE EXCEPTION 'Some requested pallet(s) are not in pallet record!';
    END IF;

    IF EXISTS(SELECT * FROM pallets_to_block WHERE status_plt <> 'R') THEN
        RAISE EXCEPTION 'Some requested pallet(s) are not on valid state!';
    END IF;

    IF EXISTS(SELECT * FROM pallets_to_block WHERE last_qty = 0) THEN
        RAISE EXCEPTION 'Some requested pallet(s) are empty! Cannot block empty pallet(s)!';
    END IF;

    WITH updated_pallets AS (
        UPDATE tbl_sp_hasilbj hasilbj
            SET status_plt = 'B', block_ref_id = block_ref_txn, keterangan = '[BLOKIR] ' || reason,
                update_tran_user = userid_requester,
                update_tran = now()
            FROM pallets_to_block t1
                JOIN tbl_sp_hasilbj t2 ON t1.requested_pallet_no = t2.pallet_no
            WHERE hasilbj.pallet_no = t1.requested_pallet_no
            RETURNING hasilbj.pallet_no, hasilbj.subplant, hasilbj.update_tran_user,
                hasilbj.keterangan AS new_keterangan, t2.keterangan AS old_keterangan,
                hasilbj.status_plt AS new_status_plt, t2.status_plt AS old_status_plt,
                hasilbj.block_ref_id AS new_block_ref_id, t2.block_ref_id AS old_block_ref_id
    )
    INSERT
    INTO pallet_events(event_id, pallet_no, userid, plant_id, old_values, new_values)
    SELECT (SELECT id FROM pallet_event_types WHERE event_name = 'block'),
           pallet_no,
           update_tran_user,
           (CASE WHEN subplant IN ('4', '5') THEN subplant || 'A' ELSE subplant END),
           jsonb_build_object(
                   'status_plt', old_status_plt,
                   'block_ref_id', old_block_ref_id,
                   'keterangan', old_keterangan
               ),
           jsonb_build_object(
                   'status_plt', new_status_plt,
                   'block_ref_id', new_block_ref_id,
                   'keterangan', new_keterangan
               )
    FROM updated_pallets;
    GET DIAGNOSTICS affected_pallets := ROW_COUNT;
    DROP TABLE pallets_to_block;
    RETURN affected_pallets;
END;
$$;


ALTER FUNCTION public.block_pallets(pallet_nos character varying[], reason character varying, userid_requester character varying, block_ref_txn character varying) OWNER TO armasi;

--
-- Name: cancel_downgrade_request(character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.cancel_downgrade_request(downgrade_id character varying, reason character varying, userid_requester character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    downgrade_rec    RECORD;
    affected_pallets INT;
BEGIN
    SELECT * INTO downgrade_rec FROM tbl_sp_downgrade_pallet WHERE no_downgrade = downgrade_id;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Downgrade record with ID % is not found!', downgrade_id;
    END IF;
    -- check status
    IF downgrade_rec.status = 'R' THEN
        RAISE EXCEPTION 'Downgrade record with ID % has been rejected!', downgrade_id;
    ELSEIF downgrade_rec.status = 'A' THEN
        RAISE EXCEPTION 'Downgrade record with ID % has been approved!', downgrade_id;
    ELSEIF downgrade_rec.status = 'C' THEN
        RAISE EXCEPTION 'Downgrade record with ID % has been cancelled!', downgrade_id;
    END IF;

    affected_pallets :=
            (SELECT unblock_pallets((SELECT array_agg(pallet_no)
                                     FROM tbl_sp_downgrade_pallet
                                     WHERE no_downgrade = downgrade_id), userid_requester,
                                    format('Batal Downgrade (%s)', reason),
                                    TRUE));
    UPDATE tbl_sp_downgrade_pallet
    SET status          = 'C',
        last_updated_at = now(),
        last_updated_by = userid_requester,
        keterangan      = keterangan || format(' [Batal: %s]', reason)
    WHERE no_downgrade = downgrade_rec.no_downgrade;
    RETURN affected_pallets;
END;
$$;


ALTER FUNCTION public.cancel_downgrade_request(downgrade_id character varying, reason character varying, userid_requester character varying) OWNER TO armasi;

--
-- Name: cancel_pallet(character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.cancel_pallet(pallet_id character varying, userid character varying, reason character varying, source character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
DECLARE
  pallet RECORD;
  txn_no INT;
  txn_str VARCHAR := '';
  ev_name VARCHAR;
BEGIN
  IF source = 'logistics' THEN
    ev_name := 'log_cancelled';
  ELSEIF source = 'production' THEN
    ev_name := 'production_cancelled';
  ELSE
    RAISE EXCEPTION 'Unknown source %!', source;
  end if;

  SELECT * INTO pallet FROM tbl_sp_hasilbj t1 WHERE t1.pallet_no = pallet_id;
  IF NOT FOUND THEN
    RAISE EXCEPTION 'Pallet with id % not found!', pallet_id;
  END IF;

  IF pallet.status_plt = 'C' THEN
    RAISE EXCEPTION 'Pallet with id % has been cancelled!', pallet_id;
  end if;

  IF pallet.last_qty = 0 THEN
    RAISE EXCEPTION 'Cannot cancel empty pallet!';
  END IF;

  IF pallet.status_plt = 'R' THEN
    -- create a CNC on mutation table
    -- format: CNC/<subplant>/<2-digit month>/<2-digit year>/<5-digit code>
    INSERT INTO txn_counters(plant_id, txn_id, period)
    VALUES (pallet.subplant, 'CNC', 'month')
    ON CONFLICT (plant_id, txn_id) DO UPDATE
    SET count = (
      CASE WHEN date_trunc(txn_counters.period, txn_counters.last_updated_at) = date_trunc(txn_counters.period, now()) THEN txn_counters.count + 1
      ELSE 1
      END
    ), last_updated_at = now()
    RETURNING count INTO txn_no;

    txn_str := 'CNC/' || pallet.subplant || '/' || to_char(now(), 'MM') || '/' || to_char(now(), 'YY') || to_char(txn_no, 'fm00000');
    INSERT INTO tbl_sp_mutasi_pallet
    (plan_kode,
     no_mutasi, tanggal,
     pallet_no, qty,
     create_date, create_user, status_mut)
    VALUES(pallet.plan_kode,
           txn_str,
           CURRENT_DATE,
           pallet.pallet_no,
           -1 * pallet.last_qty,
           now(),
           userid,
           'O');
  END IF;
  -- record the cancellation.
  INSERT INTO pallet_events(event_id, pallet_no, userid, plant_id, old_values, new_values)
  VALUES((SELECT id FROM pallet_event_types WHERE event_name = ev_name), pallet.pallet_no, userid,
         (CASE WHEN pallet.subplant = '4' THEN '4A'
           WHEN pallet.subplant = '5' THEN '5A'
           ELSE pallet.subplant
           END),
         json_build_object('status_plt', pallet.status_plt),
         json_build_object('status_plt', 'C', 'reason', reason));

  UPDATE tbl_sp_hasilbj t1
    SET status_plt = 'C', update_tran = now(), update_tran_user = userid, keterangan = reason
  WHERE t1.pallet_no = pallet_id;
  RETURN txn_str;
END;
$$;


ALTER FUNCTION public.cancel_pallet(pallet_id character varying, userid character varying, reason character varying, source character varying) OWNER TO armasi;

--
-- Name: cancel_shipping_txn(character varying, character varying, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.cancel_shipping_txn(mutasi_id character varying, pallet_id character varying, subplant_id character varying, user_id character varying, status_id character varying, noba_id character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
DECLARE
    pallet       RECORD;
    txn_no       INT;
    car_stat_mut VARCHAR;
    max_stat_mut INT;
    kod_stat_mut VARCHAR := '';
    txn_str      VARCHAR := '';
BEGIN
    SELECT * INTO pallet
    FROM tbl_sp_mutasi_pallet t1
    WHERE t1.no_mutasi = mutasi_id
      and t1.pallet_no = pallet_id
      and t1.status_mut = status_id;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Mutasi with id % not found!', mutasi_id;
    END IF;

    -- create a CNT on mutation table
    -- format: CNT/<subplant>/<2-digit year>/<6-digit code>
    INSERT INTO txn_counters(plant_id, txn_id, period)
    VALUES (pallet.plan_kode, 'CNT', 'year')
    ON CONFLICT (plant_id, txn_id) DO UPDATE
        SET count           = (
            CASE
                WHEN date_trunc(txn_counters.period, txn_counters.last_updated_at) =
                     date_trunc(txn_counters.period, now()) THEN txn_counters.count + 1
                ELSE 1
                END
            ),
            last_updated_at = now()
            RETURNING count INTO txn_no;
            
    DELETE FROM item_retur_produksi WHERE retur_kode = mutasi_id AND pallet_no = pallet_id;
    DELETE FROM tbl_retur_produksi WHERE retur_kode = mutasi_id;

    txn_str := format('CNT/%s/%s/%s', pallet.plan_kode, to_char(now(), 'YY'), to_char(txn_no, 'fm000000'));
    INSERT INTO tbl_sp_mutasi_pallet(plan_kode, no_mutasi, tanggal,
                                     pallet_no, qty, create_date, create_user, status_mut, reff_txn)
    VALUES (pallet.plan_kode, txn_str, CURRENT_DATE,
            pallet.pallet_no, -1 * pallet.qty, now(), user_id, 'O', pallet.no_mutasi);

    SELECT MAX(SUBSTRING(status_mut FROM 2)) INTO car_stat_mut
    FROM tbl_sp_mutasi_pallet
    WHERE no_mutasi = pallet.no_mutasi
      and pallet_no = pallet.pallet_no
      and status_mut like 'C%';
    IF NOT FOUND THEN
        kod_stat_mut := 'C';
    END IF;

    IF car_stat_mut IS NULL THEN
        kod_stat_mut := 'C';
    ELSEIF car_stat_mut = '' THEN
        kod_stat_mut := 'C1';
    ELSE
        max_stat_mut := car_stat_mut::integer + 1;
        kod_stat_mut := 'C' || max_stat_mut;
    END IF;
    
    UPDATE tbl_sp_mutasi_pallet
    SET status_mut = kod_stat_mut,
        keterangan = 'Cancel Input Palet [' || txn_str || ']'
    WHERE no_mutasi = pallet.no_mutasi
      and pallet_no = pallet.pallet_no
      and status_mut = pallet.status_mut;

    UPDATE tbl_sp_hasilbj
    SET last_qty         = last_qty - pallet.qty,
        update_tran      = now(),
        update_tran_user = user_id
    WHERE pallet_no = pallet.pallet_no;

    UPDATE tbl_ba_muat_detail
    SET kode_lama =
            (SELECT CASE WHEN qty ISNULL THEN 'O' WHEN vol = qty THEN 'F' ELSE 'P' END AS sts
             FROM (SELECT no_ba, SUM(volume) AS vol
                   FROM tbl_ba_muat_detail
                   WHERE no_ba = noba_id
                     and sub_plant = subplant_id
                   GROUP BY no_ba) AS S
                      LEFT OUTER JOIN
                  (SELECT no_mutasi, SUM(qty) AS qty
                   FROM (SELECT a.no_mutasi, (a.qty * -1) AS qty
                         FROM tbl_sp_mutasi_pallet a
                                  INNER JOIN tbl_sp_hasilbj b ON a.pallet_no = b.pallet_no
                         WHERE no_mutasi = noba_id
                           AND subplant = subplant_id
                           AND (status_mut <> 'L' OR status_mut <> 'F')
                         UNION ALL
                         SELECT a.reff_txn AS no_mutasi, (a.qty * -1) AS qty
                         FROM tbl_sp_mutasi_pallet a
                                  INNER JOIN tbl_sp_hasilbj b ON a.pallet_no = b.pallet_no
                         WHERE reff_txn = noba_id
                           and subplant = subplant_id
                           and (status_mut = 'L' or status_mut = 'F')
                        )
                            AS A
                   group by no_mutasi
                  )
                      AS S1 ON no_ba = no_mutasi
            )
    WHERE no_ba = noba_id
      and sub_plant = subplant_id;

    UPDATE tbl_ba_muat
    SET kode_lama =
            (SELECT CASE WHEN hit2 IS NULL THEN 'O' WHEN hit1 = hit3 THEN 'F' ELSE 'P' END AS sts
             FROM (SELECT no_ba AS ba1, COUNT(*) AS hit1
                   FROM tbl_ba_muat_detail
                   WHERE no_ba = noba_id
                   GROUP BY no_ba) AS S
                      LEFT OUTER JOIN (SELECT no_ba as ba2, COUNT(*) AS hit2
                                       FROM tbl_ba_muat_detail
                                       WHERE no_ba = noba_id
                                         AND kode_lama = 'P'
                                       GROUP BY no_ba) AS S1 ON ba1 = ba2
                      LEFT OUTER JOIN (SELECT no_ba as ba3, COUNT(*) AS hit3
                                       FROM tbl_ba_muat_detail
                                       WHERE no_ba = noba_id
                                         AND kode_lama = 'F'
                                       GROUP BY no_ba) AS S2 ON ba1 = ba3
            )
    WHERE no_ba = noba_id;

    RETURN txn_str;
END;
$$;


ALTER FUNCTION public.cancel_shipping_txn(mutasi_id character varying, pallet_id character varying, subplant_id character varying, user_id character varying, status_id character varying, noba_id character varying) OWNER TO armasi;

--
-- Name: check_pallets_move_to_warehouse_status(character varying[]); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.check_pallets_move_to_warehouse_status(pallet_nos character varying[]) RETURNS SETOF record
    LANGUAGE plpgsql
    AS $$
DECLARE
  valid_pallet_nos       VARCHAR [];
  nonexisting_pallet_nos VARCHAR [];
  invalid_pallet_nos     VARCHAR [];
BEGIN
  -- check the pallet_nos first.
  CREATE TEMP TABLE temp_pallet_move_status ON COMMIT DROP AS
    SELECT pallet_no, subplant, 0 :: INT AS status_code
    FROM tbl_sp_hasilbj
    WHERE COALESCE(rkpterima_no, '') <> ''
      AND COALESCE(terima_no, '') = ''
      AND substr(pallet_no, 1, 3) = 'PLT'
      AND status_plt = 'O'
      AND (qa_approved IS TRUE OR flag_qa_approval_enabled() IS FALSE)
      AND pallet_no = ANY (pallet_nos);
  SELECT array_agg(pallet_no)
      INTO valid_pallet_nos FROM temp_pallet_move_status;
  invalid_pallet_nos = array_subtract(pallet_nos, valid_pallet_nos);

  /* invalid codes:
    - 0: ok.
    - 1: not exist.
    - 2: canceled by production.
    - 3: not marked for handover by production
    - 4: not verified by QA
    - 5: not in production (already shipped to logistics).
    - 6: PLM is requested for handover
    - 7: others (undefined?)
  */
  INSERT INTO temp_pallet_move_status
  SELECT pallet_no,
         subplant,
         (CASE
            WHEN status_plt = 'C' THEN 2
            WHEN (qa_approved IS FALSE AND flag_qa_approval_enabled() IS TRUE) THEN 4
            WHEN rkpterima_no IS NULL THEN 3
            WHEN pallet_no LIKE 'PLM%' THEN 6
            WHEN terima_no IS NOT NULL THEN 5
            ELSE 7
             END)
  FROM tbl_sp_hasilbj
  WHERE pallet_no = ANY (invalid_pallet_nos);

  -- add nonexisting pallets
  nonexisting_pallet_nos := array_subtract(invalid_pallet_nos,
                                           (SELECT array_agg(pallet_no) FROM temp_pallet_move_status));

  INSERT INTO temp_pallet_move_status
  SELECT pallet_no, SUBSTRING(pallet_no, subplant_regex()) AS subplant, 1 :: INT
  FROM unnest(nonexisting_pallet_nos) AS pallet_no;
  RETURN QUERY SELECT * FROM temp_pallet_move_status;
  DROP TABLE temp_pallet_move_status;
END;
$$;


ALTER FUNCTION public.check_pallets_move_to_warehouse_status(pallet_nos character varying[]) OWNER TO armasi;

--
-- Name: check_user_can_move_pallet_to_warehouse(character varying[], character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.check_user_can_move_pallet_to_warehouse(pallet_nos character varying[], userid character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
DECLARE
  valid_roles         VARCHAR [];
  requested_subplants VARCHAR [];
  user_record         RECORD;
  user_authorized     BOOLEAN;
BEGIN
  -- valid roles: check classes/UserRole.php for description of these roles.
  valid_roles := ARRAY ['SU', 'KS', 'SK', 'LM', 'LC', 'LK', 'CK', 'LH', 'PM'];
  SELECT array_agg(subplant)
      INTO requested_subplants
  FROM (SELECT DISTINCT (CASE
                           WHEN subplant = '4' THEN '4A'
                           WHEN subplant = '5' THEN '5A'
                           ELSE subplant END) subplant
        FROM check_pallets_move_to_warehouse_status(pallet_nos) AS
                 pallet_records (pallet_no VARCHAR, subplant VARCHAR, status_code INT)
        WHERE subplant IS NOT NULL) s;

  SELECT *
      INTO user_record FROM gen_user_adm WHERE gua_kode = userid;
  IF NOT FOUND
  THEN
    RAISE EXCEPTION 'User with id ''%'' not found!', userid;
  END IF;

  user_authorized := COALESCE(user_record.gua_lvl && valid_roles AND
                               requested_subplants <@
                               string_to_array(user_record.gua_subplant_handover, ',') :: VARCHAR [],
                              FALSE);
  RETURN user_authorized;
END;
$$;


ALTER FUNCTION public.check_user_can_move_pallet_to_warehouse(pallet_nos character varying[], userid character varying) OWNER TO armasi;

--
-- Name: check_user_can_move_pallets_in_warehouse(character varying[], character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.check_user_can_move_pallets_in_warehouse(pallet_nos character varying[], userid character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
DECLARE
  valid_roles         VARCHAR [];
  requested_subplants VARCHAR [];
  user_record         RECORD;
BEGIN
  -- valid roles: check classes/UserRole.php for description of these roles.
  valid_roles := ARRAY ['SU', 'KS', 'SK', 'LM', 'LC', 'LK', 'CK', 'LH', 'PM'];
  SELECT array_agg(subplant)
      INTO requested_subplants
  FROM (SELECT DISTINCT (CASE
                           WHEN subplant = '4' THEN '4A'
                           WHEN subplant = '5' THEN '5A'
                           ELSE subplant END) subplant
        FROM get_multiple_pallet_locations_with_quantity(pallet_nos) AS
                 pallet_records (pallet_no VARCHAR, subplant VARCHAR, current_location_no VARCHAR, current_quantity INT)
        WHERE subplant IS NOT NULL) s;

  SELECT *
      INTO user_record FROM gen_user_adm WHERE gua_kode = userid;
  IF NOT FOUND
  THEN
    RAISE EXCEPTION 'User with id ''%'' not found!', userid;
  END IF;

  RETURN COALESCE(user_record.gua_lvl && valid_roles AND
                  requested_subplants <@
                  string_to_array(user_record.gua_subplants, ',') :: VARCHAR [],
                  FALSE
  );
END;
$$;


ALTER FUNCTION public.check_user_can_move_pallets_in_warehouse(pallet_nos character varying[], userid character varying) OWNER TO armasi;

--
-- Name: check_user_exists(character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.check_user_exists(userid character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN
	SELECT * FROM gen_user_adm WHERE gua_kode = userid LIMIT 1;
	RETURN FOUND;
END;
$$;


ALTER FUNCTION public.check_user_exists(userid character varying) OWNER TO armasi;

--
-- Name: conf_downgrade_kw4_treat_as_broken(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.conf_downgrade_kw4_treat_as_broken() RETURNS boolean
    LANGUAGE sql IMMUTABLE
    AS $$
SELECT TRUE -- adjust based on plant requirements.
$$;


ALTER FUNCTION public.conf_downgrade_kw4_treat_as_broken() OWNER TO armasi;

--
-- Name: FUNCTION conf_downgrade_kw4_treat_as_broken(); Type: COMMENT; Schema: public; Owner: armasi
--

COMMENT ON FUNCTION public.conf_downgrade_kw4_treat_as_broken() IS '
    Flag that determines if downgrade to KW4 will be treated as broken event.
';


--
-- Name: const_downgrade_type_eco_to_kw4(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.const_downgrade_type_eco_to_kw4() RETURNS character varying
    LANGUAGE sql IMMUTABLE
    AS $$
SELECT '3';
$$;


ALTER FUNCTION public.const_downgrade_type_eco_to_kw4() OWNER TO armasi;

--
-- Name: const_downgrade_type_exp_to_eco(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.const_downgrade_type_exp_to_eco() RETURNS character varying
    LANGUAGE sql IMMUTABLE
    AS $$
SELECT '1';
$$;


ALTER FUNCTION public.const_downgrade_type_exp_to_eco() OWNER TO armasi;

--
-- Name: const_downgrade_type_exp_to_kw4(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.const_downgrade_type_exp_to_kw4() RETURNS character varying
    LANGUAGE sql IMMUTABLE
    AS $$
SELECT '2';
$$;


ALTER FUNCTION public.const_downgrade_type_exp_to_kw4() OWNER TO armasi;

--
-- Name: create_block_quantity_request(character varying, character varying, character varying, character varying, character varying, character varying, character varying[], integer[], character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.create_block_quantity_request(v_mode character varying, v_plant character varying, v_subplant character varying, v_customer character varying, v_target_date character varying, v_keterangan character varying, v_pallet_no_s character varying[], v_qty_s integer[], userid_requester character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_order_id VARCHAR;
    v_pallet_no VARCHAR;
    x int := 1;
BEGIN
    IF COALESCE(TRIM(v_customer), '') = '' THEN
        RAISE EXCEPTION 'Customer is empty!';
    END IF;
    IF COALESCE(TRIM(v_target_date), '') = '' THEN
        RAISE EXCEPTION 'Target Date is empty!';
    END IF;
    IF COALESCE(array_length(v_pallet_no_s, 1), 0) = 0 THEN
        RAISE EXCEPTION 'Tidak ada item untuk di block!';
    END IF;

    IF v_mode = 'ADD' THEN
      INSERT INTO txn_counters(plant_id, txn_id, period, count, last_period)
      VALUES (v_subplant, 'PBQ', 'month', 1, date_trunc('month', now()))
      ON CONFLICT (plant_id, txn_id) DO UPDATE
          SET count = (CASE WHEN txn_counters.last_period = excluded.last_period THEN txn_counters.count + 1 ELSE 1 END),
              last_period     = excluded.last_period,
              last_updated_at = now()
              RETURNING format('%s/%s/%s/%s/%s', txn_counters.txn_id, txn_counters.plant_id, to_char(txn_counters.last_period, 'MM'), to_char(txn_counters.last_period, 'YY'), to_char(txn_counters.count, 'fm00000')) INTO v_order_id;
      INSERT INTO tbl_gbj_stockblock(plan_kode, subplant, order_id, customer_id, order_target_date, keterangan, order_status, create_date, create_user, last_updated_at, last_updated_by) VALUES(v_plant, v_subplant, v_order_id, v_customer, v_target_date::date, v_keterangan, 'O', now(), userid_requester, now(), userid_requester);
    ELSE
      v_order_id := v_plant;
      UPDATE tbl_gbj_stockblock SET customer_id = v_customer, order_target_date = v_target_date::date, keterangan = v_keterangan, last_updated_at = now(), last_updated_by = userid_requester WHERE order_id = v_order_id;
      DELETE FROM item_gbj_stockblock WHERE order_id = v_order_id;
    END IF;

    FOREACH v_pallet_no IN ARRAY v_pallet_no_s
    LOOP
      INSERT INTO item_gbj_stockblock(order_id, subplant, pallet_no, quantity, order_status) VALUES(v_order_id, v_subplant, v_pallet_no, v_qty_s[x], 'O');
      x := x + 1;
    END LOOP;

    RETURN v_order_id;
END;
$$;


ALTER FUNCTION public.create_block_quantity_request(v_mode character varying, v_plant character varying, v_subplant character varying, v_customer character varying, v_target_date character varying, v_keterangan character varying, v_pallet_no_s character varying[], v_qty_s integer[], userid_requester character varying) OWNER TO armasi;

--
-- Name: create_downgrade_request(character varying, character varying[], character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.create_downgrade_request(requested_subplant character varying, pallet_nos character varying[], downgrade_type character varying, reason character varying, userid_requester character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
DECLARE
    source_quality VARCHAR;
    dwg_id         VARCHAR;
BEGIN
    IF COALESCE(array_length(pallet_nos, 1), 0) = 0 THEN
        RAISE EXCEPTION 'No pallet to downgrade!';
    END IF;
    IF downgrade_type NOT IN
       (const_downgrade_type_eco_to_kw4(), const_downgrade_type_exp_to_eco(), const_downgrade_type_exp_to_kw4()) THEN
        RAISE EXCEPTION 'Invalid downgrade type request %!', downgrade_type;
    END IF;
    IF COALESCE(TRIM(reason), '') = '' THEN
        RAISE EXCEPTION 'Reason is empty!';
    END IF;
    source_quality := (
        CASE
            WHEN downgrade_type IN (const_downgrade_type_exp_to_eco(), const_downgrade_type_exp_to_kw4()) THEN 'EXPORT'
            WHEN downgrade_type IN (const_downgrade_type_eco_to_kw4()) THEN 'EKONOMI'
            ELSE NULL END
        );
    IF source_quality IS NULL THEN
        RAISE EXCEPTION 'Unidentified source quality for downgrade request %', downgrade_type;
    END IF;

    -- assume everything are pallet numbers
    CREATE TEMP TABLE pallets_to_add ON COMMIT DROP AS
    SELECT plan_kode, requested_pallet_no, subplant, quality, item_kode, last_qty
    FROM unnest(pallet_nos) requested_pallet_no
             LEFT JOIN tbl_sp_hasilbj hasilbj ON requested_pallet_no = hasilbj.pallet_no;

    -- check existence
    IF EXISTS(SELECT *
              FROM pallets_to_add
              WHERE plan_kode IS NULL)
    THEN
        RAISE EXCEPTION 'Some pallet(s) does no exist in the database!';
    END IF;

    -- check quality
    IF EXISTS(SELECT *
              FROM pallets_to_add
              WHERE quality <> source_quality)
    THEN
        RAISE EXCEPTION 'Some pallet(s) quality does not match. Expected quality: %', source_quality;
    END IF;

    -- check subplant
    IF EXISTS(SELECT * FROM pallets_to_add WHERE subplant <> requested_subplant)
    THEN
        RAISE EXCEPTION 'Cannot request downgrade for pallets from other subplant than %!', requested_subplant;
    END IF;

    -- check if they're still on existing downgrade requests
    IF EXISTS(SELECT *
              FROM pallets_to_add
                       JOIN tbl_sp_downgrade_pallet dwg
                            ON requested_pallet_no = pallet_no AND dwg.status = 'O')
    THEN
        RAISE EXCEPTION 'Some pallet(s) are still being requested for downgrade! Cannot add to new downgrade record!';
    END IF;

    -- generate transaction id
    INSERT INTO txn_counters(plant_id, txn_id, period, count, last_period)
    VALUES ((SELECT subplant FROM pallets_to_add LIMIT 1),
            'PDG',
            'month',
            1,
            date_trunc('month', now()))
    ON CONFLICT (plant_id, txn_id) DO UPDATE
        SET count           = (CASE
                                   WHEN txn_counters.last_period = excluded.last_period THEN txn_counters.count + 1
                                   ELSE 1 END),
            last_period     = excluded.last_period,
            last_updated_at = now()
            RETURNING format('%s/%s/%s/%s/%s',
                             txn_counters.txn_id, txn_counters.plant_id,
                             to_char(txn_counters.last_period, 'MM'), to_char(txn_counters.last_period, 'YY'),
                             to_char(txn_counters.count, 'fm00000')) INTO dwg_id;

    -- block_pallets will throw error if the blocking fails.
    PERFORM block_pallets(array_agg(requested_pallet_no), 'Downgrade', userid_requester, dwg_id) FROM pallets_to_add;
    INSERT INTO tbl_sp_downgrade_pallet
    (plan_kode, no_downgrade, tanggal, pallet_no,
     create_date, create_user, item_kode_lama,
     keterangan, qty, jenis_downgrade, status, last_updated_at, last_updated_by, subplant)
    SELECT plan_kode,
           dwg_id,
           CURRENT_DATE,
           requested_pallet_no,
           now(),
           userid_requester,
           item_kode,
           reason,
           last_qty,
           downgrade_type,
           'O',
           now(),
           userid_requester,
           subplant
    FROM pallets_to_add;

    RETURN dwg_id;
END;
$$;


ALTER FUNCTION public.create_downgrade_request(requested_subplant character varying, pallet_nos character varying[], downgrade_type character varying, reason character varying, userid_requester character varying) OWNER TO armasi;

--
-- Name: crypt(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.crypt(text, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_crypt';


ALTER FUNCTION public.crypt(text, text) OWNER TO postgres;

--
-- Name: dblink(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink(text) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_record';


ALTER FUNCTION public.dblink(text) OWNER TO postgres;

--
-- Name: dblink(text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink(text, boolean) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_record';


ALTER FUNCTION public.dblink(text, boolean) OWNER TO postgres;

--
-- Name: dblink(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink(text, text) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_record';


ALTER FUNCTION public.dblink(text, text) OWNER TO postgres;

--
-- Name: dblink(text, text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink(text, text, boolean) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_record';


ALTER FUNCTION public.dblink(text, text, boolean) OWNER TO postgres;

--
-- Name: dblink_build_sql_delete(text, int2vector, integer, text[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_build_sql_delete(text, int2vector, integer, text[]) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_build_sql_delete';


ALTER FUNCTION public.dblink_build_sql_delete(text, int2vector, integer, text[]) OWNER TO postgres;

--
-- Name: dblink_build_sql_insert(text, int2vector, integer, text[], text[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_build_sql_insert(text, int2vector, integer, text[], text[]) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_build_sql_insert';


ALTER FUNCTION public.dblink_build_sql_insert(text, int2vector, integer, text[], text[]) OWNER TO postgres;

--
-- Name: dblink_build_sql_update(text, int2vector, integer, text[], text[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_build_sql_update(text, int2vector, integer, text[], text[]) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_build_sql_update';


ALTER FUNCTION public.dblink_build_sql_update(text, int2vector, integer, text[], text[]) OWNER TO postgres;

--
-- Name: dblink_cancel_query(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_cancel_query(text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_cancel_query';


ALTER FUNCTION public.dblink_cancel_query(text) OWNER TO postgres;

--
-- Name: dblink_close(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_close(text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_close';


ALTER FUNCTION public.dblink_close(text) OWNER TO postgres;

--
-- Name: dblink_close(text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_close(text, boolean) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_close';


ALTER FUNCTION public.dblink_close(text, boolean) OWNER TO postgres;

--
-- Name: dblink_close(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_close(text, text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_close';


ALTER FUNCTION public.dblink_close(text, text) OWNER TO postgres;

--
-- Name: dblink_close(text, text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_close(text, text, boolean) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_close';


ALTER FUNCTION public.dblink_close(text, text, boolean) OWNER TO postgres;

--
-- Name: dblink_connect(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_connect(text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_connect';


ALTER FUNCTION public.dblink_connect(text) OWNER TO postgres;

--
-- Name: dblink_connect(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_connect(text, text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_connect';


ALTER FUNCTION public.dblink_connect(text, text) OWNER TO postgres;

--
-- Name: dblink_connect_u(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_connect_u(text) RETURNS text
    LANGUAGE c STRICT SECURITY DEFINER
    AS '$libdir/dblink', 'dblink_connect';


ALTER FUNCTION public.dblink_connect_u(text) OWNER TO postgres;

--
-- Name: dblink_connect_u(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_connect_u(text, text) RETURNS text
    LANGUAGE c STRICT SECURITY DEFINER
    AS '$libdir/dblink', 'dblink_connect';


ALTER FUNCTION public.dblink_connect_u(text, text) OWNER TO postgres;

--
-- Name: dblink_current_query(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_current_query() RETURNS text
    LANGUAGE c
    AS '$libdir/dblink', 'dblink_current_query';


ALTER FUNCTION public.dblink_current_query() OWNER TO postgres;

--
-- Name: dblink_disconnect(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_disconnect() RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_disconnect';


ALTER FUNCTION public.dblink_disconnect() OWNER TO postgres;

--
-- Name: dblink_disconnect(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_disconnect(text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_disconnect';


ALTER FUNCTION public.dblink_disconnect(text) OWNER TO postgres;

--
-- Name: dblink_error_message(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_error_message(text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_error_message';


ALTER FUNCTION public.dblink_error_message(text) OWNER TO postgres;

--
-- Name: dblink_exec(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_exec(text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_exec';


ALTER FUNCTION public.dblink_exec(text) OWNER TO postgres;

--
-- Name: dblink_exec(text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_exec(text, boolean) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_exec';


ALTER FUNCTION public.dblink_exec(text, boolean) OWNER TO postgres;

--
-- Name: dblink_exec(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_exec(text, text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_exec';


ALTER FUNCTION public.dblink_exec(text, text) OWNER TO postgres;

--
-- Name: dblink_exec(text, text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_exec(text, text, boolean) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_exec';


ALTER FUNCTION public.dblink_exec(text, text, boolean) OWNER TO postgres;

--
-- Name: dblink_fetch(text, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_fetch(text, integer) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_fetch';


ALTER FUNCTION public.dblink_fetch(text, integer) OWNER TO postgres;

--
-- Name: dblink_fetch(text, integer, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_fetch(text, integer, boolean) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_fetch';


ALTER FUNCTION public.dblink_fetch(text, integer, boolean) OWNER TO postgres;

--
-- Name: dblink_fetch(text, text, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_fetch(text, text, integer) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_fetch';


ALTER FUNCTION public.dblink_fetch(text, text, integer) OWNER TO postgres;

--
-- Name: dblink_fetch(text, text, integer, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_fetch(text, text, integer, boolean) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_fetch';


ALTER FUNCTION public.dblink_fetch(text, text, integer, boolean) OWNER TO postgres;

--
-- Name: dblink_get_connections(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_get_connections() RETURNS text[]
    LANGUAGE c
    AS '$libdir/dblink', 'dblink_get_connections';


ALTER FUNCTION public.dblink_get_connections() OWNER TO postgres;

--
-- Name: dblink_get_pkey(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_get_pkey(text) RETURNS SETOF public.dblink_pkey_results
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_get_pkey';


ALTER FUNCTION public.dblink_get_pkey(text) OWNER TO postgres;

--
-- Name: dblink_get_result(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_get_result(text) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_get_result';


ALTER FUNCTION public.dblink_get_result(text) OWNER TO postgres;

--
-- Name: dblink_get_result(text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_get_result(text, boolean) RETURNS SETOF record
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_get_result';


ALTER FUNCTION public.dblink_get_result(text, boolean) OWNER TO postgres;

--
-- Name: dblink_is_busy(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_is_busy(text) RETURNS integer
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_is_busy';


ALTER FUNCTION public.dblink_is_busy(text) OWNER TO postgres;

--
-- Name: dblink_open(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_open(text, text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_open';


ALTER FUNCTION public.dblink_open(text, text) OWNER TO postgres;

--
-- Name: dblink_open(text, text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_open(text, text, boolean) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_open';


ALTER FUNCTION public.dblink_open(text, text, boolean) OWNER TO postgres;

--
-- Name: dblink_open(text, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_open(text, text, text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_open';


ALTER FUNCTION public.dblink_open(text, text, text) OWNER TO postgres;

--
-- Name: dblink_open(text, text, text, boolean); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_open(text, text, text, boolean) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_open';


ALTER FUNCTION public.dblink_open(text, text, text, boolean) OWNER TO postgres;

--
-- Name: dblink_send_query(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dblink_send_query(text, text) RETURNS integer
    LANGUAGE c STRICT
    AS '$libdir/dblink', 'dblink_send_query';


ALTER FUNCTION public.dblink_send_query(text, text) OWNER TO postgres;

--
-- Name: dearmor(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.dearmor(text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_dearmor';


ALTER FUNCTION public.dearmor(text) OWNER TO postgres;

--
-- Name: decrypt(bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.decrypt(bytea, bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_decrypt';


ALTER FUNCTION public.decrypt(bytea, bytea, text) OWNER TO postgres;

--
-- Name: decrypt_iv(bytea, bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.decrypt_iv(bytea, bytea, bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_decrypt_iv';


ALTER FUNCTION public.decrypt_iv(bytea, bytea, bytea, text) OWNER TO postgres;

--
-- Name: defined(public.hstore, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.defined(public.hstore, text) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_defined';


ALTER FUNCTION public.defined(public.hstore, text) OWNER TO postgres;

--
-- Name: delete(public.hstore, text[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.delete(public.hstore, text[]) RETURNS public.hstore
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_delete_array';


ALTER FUNCTION public.delete(public.hstore, text[]) OWNER TO postgres;

--
-- Name: delete(public.hstore, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.delete(public.hstore, text) RETURNS public.hstore
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_delete';


ALTER FUNCTION public.delete(public.hstore, text) OWNER TO postgres;

--
-- Name: delete(public.hstore, public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.delete(public.hstore, public.hstore) RETURNS public.hstore
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_delete_hstore';


ALTER FUNCTION public.delete(public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: digest(bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.digest(bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_digest';


ALTER FUNCTION public.digest(bytea, text) OWNER TO postgres;

--
-- Name: digest(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.digest(text, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_digest';


ALTER FUNCTION public.digest(text, text) OWNER TO postgres;

--
-- Name: each(public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.each(hs public.hstore, OUT key text, OUT value text) RETURNS SETOF record
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_each';


ALTER FUNCTION public.each(hs public.hstore, OUT key text, OUT value text) OWNER TO postgres;

--
-- Name: encrypt(bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.encrypt(bytea, bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_encrypt';


ALTER FUNCTION public.encrypt(bytea, bytea, text) OWNER TO postgres;

--
-- Name: encrypt_iv(bytea, bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.encrypt_iv(bytea, bytea, bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_encrypt_iv';


ALTER FUNCTION public.encrypt_iv(bytea, bytea, bytea, text) OWNER TO postgres;

--
-- Name: exist(public.hstore, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.exist(public.hstore, text) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_exists';


ALTER FUNCTION public.exist(public.hstore, text) OWNER TO postgres;

--
-- Name: exists_all(public.hstore, text[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.exists_all(public.hstore, text[]) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_exists_all';


ALTER FUNCTION public.exists_all(public.hstore, text[]) OWNER TO postgres;

--
-- Name: exists_any(public.hstore, text[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.exists_any(public.hstore, text[]) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_exists_any';


ALTER FUNCTION public.exists_any(public.hstore, text[]) OWNER TO postgres;

--
-- Name: fetchval(public.hstore, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fetchval(public.hstore, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_fetchval';


ALTER FUNCTION public.fetchval(public.hstore, text) OWNER TO postgres;

--
-- Name: flag_qa_approval_enabled(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.flag_qa_approval_enabled() RETURNS boolean
    LANGUAGE sql IMMUTABLE
    AS $$
SELECT TRUE
$$;


ALTER FUNCTION public.flag_qa_approval_enabled() OWNER TO armasi;

--
-- Name: FUNCTION flag_qa_approval_enabled(); Type: COMMENT; Schema: public; Owner: armasi
--

COMMENT ON FUNCTION public.flag_qa_approval_enabled() IS 'Flag that indicates QA approval is enabled on this database. Queries should use this flag to adjust qa_approved column output in views/filters.';


--
-- Name: fn_del_pallet_loc(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.fn_del_pallet_loc() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

DECLARE
  v_pal_no   VARCHAR;
  v_type_trx VARCHAR;
  n_last_qty INT;
  v_user     VARCHAR;
  v_no_ba    VARCHAR;
  v_plant    VARCHAR;
BEGIN
  if TG_OP = 'INSERT' then
    v_pal_no := new.pallet_no;
    v_type_trx := left(new.no_mutasi, 3);
    v_plant := substring(new.pallet_no, subplant_regex());
    v_user := new.create_user;
    v_no_ba := new.no_mutasi;

    -- auto-transit for P2
    IF get_plant_code() = 2 THEN
      IF v_plant NOT IN ('2A', '2B', '2C') THEN
        RAISE EXCEPTION 'Invalid subplant for pallet %: %!', v_pal_no, v_plant;
      END IF;

      IF v_type_trx IN ('SEQ', 'MBJ') THEN
        WITH inserted_pallet AS (
          INSERT INTO inv_opname(io_plan_kode, io_kd_lok, io_no_pallet, io_qty_pallet, io_tgl)
          VALUES (RIGHT(v_plant, 1),
                  CASE WHEN v_plant = '2A' THEN 'A0801001'
                    WHEN v_plant = '2B' THEN 'B2201001'
                    WHEN v_plant = '2C' THEN 'C2201001'
                  END,
                  v_pal_no,
                  0,
                  now())
          ON CONFLICT (io_no_pallet) DO NOTHING
          RETURNING io_plan_kode, io_kd_lok, io_no_pallet, io_qty_pallet, io_tgl
        ) INSERT INTO inv_opname_hist(ioh_plan_kode, ioh_kd_lok, ioh_no_pallet, ioh_qty_pallet, ioh_tgl, ioh_txn,
                                  ioh_userid)
          SELECT io_plan_kode, io_kd_lok, io_no_pallet, io_qty_pallet, io_tgl, '[System] Auto Transit', v_user
          FROM inserted_pallet;
      END IF;
    END IF;

    -- check quantity by summation.
    SELECT COALESCE(SUM(qty), 0) INTO n_last_qty FROM tbl_sp_mutasi_pallet WHERE pallet_no = v_pal_no;

    -- validate quantity
    IF n_last_qty < 0 THEN
      RAISE EXCEPTION 'Qty palet % tidak boleh kurang dari 0!', v_pal_no;
    ELSEIF n_last_qty = 0 THEN
      WITH deleted_pallet AS (
        DELETE FROM inv_opname WHERE io_no_pallet = v_pal_no
        RETURNING io_plan_kode, io_no_pallet, io_qty_pallet, io_tgl, io_kd_lok
      )
      insert into inv_opname_hist(ioh_plan_kode, ioh_kd_lok, ioh_no_pallet, ioh_qty_pallet, ioh_tgl, ioh_txn,
                                  ioh_userid, ioh_kd_lok_old)
      SELECT io_plan_kode,
             '0',
             io_no_pallet,
             io_qty_pallet,
             now(),
             'Txn No. ' || v_no_ba,
             v_user,
             io_kd_lok
      FROM deleted_pallet;
    END IF;
  END IF;
  RETURN NEW;
END;
$$;


ALTER FUNCTION public.fn_del_pallet_loc() OWNER TO armasi;

--
-- Name: fn_get_area(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_get_area(text) RETURNS text
    LANGUAGE plpgsql
    AS $_$
declare
csheet alias for $1;
cnmsheet integer;
begin

--select (select isc_name from im_sheet_code where isc_code=substring(csheet,4,1)) into cnmsheet;

select (
select count(*) from (
select iml_kd_area from inv_opname left outer join tbl_sp_hasilbj on io_no_pallet=pallet_no
left outer join item on item.item_kode=tbl_sp_hasilbj.item_kode
left outer join inv_master_lok_pallet on iml_kd_lok=io_kd_lok
left outer join inv_master_area on kd_area=iml_kd_area
where tbl_sp_hasilbj.item_kode=csheet
group by iml_kd_area) as a
) into cnmsheet;

return cnmsheet;

end;
$_$;


ALTER FUNCTION public.fn_get_area(text) OWNER TO postgres;

--
-- Name: fn_insert_to_hist(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.fn_insert_to_hist() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

declare
v_pal_no text;
v_loc_no text;
v_qty integer;
v_plant text;
v_area text;
begin
	if TG_OP='INSERT' then
		v_pal_no:=new.io_no_pallet;
		v_loc_no:=new.io_kd_lok;
		
		select last_qty into v_qty  from tbl_sp_hasilbj where pallet_no=v_pal_no;
		insert into inv_pallet_txn values (left(v_loc_no,1),substring(v_loc_no,2,2),now(),'P',v_loc_no,v_pal_no,v_qty);
		delete from inv_opname where io_kd_lok<>v_loc_no and io_no_pallet=v_pal_no;
	
	end if;
return new;
end;

$$;


ALTER FUNCTION public.fn_insert_to_hist() OWNER TO armasi;

--
-- Name: fn_mutation_report_downgrade(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.fn_mutation_report_downgrade() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    pallet_record RECORD;
    -- NOTE: DGI for downgrade in (new motif), DGO for downgrade out (old motif).
BEGIN
    IF OLD.item_kode_baru IS NULL AND NEW.item_kode_baru IS NOT NULL THEN
        SELECT * INTO pallet_record FROM tbl_sp_hasilbj WHERE pallet_no = NEW.pallet_no;
        INSERT INTO gbj_report.mutation_records(plant_id, subplant, pallet_no,
                                                motif_id, size, shading,
                                                mutation_type, mutation_id, ref_txn_id, quantity,
                                                mutation_time)
        VALUES (NEW.plan_kode, SUBSTRING(NEW.pallet_no, subplant_regex()), NEW.pallet_no,
                NEW.item_kode_baru, pallet_record.size, pallet_record.shade,
                'DGI', NEW.no_downgrade, NULL, pallet_record.last_qty, NEW.date_approval),
               (NEW.plan_kode, SUBSTRING(NEW.pallet_no, subplant_regex()), NEW.pallet_no,
                NEW.item_kode_lama, pallet_record.size, pallet_record.shade,
                'DGO', NEW.no_downgrade, NULL, -1 * pallet_record.last_qty, NEW.date_approval);
        IF pallet_record.last_qty <> NEW.qty
        THEN
            -- update the downgraded qty with the one present in master pallet table.
            UPDATE tbl_sp_downgrade_pallet
            SET qty = pallet_record.last_qty
            WHERE pallet_no = NEW.pallet_no
              AND no_downgrade = NEW.no_downgrade;
        END IF;
    END IF;
    RETURN NULL;
END;
$$;


ALTER FUNCTION public.fn_mutation_report_downgrade() OWNER TO armasi;

--
-- Name: fn_mutation_report_record(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.fn_mutation_report_record() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    txn_type        VARCHAR;
    ignored_txn_ids VARCHAR[] = ARRAY ['CNT']::VARCHAR[];
BEGIN
    -- copy/update the value
    IF TG_OP = 'INSERT' THEN
        txn_type := LEFT(NEW.no_mutasi, 3);
        IF txn_type IN ('BAM', 'BAL') THEN
            IF NEW.status_mut = 'L' THEN
                txn_type := 'SMP';
            ELSEIF NEW.status_mut = 'F' THEN
                txn_type := 'FOC';
            END IF;
        END IF;
        IF txn_type <> ALL (ignored_txn_ids) THEN
            INSERT INTO gbj_report.mutation_records(plant_id, subplant, pallet_no,
                                                    motif_id, size, shading,
                                                    mutation_type, mutation_id, ref_txn_id, quantity,
                                                    mutation_time)
            SELECT plan_kode,
                   (CASE
                        WHEN subplant = '4' THEN '4A'
                        WHEN subplant = '5' THEN '5A'
                        ELSE subplant
                       END),
                   pallet_no,
                   item_kode,
                   size,
                   shade,
                   txn_type,
                   NEW.no_mutasi,
                   NEW.reff_txn,
                   NEW.qty,
                   NEW.create_date
            FROM tbl_sp_hasilbj
            WHERE tbl_sp_hasilbj.pallet_no = NEW.pallet_no
            ON CONFLICT (pallet_no, mutation_type, mutation_id, mutation_time) DO UPDATE
                SET quantity      = EXCLUDED.quantity,
                    ref_txn_id    = EXCLUDED.ref_txn_id,
                    mutation_time = EXCLUDED.mutation_time;
        END IF;
    ELSEIF TG_OP = 'UPDATE' THEN
        txn_type := LEFT(NEW.no_mutasi, 3);
        IF txn_type IN ('BAM', 'BAL') THEN
            IF NEW.status_mut = 'L' THEN
                txn_type := 'SMP';
            ELSEIF NEW.status_mut = 'F' THEN
                txn_type := 'FOC';
            END IF;
        END IF;
        IF NEW.status_mut LIKE 'C%' AND txn_type IN ('BAM', 'BAL') THEN
            DELETE
            FROM gbj_report.mutation_records
            WHERE pallet_no = OLD.pallet_no
              AND mutation_id = OLD.no_mutasi
              AND mutation_type = (CASE
                                       WHEN OLD.status_mut = 'L' THEN 'SMP'
                                       WHEN OLD.status_mut = 'F' THEN 'FOC'
                                       ELSE LEFT(OLD.no_mutasi, 3) END);
        ELSE
            UPDATE gbj_report.mutation_records
            SET mutation_id   = NEW.no_mutasi,
                mutation_type = (CASE
                                     WHEN LEFT(OLD.no_mutasi, 3) IN ('BAM', 'BAL') AND NEW.status_mut = 'L' THEN 'SMP'
                                     WHEN LEFT(OLD.no_mutasi, 3) IN ('BAM', 'BAL') AND NEW.status_mut = 'F' THEN 'FOC'
                                     ELSE LEFT(NEW.no_mutasi, 3) END),
                ref_txn_id    = NEW.reff_txn,
                quantity      = NEW.qty,
                mutation_time = NEW.create_date
            WHERE pallet_no = OLD.pallet_no
              AND mutation_id = OLD.no_mutasi
              AND mutation_type = (CASE
                                       WHEN NEW.status_mut = 'L' THEN 'SMP'
                                       WHEN NEW.status_mut = 'F' THEN 'FOC'
                                       ELSE mutation_type END);
        END IF;
    ELSEIF TG_OP = 'DELETE' THEN
        DELETE
        FROM gbj_report.mutation_records
        WHERE mutation_id = OLD.no_mutasi
          AND pallet_no = OLD.pallet_no
          AND mutation_type = (CASE
                                   WHEN OLD.status_mut = 'L' THEN 'SMP'
                                   WHEN OLD.status_mut = 'F' THEN 'FOC'
                                   ELSE LEFT(OLD.no_mutasi, 3) END);
    END IF;
    RETURN NULL; -- after trigger, update result is ignored.
END;
$$;


ALTER FUNCTION public.fn_mutation_report_record() OWNER TO armasi;

--
-- Name: fn_qty_plt(text); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.fn_qty_plt(text) RETURNS text
    LANGUAGE plpgsql
    AS $_$
declare
v_no_plt alias for $1;
v_qty integer;

begin

select sum(qty) into v_qty from tbl_sp_mutasi_pallet where pallet_no=v_no_plt;
return v_qty;

end;
$_$;


ALTER FUNCTION public.fn_qty_plt(text) OWNER TO armasi;

--
-- Name: fn_record_txn_counter_update(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.fn_record_txn_counter_update() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    do_update BOOLEAN;
BEGIN
    do_update := (TG_OP = 'INSERT');
    IF NOT do_update THEN
        do_update := (TG_OP = 'UPDATE' AND (OLD.count <> NEW.count OR OLD.last_period <> NEW.last_period));
    END IF;
    IF do_update THEN
        INSERT INTO txn_counters_details(plant_id, txn_id, period_count, period_time, last_updated_at)
        VALUES (NEW.plant_id, NEW.txn_id, NEW.count, NEW.last_period, NEW.last_updated_at)
        ON CONFLICT(plant_id, txn_id, period_time) DO UPDATE
            SET period_count    = EXCLUDED.period_count,
                last_updated_at = EXCLUDED.last_updated_at;
    END IF;
    RETURN NULL; -- after trigger, result will be ignored.
END;
$$;


ALTER FUNCTION public.fn_record_txn_counter_update() OWNER TO armasi;

--
-- Name: fn_tbl_sp_permintaan_brp_no_double_request(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.fn_tbl_sp_permintaan_brp_no_double_request() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    current_pallet_record    RECORD;
    current_request_quantity INT;
    is_request_valid BOOLEAN;
BEGIN
    IF TG_OP = 'UPDATE' THEN
        -- ignore if no change to the requested quantity.
        IF NEW.qty_awal = OLD.qty_awal THEN
            RETURN NEW;
        END IF;
    END IF;
    
    SELECT * INTO current_pallet_record FROM tbl_sp_hasilbj WHERE pallet_no = NEW.pallet_no;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Pallet with no. % not found!', current_pallet_record.pallet_no;
    END IF;

    -- find current request quantity
    IF TG_OP = 'INSERT' THEN
        current_request_quantity := COALESCE((SELECT SUM(qty_awal)::INT
                                              FROM tbl_sp_permintaan_brp
                                              WHERE pallet_no = NEW.pallet_no AND no_brp IS NULL), 0);
    ELSEIF TG_OP = 'UPDATE' THEN
        current_request_quantity := COALESCE((SELECT SUM(qty_awal)::INT
                                              FROM tbl_sp_permintaan_brp
                                              WHERE pallet_no = NEW.pallet_no
                                                AND no_brp IS NULL
                                                AND no_pbp <> OLD.no_pbp), 0);
    END IF;
    
    -- calculate resulting quantity
    is_request_valid := (NEW.qty_awal + current_request_quantity) <= current_pallet_record.last_qty;
    IF NOT is_request_valid THEN
        RAISE EXCEPTION 'Permintaan akan menyebabkan kuantitas palet % menjadi minus!', current_pallet_record.pallet_no;
    END IF;
    RETURN NEW;
END
$$;


ALTER FUNCTION public.fn_tbl_sp_permintaan_brp_no_double_request() OWNER TO armasi;

--
-- Name: fn_tbl_surat_jalan_auto_set_updated_at(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.fn_tbl_surat_jalan_auto_set_updated_at() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        IF NEW.update_tran IS NULL THEN NEW.update_tran = now(); END IF;
        IF NEW.update_tran_user IS NULL THEN NEW.update_tran_user = NEW.create_by; END IF;
    ELSEIF TG_OP = 'UPDATE' THEN
        IF NEW.update_tran IS NULL THEN NEW.update_tran = now(); END IF;
        IF NEW.update_tran_user IS NULL THEN NEW.update_tran_user = COALESCE(OLD.update_tran_user, COALESCE(NEW.modiby, NEW.create_by)); END IF;
    END IF;

    RETURN NEW;
END;
$$;


ALTER FUNCTION public.fn_tbl_surat_jalan_auto_set_updated_at() OWNER TO armasi;

--
-- Name: gen_random_bytes(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.gen_random_bytes(integer) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pg_random_bytes';


ALTER FUNCTION public.gen_random_bytes(integer) OWNER TO postgres;

--
-- Name: gen_salt(text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.gen_salt(text) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pg_gen_salt';


ALTER FUNCTION public.gen_salt(text) OWNER TO postgres;

--
-- Name: gen_salt(text, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.gen_salt(text, integer) RETURNS text
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pg_gen_salt_rounds';


ALTER FUNCTION public.gen_salt(text, integer) OWNER TO postgres;

--
-- Name: get_multiple_pallet_locations_with_quantity(character varying[]); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.get_multiple_pallet_locations_with_quantity(pallet_nos character varying[]) RETURNS SETOF record
    LANGUAGE plpgsql
    AS $$
DECLARE
  existing_pallet_nos    VARCHAR [];
  nonexisting_pallet_nos VARCHAR [];
BEGIN
  CREATE TEMP TABLE temp_pallet_locations ON COMMIT DROP AS
    SELECT finished_goods.pallet_no     AS pallet_no,
           finished_goods.subplant      AS subplant,
           inventory_location.io_kd_lok AS current_location_no,
           CASE
             WHEN status_plt = 'C' THEN -4 -- canceled.
             WHEN status_plt = 'O' THEN -3 -- not handed over.
             --WHEN finished_goods.qa_approved IS FALSE AND pallet_no LIKE 'PLT%' THEN -2 -- not approved by QA
             ELSE CAST(finished_goods.last_qty AS INTEGER)
               END                      AS current_quantity
    FROM tbl_sp_hasilbj finished_goods
           LEFT OUTER JOIN inv_opname inventory_location ON inventory_location.io_no_pallet = finished_goods.pallet_no
    WHERE finished_goods.pallet_no = ANY (pallet_nos)
      AND status_plt <> 'C'; -- C designates those that are cancelled by production.

  SELECT array_agg(temp_pallet_locations.pallet_no)
      INTO existing_pallet_nos FROM temp_pallet_locations;
  nonexisting_pallet_nos := array_subtract(pallet_nos, existing_pallet_nos);

  -- add the nonexistent pallet codes into the list
  INSERT INTO temp_pallet_locations
  SELECT pallet_no, '', NULL, -1 -- indicates nonexistent pallet
  FROM unnest(nonexisting_pallet_nos) AS pallet_no;

  RETURN QUERY SELECT * FROM temp_pallet_locations;
END;
$$;


ALTER FUNCTION public.get_multiple_pallet_locations_with_quantity(pallet_nos character varying[]) OWNER TO armasi;

--
-- Name: get_next_ivc_number(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.get_next_ivc_number() RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
  new_ivc_number      INT;
  last_created_at_day INT;
BEGIN
  SELECT EXTRACT(DAY FROM created_at) :: INT
      INTO last_created_at_day
  FROM inventory_checking
  ORDER BY created_at DESC
  LIMIT 1;

  IF date_part('day', CURRENT_DATE) :: INT <> last_created_at_day
  THEN
    ALTER SEQUENCE ivc_number RESTART 1;
  END IF;

  new_ivc_number := nextval('ivc_number');
  RETURN new_ivc_number;
END;
$$;


ALTER FUNCTION public.get_next_ivc_number() OWNER TO armasi;

--
-- Name: get_next_mbj_number(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.get_next_mbj_number() RETURNS integer
    LANGUAGE plpgsql STABLE
    AS $$
DECLARE
  new_number         INTEGER;
  last_number_string VARCHAR;
  last_year          INTEGER;
BEGIN
  SELECT
    terima_no,
    date_part('year', tanggal_terima) :: INT
  INTO last_number_string, last_year
  FROM tbl_sp_hasilbj
  WHERE status_plt <> 'C' -- C designates canceled pallets by Production team.
        AND substr(terima_no, 1, 3) = 'MBJ'
  ORDER BY tanggal_terima DESC, terima_no DESC
  LIMIT 1;

  IF date_part('year', CURRENT_DATE)::INT = last_year
  THEN
    new_number := CAST(RIGHT(last_number_string, 5) AS INT) + 1;
  ELSE
    new_number := 1; -- restart from new number
  END IF;

  RETURN new_number;
END;
$$;


ALTER FUNCTION public.get_next_mbj_number() OWNER TO armasi;

--
-- Name: get_next_pbp_number(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.get_next_pbp_number() RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
  new_number         INTEGER;
  last_number_string VARCHAR;
  last_year          INTEGER;
BEGIN
  SELECT
    no_pbp,
    date_part('year', create_date) :: INT
  INTO last_number_string, last_year
  FROM tbl_sp_permintaan_brp
  ORDER BY create_date DESC, no_pbp DESC
  LIMIT 1;

  IF date_part('year', CURRENT_DATE)::INT = last_year
  THEN
    new_number := CAST(RIGHT(last_number_string, 5) AS INT) + 1;
  ELSE
    new_number := 1; -- restart from new number
  END IF;

  RETURN new_number;
END;
$$;


ALTER FUNCTION public.get_next_pbp_number() OWNER TO armasi;

--
-- Name: get_next_plt_number(character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.get_next_plt_number(sub_plant character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
  new_number         INTEGER;
  last_month         INTEGER;
  last_number_string VARCHAR;
BEGIN
  SELECT
    pallet_no,
    date_part('month', tanggal) :: INT
  INTO last_number_string, last_month
  FROM tbl_sp_hasilbj
  WHERE SUBSTR(pallet_no, 5, 2) = sub_plant
  ORDER BY create_date DESC, pallet_no DESC
  LIMIT 1;

  IF date_part('month', CURRENT_DATE)::INT = last_month
  THEN
    new_number := CAST(RIGHT(last_number_string, 5) AS INT) + 1;
  ELSE
    new_number := 1; -- restart from new number
  END IF;
  RETURN new_number;
END;
$$;


ALTER FUNCTION public.get_next_plt_number(sub_plant character varying) OWNER TO armasi;

--
-- Name: get_next_rlt_number(character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.get_next_rlt_number(subplant_id character varying) RETURNS integer
    LANGUAGE plpgsql STABLE
    AS $$
DECLARE
  new_number         INTEGER;
  last_month         INTEGER;
  last_number_string VARCHAR;
BEGIN
  SELECT
    rkpterima_no,
    date_part('month', rkpterima_tanggal) :: INT
  INTO last_number_string, last_month
  FROM tbl_sp_hasilbj
  WHERE coalesce(rkpterima_no, '') <> ''
    AND SUBSTR(rkpterima_no, 5, 2) = subplant_id
  ORDER BY rkpterima_tanggal DESC, rkpterima_no DESC
  LIMIT 1;

  IF date_part('month', CURRENT_DATE)::INT = last_month
  THEN
    new_number := CAST(RIGHT(last_number_string, 5) AS INT) + 1;
  ELSE
    new_number := 1; -- restart from new number
  END IF;
  RETURN new_number;
END;
$$;


ALTER FUNCTION public.get_next_rlt_number(subplant_id character varying) OWNER TO armasi;

--
-- Name: get_next_seq_number(character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.get_next_seq_number(sub_plant character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
  new_number         INTEGER;
  last_number_string VARCHAR;
  last_year          INTEGER;
BEGIN
  SELECT
    seq_no,
    date_part('year', tanggal) :: INT
  INTO last_number_string, last_year
  FROM tbl_sp_hasilbj
  WHERE SUBSTR(seq_no, 5, 2) = sub_plant
  ORDER BY create_date DESC, seq_no DESC
  LIMIT 1;

  IF date_part('year', CURRENT_DATE)::INT = last_year
  THEN
    new_number := CAST(RIGHT(last_number_string, 5) AS INT) + 1;
  ELSE
    new_number := 1; -- restart from new number
  END IF;

  RETURN new_number;
END;
$$;


ALTER FUNCTION public.get_next_seq_number(sub_plant character varying) OWNER TO armasi;

--
-- Name: get_plant_code(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.get_plant_code() RETURNS integer
    LANGUAGE sql IMMUTABLE
    AS $$SELECT 2$$;


ALTER FUNCTION public.get_plant_code() OWNER TO armasi;

--
-- Name: ghstore_compress(internal); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.ghstore_compress(internal) RETURNS internal
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'ghstore_compress';


ALTER FUNCTION public.ghstore_compress(internal) OWNER TO postgres;

--
-- Name: ghstore_consistent(internal, internal, integer, oid, internal); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.ghstore_consistent(internal, internal, integer, oid, internal) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'ghstore_consistent';


ALTER FUNCTION public.ghstore_consistent(internal, internal, integer, oid, internal) OWNER TO postgres;

--
-- Name: ghstore_decompress(internal); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.ghstore_decompress(internal) RETURNS internal
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'ghstore_decompress';


ALTER FUNCTION public.ghstore_decompress(internal) OWNER TO postgres;

--
-- Name: ghstore_penalty(internal, internal, internal); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.ghstore_penalty(internal, internal, internal) RETURNS internal
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'ghstore_penalty';


ALTER FUNCTION public.ghstore_penalty(internal, internal, internal) OWNER TO postgres;

--
-- Name: ghstore_picksplit(internal, internal); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.ghstore_picksplit(internal, internal) RETURNS internal
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'ghstore_picksplit';


ALTER FUNCTION public.ghstore_picksplit(internal, internal) OWNER TO postgres;

--
-- Name: ghstore_same(internal, internal, internal); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.ghstore_same(internal, internal, internal) RETURNS internal
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'ghstore_same';


ALTER FUNCTION public.ghstore_same(internal, internal, internal) OWNER TO postgres;

--
-- Name: ghstore_union(internal, internal); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.ghstore_union(internal, internal) RETURNS internal
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'ghstore_union';


ALTER FUNCTION public.ghstore_union(internal, internal) OWNER TO postgres;

--
-- Name: gin_consistent_hstore(internal, smallint, internal, integer, internal, internal); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.gin_consistent_hstore(internal, smallint, internal, integer, internal, internal) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'gin_consistent_hstore';


ALTER FUNCTION public.gin_consistent_hstore(internal, smallint, internal, integer, internal, internal) OWNER TO postgres;

--
-- Name: gin_extract_hstore(internal, internal); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.gin_extract_hstore(internal, internal) RETURNS internal
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'gin_extract_hstore';


ALTER FUNCTION public.gin_extract_hstore(internal, internal) OWNER TO postgres;

--
-- Name: gin_extract_hstore_query(internal, internal, smallint, internal, internal); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.gin_extract_hstore_query(internal, internal, smallint, internal, internal) RETURNS internal
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'gin_extract_hstore_query';


ALTER FUNCTION public.gin_extract_hstore_query(internal, internal, smallint, internal, internal) OWNER TO postgres;

--
-- Name: hmac(bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hmac(bytea, bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_hmac';


ALTER FUNCTION public.hmac(bytea, bytea, text) OWNER TO postgres;

--
-- Name: hmac(text, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hmac(text, text, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pg_hmac';


ALTER FUNCTION public.hmac(text, text, text) OWNER TO postgres;

--
-- Name: hs_concat(public.hstore, public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hs_concat(public.hstore, public.hstore) RETURNS public.hstore
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_concat';


ALTER FUNCTION public.hs_concat(public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: hs_contained(public.hstore, public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hs_contained(public.hstore, public.hstore) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_contained';


ALTER FUNCTION public.hs_contained(public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: hs_contains(public.hstore, public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hs_contains(public.hstore, public.hstore) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_contains';


ALTER FUNCTION public.hs_contains(public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: hstore(text[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore(text[]) RETURNS public.hstore
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_from_array';


ALTER FUNCTION public.hstore(text[]) OWNER TO postgres;

--
-- Name: hstore(record); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore(record) RETURNS public.hstore
    LANGUAGE c IMMUTABLE
    AS '$libdir/hstore', 'hstore_from_record';


ALTER FUNCTION public.hstore(record) OWNER TO postgres;

--
-- Name: hstore(text[], text[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore(text[], text[]) RETURNS public.hstore
    LANGUAGE c IMMUTABLE
    AS '$libdir/hstore', 'hstore_from_arrays';


ALTER FUNCTION public.hstore(text[], text[]) OWNER TO postgres;

--
-- Name: hstore(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore(text, text) RETURNS public.hstore
    LANGUAGE c IMMUTABLE
    AS '$libdir/hstore', 'hstore_from_text';


ALTER FUNCTION public.hstore(text, text) OWNER TO postgres;

--
-- Name: hstore_cmp(public.hstore, public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_cmp(public.hstore, public.hstore) RETURNS integer
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_cmp';


ALTER FUNCTION public.hstore_cmp(public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: hstore_eq(public.hstore, public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_eq(public.hstore, public.hstore) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_eq';


ALTER FUNCTION public.hstore_eq(public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: hstore_ge(public.hstore, public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_ge(public.hstore, public.hstore) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_ge';


ALTER FUNCTION public.hstore_ge(public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: hstore_gt(public.hstore, public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_gt(public.hstore, public.hstore) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_gt';


ALTER FUNCTION public.hstore_gt(public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: hstore_hash(public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_hash(public.hstore) RETURNS integer
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_hash';


ALTER FUNCTION public.hstore_hash(public.hstore) OWNER TO postgres;

--
-- Name: hstore_le(public.hstore, public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_le(public.hstore, public.hstore) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_le';


ALTER FUNCTION public.hstore_le(public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: hstore_lt(public.hstore, public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_lt(public.hstore, public.hstore) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_lt';


ALTER FUNCTION public.hstore_lt(public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: hstore_ne(public.hstore, public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_ne(public.hstore, public.hstore) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_ne';


ALTER FUNCTION public.hstore_ne(public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: hstore_to_array(public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_to_array(public.hstore) RETURNS text[]
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_to_array';


ALTER FUNCTION public.hstore_to_array(public.hstore) OWNER TO postgres;

--
-- Name: hstore_to_matrix(public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_to_matrix(public.hstore) RETURNS text[]
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_to_matrix';


ALTER FUNCTION public.hstore_to_matrix(public.hstore) OWNER TO postgres;

--
-- Name: hstore_version_diag(public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hstore_version_diag(public.hstore) RETURNS integer
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_version_diag';


ALTER FUNCTION public.hstore_version_diag(public.hstore) OWNER TO postgres;

--
-- Name: isdefined(public.hstore, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.isdefined(public.hstore, text) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_defined';


ALTER FUNCTION public.isdefined(public.hstore, text) OWNER TO postgres;

--
-- Name: isexists(public.hstore, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.isexists(public.hstore, text) RETURNS boolean
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_exists';


ALTER FUNCTION public.isexists(public.hstore, text) OWNER TO postgres;

--
-- Name: ivc_approve_checking_results(character varying, character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.ivc_approve_checking_results(ivc_ref_no character varying, userid character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
  rec_ivc              RECORD;
  invalid_location_nos VARCHAR [];
BEGIN
  -- check the IVC is ok for submission.
  SELECT INTO rec_ivc * FROM inventory_checking WHERE ref_no = ivc_ref_no LIMIT 1;
  IF NOT FOUND
  THEN
    RAISE EXCEPTION 'Cannot find IVC with ref_no (%)!', ivc_ref_no;
  END IF;
  IF rec_ivc.current_status <> 'R'
  THEN
    IF rec_ivc.current_status = 'A' -- approved
    THEN
      RAISE EXCEPTION 'IVC (%) sudah disetujui!', ivc_ref_no;
    ELSEIF rec_ivc.current_status = 'C' -- cancelled
      THEN
        RAISE EXCEPTION 'IVC (%) sudah dibatalkan!', ivc_ref_no;
    ELSEIF rec_ivc.current_status IN ('L', 'I') -- logistics/internal stuff.
      THEN
        RAISE EXCEPTION 'IVC (%) masih dalam proses!', ivc_ref_no;
    ELSE
      RAISE EXCEPTION 'Status IVC (%) tidak diketahui! [%]', ivc_ref_no, rec_ivc.current_status;
    END IF;
  END IF;

  -- update the pallets
  INSERT INTO tbl_sp_mutasi_pallet (plan_kode, no_mutasi, pallet_no, tanggal, create_date, create_user, qty, status_mut)
  SELECT plan_kode,
         ivc_ref_no,
         tbl_sp_hasilbj.pallet_no,
         CURRENT_DATE,
         CURRENT_TIMESTAMP,
         userid,
         checked_qty - last_qty,
         'V'
  FROM tbl_sp_hasilbj
         INNER JOIN inventory_checking_details ON tbl_sp_hasilbj.pallet_no = inventory_checking_details.pallet_no
  WHERE ref_no = ivc_ref_no
    AND status = 'V'
    AND checked_qty IS NOT NULL
    AND checked_qty <> current_qty;

  -- TODO insert into pallet_events

  -- check that every location is invalid
  SELECT array_agg(checked_location)
      INTO invalid_location_nos
  FROM inventory_checking_details ivc
         LEFT JOIN inv_master_lok_pallet iml ON ivc.checked_location = iml.iml_kd_lok
  WHERE ivc.ref_no = ivc_ref_no
    AND ivc.status IN ('X', 'V')
    AND ivc.checked_location IS NOT NULL
    AND iml_kd_area IS NULL;
  IF FOUND
  THEN
    RAISE EXCEPTION 'Invalid location id(s) recorded in %! (%)', ivc_ref_no, array_to_string(invalid_location_nos, ',');
  END IF;

  -- update location: insert to history table
  INSERT INTO inv_opname_hist (ioh_plan_kode,
                               ioh_kd_lok,
                               ioh_no_pallet,
                               ioh_qty_pallet,
                               ioh_tgl,
                               ioh_txn,
                               ioh_userid,
                               ioh_kd_lok_old)
  SELECT io_plan_kode,
         '0',
         pallet_no,
         0,
         CURRENT_TIMESTAMP,
         (CASE
            WHEN ivc.status IN ('V', 'X') AND ivc.checked_location IS NOT NULL THEN '[IVC] Pindah dari lokasi ' ||
                                                                                    io_kd_lok ||
                                                                                    ' ke lokasi ' ||
                                                                                    ivc.checked_location || ' (' ||
                                                                                    ivc.ref_no || ')'
            ELSE -- pallet is missing.
             '[IVC] Dihapus dari lokasi ' || io_kd_lok || ', karena tidak ditemukan. (' || ivc.ref_no || ')'
             END),
         userid,
         io_kd_lok
  FROM inv_opname
         INNER JOIN inventory_checking_details ivc ON inv_opname.io_no_pallet = ivc.pallet_no
  WHERE ivc.ref_no = ivc_ref_no
    AND ivc.status IN ('V', 'X');

  -- update pallet locations.
  UPDATE inv_opname
  SET io_tgl        = CURRENT_TIMESTAMP,
      io_plan_kode  = t1.plant_code,
      io_kd_lok     = t1.checked_location,
      io_qty_pallet = 0
  FROM (SELECT pallet_no, iml_plan_kode AS plant_code, checked_location
        FROM inventory_checking_details ivc
               INNER JOIN inv_master_lok_pallet iml ON ivc.checked_location = iml.iml_kd_lok
        WHERE ivc.ref_no = ivc_ref_no
          AND ivc.status IN ('V', 'X')
          AND ivc.checked_location IS NOT NULL) t1
  WHERE io_no_pallet = t1.pallet_no;

  -- remove missing pallets from location.
  DELETE
  FROM inv_opname
  WHERE io_no_pallet IN (SELECT pallet_no
                         FROM inventory_checking_details ivc
                         WHERE ivc.ref_no = ivc_ref_no
                           AND ivc.status = 'X'
                           AND ivc.checked_location IS NULL);

  -- update pallets
  UPDATE tbl_sp_hasilbj
  SET size             = t1.checked_size,
      shade            = t1.checked_shading,
      last_qty         = t1.checked_qty,
      update_tran      = CURRENT_TIMESTAMP,
      update_tran_user = userid
  FROM (SELECT pallet_no, checked_size, checked_shading, checked_qty
        FROM inventory_checking_details
        WHERE ref_no = ivc_ref_no
          AND status = 'V'
          AND checked_size IS NOT NULL
          AND checked_shading IS NOT NULL
          AND checked_qty IS NOT NULL) t1
  WHERE pallet_no = t1.pallet_no;

  -- set approved.
  UPDATE inventory_checking
  SET current_status  = 'A',
      approved_at     = CURRENT_TIMESTAMP,
      approved_userid = userid
  WHERE ref_no = ivc_ref_no;
END;
$$;


ALTER FUNCTION public.ivc_approve_checking_results(ivc_ref_no character varying, userid character varying) OWNER TO armasi;

--
-- Name: ivc_close_stale_checks(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.ivc_close_stale_checks() RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
  rec_ivc_details RECORD;
BEGIN
  FOR rec_ivc_details IN
  SELECT *
  FROM inventory_checking_details
         LEFT JOIN inv_opname ON inventory_checking_details.pallet_no = io_no_pallet
  WHERE ref_no IN (SELECT ref_no FROM inventory_checking WHERE current_status IN ('L', 'I', 'R')
                                                           AND created_at < CURRENT_DATE)
    AND status IN ('O', 'V', 'X')
  LOOP
    -- unblock pallets
    IF rec_ivc_details.io_kd_lok IS NOT NULL
    THEN
      INSERT INTO inv_opname_hist (ioh_plan_kode,
                                   ioh_kd_lok,
                                   ioh_no_pallet,
                                   ioh_qty_pallet,
                                   ioh_tgl,
                                   ioh_txn,
                                   ioh_userid,
                                   ioh_kd_lok_old)
      VALUES (rec_ivc_details.io_plan_kode,
              rec_ivc_details.io_kd_lok,
              rec_ivc_details.pallet_no,
              0,
              CURRENT_TIMESTAMP,
              '[IVC] Opname dibatalkan (' || rec_ivc_details.ref_no || ')',
              'admin',
              rec_ivc_details.io_kd_lok);
      UPDATE inv_opname
      SET io_tgl        = CURRENT_TIMESTAMP,
          io_qty_pallet = 0
      WHERE io_no_pallet = rec_ivc_details.pallet_no;
      UPDATE inventory_checking_details
      SET status         = 'C',
          updated_at     = CURRENT_TIMESTAMP,
          updated_userid = 'admin'
      WHERE ref_no = rec_ivc_details.ref_no
        AND pallet_no = rec_ivc_details.pallet_no;
    END IF;

    UPDATE inventory_checking SET current_status = 'C' WHERE ref_no = rec_ivc_details.ref_no;
  END LOOP;
END;
$$;


ALTER FUNCTION public.ivc_close_stale_checks() OWNER TO armasi;

--
-- Name: ivc_create_new_checking(character varying, character varying, integer, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.ivc_create_new_checking(loc_subplant character varying, loc_area_no character varying, loc_line_no integer, userid character varying) RETURNS SETOF record
    LANGUAGE plpgsql
    AS $$
DECLARE
  rec_pallet         RECORD;
  rec_pallet_checked RECORD;
  ivc_no             INT;
  ivc_s_no           VARCHAR;
  is_transferred     BOOLEAN;
BEGIN

  -- check if there's something already.
  SELECT ref_no INTO ivc_s_no
  FROM inventory_checking
  WHERE location_plant = loc_subplant
    AND location_area_no = loc_area_no
    AND location_line_no = loc_line_no
    AND created_at >= CURRENT_DATE
    AND (current_status NOT IN ('C', 'A'))
  ORDER BY created_at DESC
  LIMIT 1;

  IF FOUND
  THEN
    RAISE EXCEPTION 'Current inspection for (%, %, %) is in progress! ref_no: %', loc_subplant, loc_area_no, loc_line_no, ivc_s_no;
  ELSE
    -- check location first
    SELECT INTO rec_pallet *
    FROM pallets_with_location
    WHERE location_subplant = loc_subplant
      AND location_area_no = loc_area_no
      AND location_row_no = loc_line_no
    LIMIT 1;
    IF NOT FOUND
    THEN
      RAISE EXCEPTION 'Selected location (%-%-%) is empty/nonexistent!', loc_subplant, loc_area_no, loc_line_no;
    END IF;

    -- generate new ref_no
    ivc_no := get_next_ivc_number();
    ivc_s_no :=
    'IVC/' || get_plant_code() || '/' || to_char(CURRENT_DATE, 'YYYY') || '/' || to_char(CURRENT_DATE, 'MM') || '/' ||
    to_char(CURRENT_DATE, 'DD') || '/' || to_char(ivc_no, 'fm000');

    INSERT INTO inventory_checking (ref_no,
                                    location_plant,
                                    location_area_no,
                                    location_line_no,
                                    current_status,
                                    user_comments,
                                    created_userid)
    VALUES (ivc_s_no, loc_subplant, loc_area_no, loc_line_no, 'I', '[]', userid);

    -- add pallets to inv_checking_details
    FOR rec_pallet IN SELECT *
                      FROM pallets_with_location
                      WHERE location_subplant = loc_subplant
                        AND location_area_no = loc_area_no
                        AND location_row_no = loc_line_no
    LOOP
      -- check if existing result from in progress is there.
      SELECT INTO rec_pallet_checked *
      FROM inventory_checking_details
      WHERE created_at >= CURRENT_DATE
        AND pallet_no = rec_pallet.pallet_no
        AND status = 'V'
      ORDER BY created_at
      LIMIT 1;

      is_transferred := FALSE;
      IF FOUND
      THEN
        is_transferred := TRUE;
      END IF;

      IF is_transferred
      THEN
        INSERT INTO inventory_checking_details (ref_no,
                                                pallet_no,
                                                status,
                                                user_comments,
                                                current_qty,
                                                checked_qty,
                                                current_size,
                                                checked_size,
                                                current_shading,
                                                checked_shading,
                                                current_location,
                                                checked_location,
                                                created_userid,
                                                updated_userid)
        VALUES (ivc_s_no,
                rec_pallet.pallet_no,
                'T',
                'Sudah divalidasi di ' || rec_pallet_checked.checked_location,
                rec_pallet.last_qty,
                rec_pallet_checked.checked_qty,
                rec_pallet.size,
                rec_pallet_checked.checked_size,
                rec_pallet.shade,
                rec_pallet_checked.checked_shading,
                rec_pallet.location_id,
                rec_pallet_checked.checked_location,
                userid,
                rec_pallet_checked.updated_userid);
      ELSE
        INSERT INTO inventory_checking_details (ref_no,
                                                pallet_no,
                                                status,
                                                current_qty,
                                                current_size,
                                                current_shading,
                                                current_location,
                                                created_userid,
                                                updated_userid)
        VALUES (ivc_s_no, rec_pallet.pallet_no, 'O', rec_pallet.last_qty, rec_pallet.size, rec_pallet.shade, rec_pallet.location_id, userid, userid);

        -- block the pallet with code 3: inspection in progress.
        INSERT INTO inv_opname_hist (ioh_plan_kode,
                                     ioh_kd_lok,
                                     ioh_no_pallet,
                                     ioh_qty_pallet,
                                     ioh_tgl,
                                     ioh_txn,
                                     ioh_userid,
                                     ioh_kd_lok_old)
        VALUES (rec_pallet.location_subplant, rec_pallet.location_id, rec_pallet.pallet_no, 3, -- for inspection block.
                CURRENT_TIMESTAMP, '[IVC] Blokir Opname (' || ivc_s_no || ')', userid, rec_pallet.location_id);
        UPDATE inv_opname
        SET io_qty_pallet = 3,
            io_tgl        = CURRENT_TIMESTAMP
        WHERE io_no_pallet = rec_pallet.pallet_no;
      END IF;
    END LOOP;
  END IF;

  RETURN QUERY SELECT * FROM inventory_checking WHERE ref_no = ivc_s_no;
END;
$$;


ALTER FUNCTION public.ivc_create_new_checking(loc_subplant character varying, loc_area_no character varying, loc_line_no integer, userid character varying) OWNER TO postgres;

--
-- Name: ivc_submit_checking_results(character varying, public.hstore[], character varying, boolean); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.ivc_submit_checking_results(ivc_ref_no character varying, ivc_results public.hstore[], userid character varying, is_done boolean) RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
  rec_ivc         RECORD;
  rec_ivc_details RECORD;
  rec_pallet      RECORD;
  rec_master_loc  RECORD;
  new_status      VARCHAR;
  ioh_comment     VARCHAR;
BEGIN
  -- validate ivc_results first
  IF COALESCE(array_length(ivc_results, 1), 0) = 0
  THEN
    RAISE EXCEPTION 'No result to save!';
  END IF;
  FOR i IN COALESCE(array_lower(ivc_results, 1), 0) .. COALESCE(array_upper(ivc_results, 1), -1)
  LOOP
    IF NOT (akeys(ivc_results [i]) @>
            ARRAY ['pallet_no', 'label_matches', 'size', 'shading', 'qty', 'location', 'comments'])
    THEN
      RAISE EXCEPTION 'incomplete result entry at index %', i;
    END IF;
  END LOOP;

  -- check ref_no
  SELECT INTO rec_ivc * FROM inventory_checking WHERE ref_no = ivc_ref_no;
  IF NOT FOUND
  THEN
    RAISE EXCEPTION 'Cannot find IVC with ref_no (%)!', ivc_ref_no;
  END IF;

  FOR i IN COALESCE(array_lower(ivc_results, 1), 0) .. COALESCE(array_upper(ivc_results, 1), -1)
  LOOP
    -- check existing details
    SELECT INTO rec_ivc_details *
    FROM inventory_checking_details
    WHERE ref_no = ivc_ref_no
      AND pallet_no = ivc_results [ i ] -> 'pallet_no'
    LIMIT 1;

    IF NOT FOUND
    THEN
      -- update every record in the table, where the pallet_no is matching.
      -- NOTE: it may be possible that no record will be updated.
      UPDATE inventory_checking_details
      SET status           = 'T',
          label_matches    = (ivc_results [ i ] -> 'label_matches') :: BOOLEAN,
          checked_size     = (ivc_results [ i ] -> 'size'),
          checked_shading  = (ivc_results [ i ] -> 'shading'),
          checked_location = ivc_results [ i ] -> 'location',
          checked_qty      = (ivc_results [ i ] -> 'qty') :: INT,
          updated_at       = CURRENT_TIMESTAMP,
          updated_userid   = userid
      WHERE pallet_no = ivc_results [ i ] -> 'pallet_no'
        AND ref_no IN (SELECT ref_no
                       FROM inventory_checking
                       WHERE current_status IN ('I', 'L')
                         AND inventory_checking.created_at >= CURRENT_DATE)
        AND status IN ('X', 'O', 'T');

      -- insert new record with the IVC
      -- need to use left join, since pallet may not have existing location.
      SELECT INTO rec_pallet *
      FROM tbl_sp_hasilbj
             LEFT JOIN inv_opname ON tbl_sp_hasilbj.pallet_no = inv_opname.io_no_pallet
             LEFT JOIN inv_master_lok_pallet ON io_kd_lok = iml_kd_lok AND io_plan_kode = iml_plan_kode
      WHERE pallet_no = ivc_results [ i ] -> 'pallet_no';

      INSERT INTO inventory_checking_details (ref_no,
                                              pallet_no,
                                              status,
                                              label_matches,
                                              user_comments,
                                              current_size,
                                              checked_size,
                                              current_shading,
                                              checked_shading,
                                              current_qty,
                                              checked_qty,
                                              current_location,
                                              checked_location,
                                              created_userid,
                                              updated_userid)
      VALUES (ivc_ref_no,
              ivc_results [ i ] -> 'pallet_no',
              'V',
              (ivc_results [ i ] -> 'label_matches') :: BOOLEAN,
              ivc_results [ i ] -> 'comments',
              rec_pallet.size,
              ivc_results [ i ] -> 'size',
              rec_pallet.shade,
              ivc_results [ i ] -> 'shading',
              rec_pallet.last_qty,
              (ivc_results [ i ] -> 'qty') :: INT,
              COALESCE(rec_pallet.io_kd_lok, '0'),
              ivc_results [ i ] -> 'location',
              userid,
              userid);
      -- update the other IVC that has this pallet.
      RAISE NOTICE '[IVC] New pallet no % is registered with %', ivc_results [i] -> 'pallet_no', ivc_ref_no;
    ELSE
      -- new status/
      new_status := 'V';

      -- handling transferred status
      -- will put that as XYZ.
      IF rec_ivc_details.status = 'T'
      THEN
        UPDATE inventory_checking_details
        SET status           = 'T',
            label_matches    = (ivc_results [ i ] -> 'label_matches') :: BOOLEAN,
            checked_size     = (ivc_results [ i ] -> 'size'),
            checked_shading  = (ivc_results [ i ] -> 'shading'),
            checked_location = ivc_results [ i ] -> 'location',
            checked_qty      = (ivc_results [ i ] -> 'qty') :: INT,
            user_comments    = ivc_results [ i ] -> 'comments',
            updated_at       = CURRENT_TIMESTAMP,
            updated_userid   = userid
        WHERE pallet_no = ivc_results [ i ] -> 'pallet_no'
          AND ref_no <> ivc_ref_no
          AND created_at >= CURRENT_DATE;
      END IF;

      UPDATE inventory_checking_details
      SET status           = new_status,
          label_matches    = (ivc_results [ i ] -> 'label_matches') :: BOOLEAN,
          checked_size     = (ivc_results [ i ] -> 'size'),
          checked_shading  = (ivc_results [ i ] -> 'shading'),
          checked_location = ivc_results [ i ] -> 'location',
          checked_qty      = (ivc_results [ i ] -> 'qty') :: INT,
          user_comments    = ivc_results [ i ] -> 'comments',
          updated_at       = CURRENT_TIMESTAMP,
          updated_userid   = userid
      WHERE pallet_no = ivc_results [ i ] -> 'pallet_no'
        AND ref_no = ivc_ref_no;
    END IF;
  END LOOP;

  IF is_done AND rec_ivc.current_status = 'I'
  THEN
    FOR rec_ivc_details IN SELECT * FROM inventory_checking_details WHERE ref_no = ivc_ref_no
    LOOP
      -- update every pallet location with verified status.
      IF rec_ivc_details.status = 'V'
      THEN
        IF rec_ivc_details.checked_location <> rec_ivc_details.current_location AND
           rec_ivc_details.checked_location IS NOT NULL
        THEN
          -- update pallet location
          SELECT INTO rec_master_loc *
          FROM inv_master_lok_pallet
          WHERE iml_kd_lok = rec_ivc_details.checked_location
          LIMIT 1;
          IF NOT FOUND
          THEN
            RAISE EXCEPTION 'Location % not found in DB!', rec_ivc_details.checked_location;
          END IF;

          IF rec_ivc_details.current_location = '0'
          THEN
            ioh_comment := '[IVC] Masuk ke ' || rec_ivc_details.checked_location || ' (' || rec_ivc_details.ref_no ||
                           ')';
          ELSE
            ioh_comment := '[IVC] Pindah dari ' || rec_ivc_details.current_location || ' ke ' ||
                           rec_ivc_details.checked_location || ' (' || rec_ivc_details.ref_no || ')';
          end if;

          INSERT INTO inv_opname_hist (ioh_plan_kode,
                                       ioh_kd_lok,
                                       ioh_no_pallet,
                                       ioh_qty_pallet,
                                       ioh_tgl,
                                       ioh_txn,
                                       ioh_userid,
                                       ioh_kd_lok_old)
          VALUES (rec_master_loc.iml_plan_kode,
                  rec_master_loc.iml_kd_area,
                  rec_ivc_details.pallet_no,
                  3,
                  CURRENT_TIMESTAMP,
                  ioh_comment,
                  userid,
                  rec_ivc_details.current_location);
          UPDATE inv_opname
          SET io_tgl       = CURRENT_TIMESTAMP,
              io_plan_kode = rec_master_loc.iml_plan_kode,
              io_kd_lok    = rec_ivc_details.checked_location
          WHERE io_no_pallet = rec_ivc_details.pallet_no;
        END IF;
      ELSEIF rec_ivc_details.status = 'O' -- set every pallet with status open to missing.
        THEN
          UPDATE inventory_checking_details
          SET status         = 'X',
              updated_userid = userid,
              updated_at     = CURRENT_TIMESTAMP
          WHERE ref_no = ivc_ref_no
            AND pallet_no = rec_ivc_details.pallet_no;
      END IF;
      -- pallets with status transferred (T) are ignored.
    END LOOP;

    -- waiting for logistics approval
    UPDATE inventory_checking SET current_status = 'L' WHERE ref_no = ivc_ref_no;
  END IF;
END;
$$;


ALTER FUNCTION public.ivc_submit_checking_results(ivc_ref_no character varying, ivc_results public.hstore[], userid character varying, is_done boolean) OWNER TO armasi;

--
-- Name: FUNCTION ivc_submit_checking_results(ivc_ref_no character varying, ivc_results public.hstore[], userid character varying, is_done boolean); Type: COMMENT; Schema: public; Owner: armasi
--

COMMENT ON FUNCTION public.ivc_submit_checking_results(ivc_ref_no character varying, ivc_results public.hstore[], userid character varying, is_done boolean) IS 'each hstore in ivc_results (hstore[]) should contain the following keys: pallet_no, label_matches, qty, location, comments';


--
-- Name: left(text, integer); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public."left"(str text, i integer) RETURNS character varying
    LANGUAGE plpgsql
    AS $_$
BEGIN
return substr($1, 1, $2);
END;
$_$;


ALTER FUNCTION public."left"(str text, i integer) OWNER TO armasi;

--
-- Name: lock_inactive_armasi_users(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.lock_inactive_armasi_users() RETURNS integer
    LANGUAGE sql
    AS $$
  WITH locked_users AS (
    UPDATE tbl_user
    SET is_active = FALSE, update_tran = now()
    WHERE last_activity < CURRENT_DATE - audit.max_user_inactivity_interval()
      AND is_active IS TRUE
    RETURNING 1
  )
  SELECT COUNT(*)::INT FROM locked_users;
$$;


ALTER FUNCTION public.lock_inactive_armasi_users() OWNER TO armasi;

--
-- Name: move_location(character varying, character varying, character varying, integer, boolean, character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.move_location(current_location_no character varying, new_location_no character varying, userid character varying, quantity integer, override boolean, reason character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$

DECLARE
	location_record RECORD;
	moved_pallets integer;
	new_location_plan_kode character varying;
	totalquantity integer;
	move_description text;
	current_location_no_exists boolean;
	new_location_no_exists boolean;
	

	user_exists boolean;
BEGIN
	-- check user existence
	user_exists := check_user_exists(userid);
	IF NOT user_exists THEN
		RETURN -2;
	END IF;

	-- check the location existence
	current_location_no_exists := 'f';
	new_location_no_exists := 'f';
	FOR location_record IN 
		SELECT * FROM current_location WHERE iml_kd_lok IN(current_location_no, new_location_no)
	LOOP
		IF location_record.iml_kd_lok = current_location_no THEN
			current_location_no_exists := 't';
		ELSE 
			IF location_record.iml_kd_lok = new_location_no THEN
				new_location_no_exists := 't';
				new_location_plan_kode := location_record.iml_plan_kode;
			ELSE
				CONTINUE;
			END IF;
		END IF;
	END LOOP;

	IF current_location_no_exists = 'f' AND new_location_no_exists = 'f' THEN
		RETURN -3;
	ELSEIF current_location_no_exists = 'f' THEN
		RETURN -4;
	ELSEIF new_location_no_exists = 'f' THEN
		RETURN -5;
	END IF;

	-- check if current location is empty
	SELECT
		inv_opname.io_kd_lok    AS "location",
		inv_opname.io_no_pallet AS pallet_no,
		item.item_nama 		AS motif,
		tbl_sp_hasilbj.last_qty AS current_quantity,
		tbl_sp_hasilbj.quality  AS quality,
		tbl_sp_hasilbj.shade    AS shading,
		tbl_sp_hasilbj.size     AS size
		FROM inv_opname
		INNER JOIN inv_master_lok_pallet ON iml_kd_lok = io_kd_lok
		INNER JOIN tbl_sp_hasilbj ON io_no_pallet = pallet_no
		INNER JOIN item ON item.item_kode = tbl_sp_hasilbj.item_kode
		WHERE io_no_pallet IS NOT NULL AND io_kd_lok=current_location_no and tbl_sp_hasilbj.last_qty > 0;
	IF NOT FOUND THEN
		RETURN -6;
	END IF;
	-- add if block for over ride
	-- Check Quantity if Greater than #quantity
	IF !override THEN
		SELECT sum(tbl_sp_hasilbj.last_qty)
		INTO totalquantity
		FROM inv_opname
			INNER JOIN inv_master_lok_pallet ON iml_kd_lok = io_kd_lok
			INNER JOIN tbl_sp_hasilbj ON io_no_pallet = pallet_no
			INNER JOIN item ON item.item_kode = tbl_sp_hasilbj.item_kode
		WHERE io_no_pallet IS NOT NULL AND 
			io_kd_lok in (current_location_no,new_location_code) AND 
			tbl_sp_hasilbj.last_qty > 0;
		IF quantity < totalquantity  THEN
			RETURN -7;
		END IF;
	END IF;

	-- commit the move
	BEGIN
		moved_pallets := 0;
		FOR location_record IN
			SELECT io_no_pallet FROM inv_opname WHERE io_kd_lok = current_location_no AND io_qty_pallet > 0
		LOOP
			-- update one by one
			SELECT last_qty from tbl_sp_hasilbj WHERE pallet_no=location_record.io_no_pallet AND last_qty > 0;
			IF NOT FOUND THEN
			   DELETE FROM inv_opname WHERE io_kd_lok=current_location_no AND io_no_pallet=location_record.io_no_pallet;
			   move_description := 'baris dihapus untuk ' || location_record.io_no_pallet || ' dan ' || current_location_no || ' disebabkan oleh palet kosong.';
			  INSERT INTO inv_opname_hist
				VALUES (new_location_plan_kode, new_location_code, location_record.io_no_pallet,
						location_record.io_qty_pallet, CURRENT_TIMESTAMP, move_description, userid);
			ELSE
			UPDATE inv_opname 
				SET io_kd_lok = new_location_code, io_tgl = CURRENT_TIMESTAMP
				WHERE io_no_pallet = location_record.io_no_pallet;
			move_description := 'Pindah dari ' || current_location_no || ' ke ' || new_location_no || 'oleh'||userid||'  #Approval#'||reason;
			INSERT INTO inv_opname_hist
				VALUES (new_location_plan_kode, new_location_code, location_record.io_no_pallet,
						location_record.io_qty_pallet, CURRENT_TIMESTAMP, move_description, userid);
			moved_pallets := moved_pallets + 1;
			END IF;

			-- TODO check the plan_code
		END LOOP;

		RETURN moved_pallets;
	END;
END;
$$;


ALTER FUNCTION public.move_location(current_location_no character varying, new_location_no character varying, userid character varying, quantity integer, override boolean, reason character varying) OWNER TO armasi;

--
-- Name: mv_refresh(regclass, boolean); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.mv_refresh(mv_id regclass, concurrent boolean DEFAULT false) RETURNS timestamp without time zone
    LANGUAGE plpgsql
    AS $$
DECLARE
  query_str TEXT;
BEGIN
  query_str := 'REFRESH MATERIALIZED VIEW ';
  IF concurrent
  THEN
    query_str := query_str || ' CONCURRENTLY ';
  END IF;
  EXECUTE query_str || mv_id;

  IF EXISTS(SELECT * FROM db_maintenance.meta_mv_refresh WHERE mv_name = mv_id::TEXT)
  THEN
    UPDATE db_maintenance.meta_mv_refresh SET mv_last_updated_at = CURRENT_TIMESTAMP WHERE mv_name = mv_id::TEXT;
  ELSE
    INSERT INTO db_maintenance.meta_mv_refresh VALUES (mv_id::TEXT);
  END IF;
  RETURN CURRENT_TIMESTAMP;
END;
$$;


ALTER FUNCTION public.mv_refresh(mv_id regclass, concurrent boolean) OWNER TO armasi;

--
-- Name: FUNCTION mv_refresh(mv_id regclass, concurrent boolean); Type: COMMENT; Schema: public; Owner: armasi
--

COMMENT ON FUNCTION public.mv_refresh(mv_id regclass, concurrent boolean) IS 'Updates a materialized view and stores the update time to "meta_mv_update".';


--
-- Name: password_validity_duration(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.password_validity_duration() RETURNS interval
    LANGUAGE sql IMMUTABLE
    AS $$ SELECT INTERVAL '60 days' $$;


ALTER FUNCTION public.password_validity_duration() OWNER TO armasi;

--
-- Name: pgp_key_id(bytea); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_key_id(bytea) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_key_id_w';


ALTER FUNCTION public.pgp_key_id(bytea) OWNER TO postgres;

--
-- Name: pgp_pub_decrypt(bytea, bytea); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_pub_decrypt(bytea, bytea) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_decrypt_text';


ALTER FUNCTION public.pgp_pub_decrypt(bytea, bytea) OWNER TO postgres;

--
-- Name: pgp_pub_decrypt(bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_pub_decrypt(bytea, bytea, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_decrypt_text';


ALTER FUNCTION public.pgp_pub_decrypt(bytea, bytea, text) OWNER TO postgres;

--
-- Name: pgp_pub_decrypt(bytea, bytea, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_pub_decrypt(bytea, bytea, text, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_decrypt_text';


ALTER FUNCTION public.pgp_pub_decrypt(bytea, bytea, text, text) OWNER TO postgres;

--
-- Name: pgp_pub_decrypt_bytea(bytea, bytea); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_pub_decrypt_bytea(bytea, bytea) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_decrypt_bytea';


ALTER FUNCTION public.pgp_pub_decrypt_bytea(bytea, bytea) OWNER TO postgres;

--
-- Name: pgp_pub_decrypt_bytea(bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_pub_decrypt_bytea(bytea, bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_decrypt_bytea';


ALTER FUNCTION public.pgp_pub_decrypt_bytea(bytea, bytea, text) OWNER TO postgres;

--
-- Name: pgp_pub_decrypt_bytea(bytea, bytea, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_pub_decrypt_bytea(bytea, bytea, text, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_decrypt_bytea';


ALTER FUNCTION public.pgp_pub_decrypt_bytea(bytea, bytea, text, text) OWNER TO postgres;

--
-- Name: pgp_pub_encrypt(text, bytea); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_pub_encrypt(text, bytea) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_encrypt_text';


ALTER FUNCTION public.pgp_pub_encrypt(text, bytea) OWNER TO postgres;

--
-- Name: pgp_pub_encrypt(text, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_pub_encrypt(text, bytea, text) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_encrypt_text';


ALTER FUNCTION public.pgp_pub_encrypt(text, bytea, text) OWNER TO postgres;

--
-- Name: pgp_pub_encrypt_bytea(bytea, bytea); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_pub_encrypt_bytea(bytea, bytea) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_encrypt_bytea';


ALTER FUNCTION public.pgp_pub_encrypt_bytea(bytea, bytea) OWNER TO postgres;

--
-- Name: pgp_pub_encrypt_bytea(bytea, bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_pub_encrypt_bytea(bytea, bytea, text) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_pub_encrypt_bytea';


ALTER FUNCTION public.pgp_pub_encrypt_bytea(bytea, bytea, text) OWNER TO postgres;

--
-- Name: pgp_sym_decrypt(bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_sym_decrypt(bytea, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_decrypt_text';


ALTER FUNCTION public.pgp_sym_decrypt(bytea, text) OWNER TO postgres;

--
-- Name: pgp_sym_decrypt(bytea, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_sym_decrypt(bytea, text, text) RETURNS text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_decrypt_text';


ALTER FUNCTION public.pgp_sym_decrypt(bytea, text, text) OWNER TO postgres;

--
-- Name: pgp_sym_decrypt_bytea(bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_sym_decrypt_bytea(bytea, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_decrypt_bytea';


ALTER FUNCTION public.pgp_sym_decrypt_bytea(bytea, text) OWNER TO postgres;

--
-- Name: pgp_sym_decrypt_bytea(bytea, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_sym_decrypt_bytea(bytea, text, text) RETURNS bytea
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_decrypt_bytea';


ALTER FUNCTION public.pgp_sym_decrypt_bytea(bytea, text, text) OWNER TO postgres;

--
-- Name: pgp_sym_encrypt(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_sym_encrypt(text, text) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_encrypt_text';


ALTER FUNCTION public.pgp_sym_encrypt(text, text) OWNER TO postgres;

--
-- Name: pgp_sym_encrypt(text, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_sym_encrypt(text, text, text) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_encrypt_text';


ALTER FUNCTION public.pgp_sym_encrypt(text, text, text) OWNER TO postgres;

--
-- Name: pgp_sym_encrypt_bytea(bytea, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_sym_encrypt_bytea(bytea, text) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_encrypt_bytea';


ALTER FUNCTION public.pgp_sym_encrypt_bytea(bytea, text) OWNER TO postgres;

--
-- Name: pgp_sym_encrypt_bytea(bytea, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.pgp_sym_encrypt_bytea(bytea, text, text) RETURNS bytea
    LANGUAGE c STRICT
    AS '$libdir/pgcrypto', 'pgp_sym_encrypt_bytea';


ALTER FUNCTION public.pgp_sym_encrypt_bytea(bytea, text, text) OWNER TO postgres;

--
-- Name: populate_record(anyelement, public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.populate_record(anyelement, public.hstore) RETURNS anyelement
    LANGUAGE c IMMUTABLE
    AS '$libdir/hstore', 'hstore_populate_record';


ALTER FUNCTION public.populate_record(anyelement, public.hstore) OWNER TO postgres;

--
-- Name: qa_check_pallets_approve_for_handover_status(character varying[]); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.qa_check_pallets_approve_for_handover_status(pallet_nos character varying[]) RETURNS SETOF record
    LANGUAGE plpgsql
    AS $$
DECLARE
  valid_pallet_nos       VARCHAR [];
  nonexisting_pallet_nos VARCHAR [];
  invalid_pallet_nos     VARCHAR [];
BEGIN
  -- check the pallet_nos first.
  CREATE TEMP TABLE temp_pallet_validation_status ON COMMIT DROP AS
    SELECT pallet_no, subplant, 0 :: INT
    FROM tbl_sp_hasilbj
    WHERE
        --rkpterima_no IS NOT NULL AND -- already marked for handover by production
        COALESCE(terima_no, '') = ''
      AND -- not yet handed over to production.
        substr(pallet_no, 1, 3) = 'PLT'
      AND status_plt = 'O'
      AND qa_approved = FALSE
      AND pallet_no = ANY (pallet_nos);
  SELECT array_agg(pallet_no)
      INTO valid_pallet_nos FROM temp_pallet_validation_status;
  invalid_pallet_nos = array_subtract(pallet_nos, valid_pallet_nos);

  /* pallet status codes:
     - 0: ok.
     - 1: not exist.
     - 2: canceled by production.
     - 3: not marked for handover by production (disabled for now)
     - 4: already verified by QA
     - 5: not in production (already handed over to logistics).
     - 6: PLM is requested for handover
     - 7: others (undefined?)
   */
  INSERT INTO temp_pallet_validation_status
  SELECT pallet_no,
         subplant,
         (CASE
            WHEN status_plt = 'C' THEN 2
            WHEN qa_approved IS TRUE THEN 4
            WHEN pallet_no LIKE 'PLM%' THEN 6
            WHEN terima_no IS NOT NULL THEN 5
            ELSE 7
             END)
  FROM tbl_sp_hasilbj
  WHERE pallet_no = ANY (invalid_pallet_nos);
  nonexisting_pallet_nos := array_subtract(invalid_pallet_nos,
                                           (SELECT array_agg(pallet_no) FROM temp_pallet_validation_status));
  INSERT INTO temp_pallet_validation_status
  SELECT pallet_no, SUBSTRING(pallet_no, subplant_regex()) AS subplant, 1 :: INT
  FROM unnest(nonexisting_pallet_nos) AS pallet_no;
  RETURN QUERY SELECT * FROM temp_pallet_validation_status;
  DROP TABLE temp_pallet_validation_status;
END;
$$;


ALTER FUNCTION public.qa_check_pallets_approve_for_handover_status(pallet_nos character varying[]) OWNER TO armasi;

--
-- Name: qa_check_user_can_approve_pallet_for_handover(character varying[], character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.qa_check_user_can_approve_pallet_for_handover(pallet_nos character varying[], userid character varying) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
DECLARE
  -- valid roles: SU (Super User), QM (QA Manager), QS (QA Kasubsie), QO (QA Operator), QK, (QA Kabag)
  valid_roles         VARCHAR [];
  requested_subplants VARCHAR [];
  user_record         RECORD;
  user_authorized     BOOLEAN;
BEGIN
  valid_roles := ARRAY ['SU', 'QM', 'QS', 'QO', 'QK', 'PM'];
  SELECT array_agg(subplant)
      INTO requested_subplants
  FROM (SELECT DISTINCT (CASE -- handling plants with single subplant, for now.
                           WHEN subplant = '4' THEN '4A'
                           WHEN subplant = '5' THEN '5A'
                           ELSE subplant END) subplant
        FROM qa_check_pallets_approve_for_handover_status(pallet_nos) AS
                 pallet_records (pallet_no VARCHAR, subplant VARCHAR, status_code INT)
        WHERE subplant IS NOT NULL /*ignore invalid pallet at this point.*/) s;

  SELECT *
      INTO user_record FROM gen_user_adm WHERE gua_kode = userid;
  IF NOT FOUND
  THEN
    RAISE EXCEPTION 'User with id ''%'' not found!', userid;
  END IF;

  user_authorized := COALESCE((user_record.gua_lvl && valid_roles AND
                               requested_subplants <@
                               string_to_array(user_record.gua_subplant_handover, ',') :: VARCHAR []),
                              FALSE);
  RETURN user_authorized;
END;
$$;


ALTER FUNCTION public.qa_check_user_can_approve_pallet_for_handover(pallet_nos character varying[], userid character varying) OWNER TO armasi;

--
-- Name: qa_set_pallet_approval(character varying[], character varying, boolean, character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.qa_set_pallet_approval(pallet_nos character varying[], userid_val character varying, is_approved boolean, reason character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
DECLARE
  pallet_record        RECORD;
  pallet_event_id      INTEGER;
  can_move_all_pallets BOOLEAN;

  -- only for P4, for now
  rkpterima_nos        hstore;
BEGIN
  -- check the pallet_nos first.
  CREATE TEMP TABLE temp_pallet_validation_status_result ON COMMIT DROP AS
    SELECT pallet_no, status_code
    FROM qa_check_pallets_approve_for_handover_status(pallet_nos) AS
             f (pallet_no VARCHAR, subplant VARCHAR, status_code INT);
  SELECT * INTO pallet_record
  FROM temp_pallet_validation_status_result
  WHERE (status_code <> 0 AND is_approved IS TRUE)
     OR (status_code <> 4 AND is_approved IS FALSE);

  can_move_all_pallets := NOT FOUND;
  IF NOT can_move_all_pallets
  THEN
    RAISE EXCEPTION 'Invalid pallet number(s) requested! (%)', (SELECT array_to_string(array_agg(pallet_no), ',')
                                                                FROM pallet_record);
  -- the user then reuses check_pallets_move_to_warehouse_status(VARCHAR) function to check the pallets. preferably before.
  END IF;

  -- insert to respective tables.
  IF is_approved
  THEN
    -- 1. get new RLT numbers, for every distinct subplant the pallet_no was
    SELECT hstore(array_agg(t1.subplant),
                  array_agg('RLT/' || t1.subplant || '/' || to_char(CURRENT_DATE, 'MM') || '/' ||
                            to_char(CURRENT_DATE, 'YY') || '/' ||
                            to_char(get_next_rlt_number(t1.subplant), 'fm00000')))
        INTO rkpterima_nos
    FROM (SELECT DISTINCT SUBSTRING(pallet_no, subplant_regex()) AS subplant
          FROM unnest(pallet_nos) AS pallet_no
          ORDER BY subplant) t1
    WHERE subplant IS NOT NULL;

    -- 2. insert to pallet_event.
    SELECT id INTO pallet_event_id FROM pallet_event_types WHERE event_name = 'production_to_qa';
    IF NOT FOUND
    THEN
      RAISE EXCEPTION 'Event name ''%'' not found in "pallet_event_types"!', 'production_to_qa';
    END IF;

    INSERT INTO pallet_events (event_id, pallet_no, userid, plant_id, event_time, old_values, new_values)
    SELECT pallet_event_id,
           t2.pallet_no,
           userid_val                                                                                       AS userid,
           (CASE
              WHEN t2.plant_id = '4' THEN '4A'
              WHEN t2.plant_id = '5' THEN '5A'
              ELSE t2.plant_id
               END)                                                                                         AS plant_id,
           CURRENT_TIMESTAMP                                                                                AS event_time,
           '{"rkpterima_no":null,"rkpterima_tanggal":null,"rkpterima_user":null,qa_approved:false}' :: TEXT as old_val,
           CONCAT('{"rkpterima_no":"', rkpterima_nos -> t2.plant_id, '","rkpterima_tanggal":"', CURRENT_DATE :: TEXT,
                  '","rkpterima_user":"', userid_val, '",qa_approved:true}')                                as new_val
    FROM (SELECT pallet_no AS pallet_no, SUBSTRING(pallet_no, subplant_regex()) AS plant_id
          FROM unnest(pallet_nos) AS pallet_no) t2;

    UPDATE tbl_sp_hasilbj
    SET qa_approved       = is_approved,
        rkpterima_no      = rkpterima_nos -> tbl_sp_hasilbj.subplant,
        rkpterima_user    = userid_val,
        rkpterima_tanggal = CURRENT_DATE,
        keterangan        = ''
    WHERE pallet_no = ANY (pallet_nos)
      AND qa_approved IS FALSE;

    RETURN array_to_string(avals(rkpterima_nos), ',');
  ELSE
    SELECT id INTO pallet_event_id FROM pallet_event_types WHERE event_name = 'qa_to_production';
    IF NOT FOUND
    THEN
      RAISE EXCEPTION 'Event name ''%'' not found in "pallet_event_types"!', 'qa_to_production';
    END IF;

    INSERT INTO pallet_events (event_id, pallet_no, userid, plant_id, event_time, old_values, new_values)
    SELECT pallet_event_id,
           t1.pallet_no AS pallet_no,
           userid_val   AS userid,
           (CASE
              WHEN t1.subplant = '4' THEN '4A'
              WHEN t1.subplant = '5' THEN '5A'
              ELSE t1.subplant
               END)                                                                                         AS plant_id,
           CURRENT_TIMESTAMP,
           '{"rkpterima_no":"' || t1.rkpterima_no || '","rkpterima_tanggal":"'|| t1.rkpterima_tanggal ||'","rkpterima_user":"'||
           t1.rkpterima_user ||'",qa_approved:true}',
           '{"rkpterima_no":null,"rkpterima_tanggal":null,"rkpterima_user":null,qa_approved:false,reason:"'||
           COALESCE(reason, 'NOT SPECIFIED') ||'"}'
    FROM tbl_sp_hasilbj t1
    WHERE pallet_no = ANY(pallet_nos);

    UPDATE tbl_sp_hasilbj
    SET qa_approved       = is_approved,
        rkpterima_no      = null,
        rkpterima_user    = null,
        rkpterima_tanggal = null,
        keterangan        = COALESCE(reason, 'NOT SPECIFIED')
    WHERE pallet_no = ANY (pallet_nos)
      AND qa_approved IS TRUE;

    RETURN '';
  END IF;
  DROP TABLE temp_pallet_validation_status_result;
END;
$$;


ALTER FUNCTION public.qa_set_pallet_approval(pallet_nos character varying[], userid_val character varying, is_approved boolean, reason character varying) OWNER TO armasi;

--
-- Name: FUNCTION qa_set_pallet_approval(pallet_nos character varying[], userid_val character varying, is_approved boolean, reason character varying); Type: COMMENT; Schema: public; Owner: armasi
--

COMMENT ON FUNCTION public.qa_set_pallet_approval(pallet_nos character varying[], userid_val character varying, is_approved boolean, reason character varying) IS 'This function will change the approval status, designated by "qa_approved" changing to TRUE/FALSE, and also by adding/removing new rkpterima-related columns (rkpterima_no, rkpterima_user, tanggal_terima).
This function assumes that the userid_val supplied is valid. Since no validation is performed, user should ensure that the supplied userid is authorized to perform the move (using qa_check_user_can_approve_pallet_for_handover).';


--
-- Name: remove_pallets_from_location(character varying[], character varying, character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.remove_pallets_from_location(pallet_nos character varying[], reason character varying, userid character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
  pallets_affected       INTEGER;
  nonexistent_pallet_nos VARCHAR [];
  authorized_to_move     BOOLEAN;
BEGIN
  pallets_affected := 0;

  IF (COALESCE(reason, '') = '')
  THEN
    RAISE EXCEPTION 'No reason given for location removal!';
  END IF;

  IF (COALESCE(userid, '') = '')
  THEN
    RAISE EXCEPTION 'No userid given!';
  END IF;

  -- check pallets
  nonexistent_pallet_nos := array_subtract(pallet_nos, (SELECT array_agg(pallet_no)
                                                        FROM tbl_sp_hasilbj
                                                        WHERE pallet_no = ANY(pallet_nos)));
  IF nonexistent_pallet_nos <> '{}'
  THEN
    RAISE EXCEPTION 'Nonexistent pallet nos detected! (%)', array_to_string(nonexistent_pallet_nos, ',');
  END IF;

  -- check if user is authorized to remove pallets from the location.
  authorized_to_move := check_user_can_move_pallets_in_warehouse(pallet_nos, userid);
  IF NOT authorized_to_move
  THEN
    RAISE EXCEPTION 'User % not authorized to move pallets!', userid;
  END IF;

  -- pallets with no existing location will be ignored from the list.
  -- step 1: insert to history table.
  INSERT INTO inv_opname_hist (ioh_plan_kode,
                               ioh_kd_lok,
                               ioh_no_pallet,
                               ioh_qty_pallet,
                               ioh_tgl,
                               ioh_txn,
                               ioh_userid,
                               ioh_kd_lok_old)
  SELECT io_plan_kode,
         '0',
         io_no_pallet,
         COALESCE(io_qty_pallet, 0),
         CURRENT_TIMESTAMP,
         'Dihapus dari ' || io_kd_lok || '. Alasan: ' || reason,
         userid,
         io_kd_lok
  FROM inv_opname
  WHERE io_no_pallet = ANY(pallet_nos);
  -- set return value
  GET DIAGNOSTICS pallets_affected := ROW_COUNT;

  -- step 2: remove from location.
  DELETE FROM inv_opname WHERE io_no_pallet = ANY(pallet_nos);

  RETURN pallets_affected;
END;
$$;


ALTER FUNCTION public.remove_pallets_from_location(pallet_nos character varying[], reason character varying, userid character varying) OWNER TO armasi;

--
-- Name: replarr(numeric[]); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.replarr(str numeric[]) RETURNS character varying
    LANGUAGE plpgsql
    AS $_$
BEGIN
--return substr($1, 1, $2);
return
replace(
replace(
replace(
replace(
replace(
replace(cast(str as text),',0',''),'{0,',''),'{0',''),'}',''),'{',''),',','/');

END;
$_$;


ALTER FUNCTION public.replarr(str numeric[]) OWNER TO armasi;

--
-- Name: replarrtext(text[]); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.replarrtext(str text[]) RETURNS character varying
    LANGUAGE plpgsql
    AS $_$
BEGIN
--return substr($1, 1, $2);
return
replace(
replace(
replace(
replace(
replace(cast(str as text),'"",',''),'{',''),'}',''),'""',''),',','/');

END;
$_$;


ALTER FUNCTION public.replarrtext(str text[]) OWNER TO armasi;

--
-- Name: right(text, integer); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public."right"(str text, i integer) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
BEGIN
return substr(str,char_length(str)-i+1,i);
END;
$$;


ALTER FUNCTION public."right"(str text, i integer) OWNER TO armasi;

--
-- Name: skeys(public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.skeys(public.hstore) RETURNS SETOF text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_skeys';


ALTER FUNCTION public.skeys(public.hstore) OWNER TO postgres;

--
-- Name: slice(public.hstore, text[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.slice(public.hstore, text[]) RETURNS public.hstore
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_slice_to_hstore';


ALTER FUNCTION public.slice(public.hstore, text[]) OWNER TO postgres;

--
-- Name: slice_array(public.hstore, text[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.slice_array(public.hstore, text[]) RETURNS text[]
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_slice_to_array';


ALTER FUNCTION public.slice_array(public.hstore, text[]) OWNER TO postgres;

--
-- Name: stock_card_gbj(date, date, character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.stock_card_gbj(date, date, character varying) RETURNS text
    LANGUAGE plpgsql
    AS $_$DECLARE
    cdate1 alias for $1;
    cdate2 alias for $2;
    cplan alias for $3;
    lsql1 text;
	lsql2 text;
	lsql3 text;
	saldo numeric;
	rs record;
BEGIN

CREATE TEMP TABLE tmp_stock_card (item_kode text, no_mutasi text,kuwalitas text, tanggal date, keterangan text,  qty_in numeric,  qty_out numeric,  saldo numeric, noq integer);
   /*lsql1 = 'INSERT INTO tmp_stock_card
			select groupmut,null,null,null,null,null,null,null from tbl_sp_mutasi_pallet_stock_card where date_part(''year'',tanggal)=2017
			and groupmut in (''AD 1815'',''AD 1817'')
			group by groupmut
			order by groupmut';*/
	lsql1 = 'INSERT INTO tmp_stock_card
			select groupmut,null,null,null,null,null,null,null,null from tbl_sp_mutasi_pallet_stock_card where date_part(''year'',tanggal)=2017
			group by groupmut
			order by groupmut';			
   EXECUTE lsql1;
   saldo := 0;
   FOR rs IN
	select item_kode from tmp_stock_card 
	--where item_kode in ('AD 1815','AD 1817')
	group by item_kode
	order by item_kode
	
   LOOP
	    lsql2 = 'INSERT INTO tmp_stock_card
				select '''||rs.item_kode||''',''1 Saldo Awal'',null,null,null,null,null,coalesce(sum(qty),0) as qty,0 from (
				select *from tbl_sp_mutasi_pallet_stock_card 
				where qty<>0 and tanggal<'''||cdate1||'''
				and groupmut='''||rs.item_kode||'''
				UNION ALL
				select * from tbl_sp_mutasi_pallet_stock_card_2018 
				where qty<>0 and tanggal<'''||cdate1||'''
				and groupmut='''||rs.item_kode||'''
				UNION ALL
				select * from (select * from tbl_sp_mutasi_pallet where tanggal>''2019-01-13'' and groupmut='''||rs.item_kode||''') AS b
				where groupmut='''||rs.item_kode||''' and tanggal<'''||cdate1||''' 
				) as c;';
	    EXECUTE lsql2;   
		
	    lsql3 = 'INSERT INTO tmp_stock_card
				select '''||rs.item_kode||''',no_mutasi,kuamut,tanggal,
				case when left(no_mutasi,3)=''MBJ'' THEN ''Penerimaan Produksi''
				when left(no_mutasi,3)=''SEQ'' THEN ''Pallet Baru''
				when left(no_mutasi,3)=''BAL'' THEN ''Pemuatan''
				when left(no_mutasi,3)=''BAM'' THEN ''Pemuatan''
				when left(no_mutasi,3)=''OBJ'' THEN ''Adjusment''
				when left(no_mutasi,3)=''ADJ'' THEN ''Adjusment''
				when left(no_mutasi,3)=''BRP'' THEN ''Barang Pecah''
				when left(no_mutasi,3)=''SMP'' THEN ''Sample''
				when left(no_mutasi,3)=''FOC'' THEN ''Free Of Charge''
				when left(no_mutasi,3)=''MLT'' THEN ''Mutasi''
				when left(no_mutasi,3)=''JSR'' THEN ''Penjualan''
				when left(no_mutasi,3)=''JSP'' THEN ''Penjualan''
				when left(no_mutasi,3)=''GRT'' THEN ''Retur Penjualan''
				when left(no_mutasi,3)=''FAA'' THEN ''Penjualan''
				else ''''
				end as ket,
				case when qty > 0 then qty else null end as qty_in,
				case when qty < 0 then qty else null end as qty_out,null, noq
				from (
				select no_mutasi,kuamut,tanggal,sum(qty) as qty,case when sum(qty)>0 then 1 else 2 end noq
				from tbl_sp_mutasi_pallet_stock_card 
				where qty<>0 and tanggal>='''||cdate1||''' and tanggal<='''||cdate2||''' 
				and groupmut='''||rs.item_kode||'''
				group by no_mutasi,tanggal,kuamut
				union all
				select no_mutasi,kuamut,tanggal,sum(qty) as qty,case when sum(qty)>0 then 1 else 2 end noq
				from tbl_sp_mutasi_pallet_stock_card_2018 
				where qty<>0 and tanggal>='''||cdate1||''' and tanggal<='''||cdate2||''' 
				and groupmut='''||rs.item_kode||'''
				group by no_mutasi,tanggal,kuamut
				UNION ALL
				select no_mutasi,kuamut,tanggal,sum(qty) as qty ,case when sum(qty)>0 then 1 else 2 end noq 
				from (select * from tbl_sp_mutasi_pallet where tanggal>='''||cdate1||''' and tanggal<='''||cdate2||'''  
					  and groupmut='''||rs.item_kode||'''
					 ) AS b
				where 
					groupmut='''||rs.item_kode||'''
					and tanggal>='''||cdate1||''' and tanggal<='''||cdate2||'''
				group by no_mutasi,tanggal,kuamut
				) as x where qty<>0
				order by tanggal,noq,no_mutasi;';
	    EXECUTE lsql3;		
   		--raise notice ' tes ":  %', lsql2;
   END LOOP;
   RETURN lsql1;  -- semula lsql3
END;
$_$;


ALTER FUNCTION public.stock_card_gbj(date, date, character varying) OWNER TO armasi;

--
-- Name: subplant_regex(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.subplant_regex() RETURNS character varying
    LANGUAGE sql IMMUTABLE
    AS $$SELECT '/([1-5][A-C]?)/'$$;


ALTER FUNCTION public.subplant_regex() OWNER TO postgres;

--
-- Name: svals(public.hstore); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.svals(public.hstore) RETURNS SETOF text
    LANGUAGE c IMMUTABLE STRICT
    AS '$libdir/hstore', 'hstore_svals';


ALTER FUNCTION public.svals(public.hstore) OWNER TO postgres;

--
-- Name: sync_tarifongkosangkut(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.sync_tarifongkosangkut() RETURNS text
    LANGUAGE sql
    AS $$
--Program Update Tarif Ekspedisi atau ongkos angkut setiap plant by tejo 28042016

delete from tbl_tarif_angkutan;
insert into tbl_tarif_angkutan
select * from dblink('host=192.168.111.8 user=armasi password=armasi dbname=armasi', 
'select * from tbl_tarif_angkutan')
as (tarif_id integer,supplier_kode character varying(20),awal text,tujuan text,
  satuan text,tarif numeric,tgl_berlaku date,no_pol text,jenis text,tanggal_terima_surat date);


select 'OK'::text;

$$;


ALTER FUNCTION public.sync_tarifongkosangkut() OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_auto_set_create_date(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.tbl_sp_mutasi_pallet_auto_set_create_date() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
  BEGIN
    IF TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN
      NEW.create_date = now();
    END IF;
    RETURN NEW;
  END;
  $$;


ALTER FUNCTION public.tbl_sp_mutasi_pallet_auto_set_create_date() OWNER TO armasi;

--
-- Name: tconvert(text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.tconvert(text, text) RETURNS public.hstore
    LANGUAGE c IMMUTABLE
    AS '$libdir/hstore', 'hstore_from_text';


ALTER FUNCTION public.tconvert(text, text) OWNER TO postgres;

--
-- Name: tg_tbl_user_audit_login(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.tg_tbl_user_audit_login() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
  event_type VARCHAR;
BEGIN
  IF NEW.last_activity IS NULL THEN
    NEW.last_activity = now();
  END IF;

  IF NEW.monthly_logout_count <> OLD.monthly_logout_count THEN
    event_type := 'O'; -- logout
  ELSE
    event_type := 'I'; -- login
  END IF;

  INSERT INTO audit.user_login(username, event_time, event_type)
  VALUES(OLD.user_name, now(), event_type);
  RETURN NEW;
END;
$$;


ALTER FUNCTION public.tg_tbl_user_audit_login() OWNER TO armasi;

--
-- Name: unblock_pallets(character varying[], character varying, character varying, boolean); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.unblock_pallets(pallet_nos character varying[], userid_requester character varying, remarks character varying, override_block boolean DEFAULT false) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    affected_pallets INT;
BEGIN
    IF COALESCE(array_length(pallet_nos, 1), 0) = 0 THEN
        RAISE EXCEPTION 'No requested pallet!';
    END IF;
    IF COALESCE(TRIM(userid_requester), '') = '' THEN
        RAISE EXCEPTION 'userid_requester is empty!';
    END IF;

    CREATE TEMP TABLE pallets_to_unblock ON COMMIT DROP AS
    SELECT requested_pallet_no, status_plt, block_ref_id
    FROM unnest(pallet_nos) requested_pallet_no
             LEFT JOIN tbl_sp_hasilbj ON requested_pallet_no = pallet_no;

    IF EXISTS(SELECT * FROM pallets_to_unblock WHERE status_plt IS NULL) THEN
        RAISE EXCEPTION 'Some requested pallet(s) are not under in pallet record!';
    END IF;

    IF EXISTS(SELECT * FROM pallets_to_unblock WHERE status_plt <> 'B') THEN
        RAISE EXCEPTION 'Some requested pallet(s) are not being blocked!';
    END IF;

    IF EXISTS(SELECT * FROM pallets_to_unblock WHERE block_ref_id IS NOT NULL) AND NOT override_block THEN
        RAISE EXCEPTION 'Some requested pallet(s) are blocked using other method! Please override the block if this is inteded.';
    END IF;

    WITH updated_pallets AS (
        UPDATE tbl_sp_hasilbj hasilbj
            SET status_plt = 'R', block_ref_id = null, keterangan = remarks,
                update_tran_user = userid_requester,
                update_tran = now()
            FROM pallets_to_unblock t1
                JOIN tbl_sp_hasilbj t2 ON t1.requested_pallet_no = t2.pallet_no
            WHERE hasilbj.pallet_no = t1.requested_pallet_no
            RETURNING hasilbj.pallet_no, hasilbj.subplant, hasilbj.update_tran_user,
                hasilbj.keterangan AS new_keterangan, t2.keterangan AS old_keterangan,
                hasilbj.status_plt AS new_status_plt, t2.status_plt AS old_status_plt,
                hasilbj.block_ref_id AS new_block_ref_id, t2.block_ref_id AS old_block_ref_id
    )
    INSERT
    INTO pallet_events(event_id, pallet_no, userid, plant_id, old_values, new_values)
    SELECT (SELECT id FROM pallet_event_types WHERE event_name = 'unblock'),
           pallet_no,
           update_tran_user,
           (CASE WHEN subplant IN ('4', '5') THEN subplant || 'A' ELSE subplant END),
           jsonb_build_object(
                   'status_plt', old_status_plt,
                   'keterangan', old_keterangan,
                   'block_ref_id', old_block_ref_id
               ),
           jsonb_build_object(
                   'status_plt', new_status_plt,
                   'keterangan', new_keterangan,
                   'block_ref_id', new_block_ref_id
               )
    FROM updated_pallets;
    GET DIAGNOSTICS affected_pallets := ROW_COUNT;
    DROP TABLE pallets_to_unblock;
    RETURN affected_pallets;
END;
$$;


ALTER FUNCTION public.unblock_pallets(pallet_nos character varying[], userid_requester character varying, remarks character varying, override_block boolean) OWNER TO armasi;

--
-- Name: update_downgrade_request(json, character varying); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.update_downgrade_request(request json, userid_requester character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    downgrade_rec    RECORD;
    downgrade_id     VARCHAR;
    affected_pallets INT := 0;
BEGIN
    IF json_typeof(request -> 'downgrade_id') <> 'string' OR
       json_typeof(request -> 'add') <> 'array' OR
       json_typeof(request -> 'del') <> 'array' OR
       json_typeof(request -> 'reason') <> 'string'
    THEN
        RAISE EXCEPTION 'malformed request! Please check if all parameters are of the correct type!';
    END IF;

    IF COALESCE(TRIM(userid_requester), '') = '' THEN
        RAISE EXCEPTION 'malformed request! Please check if userid_requester exists!';
    END IF;

    IF EXISTS(SELECT *
              FROM json_array_elements_text(request -> 'add') t1
                       JOIN json_array_elements_text(request -> 'del') t2 ON t1 = t2) THEN
        RAISE EXCEPTION 'Some pallet(s) are being specified to be added and to be deleted! Make sure pallet(s) are going to be either exclusively added or deleted from the record.';
    END IF;

    downgrade_id := request ->> 'downgrade_id';
    SELECT * INTO downgrade_rec FROM tbl_sp_downgrade_pallet WHERE no_downgrade = downgrade_id;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Downgrade record with ID % is not found!', downgrade_id;
    END IF;

    IF downgrade_rec.status = 'R' THEN
        RAISE EXCEPTION 'Downgrade record with ID % has been rejected!', downgrade_id;
    ELSEIF downgrade_rec.status = 'A' THEN
        RAISE EXCEPTION 'Downgrade record with ID % has been approved!', downgrade_id;
    ELSEIF downgrade_rec.status = 'C' THEN
        RAISE EXCEPTION 'Downgrade record with ID % has been cancelled!', downgrade_id;
    END IF;

    -- add stuff, if request exists
    IF json_array_length(request -> 'add') > 0 THEN
        -- assume everything are pallet numbers
        CREATE TEMP TABLE pallets_to_add ON COMMIT DROP AS
        SELECT plan_kode, requested_pallet_no, subplant, quality, item_kode, last_qty
        FROM json_array_elements_text(request -> 'add') requested_pallet_no
                 LEFT JOIN tbl_sp_hasilbj hasilbj ON requested_pallet_no = hasilbj.pallet_no;

        -- check quality
        IF EXISTS(SELECT *
                  FROM pallets_to_add
                  WHERE quality <> (
                      CASE
                          WHEN downgrade_rec.jenis_downgrade IN
                               (const_downgrade_type_exp_to_kw4(), const_downgrade_type_exp_to_eco()) THEN 'EXPORT'
                          WHEN downgrade_rec.jenis_downgrade IN (const_downgrade_type_eco_to_kw4()) THEN 'EKONOMI'
                          ELSE 'KW4' END
                      ))
        THEN
            RAISE EXCEPTION 'Some pallet(s) are still being requested for downgrade! Cannot add to the existing downgrade record!';
        END IF;

        -- check subplant
        IF EXISTS(SELECT *
                  FROM pallets_to_add
                  WHERE subplant <> downgrade_rec.subplant)
        THEN
            RAISE EXCEPTION 'Some pallet(s) are not in %! Cannot add to the existing downgrade record %!',
                downgrade_rec.subplant, downgrade_rec.no_downgrade;
        END IF;

        -- check if they're still on existing downgrade requests
        IF EXISTS(SELECT *
                  FROM pallets_to_add
                           JOIN tbl_sp_downgrade_pallet dwg
                                ON requested_pallet_no = pallet_no AND dwg.status = 'O')
        THEN
            RAISE EXCEPTION 'Some pallet(s) are still being requested for downgrade! Cannot add to the existing downgrade record!';
        END IF;

        -- block_pallets will throw error if the blocking fails.
        affected_pallets := affected_pallets +
                            (SELECT block_pallets((SELECT array_agg(requested_pallet_no) FROM pallets_to_add),
                                                  'Downgrade',
                                                  userid_requester, downgrade_rec.no_downgrade));
        INSERT INTO tbl_sp_downgrade_pallet
        (plan_kode, no_downgrade, tanggal, pallet_no,
         create_date, create_user, item_kode_lama,
         keterangan, qty, jenis_downgrade, status, last_updated_at, last_updated_by, subplant)
        SELECT plan_kode,
               downgrade_rec.no_downgrade,
               downgrade_rec.tanggal,
               requested_pallet_no,
               now(),
               userid_requester,
               item_kode,
               downgrade_rec.keterangan,
               last_qty,
               downgrade_rec.jenis_downgrade,
               downgrade_rec.status,
               now(),
               userid_requester,
               subplant
        FROM pallets_to_add
        ON CONFLICT (plan_kode, pallet_no, no_downgrade) DO UPDATE
            SET qty             = excluded.qty,
                last_updated_at = excluded.last_updated_at,
                last_updated_by = excluded.last_updated_by;
    END IF;

    -- remove stuff, if request exists
    IF json_array_length(request -> 'del') > 0 THEN
        -- assume everything are pallet numbers
        CREATE TEMP TABLE pallets_to_del ON COMMIT DROP AS
        SELECT plan_kode, requested_pallet_no, subplant
        FROM json_array_elements_text(request -> 'del') requested_pallet_no
                 LEFT JOIN tbl_sp_downgrade_pallet dwg ON requested_pallet_no = pallet_no;

        -- check presence in record.
        IF EXISTS(SELECT * FROM pallets_to_del WHERE subplant IS NULL)
        THEN
            RAISE EXCEPTION 'Some pallet(s) are not present in downgrade record %!', downgrade_rec.no_downgrade;
        END IF;

        affected_pallets := affected_pallets +
                            (SELECT unblock_pallets((SELECT array_agg(requested_pallet_no) FROM pallets_to_del),
                                                    userid_requester, 'Batal Downgrade',
                                                    TRUE));
        DELETE
        FROM tbl_sp_downgrade_pallet
        WHERE no_downgrade = downgrade_rec.no_downgrade
          AND pallet_no IN (SELECT requested_pallet_no FROM pallets_to_del);
    END IF;

    -- put new reason
    UPDATE tbl_sp_downgrade_pallet
    SET keterangan      = request ->> 'reason',
        last_updated_at = now(),
        last_updated_by = userid_requester
    WHERE no_downgrade = downgrade_rec.no_downgrade;
    RETURN affected_pallets;
END;
$$;


ALTER FUNCTION public.update_downgrade_request(request json, userid_requester character varying) OWNER TO armasi;

--
-- Name: FUNCTION update_downgrade_request(request json, userid_requester character varying); Type: COMMENT; Schema: public; Owner: armasi
--

COMMENT ON FUNCTION public.update_downgrade_request(request json, userid_requester character varying) IS '
    request contains the following params:
        - downgrade_id: ID of the downgrade request.
        - reason: reason for downgrade
        - add: pallets to be added to the request.
            pallets should be under the same subplant as the downgrade request and of the same source quality.
            may be declared as empty array to indicate empty request.
        - del: pallets to be removed from the request.
            may be declared as empty array to indicate empty request.
    all parameters in the request should be present.
    all elements in the request should be valid. if not request will be rejected with apropriate.

';


--
-- Name: update_itemkode(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.update_itemkode() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
    BEGIN
        IF (TG_OP = 'UPDATE') THEN
			update gbj_report.mutation_records
			set motif_id = NEW.item_kode,
				size     = NEW.size,
				shading  = NEW.shade				
			where pallet_no=NEW.pallet_no;
			
            RETURN NEW;			
        END IF;
        RETURN NULL;
    END;
$$;


ALTER FUNCTION public.update_itemkode() OWNER TO armasi;

--
-- Name: updatetran_ins_upd(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.updatetran_ins_upd() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
    BEGIN
        IF (TG_OP = 'UPDATE') THEN
            NEW.update_tran := current_timestamp;
	    NEW.status_tran := 'U';
            RETURN NEW;
        ELSIF (TG_OP = 'INSERT') THEN
            NEW.update_tran := current_timestamp;
	    NEW.status_tran := 'I';
            RETURN NEW;
        END IF;
        RETURN NULL;
    END;
$$;


ALTER FUNCTION public.updatetran_ins_upd() OWNER TO armasi;

--
-- Name: validate_hasilbj(); Type: FUNCTION; Schema: public; Owner: armasi
--

CREATE FUNCTION public.validate_hasilbj() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    max_qty           SMALLINT;
    motif_dimension   VARCHAR;
    is_qty_changeable BOOLEAN;
BEGIN
    IF TG_OP = 'UPDATE'
    THEN
        -- lock immutable parameters of the pallet (pallet_no)
        IF NEW.pallet_no IS NOT NULL AND OLD.pallet_no <> NEW.pallet_no THEN
            RAISE EXCEPTION 'nomor palet % tidak bisa diubah!', OLD.pallet_no;
        END IF;

        -- lock immutable parameters of the pallet (subplant)
        IF NEW.subplant IS NOT NULL AND OLD.subplant <> NEW.subplant THEN
            RAISE EXCEPTION 'subplant palet % tidak bisa diubah (dari % ke %)!', OLD.pallet_no, OLD.subplant, NEW.subplant;
        END IF;

        -- conditions for changing qty
        IF OLD.qty <> NEW.qty AND OLD.plan_kode = get_plant_code()::VARCHAR
        THEN
            is_qty_changeable := (LEFT(OLD.pallet_no, 3) = 'PLT' AND OLD.status_plt = 'O')
                OR (LEFT(OLD.pallet_no, 3) = 'PLM' AND OLD.status_plt = 'R');

            IF is_qty_changeable THEN
                is_qty_changeable := is_qty_changeable AND NOT EXISTS(
                        SELECT *
                        FROM tbl_sp_mutasi_pallet
                        WHERE pallet_no = OLD.pallet_no
                          AND LEFT(no_mutasi, 3) NOT IN ('SEQ', 'MBJ')
                    );
            END IF;

            IF NOT is_qty_changeable THEN
                RAISE EXCEPTION 'quantity awal % tidak bisa diubah!', OLD.pallet_no;
            END IF;
        END IF;

        IF NEW.last_qty < 0 THEN
            RAISE EXCEPTION 'Quantity palet % tidak oleh kurang dari 0! (dari % ke %, oleh %)',
                OLD.pallet_no, OLD.last_qty, NEW.last_qty, NEW.update_tran_user;
        END IF;
    ELSEIF TG_OP = 'INSERT' AND NEW.plan_kode = get_plant_code()::VARCHAR
    THEN
        -- validate new quantity (non zero)
        IF NEW.qty <= 0 THEN
            RAISE EXCEPTION 'quantity palet baru harus positif (lebih dari 0)! (nilai yang salah: %, oleh %)',
                NEW.qty, NEW.create_user;
        END IF;

        -- validate
        SELECT jumlah_m2, category_nama INTO max_qty, motif_dimension
        FROM category
        WHERE category_kode = LEFT(NEW.item_kode, 2);
        IF FOUND THEN
            IF NEW.qty > max_qty THEN
                RAISE EXCEPTION 'quantity palet dengan ukuran % tidak boleh lebih dari %!', motif_dimension, max_qty;
            END IF;
        END IF;
        NEW.last_qty = NEW.qty;
    END IF;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.validate_hasilbj() OWNER TO armasi;

--
-- Name: array_accum(anyelement); Type: AGGREGATE; Schema: public; Owner: armasi
--

CREATE AGGREGATE public.array_accum(anyelement) (
    SFUNC = array_append,
    STYPE = anyarray,
    INITCOND = '{}'
);


ALTER AGGREGATE public.array_accum(anyelement) OWNER TO armasi;

--
-- Name: #<#; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.#<# (
    FUNCTION = public.hstore_lt,
    LEFTARG = public.hstore,
    RIGHTARG = public.hstore,
    COMMUTATOR = OPERATOR(public.#>#),
    NEGATOR = OPERATOR(public.#>=#),
    RESTRICT = scalarltsel,
    JOIN = scalarltjoinsel
);


ALTER OPERATOR public.#<# (public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: #<=#; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.#<=# (
    FUNCTION = public.hstore_le,
    LEFTARG = public.hstore,
    RIGHTARG = public.hstore,
    COMMUTATOR = OPERATOR(public.#>=#),
    NEGATOR = OPERATOR(public.#>#),
    RESTRICT = scalarltsel,
    JOIN = scalarltjoinsel
);


ALTER OPERATOR public.#<=# (public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: #=; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.#= (
    FUNCTION = public.populate_record,
    LEFTARG = anyelement,
    RIGHTARG = public.hstore
);


ALTER OPERATOR public.#= (anyelement, public.hstore) OWNER TO postgres;

--
-- Name: #>#; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.#># (
    FUNCTION = public.hstore_gt,
    LEFTARG = public.hstore,
    RIGHTARG = public.hstore,
    COMMUTATOR = OPERATOR(public.#<#),
    NEGATOR = OPERATOR(public.#<=#),
    RESTRICT = scalargtsel,
    JOIN = scalargtjoinsel
);


ALTER OPERATOR public.#># (public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: #>=#; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.#>=# (
    FUNCTION = public.hstore_ge,
    LEFTARG = public.hstore,
    RIGHTARG = public.hstore,
    COMMUTATOR = OPERATOR(public.#<=#),
    NEGATOR = OPERATOR(public.#<#),
    RESTRICT = scalargtsel,
    JOIN = scalargtjoinsel
);


ALTER OPERATOR public.#>=# (public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: %#; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.%# (
    FUNCTION = public.hstore_to_matrix,
    RIGHTARG = public.hstore
);


ALTER OPERATOR public.%# (NONE, public.hstore) OWNER TO postgres;

--
-- Name: %%; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.%% (
    FUNCTION = public.hstore_to_array,
    RIGHTARG = public.hstore
);


ALTER OPERATOR public.%% (NONE, public.hstore) OWNER TO postgres;

--
-- Name: -; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.- (
    FUNCTION = public.delete,
    LEFTARG = public.hstore,
    RIGHTARG = text
);


ALTER OPERATOR public.- (public.hstore, text) OWNER TO postgres;

--
-- Name: -; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.- (
    FUNCTION = public.delete,
    LEFTARG = public.hstore,
    RIGHTARG = text[]
);


ALTER OPERATOR public.- (public.hstore, text[]) OWNER TO postgres;

--
-- Name: -; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.- (
    FUNCTION = public.delete,
    LEFTARG = public.hstore,
    RIGHTARG = public.hstore
);


ALTER OPERATOR public.- (public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: ->; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.-> (
    FUNCTION = public.fetchval,
    LEFTARG = public.hstore,
    RIGHTARG = text
);


ALTER OPERATOR public.-> (public.hstore, text) OWNER TO postgres;

--
-- Name: ->; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.-> (
    FUNCTION = public.slice_array,
    LEFTARG = public.hstore,
    RIGHTARG = text[]
);


ALTER OPERATOR public.-> (public.hstore, text[]) OWNER TO postgres;

--
-- Name: <>; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.<> (
    FUNCTION = public.hstore_ne,
    LEFTARG = public.hstore,
    RIGHTARG = public.hstore,
    COMMUTATOR = OPERATOR(public.<>),
    NEGATOR = OPERATOR(public.=),
    RESTRICT = neqsel,
    JOIN = neqjoinsel
);


ALTER OPERATOR public.<> (public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: <@; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.<@ (
    FUNCTION = public.hs_contained,
    LEFTARG = public.hstore,
    RIGHTARG = public.hstore,
    COMMUTATOR = OPERATOR(public.@>),
    RESTRICT = contsel,
    JOIN = contjoinsel
);


ALTER OPERATOR public.<@ (public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: =; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.= (
    FUNCTION = public.hstore_eq,
    LEFTARG = public.hstore,
    RIGHTARG = public.hstore,
    COMMUTATOR = OPERATOR(public.=),
    NEGATOR = OPERATOR(public.<>),
    MERGES,
    HASHES,
    RESTRICT = eqsel,
    JOIN = eqjoinsel
);


ALTER OPERATOR public.= (public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: ?; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.? (
    FUNCTION = public.exist,
    LEFTARG = public.hstore,
    RIGHTARG = text,
    RESTRICT = contsel,
    JOIN = contjoinsel
);


ALTER OPERATOR public.? (public.hstore, text) OWNER TO postgres;

--
-- Name: ?&; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.?& (
    FUNCTION = public.exists_all,
    LEFTARG = public.hstore,
    RIGHTARG = text[],
    RESTRICT = contsel,
    JOIN = contjoinsel
);


ALTER OPERATOR public.?& (public.hstore, text[]) OWNER TO postgres;

--
-- Name: ?|; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.?| (
    FUNCTION = public.exists_any,
    LEFTARG = public.hstore,
    RIGHTARG = text[],
    RESTRICT = contsel,
    JOIN = contjoinsel
);


ALTER OPERATOR public.?| (public.hstore, text[]) OWNER TO postgres;

--
-- Name: @; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.@ (
    FUNCTION = public.hs_contains,
    LEFTARG = public.hstore,
    RIGHTARG = public.hstore,
    COMMUTATOR = OPERATOR(public.~),
    RESTRICT = contsel,
    JOIN = contjoinsel
);


ALTER OPERATOR public.@ (public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: @>; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.@> (
    FUNCTION = public.hs_contains,
    LEFTARG = public.hstore,
    RIGHTARG = public.hstore,
    COMMUTATOR = OPERATOR(public.<@),
    RESTRICT = contsel,
    JOIN = contjoinsel
);


ALTER OPERATOR public.@> (public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: ||; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.|| (
    FUNCTION = public.hs_concat,
    LEFTARG = public.hstore,
    RIGHTARG = public.hstore
);


ALTER OPERATOR public.|| (public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: ~; Type: OPERATOR; Schema: public; Owner: postgres
--

CREATE OPERATOR public.~ (
    FUNCTION = public.hs_contained,
    LEFTARG = public.hstore,
    RIGHTARG = public.hstore,
    COMMUTATOR = OPERATOR(public.@),
    RESTRICT = contsel,
    JOIN = contjoinsel
);


ALTER OPERATOR public.~ (public.hstore, public.hstore) OWNER TO postgres;

--
-- Name: btree_hstore_ops; Type: OPERATOR FAMILY; Schema: public; Owner: postgres
--

CREATE OPERATOR FAMILY public.btree_hstore_ops USING btree;


ALTER OPERATOR FAMILY public.btree_hstore_ops USING btree OWNER TO postgres;

--
-- Name: btree_hstore_ops; Type: OPERATOR CLASS; Schema: public; Owner: postgres
--

CREATE OPERATOR CLASS public.btree_hstore_ops
    DEFAULT FOR TYPE public.hstore USING btree FAMILY public.btree_hstore_ops AS
    OPERATOR 1 public.#<#(public.hstore,public.hstore) ,
    OPERATOR 2 public.#<=#(public.hstore,public.hstore) ,
    OPERATOR 3 public.=(public.hstore,public.hstore) ,
    OPERATOR 4 public.#>=#(public.hstore,public.hstore) ,
    OPERATOR 5 public.#>#(public.hstore,public.hstore) ,
    FUNCTION 1 (public.hstore, public.hstore) public.hstore_cmp(public.hstore,public.hstore);


ALTER OPERATOR CLASS public.btree_hstore_ops USING btree OWNER TO postgres;

--
-- Name: gin_hstore_ops; Type: OPERATOR FAMILY; Schema: public; Owner: postgres
--

CREATE OPERATOR FAMILY public.gin_hstore_ops USING gin;


ALTER OPERATOR FAMILY public.gin_hstore_ops USING gin OWNER TO postgres;

--
-- Name: gin_hstore_ops; Type: OPERATOR CLASS; Schema: public; Owner: postgres
--

CREATE OPERATOR CLASS public.gin_hstore_ops
    DEFAULT FOR TYPE public.hstore USING gin FAMILY public.gin_hstore_ops AS
    STORAGE text ,
    OPERATOR 7 public.@>(public.hstore,public.hstore) ,
    OPERATOR 9 public.?(public.hstore,text) ,
    OPERATOR 10 public.?|(public.hstore,text[]) ,
    OPERATOR 11 public.?&(public.hstore,text[]) ,
    FUNCTION 1 (public.hstore, public.hstore) bttextcmp(text,text) ,
    FUNCTION 2 (public.hstore, public.hstore) public.gin_extract_hstore(internal,internal) ,
    FUNCTION 3 (public.hstore, public.hstore) public.gin_extract_hstore_query(internal,internal,smallint,internal,internal) ,
    FUNCTION 4 (public.hstore, public.hstore) public.gin_consistent_hstore(internal,smallint,internal,integer,internal,internal);


ALTER OPERATOR CLASS public.gin_hstore_ops USING gin OWNER TO postgres;

--
-- Name: gist_hstore_ops; Type: OPERATOR FAMILY; Schema: public; Owner: postgres
--

CREATE OPERATOR FAMILY public.gist_hstore_ops USING gist;


ALTER OPERATOR FAMILY public.gist_hstore_ops USING gist OWNER TO postgres;

--
-- Name: gist_hstore_ops; Type: OPERATOR CLASS; Schema: public; Owner: postgres
--

CREATE OPERATOR CLASS public.gist_hstore_ops
    DEFAULT FOR TYPE public.hstore USING gist FAMILY public.gist_hstore_ops AS
    STORAGE public.ghstore ,
    OPERATOR 7 public.@>(public.hstore,public.hstore) ,
    OPERATOR 9 public.?(public.hstore,text) ,
    OPERATOR 10 public.?|(public.hstore,text[]) ,
    OPERATOR 11 public.?&(public.hstore,text[]) ,
    OPERATOR 13 public.@(public.hstore,public.hstore) ,
    FUNCTION 1 (public.hstore, public.hstore) public.ghstore_consistent(internal,internal,integer,oid,internal) ,
    FUNCTION 2 (public.hstore, public.hstore) public.ghstore_union(internal,internal) ,
    FUNCTION 3 (public.hstore, public.hstore) public.ghstore_compress(internal) ,
    FUNCTION 4 (public.hstore, public.hstore) public.ghstore_decompress(internal) ,
    FUNCTION 5 (public.hstore, public.hstore) public.ghstore_penalty(internal,internal,internal) ,
    FUNCTION 6 (public.hstore, public.hstore) public.ghstore_picksplit(internal,internal) ,
    FUNCTION 7 (public.hstore, public.hstore) public.ghstore_same(internal,internal,internal);


ALTER OPERATOR CLASS public.gist_hstore_ops USING gist OWNER TO postgres;

--
-- Name: hash_hstore_ops; Type: OPERATOR FAMILY; Schema: public; Owner: postgres
--

CREATE OPERATOR FAMILY public.hash_hstore_ops USING hash;


ALTER OPERATOR FAMILY public.hash_hstore_ops USING hash OWNER TO postgres;

--
-- Name: hash_hstore_ops; Type: OPERATOR CLASS; Schema: public; Owner: postgres
--

CREATE OPERATOR CLASS public.hash_hstore_ops
    DEFAULT FOR TYPE public.hstore USING hash FAMILY public.hash_hstore_ops AS
    OPERATOR 1 public.=(public.hstore,public.hstore) ,
    FUNCTION 1 (public.hstore, public.hstore) public.hstore_hash(public.hstore);


ALTER OPERATOR CLASS public.hash_hstore_ops USING hash OWNER TO postgres;

--
-- Name: CAST (text[] AS public.hstore); Type: CAST; Schema: -; Owner: -
--

CREATE CAST (text[] AS public.hstore) WITH FUNCTION public.hstore(text[]);


SET default_tablespace = '';

--
-- Name: inactive_armasi_users; Type: TABLE; Schema: audit; Owner: armasi
--

CREATE TABLE audit.inactive_armasi_users (
    year smallint NOT NULL,
    month smallint NOT NULL,
    username character varying(20) NOT NULL,
    last_login timestamp without time zone NOT NULL
);


ALTER TABLE audit.inactive_armasi_users OWNER TO armasi;

--
-- Name: user_login; Type: TABLE; Schema: audit; Owner: armasi
--

CREATE TABLE audit.user_login (
    username character varying(20) NOT NULL,
    event_time timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    event_type character varying NOT NULL
);


ALTER TABLE audit.user_login OWNER TO armasi;

--
-- Name: meta_mv_refresh; Type: TABLE; Schema: db_maintenance; Owner: armasi
--

CREATE TABLE db_maintenance.meta_mv_refresh (
    mv_name character varying(63) NOT NULL,
    mv_last_updated_at timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE db_maintenance.meta_mv_refresh OWNER TO armasi;

--
-- Name: tbl_sp_downgrade_pallet; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_downgrade_pallet (
    plan_kode character(1) NOT NULL,
    no_downgrade character varying(18) NOT NULL,
    tanggal date NOT NULL,
    pallet_no character varying(18) NOT NULL,
    create_date timestamp without time zone NOT NULL,
    create_user character varying(20) NOT NULL,
    item_kode_lama character varying(20) NOT NULL,
    item_kode_baru character varying(20),
    approval boolean DEFAULT false NOT NULL,
    approval_user character varying(18),
    date_approval timestamp without time zone,
    keterangan character varying(300) NOT NULL,
    qty smallint NOT NULL,
    jenis_downgrade character varying(1) NOT NULL,
    subplant character varying(2) NOT NULL,
    status character varying(1) DEFAULT 'O'::character varying NOT NULL,
    last_updated_at timestamp without time zone DEFAULT now() NOT NULL,
    last_updated_by character varying(20) NOT NULL
);


ALTER TABLE public.tbl_sp_downgrade_pallet OWNER TO armasi;

--
-- Name: COLUMN tbl_sp_downgrade_pallet.status; Type: COMMENT; Schema: public; Owner: armasi
--

COMMENT ON COLUMN public.tbl_sp_downgrade_pallet.status IS '
    ''O'' - in process
    ''C'' - cancelled
    ''R'' - rejected
    ''A'' - approved
';


--
-- Name: downgraded_pallets; Type: VIEW; Schema: gbj_report; Owner: armasi
--

CREATE VIEW gbj_report.downgraded_pallets AS
 SELECT d1.no_downgrade AS downgrade_id,
    d1.pallet_no,
    d1.item_kode_lama,
    d1.item_kode_baru,
    d1.date_approval,
    lead(d1.date_approval) OVER (PARTITION BY d1.pallet_no) AS date_end,
    d1.qty AS quantity
   FROM (public.tbl_sp_downgrade_pallet d1
     LEFT JOIN public.tbl_sp_downgrade_pallet d2 ON ((((d1.pallet_no)::text = (d2.pallet_no)::text) AND (d2.approval = true) AND ((d1.item_kode_lama)::text = (d2.item_kode_lama)::text) AND (d1.date_approval < d2.date_approval))))
  WHERE ((d1.approval = true) AND (d1.item_kode_baru IS NOT NULL) AND (d2.no_downgrade IS NULL));


ALTER TABLE gbj_report.downgraded_pallets OWNER TO armasi;

--
-- Name: mutation_records; Type: TABLE; Schema: gbj_report; Owner: armasi
--

CREATE TABLE gbj_report.mutation_records (
    plant_id character varying(1) NOT NULL,
    subplant character varying(2) NOT NULL,
    pallet_no character varying(18) NOT NULL,
    mutation_type character varying(3) NOT NULL,
    mutation_id character varying(18) NOT NULL,
    motif_id character varying(20) NOT NULL,
    size character varying(4) NOT NULL,
    shading character varying(4) NOT NULL,
    ref_txn_id character varying(18),
    quantity smallint NOT NULL,
    mutation_time timestamp without time zone NOT NULL
);


ALTER TABLE gbj_report.mutation_records OWNER TO armasi;

--
-- Name: tbl_ba_muat; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_ba_muat (
    no_ba text NOT NULL,
    tanggal date,
    customer_kode text,
    create_by character varying(40),
    check_by character varying(40),
    approve_by character varying(40),
    modiby character varying(40),
    modidate date,
    no_inv text,
    no_surat_jalan_rekap text,
    tujuan_surat_jalan_rekap text,
    no_bukti_tagihan text,
    tipe text,
    status text,
    waktu text,
    plan_kode text,
    keterangan text,
    alamat_surat_jalan_rekap text,
    kode_lama text DEFAULT 'O'::text,
    sub_plan text,
    tokogudang character(1),
    flag character(1) DEFAULT 0,
    no_surat_jalan_induk text,
    berat_masuk numeric,
    berat_keluar numeric,
    tanggal_real_muat date,
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean DEFAULT false,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_ba_muat OWNER TO armasi;

--
-- Name: mutation_records_adjusted; Type: VIEW; Schema: gbj_report; Owner: armasi
--

CREATE VIEW gbj_report.mutation_records_adjusted AS
 SELECT mut.plant_id,
    mut.subplant,
    mut.pallet_no,
    mut.mutation_type,
    mut.mutation_id,
    mut.motif_id,
    mut.size,
    mut.shading,
        CASE
            WHEN ((tbm.no_surat_jalan_rekap IS NOT NULL) AND ((mut.mutation_type)::text <> ALL (ARRAY[('SMP'::character varying)::text, ('FOC'::character varying)::text]))) THEN (tbm.no_surat_jalan_rekap)::character varying
            WHEN (COALESCE(btrim((mut.ref_txn_id)::text), ''::text) = ''::text) THEN NULL::character varying
            ELSE mut.ref_txn_id
        END AS ref_txn_id,
    mut.quantity,
    mut.mutation_time
   FROM (gbj_report.mutation_records mut
     LEFT JOIN public.tbl_ba_muat tbm ON (((((mut.mutation_id)::text = tbm.no_ba) OR ((mut.ref_txn_id)::text = tbm.no_ba)) AND (COALESCE(tbm.no_surat_jalan_rekap, ''::text) <> ''::text))));


ALTER TABLE gbj_report.mutation_records_adjusted OWNER TO armasi;

--
-- Name: VIEW mutation_records_adjusted; Type: COMMENT; Schema: gbj_report; Owner: armasi
--

COMMENT ON VIEW gbj_report.mutation_records_adjusted IS 'mutation records, with shipping-related mutation adjusted to use delivery order date as reference, when available.';


--
-- Name: mutation_records_adjusted_old; Type: VIEW; Schema: gbj_report; Owner: armasi
--

CREATE VIEW gbj_report.mutation_records_adjusted_old AS
 SELECT mut.plant_id,
    mut.subplant,
    mut.pallet_no,
    mut.mutation_type,
    mut.mutation_id,
    mut.motif_id,
    mut.size,
    mut.shading,
        CASE
            WHEN ((tbm.no_surat_jalan_rekap IS NOT NULL) AND ((mut.mutation_type)::text <> ALL (ARRAY[('SMP'::character varying)::text, ('FOC'::character varying)::text]))) THEN (tbm.no_surat_jalan_rekap)::character varying
            WHEN (COALESCE(btrim((mut.ref_txn_id)::text), ''::text) = ''::text) THEN NULL::character varying
            ELSE mut.ref_txn_id
        END AS ref_txn_id,
    mut.quantity,
    COALESCE((tbm.tanggal_real_muat + '23:59:59'::time without time zone), mut.mutation_time) AS mutation_time
   FROM (gbj_report.mutation_records mut
     LEFT JOIN public.tbl_ba_muat tbm ON (((((mut.mutation_id)::text = tbm.no_ba) OR ((mut.ref_txn_id)::text = tbm.no_ba)) AND (COALESCE(tbm.no_surat_jalan_rekap, ''::text) <> ''::text))));


ALTER TABLE gbj_report.mutation_records_adjusted_old OWNER TO armasi;

--
-- Name: VIEW mutation_records_adjusted_old; Type: COMMENT; Schema: gbj_report; Owner: armasi
--

COMMENT ON VIEW gbj_report.mutation_records_adjusted_old IS 'mutation records, with shipping-related mutation adjusted to use delivery order date as reference, when available.';


--
-- Name: category; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.category (
    category_kode character varying(50) NOT NULL,
    jenis_kode character varying(50) NOT NULL,
    category_nama character varying(200),
    inactive boolean,
    modiby character varying(10),
    modidate date,
    gl_account text,
    kelompok_mesin text,
    kelompok_barang text,
    jumlah_m2 numeric,
    status_tran character varying(1)
);


ALTER TABLE public.category OWNER TO armasi;

--
-- Name: item; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.item (
    item_kode character varying(50) NOT NULL,
    category_kode character varying(50),
    item_nama character varying(200),
    spesification character varying(40),
    gl_account text,
    satuan character varying(15),
    color character varying(15),
    quality character varying(15),
    inactive boolean,
    modiby character varying(10),
    modidate date,
    ipc text,
    gambar text,
    kode_lama text,
    group_nama character varying(30),
    item_nama_baru character varying(200),
    plant_kode numeric,
    sub_plant character(1),
    item_nama_lama character varying(200),
    konversi character varying(15),
    nilai_konversi numeric,
    jenis_barang character varying(15),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1)
);


ALTER TABLE public.item OWNER TO armasi;

--
-- Name: summary_mutation_by_motif_size_shading; Type: MATERIALIZED VIEW; Schema: gbj_report; Owner: armasi
--

CREATE MATERIALIZED VIEW gbj_report.summary_mutation_by_motif_size_shading AS
 SELECT (t1.mutation_time)::date AS mutation_date,
    t1.subplant,
    t1.motif_id,
    t1.motif_dimension,
    t1.motif_name,
    t1.size,
    t1.shading,
    t1.quality,
    sum(t1.prod_initial_quantity) AS prod_initial_quantity,
    sum(t1.manual_initial_quantity) AS manual_initial_quantity,
    sum(t1.in_mut_quantity) AS in_mut_quantity,
    sum(t1.out_mut_quantity) AS out_mut_quantity,
    sum(t1.in_adjusted_quantity) AS in_adjusted_quantity,
    sum(t1.out_adjusted_quantity) AS out_adjusted_quantity,
    sum(t1.returned_quantity) AS returned_quantity,
    sum(t1.broken_quantity) AS broken_quantity,
    sum(t1.sales_confirmed_quantity) AS sales_confirmed_quantity,
    sum(t1.sales_in_progress_quantity) AS sales_in_progress_quantity,
    sum(t1.foc_quantity) AS foc_quantity,
    sum(t1.sample_quantity) AS sample_quantity,
    sum(t1.in_downgrade_quantity) AS in_downgrade_quantity,
    sum(t1.out_downgrade_quantity) AS out_downgrade_quantity
   FROM ( SELECT mutation.mutation_time,
            mutation.subplant,
            mutation.motif_id,
            cat.category_nama AS motif_dimension,
            item.item_nama AS motif_name,
            mutation.size,
            mutation.shading,
            item.quality,
            COALESCE(sum(
                CASE
                    WHEN ((mutation.mutation_type)::text = 'MBJ'::text) THEN (abs(mutation.quantity))::integer
                    ELSE 0
                END), (0)::bigint) AS prod_initial_quantity,
            COALESCE(sum(
                CASE
                    WHEN ((mutation.mutation_type)::text = 'SEQ'::text) THEN (abs(mutation.quantity))::integer
                    ELSE 0
                END), (0)::bigint) AS manual_initial_quantity,
            COALESCE(sum(
                CASE
                    WHEN (((mutation.mutation_type)::text = 'MLT'::text) AND (mutation.quantity > 0)) THEN (mutation.quantity)::integer
                    ELSE 0
                END), (0)::bigint) AS in_mut_quantity,
            COALESCE(sum(
                CASE
                    WHEN (((mutation.mutation_type)::text = 'MLT'::text) AND (mutation.quantity < 0)) THEN (abs(mutation.quantity))::integer
                    ELSE 0
                END), (0)::bigint) AS out_mut_quantity,
            COALESCE(sum(
                CASE
                    WHEN (((mutation.mutation_type)::text = ANY (ARRAY[('OBJ'::character varying)::text, ('ADJ'::character varying)::text, ('OPN'::character varying)::text])) AND (mutation.quantity > 0)) THEN (mutation.quantity)::integer
                    ELSE 0
                END), (0)::bigint) AS in_adjusted_quantity,
            COALESCE(sum(
                CASE
                    WHEN (((mutation.mutation_type)::text = ANY (ARRAY[('OBJ'::character varying)::text, ('ADJ'::character varying)::text, ('OPN'::character varying)::text, ('CNC'::character varying)::text])) AND (mutation.quantity < 0)) THEN (abs(mutation.quantity))::integer
                    ELSE 0
                END), (0)::bigint) AS out_adjusted_quantity,
            COALESCE(sum(
                CASE
                    WHEN ((mutation.mutation_type)::text = 'ULT'::text) THEN (abs(mutation.quantity))::integer
                    ELSE 0
                END), (0)::bigint) AS returned_quantity,
            COALESCE(sum(
                CASE
                    WHEN ((mutation.mutation_type)::text = ANY (ARRAY[('BRP'::character varying)::text, ('PBP'::character varying)::text])) THEN (abs(mutation.quantity))::integer
                    ELSE 0
                END), (0)::bigint) AS broken_quantity,
            COALESCE(sum(
                CASE
                    WHEN (((mutation.mutation_type)::text = ANY (ARRAY[('BAM'::character varying)::text, ('BAL'::character varying)::text, ('JSP'::character varying)::text])) AND ((COALESCE(mutation.ref_txn_id, ''::character varying))::text <> ''::text)) THEN (abs(mutation.quantity))::integer
                    ELSE 0
                END), (0)::bigint) AS sales_confirmed_quantity,
            COALESCE(sum(
                CASE
                    WHEN (((mutation.mutation_type)::text = ANY (ARRAY[('BAM'::character varying)::text, ('BAL'::character varying)::text])) AND ((COALESCE(mutation.ref_txn_id, ''::character varying))::text = ''::text)) THEN (abs(mutation.quantity))::integer
                    ELSE 0
                END), (0)::bigint) AS sales_in_progress_quantity,
            COALESCE(sum(
                CASE
                    WHEN ((mutation.mutation_type)::text = 'FOC'::text) THEN (abs(mutation.quantity))::integer
                    ELSE 0
                END), (0)::bigint) AS foc_quantity,
            COALESCE(sum(
                CASE
                    WHEN ((mutation.mutation_type)::text = 'SMP'::text) THEN (abs(mutation.quantity))::integer
                    ELSE 0
                END), (0)::bigint) AS sample_quantity,
            COALESCE(sum(
                CASE
                    WHEN ((mutation.mutation_type)::text = 'DGI'::text) THEN (mutation.quantity)::integer
                    ELSE 0
                END), (0)::bigint) AS in_downgrade_quantity,
            COALESCE(sum(
                CASE
                    WHEN ((mutation.mutation_type)::text = 'DGO'::text) THEN (abs(mutation.quantity))::integer
                    ELSE 0
                END), (0)::bigint) AS out_downgrade_quantity
           FROM ((gbj_report.mutation_records_adjusted mutation
             JOIN public.item ON (((mutation.motif_id)::text = (item.item_kode)::text)))
             JOIN public.category cat ON (("left"((item.item_kode)::text, 2) = (cat.category_kode)::text)))
          GROUP BY mutation.mutation_time, mutation.subplant, mutation.motif_id, cat.category_nama, item.item_nama, mutation.size, mutation.shading, item.quality) t1
  GROUP BY ((t1.mutation_time)::date), t1.subplant, t1.motif_id, t1.motif_dimension, t1.motif_name, t1.size, t1.shading, t1.quality
  ORDER BY ((t1.mutation_time)::date), t1.subplant, t1.motif_id
  WITH NO DATA;


ALTER TABLE gbj_report.summary_mutation_by_motif_size_shading OWNER TO armasi;

--
-- Name: app_menu_id_seq; Type: SEQUENCE; Schema: man; Owner: armasi_man
--

CREATE SEQUENCE man.app_menu_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE man.app_menu_id_seq OWNER TO armasi_man;

--
-- Name: app_user_id_seq; Type: SEQUENCE; Schema: man; Owner: armasi_man
--

CREATE SEQUENCE man.app_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE man.app_user_id_seq OWNER TO armasi_man;

--
-- Name: app_user; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.app_user (
    user_id integer DEFAULT nextval('man.app_user_id_seq'::regclass) NOT NULL,
    user_name character varying(20),
    first_name character varying(20),
    last_name character varying(20),
    password character varying(100),
    user_create character varying(100),
    date_create timestamp without time zone,
    user_modify character varying(100),
    date_modify timestamp without time zone
);


ALTER TABLE man.app_user OWNER TO armasi_man;

--
-- Name: assets_master_main; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.assets_master_main (
    amm_code character varying(10) NOT NULL,
    amm_desc text NOT NULL,
    amm_category character varying(3) NOT NULL,
    amm_manufacture text NOT NULL,
    amm_model text NOT NULL,
    amm_serial_no text NOT NULL,
    amm_year character varying(4) NOT NULL,
    amm_type text NOT NULL,
    amm_location character varying(2) NOT NULL,
    amm_sub_location character varying(2) NOT NULL,
    amm_group character varying(10) NOT NULL,
    amm_parent character varying(10) NOT NULL,
    amm_status character varying(20) NOT NULL,
    amm_operator character varying(30) NOT NULL,
    amm_add_user character varying(30) NOT NULL,
    amm_add_date timestamp without time zone NOT NULL,
    amm_edit_user character varying(30) NOT NULL,
    amm_edit_date timestamp without time zone NOT NULL,
    amm_last_date date,
    amm_last_hour integer,
    amm_last_km integer,
    amm_number character varying(50)
);


ALTER TABLE man.assets_master_main OWNER TO armasi_man;

--
-- Name: assets_master_maintenance; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.assets_master_maintenance (
    amm_code character varying(10) NOT NULL,
    amms_code character varying(10) NOT NULL,
    amms_description text,
    amms_intv_cycle smallint,
    amms_type_cycle character varying(10) NOT NULL,
    amms_part text,
    amms_add_user character varying(30),
    amms_add_date timestamp without time zone,
    amms_edit_user character varying(30),
    amms_edit_date timestamp without time zone,
    amms_next_wo timestamp without time zone
);


ALTER TABLE man.assets_master_maintenance OWNER TO armasi_man;

--
-- Name: assets_master_maintenance_detail; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.assets_master_maintenance_detail (
    amm_code character varying(10) NOT NULL,
    amms_code character varying(10) NOT NULL,
    item_code character varying(50) NOT NULL,
    item_name text NOT NULL,
    unit character varying(15),
    qty numeric
);


ALTER TABLE man.assets_master_maintenance_detail OWNER TO armasi_man;

--
-- Name: assets_master_part; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.assets_master_part (
    amp_code character varying(10) NOT NULL,
    amp_part text NOT NULL,
    amp_description text NOT NULL,
    amm_qty smallint,
    amm_unit character varying(10) NOT NULL
);


ALTER TABLE man.assets_master_part OWNER TO armasi_man;

--
-- Name: assets_master_sparepart; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.assets_master_sparepart (
    amsp_code character varying(10) NOT NULL,
    amsp_sparepart_code character varying(50) NOT NULL,
    amsp_sparepart_desc text,
    amsp_unit character varying(15)
);


ALTER TABLE man.assets_master_sparepart OWNER TO armasi_man;

--
-- Name: assets_master_sparepart_old; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.assets_master_sparepart_old (
    amsp_code character varying(10) NOT NULL,
    amsp_sparepart_code character varying(50) NOT NULL,
    amsp_sparepart_desc text,
    amsp_unit character varying(15)
);


ALTER TABLE man.assets_master_sparepart_old OWNER TO armasi_man;

--
-- Name: assets_master_spesification; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.assets_master_spesification (
    ams_code character varying(10) NOT NULL,
    amm_color text NOT NULL,
    amm_length text NOT NULL,
    amm_width text NOT NULL,
    amm_height text NOT NULL,
    amm_gross_height text NOT NULL,
    amm_custom_field1 text NOT NULL,
    amm_custom_field11 text NOT NULL,
    amm_custom_field2 text NOT NULL,
    amm_custom_field21 text NOT NULL,
    amm_custom_field3 text NOT NULL,
    amm_custom_field31 text NOT NULL,
    amm_custom_field4 text NOT NULL,
    amm_custom_field41 text NOT NULL,
    amm_custom_field5 text NOT NULL,
    amm_custom_field51 text NOT NULL
);


ALTER TABLE man.assets_master_spesification OWNER TO armasi_man;

--
-- Name: sett_assets_category; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.sett_assets_category (
    sac_code character varying(3) NOT NULL,
    sac_desc text NOT NULL,
    sac_add_user character varying(30) NOT NULL,
    sac_add_date timestamp without time zone NOT NULL,
    sac_edit_user character varying(30) NOT NULL,
    sac_edit_date timestamp without time zone NOT NULL
);


ALTER TABLE man.sett_assets_category OWNER TO armasi_man;

--
-- Name: sett_assets_group; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.sett_assets_group (
    sag_code character varying(3) NOT NULL,
    sag_desc text NOT NULL,
    sag_add_user character varying(30) NOT NULL,
    sag_add_date timestamp without time zone NOT NULL,
    sag_edit_user character varying(30) NOT NULL,
    sag_edit_date timestamp without time zone NOT NULL
);


ALTER TABLE man.sett_assets_group OWNER TO armasi_man;

--
-- Name: sett_ceklist; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.sett_ceklist (
    ceklist_code character varying(7) NOT NULL,
    ceklist_name character varying(200) NOT NULL,
    user_create character varying(100),
    date_create timestamp without time zone,
    user_modify character varying(100),
    date_modify timestamp without time zone
);


ALTER TABLE man.sett_ceklist OWNER TO armasi_man;

--
-- Name: sett_ceklist_asset; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.sett_ceklist_asset (
    ceklist_code character varying(7) NOT NULL,
    asset_code character varying(10) NOT NULL
);


ALTER TABLE man.sett_ceklist_asset OWNER TO armasi_man;

--
-- Name: sett_ceklist_detail; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.sett_ceklist_detail (
    ceklist_code character varying(7) NOT NULL,
    cd_code smallint NOT NULL,
    cd_name character varying(200) NOT NULL,
    cd_parent smallint,
    cd_sort smallint
);


ALTER TABLE man.sett_ceklist_detail OWNER TO armasi_man;

--
-- Name: sett_employee; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.sett_employee (
    se_code character varying(8) NOT NULL,
    se_name text NOT NULL,
    se_department text NOT NULL,
    se_position text NOT NULL,
    se_add_user character varying(30) NOT NULL,
    se_add_date timestamp without time zone NOT NULL,
    se_edit_user character varying(30) NOT NULL,
    se_edit_date timestamp without time zone NOT NULL
);


ALTER TABLE man.sett_employee OWNER TO armasi_man;

--
-- Name: sett_location; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.sett_location (
    sl_code character varying(3) NOT NULL,
    sl_desc text NOT NULL,
    sl_add_user character varying(30) NOT NULL,
    sl_add_date timestamp without time zone NOT NULL,
    sl_edit_user character varying(30) NOT NULL,
    sl_edit_date timestamp without time zone NOT NULL
);


ALTER TABLE man.sett_location OWNER TO armasi_man;

--
-- Name: sett_maintenance_type; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.sett_maintenance_type (
    smt_work_type character varying(30) NOT NULL,
    smt_code character varying(2) NOT NULL,
    smt_description text NOT NULL,
    smt_add_user character varying(30) NOT NULL,
    smt_add_date timestamp without time zone NOT NULL,
    smt_edit_user character varying(30) NOT NULL,
    smt_edit_date timestamp without time zone NOT NULL
);


ALTER TABLE man.sett_maintenance_type OWNER TO armasi_man;

--
-- Name: sett_manufacture; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.sett_manufacture (
    sm_code character varying(3) NOT NULL,
    sm_desc text NOT NULL,
    sm_country text NOT NULL,
    sm_add_user character varying(30) NOT NULL,
    sm_add_date timestamp without time zone NOT NULL,
    sm_edit_user character varying(30) NOT NULL,
    sm_edit_date timestamp without time zone NOT NULL
);


ALTER TABLE man.sett_manufacture OWNER TO armasi_man;

--
-- Name: sett_sub_location; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.sett_sub_location (
    ssl_location_code character varying(2) NOT NULL,
    ssl_code character varying(2) NOT NULL,
    ssl_desc text NOT NULL,
    ssl_add_user character varying(30) NOT NULL,
    ssl_add_date timestamp without time zone NOT NULL,
    ssl_edit_user character varying(30) NOT NULL,
    ssl_edit_date timestamp without time zone NOT NULL
);


ALTER TABLE man.sett_sub_location OWNER TO armasi_man;

--
-- Name: sparepart_temp; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.sparepart_temp (
    amsp_code character varying(10) NOT NULL,
    amsp_sparepart_code character varying(50) NOT NULL,
    amsp_sparepart_desc text,
    amsp_unit character varying(15)
);


ALTER TABLE man.sparepart_temp OWNER TO armasi_man;

--
-- Name: tbl_ceklist; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_ceklist (
    asset_code character varying(10) NOT NULL,
    tanggal date NOT NULL,
    ceklist_code character varying(7),
    user_create character varying(100),
    date_create timestamp without time zone,
    user_modify character varying(100),
    date_modify timestamp without time zone
);


ALTER TABLE man.tbl_ceklist OWNER TO armasi_man;

--
-- Name: tbl_ceklist_detail; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_ceklist_detail (
    asset_code character varying(10) NOT NULL,
    tanggal date NOT NULL,
    cd_code smallint NOT NULL,
    cd_value character varying(10),
    cd_note character varying(200),
    cd_uty character varying(200)
);


ALTER TABLE man.tbl_ceklist_detail OWNER TO armasi_man;

--
-- Name: tbl_downtime; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_downtime (
    dt_code character varying(12) NOT NULL,
    amm_code character varying(10) NOT NULL,
    tanggal timestamp(4) without time zone NOT NULL,
    dt_value numeric NOT NULL,
    dt_desc character varying(300),
    dt_personil character varying(100),
    user_create character varying(100),
    date_create timestamp without time zone,
    user_modify character varying(100),
    date_modify timestamp without time zone
);


ALTER TABLE man.tbl_downtime OWNER TO armasi_man;

--
-- Name: tbl_dtl_spkmr; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_dtl_spkmr (
    no_mr_dtl text NOT NULL,
    no_mr text NOT NULL,
    keterangan text,
    spk text,
    qty numeric,
    jenis text
);


ALTER TABLE man.tbl_dtl_spkmr OWNER TO armasi_man;

--
-- Name: tbl_hours_asset; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_hours_asset (
    amm_code character varying(10) NOT NULL,
    tanggal date NOT NULL,
    jam numeric NOT NULL,
    user_create character varying(100),
    date_create timestamp without time zone,
    user_modify character varying(100),
    date_modify timestamp without time zone
);


ALTER TABLE man.tbl_hours_asset OWNER TO armasi_man;

--
-- Name: tbl_km_asset; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_km_asset (
    amm_code character varying(10) NOT NULL,
    tanggal date NOT NULL,
    km numeric NOT NULL,
    user_create character varying(100),
    date_create timestamp without time zone,
    user_modify character varying(100),
    date_modify timestamp without time zone
);


ALTER TABLE man.tbl_km_asset OWNER TO armasi_man;

--
-- Name: tbl_mr; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_mr (
    mr_code character varying(13) NOT NULL,
    mr_date timestamp without time zone NOT NULL,
    wo_code character varying(13),
    mr_status character varying(1),
    user_create character varying(100),
    date_create timestamp without time zone,
    user_modify character varying(100),
    date_modify timestamp without time zone
);


ALTER TABLE man.tbl_mr OWNER TO armasi_man;

--
-- Name: tbl_mr_detail; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_mr_detail (
    mr_code character varying(13) NOT NULL,
    item_code character varying(50) NOT NULL,
    item_name text NOT NULL,
    unit character varying(15),
    qty numeric
);


ALTER TABLE man.tbl_mr_detail OWNER TO armasi_man;

--
-- Name: tbl_mreqitem; Type: TABLE; Schema: man; Owner: armasi
--

CREATE TABLE man.tbl_mreqitem (
    mrequest_kode character varying(50) NOT NULL,
    item_kode character varying(50) NOT NULL,
    qty numeric,
    qty_pr numeric,
    request_kode character varying(50),
    notes character varying(254) NOT NULL,
    status character varying(20),
    tgl_kebutuhan date,
    modiby character varying(10),
    modidate date,
    requester character varying(30),
    vol character varying(10),
    qty_ numeric,
    kode_produksi character varying(4),
    skala_prioritas character varying(10),
    approve_status character varying(1),
    approve_by character varying(15),
    approve_note text,
    approve_date timestamp without time zone
);


ALTER TABLE man.tbl_mreqitem OWNER TO armasi;

--
-- Name: tbl_mrequest; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_mrequest (
    mrequest_kode character varying(50) NOT NULL,
    departemen_kode character varying(50) NOT NULL,
    requester character varying(40),
    tgl date,
    status character varying(20),
    create_by character varying(20),
    check_by character varying(20),
    approve_by character varying(20),
    closed boolean,
    modiby character varying(10),
    modidate date,
    jenis text,
    approval_date date,
    wo_code character varying(13) NOT NULL,
    departemen_nama character varying(50)
);


ALTER TABLE man.tbl_mrequest OWNER TO armasi_man;

--
-- Name: tbl_mrspk; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_mrspk (
    spkmr_code character varying(15) NOT NULL,
    spkmr_date timestamp without time zone NOT NULL,
    wo_code character varying(13),
    spkmr_status character varying(1),
    spkmr_desc text,
    user_create character varying(100),
    date_create timestamp without time zone,
    user_modify character varying(100),
    date_modify timestamp without time zone
);


ALTER TABLE man.tbl_mrspk OWNER TO armasi_man;

--
-- Name: tbl_mrspk_detail; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_mrspk_detail (
    spkmr_code character varying(15) NOT NULL,
    item_name text NOT NULL,
    qty numeric
);


ALTER TABLE man.tbl_mrspk_detail OWNER TO armasi_man;

--
-- Name: tbl_psp; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_psp (
    psp_code character varying(14) NOT NULL,
    tanggal date NOT NULL,
    status character varying(1) DEFAULT 1,
    requester character varying(40),
    wo_code character varying(13) NOT NULL,
    departemen_code character varying(50) NOT NULL,
    departemen_name character varying(50) NOT NULL,
    bon_kode_real character varying(14),
    user_create character varying(100),
    date_create timestamp without time zone,
    user_modify character varying(100),
    date_modify timestamp without time zone,
    alasan_cancel character varying(50),
    sub_plant character varying(3)
);


ALTER TABLE man.tbl_psp OWNER TO armasi_man;

--
-- Name: tbl_psp_detail; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_psp_detail (
    psp_code character varying(14) NOT NULL,
    item_code character varying(50) NOT NULL,
    item_name text NOT NULL,
    qty numeric,
    keterangan text,
    ket_kembali text
);


ALTER TABLE man.tbl_psp_detail OWNER TO armasi_man;

--
-- Name: tbl_spkmr; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_spkmr (
    no_mr text NOT NULL,
    usefor text,
    departemen_kode text,
    keterangan text,
    spk text,
    tgl date,
    keterangan1 text,
    approval_spk boolean,
    approve_by character varying(20),
    tgl_approval_spk date,
    keterangan_spk text,
    approval_time time without time zone,
    kode_produksi character varying(10),
    approve_by1 character varying(20),
    tgl_approval_spk1 timestamp without time zone,
    keterangan_spk1 text,
    approval_spk1 character varying(10),
    wo_code character varying(13) NOT NULL,
    departemen_nama character varying(50)
);


ALTER TABLE man.tbl_spkmr OWNER TO armasi_man;

--
-- Name: tbl_wo; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_wo (
    wo_code character varying(13) NOT NULL,
    wo_date timestamp without time zone NOT NULL,
    wr_code character varying(13),
    wo_status character varying(1),
    wo_urgency character varying(20),
    wo_due date,
    wo_type character varying(30),
    wo_type_code character varying(2),
    wo_desc text,
    wo_asset character varying(10),
    wo_scheduled timestamp without time zone,
    wo_duration smallint,
    wo_unit_duration character varying(20),
    wo_real_scheduled_start timestamp without time zone,
    wo_real_scheduled_end timestamp without time zone,
    wo_real_duration smallint,
    wo_instruction text,
    wo_pic_type character varying(1),
    wo_pic1 character varying(100),
    wo_pic2 character varying(100),
    wo_pic3 character varying(100),
    wo_complete_by character varying(8),
    wo_complete_date date,
    user_create character varying(100),
    date_create timestamp without time zone,
    user_modify character varying(100),
    date_modify timestamp without time zone,
    wo_source character varying(2),
    wo_location character varying(3),
    wo_maintenance character varying(10),
    wo_note text,
    wo_isdowntime character varying(1),
    wo_real_unit_duration character varying(20),
    sub_plant character varying(3)
);


ALTER TABLE man.tbl_wo OWNER TO armasi_man;

--
-- Name: tbl_wo_detail; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_wo_detail (
    wo_code character varying(13) NOT NULL,
    item_code character varying(50) NOT NULL,
    item_name text NOT NULL,
    unit character varying(15),
    qty numeric,
    netcost numeric
);


ALTER TABLE man.tbl_wo_detail OWNER TO armasi_man;

--
-- Name: tbl_wr; Type: TABLE; Schema: man; Owner: armasi_man
--

CREATE TABLE man.tbl_wr (
    wr_code character varying(13) NOT NULL,
    wr_date timestamp without time zone NOT NULL,
    wr_urgency character varying(20),
    wr_due date,
    wr_desc text,
    wr_request_by character varying(8),
    wr_to_department character varying(50),
    wr_asset character varying(10),
    wr_approve_status character varying(1),
    wr_approve_by character varying(8),
    wr_approve_date date,
    wr_reason_reject character varying(500),
    user_create character varying(100),
    date_create timestamp without time zone,
    user_modify character varying(100),
    date_modify timestamp without time zone
);


ALTER TABLE man.tbl_wr OWNER TO armasi_man;

--
-- Name: a_region; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.a_region (
    region character varying(10) NOT NULL,
    "Description" character varying(50)
);


ALTER TABLE public.a_region OWNER TO armasi;

--
-- Name: bd_mc_param; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bd_mc_param (
    bmp_id integer NOT NULL,
    bmp_plant smallint,
    bmp_sub_plant character varying(1),
    bmp_date timestamp without time zone,
    bqm_formula character varying(20),
    bmp_sp_mc_min numeric,
    bmp_sp_mc_max numeric,
    bmp_pr_mc_min numeric,
    bmp_pr_mc_max numeric,
    bmp_ket text
);


ALTER TABLE public.bd_mc_param OWNER TO postgres;

--
-- Name: bd_mc_param_bmp_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bd_mc_param_bmp_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.bd_mc_param_bmp_id_seq OWNER TO postgres;

--
-- Name: bd_mc_param_bmp_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bd_mc_param_bmp_id_seq OWNED BY public.bd_mc_param.bmp_id;


--
-- Name: bd_qc_mc; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bd_qc_mc (
    bqm_id integer NOT NULL,
    bqm_plant smallint,
    bqm_sub_plant character varying(1),
    bqm_formula character varying(20),
    bqm_date timestamp without time zone,
    bqm_sec character varying(1),
    bqm_mc_std_min numeric,
    bqm_mc_std_max numeric,
    bqm_mc_val numeric,
    bqm_mc_ket text
);


ALTER TABLE public.bd_qc_mc OWNER TO postgres;

--
-- Name: bd_qc_mc_bqm_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bd_qc_mc_bqm_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.bd_qc_mc_bqm_id_seq OWNER TO postgres;

--
-- Name: bd_qc_mc_bqm_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bd_qc_mc_bqm_id_seq OWNED BY public.bd_qc_mc.bqm_id;


--
-- Name: country; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.country (
    country_kode character varying(50) NOT NULL,
    country_nama character varying(40),
    inactive boolean,
    modiby character varying(10),
    modidate date,
    status_tran character varying(1)
);


ALTER TABLE public.country OWNER TO armasi;

--
-- Name: currency; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.currency (
    valuta_kode character varying(50) NOT NULL,
    valuta_nama character varying(40),
    inactive boolean,
    modiby character varying(10),
    modidate date,
    status_tran character varying(1)
);


ALTER TABLE public.currency OWNER TO armasi;

--
-- Name: departemen; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.departemen (
    departemen_kode character varying(50) NOT NULL,
    plan_kode character varying(50) NOT NULL,
    departemen_nama character varying(40),
    inactive boolean,
    modiby character varying(10),
    modidate date,
    subplankode character varying(5),
    header_departemen character(2),
    status_tran character varying(1)
);


ALTER TABLE public.departemen OWNER TO armasi;

--
-- Name: inv_master_area; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_master_area (
    plan_kode character varying(1) NOT NULL,
    kd_area character varying(10) NOT NULL,
    ket_area text,
    jum_brs integer,
    jum_plt integer,
    area_status boolean DEFAULT true NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_by character varying(20) NOT NULL,
    remarks text DEFAULT ''::text NOT NULL
);


ALTER TABLE public.inv_master_area OWNER TO armasi;

--
-- Name: inv_master_lok_pallet; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_master_lok_pallet (
    iml_plan_kode character varying(1) NOT NULL,
    iml_kd_area character varying(2) NOT NULL,
    iml_no_baris integer NOT NULL,
    iml_no_lok integer NOT NULL,
    iml_kd_lok character varying(8) NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    created_by character varying(20) NOT NULL
);


ALTER TABLE public.inv_master_lok_pallet OWNER TO armasi;

--
-- Name: inv_opname; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_opname (
    io_plan_kode character varying(1) NOT NULL,
    io_kd_lok character varying(8) NOT NULL,
    io_no_pallet character varying(30) NOT NULL,
    io_qty_pallet integer,
    io_tgl timestamp without time zone
);


ALTER TABLE public.inv_opname OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet (
    plan_kode character(1) NOT NULL,
    no_mutasi character varying(20) NOT NULL,
    tanggal date,
    pallet_no character varying(18) NOT NULL,
    qty numeric DEFAULT 0,
    create_date timestamp without time zone NOT NULL,
    create_user character varying(15),
    status_mut character varying(3) NOT NULL,
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean DEFAULT false,
    status_tran character varying(1),
    item_kodemut character varying,
    sizemut character varying,
    shademut character varying,
    kuamut character varying,
    groupmut character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet OWNER TO armasi;

--
-- Name: pallets_mutation_summary_by_quantity; Type: MATERIALIZED VIEW; Schema: public; Owner: armasi
--

CREATE MATERIALIZED VIEW public.pallets_mutation_summary_by_quantity AS
 SELECT tbl_sp_mutasi_pallet.pallet_no,
    COALESCE(sum(
        CASE
            WHEN ("left"((tbl_sp_mutasi_pallet.no_mutasi)::text, 3) = ANY (ARRAY['MBJ'::text, 'SEQ'::text])) THEN abs(tbl_sp_mutasi_pallet.qty)
            ELSE (0)::numeric
        END), (0)::numeric) AS initial_quantity,
    COALESCE(sum(
        CASE
            WHEN (("left"((tbl_sp_mutasi_pallet.no_mutasi)::text, 3) = 'MLT'::text) AND (tbl_sp_mutasi_pallet.qty > (0)::numeric)) THEN tbl_sp_mutasi_pallet.qty
            ELSE (0)::numeric
        END), (0)::numeric) AS in_mut_quantity,
    COALESCE(sum(
        CASE
            WHEN (("left"((tbl_sp_mutasi_pallet.no_mutasi)::text, 3) = 'MLT'::text) AND (tbl_sp_mutasi_pallet.qty < (0)::numeric)) THEN abs(tbl_sp_mutasi_pallet.qty)
            ELSE (0)::numeric
        END), (0)::numeric) AS out_mut_quantity,
    COALESCE(sum(
        CASE
            WHEN (("left"((tbl_sp_mutasi_pallet.no_mutasi)::text, 3) = 'OBJ'::text) AND (tbl_sp_mutasi_pallet.qty > (0)::numeric)) THEN tbl_sp_mutasi_pallet.qty
            ELSE (0)::numeric
        END), (0)::numeric) AS in_adjusted_quantity,
    COALESCE(sum(
        CASE
            WHEN (("left"((tbl_sp_mutasi_pallet.no_mutasi)::text, 3) = 'OBJ'::text) AND (tbl_sp_mutasi_pallet.qty < (0)::numeric)) THEN abs(tbl_sp_mutasi_pallet.qty)
            ELSE (0)::numeric
        END), (0)::numeric) AS out_adjusted_quantity,
    COALESCE(sum(
        CASE
            WHEN ("left"((tbl_sp_mutasi_pallet.no_mutasi)::text, 3) = 'ULT'::text) THEN abs(tbl_sp_mutasi_pallet.qty)
            ELSE (0)::numeric
        END), (0)::numeric) AS returned_quantity,
    COALESCE(sum(
        CASE
            WHEN ("left"((tbl_sp_mutasi_pallet.no_mutasi)::text, 3) = 'BRP'::text) THEN abs(tbl_sp_mutasi_pallet.qty)
            ELSE (0)::numeric
        END), (0)::numeric) AS broken_quantity,
    COALESCE(sum(
        CASE
            WHEN ("left"((tbl_sp_mutasi_pallet.no_mutasi)::text, 3) = ANY (ARRAY['JSR'::text, 'JSP'::text, 'BAM'::text, 'BAL'::text])) THEN abs(tbl_sp_mutasi_pallet.qty)
            ELSE (0)::numeric
        END), (0)::numeric) AS sold_quantity,
    COALESCE(sum(
        CASE
            WHEN ("left"((tbl_sp_mutasi_pallet.no_mutasi)::text, 3) = 'FOC'::text) THEN abs(tbl_sp_mutasi_pallet.qty)
            ELSE (0)::numeric
        END), (0)::numeric) AS foc_quantity,
    COALESCE(sum(
        CASE
            WHEN ("left"((tbl_sp_mutasi_pallet.no_mutasi)::text, 3) = 'SMP'::text) THEN abs(tbl_sp_mutasi_pallet.qty)
            ELSE (0)::numeric
        END), (0)::numeric) AS sample_quantity,
    max(tbl_sp_mutasi_pallet.create_date) AS last_updated_at
   FROM public.tbl_sp_mutasi_pallet
  GROUP BY tbl_sp_mutasi_pallet.pallet_no
  ORDER BY tbl_sp_mutasi_pallet.pallet_no
  WITH NO DATA;


ALTER TABLE public.pallets_mutation_summary_by_quantity OWNER TO armasi;

--
-- Name: tbl_sp_hasilbj; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_hasilbj (
    plan_kode character(1) NOT NULL,
    seq_no character varying(18) NOT NULL,
    pallet_no character varying(18) NOT NULL,
    tanggal date,
    item_kode character varying(20) NOT NULL,
    quality character varying(20),
    subplant character varying(2),
    shade character varying(4),
    size character varying(4),
    qty smallint DEFAULT 0 NOT NULL,
    create_date date NOT NULL,
    create_user character varying(15),
    status_plt character varying(1) DEFAULT 'O'::character varying,
    rkpterima_no character varying(20),
    rkpterima_tanggal date,
    rkpterima_user character varying(15),
    terima_no character varying(18),
    tanggal_terima date,
    terima_user character varying(15),
    status_item character varying(1) DEFAULT 'O'::character varying,
    txn_no character varying(18),
    shift smallint,
    last_qty smallint,
    line character varying(1),
    regu character varying(1),
    plt_status character varying(20),
    keterangan text,
    kd_customer character varying(200),
    tanggal_pending date,
    last_update timestamp without time zone,
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    area character varying(10),
    lokasi character varying(15),
    qa_approved boolean DEFAULT false NOT NULL,
    block_ref_id character varying(20)
);


ALTER TABLE public.tbl_sp_hasilbj OWNER TO armasi;

--
-- Name: COLUMN tbl_sp_hasilbj.block_ref_id; Type: COMMENT; Schema: public; Owner: armasi
--

COMMENT ON COLUMN public.tbl_sp_hasilbj.block_ref_id IS 'Reference to transaction ID that blocks this pallet. May be empty/null if it is an arbitrary inspection block.';


--
-- Name: details_pallets_handed_over; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.details_pallets_handed_over AS
 SELECT tbl_sp_hasilbj.pallet_no,
    item.item_nama AS motif_name,
    item.item_kode AS motif_id,
    ( SELECT category.category_nama
           FROM public.category
          WHERE ((category.category_kode)::text = "left"((item.item_kode)::text, 2))) AS motif_dimension,
    tbl_sp_hasilbj.status_plt AS status,
    tbl_sp_hasilbj.subplant,
    tbl_sp_hasilbj.quality,
    tbl_sp_hasilbj.shade AS shading,
    tbl_sp_hasilbj.size,
    tbl_sp_hasilbj.line,
    tbl_sp_hasilbj.qty AS initial_quantity,
    tbl_sp_hasilbj.last_qty AS current_quantity,
    COALESCE(mutation.shipped_quantity, (0)::numeric) AS shipped_quantity,
    COALESCE(mutation.adjusted_quantity, (0)::numeric) AS adjusted_quantity,
    tbl_sp_hasilbj.shift AS creator_shift,
    tbl_sp_hasilbj.regu AS creator_group,
    tbl_sp_hasilbj.status_plt AS pallet_status,
    tbl_sp_hasilbj.rkpterima_tanggal AS marked_for_handover_date,
    tbl_sp_hasilbj.rkpterima_no AS marked_for_handover_no,
    tbl_sp_hasilbj.rkpterima_user AS marked_for_handover_userid,
    tbl_sp_hasilbj.terima_no AS stwh_no,
    tbl_sp_hasilbj.tanggal_terima AS stwh_date,
    tbl_sp_hasilbj.terima_user AS stwh_userid,
    tbl_sp_hasilbj.create_date AS created_at,
    COALESCE(tbl_sp_hasilbj.update_tran,
        CASE
            WHEN (tbl_sp_hasilbj.shift = 1) THEN (tbl_sp_hasilbj.tanggal + '07:00:00'::time without time zone)
            WHEN (tbl_sp_hasilbj.shift = 2) THEN (tbl_sp_hasilbj.tanggal + '15:00:00'::time without time zone)
            WHEN (tbl_sp_hasilbj.shift = 3) THEN (tbl_sp_hasilbj.tanggal + '23:00:00'::time without time zone)
            ELSE (tbl_sp_hasilbj.tanggal + '00:00:00'::time without time zone)
        END) AS updated_at,
    tbl_sp_hasilbj.qa_approved,
    location.location_id,
    location.location_subplant,
    location.location_area_no,
    location.location_area_name,
    location.location_line_no,
    location.location_cell_no
   FROM ((((public.tbl_sp_hasilbj
     LEFT JOIN public.tbl_sp_downgrade_pallet downgrade ON ((((tbl_sp_hasilbj.pallet_no)::text = (downgrade.pallet_no)::text) AND (downgrade.approval = true))))
     JOIN public.item ON ((((downgrade.item_kode_lama IS NOT NULL) AND ((item.item_kode)::text = (downgrade.item_kode_lama)::text)) OR ((downgrade.item_kode_lama IS NULL) AND ((item.item_kode)::text = (tbl_sp_hasilbj.item_kode)::text)))))
     LEFT JOIN ( SELECT pallets_mutation_summary_by_quantity.pallet_no,
            ((pallets_mutation_summary_by_quantity.sold_quantity + pallets_mutation_summary_by_quantity.foc_quantity) + pallets_mutation_summary_by_quantity.sample_quantity) AS shipped_quantity,
            abs(((((pallets_mutation_summary_by_quantity.in_mut_quantity - pallets_mutation_summary_by_quantity.out_mut_quantity) + pallets_mutation_summary_by_quantity.in_adjusted_quantity) - pallets_mutation_summary_by_quantity.out_adjusted_quantity) - pallets_mutation_summary_by_quantity.returned_quantity)) AS adjusted_quantity
           FROM public.pallets_mutation_summary_by_quantity) mutation ON (((mutation.pallet_no)::text = (tbl_sp_hasilbj.pallet_no)::text)))
     LEFT JOIN ( SELECT io.io_no_pallet AS pallet_no,
            io.io_kd_lok AS location_id,
            io.io_plan_kode AS location_subplant,
            iml.iml_kd_area AS location_area_no,
            ima.ket_area AS location_area_name,
            iml.iml_no_baris AS location_line_no,
            iml.iml_no_lok AS location_cell_no
           FROM ((public.inv_opname io
             LEFT JOIN public.inv_master_lok_pallet iml ON ((((io.io_plan_kode)::text = (iml.iml_plan_kode)::text) AND ((io.io_kd_lok)::text = (iml.iml_kd_lok)::text))))
             LEFT JOIN public.inv_master_area ima ON ((((iml.iml_plan_kode)::text = (ima.plan_kode)::text) AND ((iml.iml_kd_area)::text = (ima.kd_area)::text))))) location ON (((tbl_sp_hasilbj.pallet_no)::text = (location.pallet_no)::text)))
  WHERE (("left"((tbl_sp_hasilbj.pallet_no)::text, 3) = 'PLT'::text) AND ((tbl_sp_hasilbj.status_plt)::text = 'R'::text) AND ((COALESCE(tbl_sp_hasilbj.terima_no, ''::character varying))::text <> ''::text))
  ORDER BY tbl_sp_hasilbj.pallet_no;


ALTER TABLE public.details_pallets_handed_over OWNER TO armasi;

--
-- Name: details_pallets_qa_approved; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.details_pallets_qa_approved AS
 SELECT tbl_sp_hasilbj.pallet_no,
    item.item_nama AS motif_name,
    item.item_kode AS motif_id,
    category.category_nama AS motif_dimension,
    tbl_sp_hasilbj.status_plt AS status,
    tbl_sp_hasilbj.subplant,
    tbl_sp_hasilbj.quality,
    tbl_sp_hasilbj.shade AS shading,
    tbl_sp_hasilbj.size,
    tbl_sp_hasilbj.line,
    tbl_sp_hasilbj.qty AS initial_quantity,
    tbl_sp_hasilbj.last_qty AS current_quantity,
    tbl_sp_hasilbj.shift AS creator_shift,
    tbl_sp_hasilbj.regu AS creator_group,
    tbl_sp_hasilbj.rkpterima_tanggal AS marked_for_handover_date,
    tbl_sp_hasilbj.rkpterima_no AS marked_for_handover_no,
    tbl_sp_hasilbj.rkpterima_user AS marked_for_handover_userid,
    tbl_sp_hasilbj.create_date AS created_at,
        CASE
            WHEN (tbl_sp_hasilbj.update_tran IS NOT NULL) THEN tbl_sp_hasilbj.update_tran
            WHEN (tbl_sp_hasilbj.shift = 1) THEN (tbl_sp_hasilbj.tanggal + '07:00:00'::time without time zone)
            WHEN (tbl_sp_hasilbj.shift = 2) THEN (tbl_sp_hasilbj.tanggal + '15:00:00'::time without time zone)
            WHEN (tbl_sp_hasilbj.shift = 3) THEN (tbl_sp_hasilbj.tanggal + '23:00:00'::time without time zone)
            ELSE (tbl_sp_hasilbj.tanggal + '00:00:00'::time without time zone)
        END AS updated_at,
    tbl_sp_hasilbj.qa_approved
   FROM (((public.tbl_sp_hasilbj
     LEFT JOIN public.tbl_sp_downgrade_pallet downgrade ON (((downgrade.approval = true) AND ((tbl_sp_hasilbj.pallet_no)::text = (downgrade.pallet_no)::text))))
     JOIN public.item ON ((((downgrade.item_kode_lama IS NOT NULL) AND ((downgrade.item_kode_lama)::text = (item.item_kode)::text)) OR ((downgrade.item_kode_lama IS NULL) AND ((tbl_sp_hasilbj.item_kode)::text = (item.item_kode)::text)))))
     JOIN public.category ON (((category.category_kode)::text = "left"((item.item_kode)::text, 2))))
  WHERE (("left"((tbl_sp_hasilbj.pallet_no)::text, 3) = 'PLT'::text) AND ((COALESCE(tbl_sp_hasilbj.rkpterima_no, ''::character varying))::text <> ''::text) AND (tbl_sp_hasilbj.qa_approved IS TRUE))
  ORDER BY tbl_sp_hasilbj.pallet_no;


ALTER TABLE public.details_pallets_qa_approved OWNER TO armasi;

--
-- Name: details_pallets_qa_approved_not_handed_over; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.details_pallets_qa_approved_not_handed_over AS
 SELECT tbl_sp_hasilbj.pallet_no,
    item.item_nama AS motif_name,
    item.item_kode AS motif_id,
    ( SELECT category.category_nama
           FROM public.category
          WHERE ((category.category_kode)::text = "left"((item.item_kode)::text, 2))) AS motif_dimension,
    tbl_sp_hasilbj.status_plt AS status,
    tbl_sp_hasilbj.subplant,
    tbl_sp_hasilbj.quality,
    tbl_sp_hasilbj.shade AS shading,
    tbl_sp_hasilbj.size,
    tbl_sp_hasilbj.line,
    tbl_sp_hasilbj.qty AS initial_quantity,
    tbl_sp_hasilbj.last_qty AS current_quantity,
    tbl_sp_hasilbj.shift AS creator_shift,
    tbl_sp_hasilbj.regu AS creator_group,
    tbl_sp_hasilbj.rkpterima_tanggal AS marked_for_handover_date,
    tbl_sp_hasilbj.rkpterima_no AS marked_for_handover_no,
    tbl_sp_hasilbj.rkpterima_user AS marked_for_handover_userid,
    tbl_sp_hasilbj.create_date AS created_at,
        CASE
            WHEN (tbl_sp_hasilbj.update_tran IS NOT NULL) THEN tbl_sp_hasilbj.update_tran
            WHEN (tbl_sp_hasilbj.shift = 1) THEN (tbl_sp_hasilbj.tanggal + '07:00:00'::time without time zone)
            WHEN (tbl_sp_hasilbj.shift = 2) THEN (tbl_sp_hasilbj.tanggal + '15:00:00'::time without time zone)
            WHEN (tbl_sp_hasilbj.shift = 3) THEN (tbl_sp_hasilbj.tanggal + '23:00:00'::time without time zone)
            ELSE (tbl_sp_hasilbj.tanggal + '00:00:00'::time without time zone)
        END AS updated_at,
    tbl_sp_hasilbj.qa_approved
   FROM (public.tbl_sp_hasilbj
     JOIN public.item ON (((item.item_kode)::text = (tbl_sp_hasilbj.item_kode)::text)))
  WHERE (("left"((tbl_sp_hasilbj.pallet_no)::text, 3) = 'PLT'::text) AND ((tbl_sp_hasilbj.status_plt)::text = 'O'::text) AND ((COALESCE(tbl_sp_hasilbj.terima_no, ''::character varying))::text = ''::text) AND ((COALESCE(tbl_sp_hasilbj.rkpterima_no, ''::character varying))::text <> ''::text) AND (tbl_sp_hasilbj.qa_approved IS TRUE))
  ORDER BY tbl_sp_hasilbj.pallet_no;


ALTER TABLE public.details_pallets_qa_approved_not_handed_over OWNER TO armasi;

--
-- Name: gen_user_adm; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.gen_user_adm (
    gua_kode character varying(32) NOT NULL,
    gua_pass character(60) NOT NULL,
    gua_nama text,
    gua_lvl character varying(2)[] DEFAULT '{}'::character varying[] NOT NULL,
    gua_subplants character varying(8) DEFAULT '2A'::character varying NOT NULL,
    gua_active boolean DEFAULT true NOT NULL,
    gua_last_pass_reset timestamp with time zone DEFAULT now() NOT NULL,
    gua_since timestamp with time zone DEFAULT now() NOT NULL,
    gua_last_updated_at timestamp with time zone DEFAULT now() NOT NULL,
    gua_subplant_handover character varying(8) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE public.gen_user_adm OWNER TO armasi;

--
-- Name: COLUMN gen_user_adm.gua_lvl; Type: COMMENT; Schema: public; Owner: armasi
--

COMMENT ON COLUMN public.gen_user_adm.gua_lvl IS 'Level/role of the user

SU: Superuser
KS: Kasubsie (supervisor)
SK: stockist (handles handover and sorting of pallets in the warehouse)
CK: checker (handles shipping)
M: Manager
A: (old value, TBD)';


--
-- Name: COLUMN gen_user_adm.gua_subplants; Type: COMMENT; Schema: public; Owner: armasi
--

COMMENT ON COLUMN public.gen_user_adm.gua_subplants IS 'To which subplant this user belongs.

one subplant: A
multi-subplant: A,B,C';


--
-- Name: gl_qc_app; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.gl_qc_app (
    gqa_id integer NOT NULL,
    gqa_plant smallint,
    gqa_sub_plant character varying(1),
    gqa_motif character varying(20),
    gqa_date timestamp without time zone,
    gqa_line smallint,
    gqa_app_type character varying(2),
    gqa_reo character varying(1),
    gqa_reo_std_min numeric,
    gqa_reo_std_max numeric,
    gqa_reo_val numeric,
    gqa_ket text
);


ALTER TABLE public.gl_qc_app OWNER TO postgres;

--
-- Name: gl_qc_app_gqa_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.gl_qc_app_gqa_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gl_qc_app_gqa_id_seq OWNER TO postgres;

--
-- Name: gl_qc_app_gqa_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.gl_qc_app_gqa_id_seq OWNED BY public.gl_qc_app.gqa_id;


--
-- Name: gl_qc_param; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.gl_qc_param (
    gqp_id integer NOT NULL,
    gqp_plant smallint,
    gqp_sub_plant character varying(1),
    gqp_line smallint,
    gqp_date timestamp without time zone,
    gqp_motif character varying(20),
    gqp_ukr character varying(20),
    gqp_eng_lw_min numeric,
    gqp_eng_lw_max numeric,
    gqp_eng_br_min numeric,
    gqp_eng_br_max numeric,
    gqp_eng_vis_min numeric,
    gqp_eng_vis_max numeric,
    gqp_gla_lw_min numeric,
    gqp_gla_lw_max numeric,
    gqp_gla_br_min numeric,
    gqp_gla_br_max numeric,
    gqp_gla_vis_min numeric,
    gqp_gla_vis_max numeric,
    gqp_pas_lw_min numeric,
    gqp_pas_lw_max numeric,
    gqp_pas_br_min numeric,
    gqp_pas_br_max numeric,
    gqp_pas_vis_min numeric,
    gqp_pas_vis_max numeric,
    gqp_gra_lw_min numeric,
    gqp_gra_lw_max numeric,
    gqp_gra_br_min numeric,
    gqp_gra_br_max numeric,
    gqp_gra_vis_min numeric,
    gqp_gra_vis_max numeric,
    gqp_ket text,
    gqp_spr_br_min numeric,
    gqp_spr_br_max numeric,
    gqp_pas2_lw_min numeric,
    gqp_pas2_lw_max numeric,
    gqp_pas2_vis_min numeric,
    gqp_pas2_vis_max numeric,
    gqp_pas3_lw_min numeric,
    gqp_pas3_lw_max numeric,
    gqp_pas3_vis_min numeric,
    gqp_pas3_vis_max numeric
);


ALTER TABLE public.gl_qc_param OWNER TO postgres;

--
-- Name: gl_qc_param2802; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.gl_qc_param2802 (
    gqp_id integer,
    gqp_plant smallint,
    gqp_sub_plant character varying(1),
    gqp_line smallint,
    gqp_date timestamp without time zone,
    gqp_motif character varying(20),
    gqp_ukr character varying(20),
    gqp_eng_lw_min numeric,
    gqp_eng_lw_max numeric,
    gqp_eng_br_min numeric,
    gqp_eng_br_max numeric,
    gqp_eng_vis_min numeric,
    gqp_eng_vis_max numeric,
    gqp_gla_lw_min numeric,
    gqp_gla_lw_max numeric,
    gqp_gla_br_min numeric,
    gqp_gla_br_max numeric,
    gqp_gla_vis_min numeric,
    gqp_gla_vis_max numeric,
    gqp_pas_lw_min numeric,
    gqp_pas_lw_max numeric,
    gqp_pas_br_min numeric,
    gqp_pas_br_max numeric,
    gqp_pas_vis_min numeric,
    gqp_pas_vis_max numeric,
    gqp_gra_lw_min numeric,
    gqp_gra_lw_max numeric,
    gqp_gra_br_min numeric,
    gqp_gra_br_max numeric,
    gqp_gra_vis_min numeric,
    gqp_gra_vis_max numeric,
    gqp_ket text,
    gqp_spr_br_min numeric,
    gqp_spr_br_max numeric,
    gqp_pas2_lw_min numeric,
    gqp_pas2_lw_max numeric,
    gqp_pas2_vis_min numeric,
    gqp_pas2_vis_max numeric,
    gqp_pas3_lw_min numeric,
    gqp_pas3_lw_max numeric,
    gqp_pas3_vis_min numeric,
    gqp_pas3_vis_max numeric
);


ALTER TABLE public.gl_qc_param2802 OWNER TO armasi;

--
-- Name: gl_qc_param_gqp_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.gl_qc_param_gqp_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gl_qc_param_gqp_id_seq OWNER TO postgres;

--
-- Name: gl_qc_param_gqp_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.gl_qc_param_gqp_id_seq OWNED BY public.gl_qc_param.gqp_id;


--
-- Name: gl_qc_paramold; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.gl_qc_paramold (
    gqp_id integer,
    gqp_plant smallint,
    gqp_sub_plant character varying(1),
    gqp_line smallint,
    gqp_date timestamp without time zone,
    gqp_motif character varying(20),
    gqp_ukr character varying(20),
    gqp_eng_lw_min numeric,
    gqp_eng_lw_max numeric,
    gqp_eng_br_min numeric,
    gqp_eng_br_max numeric,
    gqp_eng_vis_min numeric,
    gqp_eng_vis_max numeric,
    gqp_gla_lw_min numeric,
    gqp_gla_lw_max numeric,
    gqp_gla_br_min numeric,
    gqp_gla_br_max numeric,
    gqp_gla_vis_min numeric,
    gqp_gla_vis_max numeric,
    gqp_pas_lw_min numeric,
    gqp_pas_lw_max numeric,
    gqp_pas_br_min numeric,
    gqp_pas_br_max numeric,
    gqp_pas_vis_min numeric,
    gqp_pas_vis_max numeric,
    gqp_gra_lw_min numeric,
    gqp_gra_lw_max numeric,
    gqp_gra_br_min numeric,
    gqp_gra_br_max numeric,
    gqp_gra_vis_min numeric,
    gqp_gra_vis_max numeric,
    gqp_ket text
);


ALTER TABLE public.gl_qc_paramold OWNER TO armasi;

--
-- Name: glcategory; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.glcategory (
    glcategory text NOT NULL,
    glcategory_nama text,
    inactive boolean,
    modiby character varying(10),
    modidate date,
    saldo_normal text,
    status_tran character varying(1)
);


ALTER TABLE public.glcategory OWNER TO armasi;

--
-- Name: glmaster; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.glmaster (
    gl_account text NOT NULL,
    glcategory text NOT NULL,
    glmaster_nama text,
    modiby character varying(10),
    modidate date,
    saldo_normal text,
    status_tran character varying(1)
);


ALTER TABLE public.glmaster OWNER TO armasi;

--
-- Name: inv_master_area_detail; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.inv_master_area_detail (
    plan_kode character varying(1) NOT NULL,
    kd_area character varying(10) NOT NULL,
    kd_baris smallint NOT NULL,
    jum_cell smallint NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    created_by character varying(8) NOT NULL,
    updated_by character varying(20) NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT inv_master_area_detail_jum_cell_check CHECK (((jum_cell > 0) AND (jum_cell < 1000))),
    CONSTRAINT inv_master_area_detail_kd_baris_check CHECK (((kd_baris > 0) AND (kd_baris < 100)))
);


ALTER TABLE public.inv_master_area_detail OWNER TO postgres;

--
-- Name: inv_master_areanew; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_master_areanew (
    plan_kode character varying(1),
    kd_area character varying(10),
    ket_area text,
    jum_brs integer,
    jum_plt integer
);


ALTER TABLE public.inv_master_areanew OWNER TO armasi;

--
-- Name: inv_master_areaold; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_master_areaold (
    plan_kode character varying(1),
    kd_area character varying(10),
    ket_area text
);


ALTER TABLE public.inv_master_areaold OWNER TO armasi;

--
-- Name: inv_master_lok_pallet2cblokb; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_master_lok_pallet2cblokb (
    iml_plan_kode character varying(1),
    iml_kd_area character varying(2),
    iml_no_baris integer,
    iml_no_lok integer,
    iml_kd_lok character varying(8)
);


ALTER TABLE public.inv_master_lok_pallet2cblokb OWNER TO armasi;

--
-- Name: inv_master_lok_palletb; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_master_lok_palletb (
    iml_plan_kode character varying(1),
    iml_kd_area character varying(2),
    iml_no_baris integer,
    iml_no_lok integer,
    iml_kd_lok character varying(8)
);


ALTER TABLE public.inv_master_lok_palletb OWNER TO armasi;

--
-- Name: inv_master_lok_palletc; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_master_lok_palletc (
    iml_plan_kode character varying(1),
    iml_kd_area character varying(2),
    iml_no_baris integer,
    iml_no_lok integer,
    iml_kd_lok character varying(8)
);


ALTER TABLE public.inv_master_lok_palletc OWNER TO armasi;

--
-- Name: inv_mutasi09072018; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_mutasi09072018 (
    pallet_no character varying(18),
    jml numeric
);


ALTER TABLE public.inv_mutasi09072018 OWNER TO armasi;

--
-- Name: inv_opname12092018; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_opname12092018 (
    io_plan_kode character varying(1),
    io_kd_lok character varying(8),
    io_no_pallet character varying(30),
    io_qty_pallet integer,
    io_tgl timestamp without time zone
);


ALTER TABLE public.inv_opname12092018 OWNER TO armasi;

--
-- Name: inv_opname14092018; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_opname14092018 (
    io_plan_kode character varying(1),
    io_kd_lok character varying(8),
    io_no_pallet character varying(30),
    io_qty_pallet integer,
    io_tgl timestamp without time zone
);


ALTER TABLE public.inv_opname14092018 OWNER TO armasi;

--
-- Name: inv_opname_hist; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_opname_hist (
    ioh_plan_kode character varying(1) NOT NULL,
    ioh_kd_lok character varying(8) DEFAULT '0'::character varying NOT NULL,
    ioh_no_pallet character varying(30) NOT NULL,
    ioh_qty_pallet integer,
    ioh_tgl timestamp without time zone NOT NULL,
    ioh_txn text NOT NULL,
    ioh_userid character varying(20),
    ioh_kd_lok_old character varying(8) DEFAULT '0'::character varying NOT NULL
);


ALTER TABLE public.inv_opname_hist OWNER TO armasi;

--
-- Name: inv_opnmutasi09072018; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_opnmutasi09072018 (
    io_no_pallet character varying(30),
    tot bigint
);


ALTER TABLE public.inv_opnmutasi09072018 OWNER TO armasi;

--
-- Name: inv_pallet_double; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_pallet_double (
    kdpal character varying(30),
    hit bigint
);


ALTER TABLE public.inv_pallet_double OWNER TO armasi;

--
-- Name: inv_pallet_txn; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_pallet_txn (
    ipt_plan_kode character varying(1) NOT NULL,
    ipt_area character varying(4) NOT NULL,
    ipt_tgl timestamp without time zone,
    ipt_txn_kode character varying(1) NOT NULL,
    ipt_kode_lok character varying(8) NOT NULL,
    ipt_kode_pallet character varying(20) NOT NULL,
    ipt_qty integer
);


ALTER TABLE public.inv_pallet_txn OWNER TO armasi;

--
-- Name: inv_rubah; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_rubah (
    noinv text,
    sjlama text,
    sjbaru text,
    sjsementara text
);


ALTER TABLE public.inv_rubah OWNER TO armasi;

--
-- Name: inv_txn_lok_pallet; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inv_txn_lok_pallet (
    plan_kode character varying(1),
    txn_no character varying(20),
    tanggal timestamp without time zone,
    pallet_no character varying(20),
    area character varying(10),
    lokasi character varying(15),
    user_input character varying(20)
);


ALTER TABLE public.inv_txn_lok_pallet OWNER TO armasi;

--
-- Name: inventory_checking; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inventory_checking (
    ref_no character varying(22) NOT NULL,
    location_plant character varying(2) NOT NULL,
    location_area_no character varying(2) NOT NULL,
    location_line_no integer NOT NULL,
    current_status character varying(20) NOT NULL,
    user_comments text NOT NULL,
    created_userid character varying(20) NOT NULL,
    created_at timestamp(6) without time zone DEFAULT now() NOT NULL,
    approved_userid character varying(20),
    approved_at timestamp(6) without time zone
);


ALTER TABLE public.inventory_checking OWNER TO armasi;

--
-- Name: COLUMN inventory_checking.current_status; Type: COMMENT; Schema: public; Owner: armasi
--

COMMENT ON COLUMN public.inventory_checking.current_status IS 'A - Approved, C - Closed, L - Logistics Approval, R - Audit Review, I - In Progress';


--
-- Name: inventory_checking_details; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.inventory_checking_details (
    ref_no character varying(22) NOT NULL,
    pallet_no character varying(18) NOT NULL,
    status character varying(1) NOT NULL,
    label_matches boolean,
    current_qty integer NOT NULL,
    checked_qty integer,
    current_location character varying(8),
    checked_location character varying(8),
    user_comments text DEFAULT ''::text NOT NULL,
    created_userid character varying(20) NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_userid character varying(20) NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    current_size character varying(4) DEFAULT ''::character varying NOT NULL,
    checked_size character varying(4),
    current_shading character varying(4) DEFAULT ''::character varying NOT NULL,
    checked_shading character varying(4)
);


ALTER TABLE public.inventory_checking_details OWNER TO armasi;

--
-- Name: item_gbj_stockblock; Type: TABLE; Schema: public; Owner: armasi_qc
--

CREATE TABLE public.item_gbj_stockblock (
    order_id character varying(18) NOT NULL,
    pallet_no character varying(50) NOT NULL,
    quantity numeric DEFAULT 0 NOT NULL,
    subplant character varying(2) NOT NULL,
    order_status character varying(1) DEFAULT 'O'::character varying NOT NULL,
    qty_old numeric
);


ALTER TABLE public.item_gbj_stockblock OWNER TO armasi_qc;

--
-- Name: COLUMN item_gbj_stockblock.order_status; Type: COMMENT; Schema: public; Owner: armasi_qc
--

COMMENT ON COLUMN public.item_gbj_stockblock.order_status IS 'O - in process
C - cancelled
S - Complete';


--
-- Name: item_locker; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.item_locker (
    warehouse_kode character varying(50) NOT NULL,
    item_kode character varying(50) NOT NULL,
    locker_nama character varying(50),
    qty_min numeric,
    qty_max numeric,
    qty_buffer numeric,
    qty_reorder numeric,
    vol numeric,
    avarage_reorder numeric,
    avarage_month numeric,
    description text,
    size character varying(20),
    quality character varying(40),
    shading character varying(20),
    modiby character varying(10),
    modidate date,
    inactive boolean,
    prepare_by character varying(30),
    approve_by character varying(30),
    begining_stock numeric,
    check_by character varying(30),
    harga_satuan numeric,
    kode_lama text,
    status_tran character varying(1)
);


ALTER TABLE public.item_locker OWNER TO armasi;

--
-- Name: item_opname; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.item_opname (
    kode_opname character varying(50) NOT NULL,
    item_kode character varying(50) NOT NULL,
    tanggal date NOT NULL,
    qty numeric NOT NULL,
    harga numeric,
    amount numeric,
    keterangan character varying(100),
    jenis character varying,
    status_tran character varying(1)
);


ALTER TABLE public.item_opname OWNER TO armasi;

--
-- Name: item_retur_produksi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.item_retur_produksi (
    retur_kode text NOT NULL,
    item_kode text NOT NULL,
    export numeric,
    keterangan text NOT NULL,
    ekonomi numeric,
    paletan numeric,
    kwiv numeric,
    sampah numeric,
    pallet_no character varying(18) NOT NULL,
    status_tran character varying(1)
);


ALTER TABLE public.item_retur_produksi OWNER TO armasi;

--
-- Name: ivc_number; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.ivc_number
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ivc_number OWNER TO armasi;

--
-- Name: pallet_event_types; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.pallet_event_types (
    id integer NOT NULL,
    event_name character varying(20) NOT NULL,
    event_description character varying(100) NOT NULL,
    default_message_template_en character varying(200) NOT NULL,
    default_message_template_id character varying(200) NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.pallet_event_types OWNER TO armasi;

--
-- Name: pallet_event_types_id_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.pallet_event_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pallet_event_types_id_seq OWNER TO armasi;

--
-- Name: pallet_event_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.pallet_event_types_id_seq OWNED BY public.pallet_event_types.id;


--
-- Name: pallet_events; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.pallet_events (
    event_id integer NOT NULL,
    pallet_no character varying(18) NOT NULL,
    userid character varying(20) NOT NULL,
    plant_id character varying(2) NOT NULL,
    event_time timestamp without time zone DEFAULT now() NOT NULL,
    old_values text,
    new_values text
);


ALTER TABLE public.pallet_events OWNER TO armasi;

--
-- Name: pallets_with_location; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.pallets_with_location AS
 SELECT tbl_sp_hasilbj.plan_kode,
    tbl_sp_hasilbj.seq_no,
    tbl_sp_hasilbj.pallet_no,
    tbl_sp_hasilbj.tanggal,
    tbl_sp_hasilbj.item_kode,
    tbl_sp_hasilbj.quality,
    tbl_sp_hasilbj.subplant,
    tbl_sp_hasilbj.shade,
    tbl_sp_hasilbj.size,
    tbl_sp_hasilbj.qty,
    tbl_sp_hasilbj.create_date,
    tbl_sp_hasilbj.create_user,
    tbl_sp_hasilbj.status_plt,
    tbl_sp_hasilbj.rkpterima_no,
    tbl_sp_hasilbj.rkpterima_tanggal,
    tbl_sp_hasilbj.rkpterima_user,
    tbl_sp_hasilbj.terima_no,
    tbl_sp_hasilbj.tanggal_terima,
    tbl_sp_hasilbj.terima_user,
    tbl_sp_hasilbj.status_item,
    tbl_sp_hasilbj.txn_no,
    tbl_sp_hasilbj.shift,
    tbl_sp_hasilbj.last_qty,
    tbl_sp_hasilbj.line,
    tbl_sp_hasilbj.regu,
    tbl_sp_hasilbj.plt_status,
    tbl_sp_hasilbj.keterangan,
    tbl_sp_hasilbj.kd_customer,
    tbl_sp_hasilbj.tanggal_pending,
    tbl_sp_hasilbj.last_update,
    tbl_sp_hasilbj.update_tran,
    tbl_sp_hasilbj.update_tran_user,
    tbl_sp_hasilbj.upload_date,
    tbl_sp_hasilbj.upload_user,
    tbl_sp_hasilbj.status_transfer,
    tbl_sp_hasilbj.status_tran,
    tbl_sp_hasilbj.area,
    tbl_sp_hasilbj.lokasi,
    tbl_sp_hasilbj.qa_approved,
    inv_master_area.plan_kode AS location_subplant,
    inv_opname.io_kd_lok AS location_no,
    inv_opname.io_tgl AS location_since,
    inv_master_lok_pallet.iml_kd_area AS location_area_no,
    inv_master_area.ket_area AS location_area_name,
    inv_master_lok_pallet.iml_no_baris AS location_row_no,
    inv_master_lok_pallet.iml_no_lok AS location_cell_no,
    inv_master_lok_pallet.iml_kd_lok AS location_id
   FROM (((public.inv_opname
     JOIN public.tbl_sp_hasilbj ON (((tbl_sp_hasilbj.pallet_no)::text = (inv_opname.io_no_pallet)::text)))
     JOIN public.inv_master_lok_pallet ON ((((inv_opname.io_kd_lok)::text = (inv_master_lok_pallet.iml_kd_lok)::text) AND ((inv_opname.io_plan_kode)::text = (inv_master_lok_pallet.iml_plan_kode)::text))))
     JOIN public.inv_master_area ON ((((inv_master_lok_pallet.iml_kd_area)::text = (inv_master_area.kd_area)::text) AND ((inv_master_lok_pallet.iml_plan_kode)::text = (inv_master_area.plan_kode)::text))));


ALTER TABLE public.pallets_with_location OWNER TO armasi;

--
-- Name: pallets_with_location_rimpil; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.pallets_with_location_rimpil AS
SELECT
    NULL::text AS location_subplant,
    NULL::text AS location_area_name,
    NULL::character varying(10) AS location_area_no,
    NULL::integer AS location_line_no,
    NULL::integer AS location_cell_no,
    NULL::character varying(8) AS location_id,
    NULL::character varying(18) AS pallet_no,
    NULL::character varying(2) AS production_subplant,
    NULL::character varying(20) AS motif_id,
    NULL::character varying(200) AS motif_dimension,
    NULL::character varying(200) AS motif_name,
    NULL::character varying(15) AS quality,
    NULL::character varying(4) AS size,
    NULL::character varying(4) AS shading,
    NULL::date AS creation_date,
    NULL::character varying(1) AS creator_group,
    NULL::smallint AS creator_shift,
    NULL::character varying(1) AS line,
    NULL::smallint AS current_quantity,
    NULL::boolean AS is_rimpil;


ALTER TABLE public.pallets_with_location_rimpil OWNER TO armasi;

--
-- Name: pcs_jam; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.pcs_jam (
    pj_jam character varying(5) NOT NULL,
    pj_no integer
);


ALTER TABLE public.pcs_jam OWNER TO armasi;

--
-- Name: plan; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.plan (
    plan_kode character varying(50) NOT NULL,
    plan_nama text,
    inactive boolean,
    modiby text,
    modidate date,
    plan_address text,
    plan_phone character varying(24),
    plan_fax character varying(24),
    plan_email text,
    plan_website text,
    plan_npwp text,
    status_tran character varying(1)
);


ALTER TABLE public.plan OWNER TO armasi;

--
-- Name: qry_cat_item; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.qry_cat_item AS
 SELECT category.jenis_kode,
    category.category_nama,
    item.item_kode,
    item.category_kode,
    item.item_nama,
    item.spesification,
    item.gl_account,
    item.satuan,
    item.color,
    item.quality,
    item.inactive,
    item.modiby,
    item.modidate,
    item.ipc
   FROM public.category,
    public.item
  WHERE ((category.category_kode)::text = (item.category_kode)::text);


ALTER TABLE public.qry_cat_item OWNER TO armasi;

--
-- Name: tbl_detail_surat_jalan; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_detail_surat_jalan (
    detail_surat_jalan_id integer NOT NULL,
    no_surat_jalan text NOT NULL,
    item_kode character varying(20) NOT NULL,
    volume numeric,
    harga numeric,
    keterangan text,
    do_kode text,
    kode_lama text,
    itsize character varying(4),
    itshade character varying(4),
    sub_plant character varying(2),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean DEFAULT false,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_detail_surat_jalan OWNER TO armasi;

--
-- Name: tbl_surat_jalan; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_surat_jalan (
    no_surat_jalan text NOT NULL,
    tanggal date,
    customer_kode text,
    create_by character varying(20),
    check_by character varying(40),
    approve_by character varying(40),
    modiby character varying(40),
    modidate date,
    no_inv text,
    no_surat_jalan_rekap text,
    tujuan_surat_jalan_rekap text,
    no_bukti_tagihan text,
    tipe text,
    status text,
    waktu text,
    plan_kode text,
    keterangan text,
    alamat_surat_jalan_rekap text,
    kode_lama text,
    sub_plan text,
    tokogudang character(1),
    flag character(1) DEFAULT 0,
    no_surat_jalan_induk text,
    berat_masuk numeric,
    berat_keluar numeric,
    update_tran timestamp without time zone DEFAULT now(),
    update_tran_user character varying(20),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean DEFAULT false,
    status_tran character varying(1),
    created_at timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.tbl_surat_jalan OWNER TO armasi;

--
-- Name: qry_detail_surat_jalan; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.qry_detail_surat_jalan AS
 SELECT tbl_detail_surat_jalan.detail_surat_jalan_id,
    tbl_detail_surat_jalan.no_surat_jalan,
    tbl_detail_surat_jalan.item_kode,
    tbl_detail_surat_jalan.volume,
    tbl_detail_surat_jalan.harga,
    tbl_detail_surat_jalan.keterangan,
    tbl_detail_surat_jalan.do_kode,
    tbl_detail_surat_jalan.kode_lama,
    tbl_surat_jalan.tanggal,
    tbl_detail_surat_jalan.itsize,
    tbl_detail_surat_jalan.itshade
   FROM public.tbl_detail_surat_jalan,
    public.tbl_surat_jalan
  WHERE (tbl_detail_surat_jalan.no_surat_jalan = tbl_surat_jalan.no_surat_jalan);


ALTER TABLE public.qry_detail_surat_jalan OWNER TO armasi;

--
-- Name: qry_plan_dep; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.qry_plan_dep AS
 SELECT departemen.departemen_kode,
    departemen.departemen_nama,
    plan.plan_kode,
    plan.plan_nama,
    plan.inactive,
    plan.modiby,
    plan.modidate,
    plan.plan_address,
    plan.plan_phone,
    plan.plan_fax,
    plan.plan_email,
    plan.plan_website,
    plan.plan_npwp
   FROM public.departemen,
    public.plan
  WHERE ((departemen.plan_kode)::text = (plan.plan_kode)::text);


ALTER TABLE public.qry_plan_dep OWNER TO armasi;

--
-- Name: tbl_customer; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_customer (
    customer_kode text NOT NULL,
    customer_nama text,
    customer_cp text,
    customer_alamat text,
    kode_pos text,
    telp character varying(40),
    fax character varying(40),
    email character varying(40),
    website text,
    customer_country character varying(40),
    customer_state character varying(40),
    npwp text,
    no_account text,
    plan_kode text,
    wilayah_id integer,
    toko_gudang integer,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_customer OWNER TO armasi;

--
-- Name: qry_surat_jalan_pgk; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.qry_surat_jalan_pgk AS
 SELECT ts.tanggal,
    ts.no_surat_jalan,
    ts.no_surat_jalan_rekap,
    td.item_kode,
    item.item_nama,
    item.spesification,
    item.quality,
    tc.customer_kode,
    tc.customer_nama,
    td.volume,
    td.harga,
    replace((td.itsize)::text, ','::text, ''::text) AS itsize,
    td.itshade
   FROM (((public.tbl_surat_jalan ts
     LEFT JOIN public.tbl_detail_surat_jalan td ON ((td.no_surat_jalan = ts.no_surat_jalan)))
     LEFT JOIN ( SELECT DISTINCT item_1.item_nama,
            item_1.item_kode,
            item_1.spesification,
            item_1.quality
           FROM public.item item_1
          ORDER BY item_1.item_nama, item_1.item_kode, item_1.spesification, item_1.quality) item ON (((item.item_kode)::text = (td.item_kode)::text)))
     LEFT JOIN public.tbl_customer tc ON ((tc.customer_kode = ts.tujuan_surat_jalan_rekap)));


ALTER TABLE public.qry_surat_jalan_pgk OWNER TO armasi;

--
-- Name: supplier; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.supplier (
    supplier_kode character varying(50) NOT NULL,
    region_kode character varying(50),
    gl_account text NOT NULL,
    company text,
    date_entry date,
    contact text,
    address text,
    email text,
    website text,
    tax_no text,
    city text,
    province text,
    postalcode text,
    phone text,
    fax text,
    notes_product text,
    time_delivery text,
    delivery_by text,
    notes_delivery text,
    term_payment text,
    late_fee text,
    notes_payment text,
    create_by text,
    check_by text,
    approve_by text,
    modiby text,
    modidate date,
    inactive boolean,
    remarks text,
    nama_bank text,
    no_rekening text,
    tipe text,
    kode_lama text,
    plan text,
    country text,
    an text,
    spaddrnpwp character varying(100) DEFAULT '-'::character varying,
    sppostnpwp character varying(10) DEFAULT '-'::character varying,
    sppkp boolean DEFAULT false,
    spppn numeric(3,0) DEFAULT 0,
    badan_usaha character varying,
    status_tran character varying(1),
    status character varying(1)
);


ALTER TABLE public.supplier OWNER TO armasi;

--
-- Name: tbl_tarif_angkutan; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_tarif_angkutan (
    tarif_id integer NOT NULL,
    supplier_kode character varying(20),
    awal text,
    tujuan text,
    satuan text,
    tarif numeric,
    tgl_berlaku date,
    no_pol text,
    jenis text,
    tanggal_terima_surat date
);


ALTER TABLE public.tbl_tarif_angkutan OWNER TO armasi;

--
-- Name: qry_tarif_supplier; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.qry_tarif_supplier AS
 SELECT supplier.region_kode,
    supplier.gl_account,
    supplier.company,
    supplier.date_entry,
    supplier.contact,
    supplier.address,
    supplier.email,
    supplier.website,
    supplier.tax_no,
    supplier.city,
    supplier.province,
    supplier.postalcode,
    supplier.phone,
    supplier.fax,
    supplier.notes_product,
    supplier.time_delivery,
    supplier.delivery_by,
    supplier.notes_delivery,
    supplier.term_payment,
    supplier.late_fee,
    supplier.notes_payment,
    supplier.create_by,
    supplier.check_by,
    supplier.approve_by,
    supplier.modiby,
    supplier.modidate,
    supplier.inactive,
    supplier.remarks,
    supplier.nama_bank,
    supplier.no_rekening,
    supplier.tipe,
    supplier.kode_lama,
    supplier.plan,
    supplier.country,
    tbl_tarif_angkutan.tarif_id,
    tbl_tarif_angkutan.supplier_kode,
    tbl_tarif_angkutan.awal,
    tbl_tarif_angkutan.tujuan,
    tbl_tarif_angkutan.satuan,
    tbl_tarif_angkutan.tarif,
    tbl_tarif_angkutan.tgl_berlaku,
    tbl_tarif_angkutan.no_pol,
    tbl_tarif_angkutan.jenis,
    tbl_tarif_angkutan.tanggal_terima_surat
   FROM public.supplier,
    public.tbl_tarif_angkutan
  WHERE ((supplier.supplier_kode)::text = (tbl_tarif_angkutan.supplier_kode)::text);


ALTER TABLE public.qry_tarif_supplier OWNER TO armasi;

--
-- Name: tbl_toleransi_barang_pecah; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_toleransi_barang_pecah (
    plan_kode character varying(10) NOT NULL,
    truck numeric NOT NULL,
    kontainer numeric NOT NULL,
    pembulatan character varying(30) NOT NULL,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_toleransi_barang_pecah OWNER TO armasi;

--
-- Name: qry_toleransi_barang_pecah_plan; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.qry_toleransi_barang_pecah_plan AS
 SELECT plan.plan_nama,
    plan.inactive,
    plan.modiby,
    plan.modidate,
    plan.plan_address,
    plan.plan_phone,
    plan.plan_fax,
    plan.plan_email,
    plan.plan_website,
    plan.plan_npwp,
    tbl_toleransi_barang_pecah.plan_kode,
    tbl_toleransi_barang_pecah.truck,
    tbl_toleransi_barang_pecah.kontainer,
    tbl_toleransi_barang_pecah.pembulatan
   FROM public.plan,
    public.tbl_toleransi_barang_pecah
  WHERE ((plan.plan_kode)::text = (tbl_toleransi_barang_pecah.plan_kode)::text);


ALTER TABLE public.qry_toleransi_barang_pecah_plan OWNER TO armasi;

--
-- Name: tbl_level; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_level (
    level_kode character varying(10) NOT NULL,
    level_nama character varying(20),
    inactive boolean,
    modiby character varying(20),
    modidate date,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_level OWNER TO armasi;

--
-- Name: tbl_user; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_user (
    user_id integer NOT NULL,
    user_name character varying(20) NOT NULL,
    first_name character varying(20),
    last_name character varying(20),
    jabatan_kode character varying(20),
    level_akses character varying(10),
    password character(60) NOT NULL,
    alamat character varying(75),
    jenis_kelamin character varying(20),
    tanggal_lahir date,
    nip character varying(20),
    foto character varying(20),
    departemen_kode character varying(20),
    agama character varying(20),
    tanggal_masuk date,
    tanda_tangan character varying(40),
    plan_kode text,
    expired_date date DEFAULT (CURRENT_DATE + '30 days'::interval) NOT NULL,
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    last_login_status boolean DEFAULT false NOT NULL,
    monthly_login_count smallint DEFAULT 0 NOT NULL,
    monthly_logout_count smallint DEFAULT 0 NOT NULL,
    last_activity timestamp without time zone DEFAULT to_timestamp((0)::double precision) NOT NULL,
    is_active boolean DEFAULT true NOT NULL
);


ALTER TABLE public.tbl_user OWNER TO armasi;

--
-- Name: qry_user; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.qry_user AS
 SELECT departemen.departemen_kode,
    departemen.plan_kode,
    departemen.departemen_nama,
    departemen.modiby,
    departemen.modidate,
    tbl_level.level_kode,
    tbl_level.level_nama,
    tbl_user.user_id,
    tbl_user.user_name,
    tbl_user.first_name,
    tbl_user.last_name,
    tbl_user.jabatan_kode,
    tbl_user.password,
    tbl_user.alamat,
    tbl_user.jenis_kelamin,
    tbl_user.tanggal_lahir,
    tbl_user.nip,
    tbl_user.foto,
    tbl_user.agama,
    tbl_user.tanggal_masuk,
    tbl_user.monthly_login_count AS jumlah,
    tbl_user.expired_date,
    (tbl_user.last_login_status)::integer AS status,
    tbl_user.is_active
   FROM ((public.tbl_user
     JOIN public.tbl_level ON (((tbl_user.level_akses)::text = (tbl_level.level_kode)::text)))
     JOIN public.departemen ON (((tbl_user.departemen_kode)::text = (departemen.departemen_kode)::text)));


ALTER TABLE public.qry_user OWNER TO armasi;

--
-- Name: qry_user_plan; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.qry_user_plan AS
 SELECT qry_plan_dep.departemen_nama,
    qry_plan_dep.plan_kode,
    qry_plan_dep.plan_nama,
    qry_plan_dep.inactive,
    qry_plan_dep.modiby,
    qry_plan_dep.modidate,
    qry_plan_dep.plan_address,
    qry_plan_dep.plan_phone,
    qry_plan_dep.plan_fax,
    qry_plan_dep.plan_email,
    qry_plan_dep.plan_website,
    qry_plan_dep.plan_npwp,
    usr.user_id,
    usr.user_name,
    usr.first_name,
    usr.last_name,
    usr.jabatan_kode,
    usr.level_akses,
    lvl.level_nama,
    usr.password,
    usr.alamat,
    usr.jenis_kelamin,
    usr.tanggal_lahir,
    usr.nip,
    usr.foto,
    usr.departemen_kode,
    usr.agama,
    usr.tanggal_masuk,
    usr.tanda_tangan,
    usr.expired_date,
    usr.is_active
   FROM ((public.qry_plan_dep
     JOIN public.tbl_user usr ON (((qry_plan_dep.departemen_kode)::text = (usr.departemen_kode)::text)))
     JOIN public.tbl_level lvl ON (((usr.level_akses)::text = (lvl.level_kode)::text)));


ALTER TABLE public.qry_user_plan OWNER TO armasi;

--
-- Name: region; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.region (
    region_kode character varying(50) NOT NULL,
    country_kode character varying(50) NOT NULL,
    region_nama character varying(40),
    inactive boolean,
    modiby character varying(10),
    modidate date,
    status_tran character varying(1)
);


ALTER TABLE public.region OWNER TO armasi;

--
-- Name: subplan; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.subplan (
    plan_kode character varying(50) NOT NULL,
    sub_plan character varying(2) NOT NULL,
    keterangan text
);


ALTER TABLE public.subplan OWNER TO armasi;

--
-- Name: summary_pallet_handover_by_production_line; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.summary_pallet_handover_by_production_line AS
 SELECT tbl_sp_hasilbj.tanggal_terima AS stwh_date,
    item.item_kode AS motif_id,
    item.item_nama AS motif_name,
    ( SELECT category.category_nama
           FROM public.category
          WHERE ((category.category_kode)::text = substr((tbl_sp_hasilbj.item_kode)::text, 1, 2))) AS motif_dimension,
    tbl_sp_hasilbj.subplant,
    tbl_sp_hasilbj.line AS production_line,
    tbl_sp_hasilbj.quality AS pallet_quality,
    sum(tbl_sp_hasilbj.qty) AS total_quantity
   FROM (public.tbl_sp_hasilbj
     LEFT JOIN public.item ON (((tbl_sp_hasilbj.item_kode)::text = (item.item_kode)::text)))
  WHERE (((COALESCE(tbl_sp_hasilbj.terima_no, ''::character varying))::text <> ''::text) AND (substr((tbl_sp_hasilbj.pallet_no)::text, 1, 3) = 'PLT'::text))
  GROUP BY item.item_kode, item.item_nama, ( SELECT category.category_nama
           FROM public.category
          WHERE ((category.category_kode)::text = substr((tbl_sp_hasilbj.item_kode)::text, 1, 2))), tbl_sp_hasilbj.subplant, tbl_sp_hasilbj.quality, tbl_sp_hasilbj.line, tbl_sp_hasilbj.tanggal_terima
  ORDER BY tbl_sp_hasilbj.tanggal_terima, item.item_kode, tbl_sp_hasilbj.line;


ALTER TABLE public.summary_pallet_handover_by_production_line OWNER TO armasi;

--
-- Name: summary_pallet_handover_by_series; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.summary_pallet_handover_by_series AS
 SELECT tbl_sp_hasilbj.tanggal_terima AS stwh_date,
    item.item_kode AS motif_id,
    item.item_nama AS motif_name,
    ( SELECT category.category_nama
           FROM public.category
          WHERE ((category.category_kode)::text = substr((tbl_sp_hasilbj.item_kode)::text, 1, 2))) AS motif_dimension,
    tbl_sp_hasilbj.subplant,
    tbl_sp_hasilbj.quality AS pallet_quality,
    tbl_sp_hasilbj.size AS pallet_size,
    tbl_sp_hasilbj.shade AS pallet_shading,
    sum(tbl_sp_hasilbj.qty) AS total_quantity
   FROM (public.tbl_sp_hasilbj
     LEFT JOIN public.item ON (((tbl_sp_hasilbj.item_kode)::text = (item.item_kode)::text)))
  WHERE (((COALESCE(tbl_sp_hasilbj.terima_no, ''::character varying))::text <> ''::text) AND (substr((tbl_sp_hasilbj.pallet_no)::text, 1, 3) = 'PLT'::text))
  GROUP BY item.item_kode, item.item_nama, ( SELECT category.category_nama
           FROM public.category
          WHERE ((category.category_kode)::text = substr((tbl_sp_hasilbj.item_kode)::text, 1, 2))), tbl_sp_hasilbj.subplant, tbl_sp_hasilbj.quality, tbl_sp_hasilbj.size, tbl_sp_hasilbj.shade, tbl_sp_hasilbj.tanggal_terima
  ORDER BY tbl_sp_hasilbj.tanggal_terima, item.item_kode;


ALTER TABLE public.summary_pallet_handover_by_series OWNER TO armasi;

--
-- Name: summary_pallets_with_location_by_line; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.summary_pallets_with_location_by_line AS
 SELECT pallets_with_location.location_subplant AS location_warehouse_id,
    pallets_with_location.location_area_name,
    pallets_with_location.location_area_no AS location_area_id,
    pallets_with_location.location_row_no AS location_line_no,
    pallets_with_location.subplant AS production_subplant,
    item.item_kode AS motif_id,
    item.item_nama AS motif_name,
    category.category_nama AS motif_dimension,
        CASE
            WHEN ((item.quality)::text = 'EXPORT'::text) THEN 'EXP'::character varying
            WHEN (((item.quality)::text = 'ECONOMY'::text) OR ((item.quality)::text = 'EKONOMI'::text)) THEN 'ECO'::character varying
            ELSE item.quality
        END AS quality,
    pallets_with_location.status_plt AS pallet_status,
    pallets_with_location.size,
    pallets_with_location.shade AS shading,
    count(pallets_with_location.pallet_no) AS pallet_count,
    sum(pallets_with_location.last_qty) AS total_quantity
   FROM ((public.pallets_with_location
     JOIN public.item ON (((pallets_with_location.item_kode)::text = (item.item_kode)::text)))
     JOIN public.category ON (("left"((pallets_with_location.item_kode)::text, 2) = (category.category_kode)::text)))
  WHERE (pallets_with_location.last_qty > 0)
  GROUP BY pallets_with_location.location_subplant, pallets_with_location.location_area_name, pallets_with_location.location_area_no, pallets_with_location.location_row_no, pallets_with_location.subplant, item.item_kode, item.item_nama, category.category_nama, item.quality, pallets_with_location.status_plt, pallets_with_location.size, pallets_with_location.shade
  ORDER BY pallets_with_location.location_subplant, pallets_with_location.location_area_no, pallets_with_location.location_area_name, pallets_with_location.location_row_no;


ALTER TABLE public.summary_pallets_with_location_by_line OWNER TO armasi;

--
-- Name: tbl_ba_muat_detail; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_ba_muat_detail (
    detail_ba_id integer NOT NULL,
    sub_plant character varying(2) NOT NULL,
    no_ba text NOT NULL,
    item_kode character varying(20) NOT NULL,
    volume numeric,
    harga numeric,
    keterangan text,
    do_kode text,
    kode_lama text,
    itsize character varying(3),
    itshade character varying(5),
    detail_cat character varying(10) DEFAULT 'SALES'::character varying,
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean DEFAULT false,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_ba_muat_detail OWNER TO armasi;

--
-- Name: summary_shipping_by_category_sku; Type: MATERIALIZED VIEW; Schema: public; Owner: armasi
--

CREATE MATERIALIZED VIEW public.summary_shipping_by_category_sku AS
 SELECT sj_detail.sub_plant AS subplant,
    sj.tanggal AS ship_date,
    t1.motif_id,
    t1.motif_dimension,
    t1.motif_name,
    t1.quality,
    t1.ship_type,
    t1.ship_category,
        CASE
            WHEN ((COALESCE(sj_detail.itsize, ''::character varying))::text = ''::text) THEN '-'::character varying
            ELSE sj_detail.itsize
        END AS size,
        CASE
            WHEN ((COALESCE(sj_detail.itshade, ''::character varying))::text = ''::text) THEN '-'::character varying
            ELSE sj_detail.itshade
        END AS shading,
    sum(sj_detail.volume) AS total_quantity
   FROM ((public.tbl_surat_jalan sj
     JOIN public.tbl_detail_surat_jalan sj_detail ON ((sj.no_surat_jalan = sj_detail.no_surat_jalan)))
     JOIN ( SELECT ship.no_surat_jalan_rekap AS sj_no,
            ship.no_ba AS ship_no,
                CASE
                    WHEN ("left"(ship.no_ba, 3) = 'BAL'::text) THEN 'Lokal'::text
                    WHEN ("left"(ship.no_ba, 3) = 'BAM'::text) THEN 'Regular'::text
                    ELSE 'UNKNOWN'::text
                END AS ship_type,
            ship_detail.detail_cat AS ship_category,
            item.item_kode AS motif_id,
            item.item_nama AS motif_name,
            cat.category_nama AS motif_dimension,
            item.quality
           FROM (((public.tbl_ba_muat ship
             JOIN public.tbl_ba_muat_detail ship_detail ON ((ship.no_ba = ship_detail.no_ba)))
             JOIN public.item ON (((ship_detail.item_kode)::text = (item.item_kode)::text)))
             JOIN public.category cat ON ((substr((item.item_kode)::text, 1, 2) = (cat.category_kode)::text)))
          WHERE ((ship.no_surat_jalan_rekap IS NOT NULL) AND ((ship_detail.detail_cat)::text = ANY (ARRAY[('RIMPIL'::character varying)::text, ('SALES'::character varying)::text])))) t1 ON ((sj.no_surat_jalan = t1.sj_no)))
  GROUP BY sj_detail.sub_plant, sj.tanggal, t1.motif_id, t1.motif_dimension, t1.motif_name, t1.quality, t1.ship_type, t1.ship_category,
        CASE
            WHEN ((COALESCE(sj_detail.itsize, ''::character varying))::text = ''::text) THEN '-'::character varying
            ELSE sj_detail.itsize
        END,
        CASE
            WHEN ((COALESCE(sj_detail.itshade, ''::character varying))::text = ''::text) THEN '-'::character varying
            ELSE sj_detail.itshade
        END
UNION ALL
 SELECT ship.sub_plan AS subplant,
    sj.tanggal AS ship_date,
    t4.motif_id,
    t4.motif_dimension,
    t4.motif_name,
    t4.quality,
    t4.ship_type,
    t4.ship_category,
    t4.size,
    t4.shading,
    sum(abs(mutation.qty)) AS total_quantity
   FROM (((public.tbl_ba_muat ship
     JOIN public.tbl_surat_jalan sj ON ((ship.no_surat_jalan_rekap = sj.no_surat_jalan)))
     JOIN ( SELECT ship_1.no_surat_jalan_rekap AS sj_no,
            ship_1.no_ba AS ship_no,
                CASE
                    WHEN ("left"(ship_1.no_ba, 3) = 'BAL'::text) THEN 'Lokal'::text
                    WHEN ("left"(ship_1.no_ba, 3) = 'BAM'::text) THEN 'Regular'::text
                    ELSE 'UNKNOWN'::text
                END AS ship_type,
            ship_detail.detail_cat AS ship_category,
            ship_detail.item_kode AS motif_id,
            item.item_nama AS motif_name,
            cat.category_nama AS motif_dimension,
            item.quality,
                CASE
                    WHEN ((COALESCE(ship_detail.itsize, ''::character varying))::text = ''::text) THEN '-'::character varying
                    ELSE ship_detail.itsize
                END AS size,
                CASE
                    WHEN ((COALESCE(ship_detail.itshade, ''::character varying))::text = ''::text) THEN '-'::character varying
                    ELSE ship_detail.itshade
                END AS shading
           FROM (((public.tbl_ba_muat ship_1
             JOIN public.tbl_ba_muat_detail ship_detail ON ((ship_1.no_ba = ship_detail.no_ba)))
             JOIN public.item ON (((ship_detail.item_kode)::text = (item.item_kode)::text)))
             JOIN public.category cat ON ((substr((item.item_kode)::text, 1, 2) = (cat.category_kode)::text)))
          WHERE ((ship_1.no_surat_jalan_rekap IS NOT NULL) AND ((ship_detail.detail_cat)::text <> ALL (ARRAY[('RIMPIL'::character varying)::text, ('SALES'::character varying)::text])))) t4 ON ((ship.no_ba = t4.ship_no)))
     JOIN public.tbl_sp_mutasi_pallet mutation ON ((ship.no_ba = (mutation.no_mutasi)::text)))
  GROUP BY ship.sub_plan, sj.tanggal, t4.motif_id, t4.motif_dimension, t4.motif_name, t4.quality, t4.ship_type, t4.ship_category, t4.size, t4.shading
  ORDER BY 1, 2 DESC, 8, 7, 3
  WITH NO DATA;


ALTER TABLE public.summary_shipping_by_category_sku OWNER TO armasi;

--
-- Name: summary_sku_available_for_sales; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.summary_sku_available_for_sales AS
SELECT
    NULL::character varying(2) AS production_subplant,
    NULL::character varying(1) AS location_subplant,
    NULL::character varying(8) AS location_id,
    NULL::character varying(50) AS motif_group_id,
    NULL::text AS motif_group_name,
    NULL::character varying(50) AS motif_id,
    NULL::character varying(200) AS motif_name,
    NULL::character varying(200) AS motif_dimension,
    NULL::character varying(15) AS color,
    NULL::character varying AS quality,
    NULL::character varying(4) AS size,
    NULL::character varying(4) AS shading,
    NULL::boolean AS is_rimpil,
    NULL::bigint AS pallet_count,
    NULL::bigint AS current_quantity;


ALTER TABLE public.summary_sku_available_for_sales OWNER TO armasi;

--
-- Name: summary_stock_by_motif_group_with_rimpil; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.summary_stock_by_motif_group_with_rimpil AS
 SELECT summary_sku_available_for_sales.production_subplant,
    summary_sku_available_for_sales.location_subplant,
    summary_sku_available_for_sales.motif_dimension,
    summary_sku_available_for_sales.motif_group_id,
    summary_sku_available_for_sales.motif_group_name,
    summary_sku_available_for_sales.quality,
    summary_sku_available_for_sales.is_rimpil,
    count(*) AS pallet_count,
    sum(summary_sku_available_for_sales.current_quantity) AS quantity
   FROM public.summary_sku_available_for_sales
  GROUP BY summary_sku_available_for_sales.production_subplant, summary_sku_available_for_sales.location_subplant, summary_sku_available_for_sales.motif_dimension, summary_sku_available_for_sales.motif_group_id, summary_sku_available_for_sales.motif_group_name, summary_sku_available_for_sales.quality, summary_sku_available_for_sales.is_rimpil
  ORDER BY summary_sku_available_for_sales.production_subplant, summary_sku_available_for_sales.location_subplant, summary_sku_available_for_sales.motif_dimension, summary_sku_available_for_sales.motif_group_name, summary_sku_available_for_sales.quality;


ALTER TABLE public.summary_stock_by_motif_group_with_rimpil OWNER TO armasi;

--
-- Name: summary_stock_by_motif_group_with_rimpil_for_hp; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.summary_stock_by_motif_group_with_rimpil_for_hp AS
 SELECT summary_sku_available_for_sales.motif_id,
    summary_sku_available_for_sales.production_subplant,
    summary_sku_available_for_sales.location_subplant,
    summary_sku_available_for_sales.motif_dimension,
    summary_sku_available_for_sales.motif_group_id,
    summary_sku_available_for_sales.motif_group_name,
    summary_sku_available_for_sales.quality,
    summary_sku_available_for_sales.color,
    summary_sku_available_for_sales.is_rimpil,
    count(*) AS pallet_count,
    sum(summary_sku_available_for_sales.current_quantity) AS quantity
   FROM public.summary_sku_available_for_sales
  GROUP BY summary_sku_available_for_sales.motif_id, summary_sku_available_for_sales.production_subplant, summary_sku_available_for_sales.location_subplant, summary_sku_available_for_sales.motif_dimension, summary_sku_available_for_sales.motif_group_id, summary_sku_available_for_sales.motif_group_name, summary_sku_available_for_sales.quality, summary_sku_available_for_sales.color, summary_sku_available_for_sales.is_rimpil
  ORDER BY summary_sku_available_for_sales.motif_id, summary_sku_available_for_sales.production_subplant, summary_sku_available_for_sales.location_subplant, summary_sku_available_for_sales.motif_dimension, summary_sku_available_for_sales.motif_group_name, summary_sku_available_for_sales.quality;


ALTER TABLE public.summary_stock_by_motif_group_with_rimpil_for_hp OWNER TO armasi;

--
-- Name: summary_stock_by_motif_location; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.summary_stock_by_motif_location AS
 SELECT t1.subplant AS production_subplant,
    t1.location_subplant AS location_warehouse_id,
    t1.location_area_no AS location_area_id,
    t1.location_area_name,
    t1.location_row_no AS location_line_no,
        CASE
            WHEN ((item.quality)::text = 'EXPORT'::text) THEN 'EXP'::character varying
            WHEN (((item.quality)::text = 'ECONOMY'::text) OR ((item.quality)::text = 'EKONOMI'::text)) THEN 'ECO'::character varying
            ELSE item.quality
        END AS quality,
    item.item_kode AS motif_id,
    item.item_nama AS motif_name,
    category.category_nama AS motif_dimension,
    t1.status_plt AS pallet_status,
    t1.size,
    t1.shade AS shading,
    count(t1.pallet_no) AS pallet_count,
    sum(t1.last_qty) AS total_quantity
   FROM ((( SELECT pallets_with_location.plan_kode,
            pallets_with_location.seq_no,
            pallets_with_location.pallet_no,
            pallets_with_location.tanggal,
            pallets_with_location.item_kode,
            pallets_with_location.quality,
            pallets_with_location.subplant,
            pallets_with_location.shade,
            pallets_with_location.size,
            pallets_with_location.qty,
            pallets_with_location.create_date,
            pallets_with_location.create_user,
            pallets_with_location.status_plt,
            pallets_with_location.rkpterima_no,
            pallets_with_location.rkpterima_tanggal,
            pallets_with_location.rkpterima_user,
            pallets_with_location.terima_no,
            pallets_with_location.tanggal_terima,
            pallets_with_location.terima_user,
            pallets_with_location.status_item,
            pallets_with_location.txn_no,
            pallets_with_location.shift,
            pallets_with_location.last_qty,
            pallets_with_location.line,
            pallets_with_location.regu,
            pallets_with_location.plt_status,
            pallets_with_location.keterangan,
            pallets_with_location.kd_customer,
            pallets_with_location.tanggal_pending,
            pallets_with_location.last_update,
            pallets_with_location.update_tran,
            pallets_with_location.update_tran_user,
            pallets_with_location.upload_date,
            pallets_with_location.upload_user,
            pallets_with_location.status_transfer,
            pallets_with_location.status_tran,
            pallets_with_location.area,
            pallets_with_location.lokasi,
            pallets_with_location.qa_approved,
            pallets_with_location.location_subplant,
            pallets_with_location.location_no,
            pallets_with_location.location_since,
            pallets_with_location.location_area_no,
            pallets_with_location.location_area_name,
            pallets_with_location.location_row_no,
            pallets_with_location.location_cell_no,
            pallets_with_location.location_id
           FROM public.pallets_with_location
          WHERE (((pallets_with_location.status_plt)::text = ANY (ARRAY[('R'::character varying)::text, ('B'::character varying)::text, ('K'::character varying)::text])) AND (pallets_with_location.last_qty > 0))) t1
     JOIN public.item ON (((t1.item_kode)::text = (item.item_kode)::text)))
     JOIN public.category ON (("left"((t1.item_kode)::text, 2) = (category.category_kode)::text)))
  GROUP BY t1.location_subplant, t1.location_area_no, t1.location_area_name, t1.location_row_no, t1.subplant, item.quality, item.item_kode, item.item_nama, category.category_nama, t1.status_plt, t1.size, t1.shade;


ALTER TABLE public.summary_stock_by_motif_location OWNER TO armasi;

--
-- Name: t_brg_type; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.t_brg_type (
    type_kode character(5) NOT NULL,
    type_nama character varying(30),
    status_tran character varying(1)
);


ALTER TABLE public.t_brg_type OWNER TO armasi;

--
-- Name: t_brg_warna; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.t_brg_warna (
    warna_kode character(5) NOT NULL,
    warna_nama character varying(30),
    status_tran character varying(1)
);


ALTER TABLE public.t_brg_warna OWNER TO armasi;

--
-- Name: tbl_autority; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_autority (
    user_id integer NOT NULL,
    sub_f_id integer NOT NULL,
    level_id integer,
    since timestamp without time zone DEFAULT now() NOT NULL,
    given_by character varying(20) DEFAULT 'admin'::character varying NOT NULL
);


ALTER TABLE public.tbl_autority OWNER TO armasi;

--
-- Name: tbl_ba_muat_detail_detail_ba_id_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.tbl_ba_muat_detail_detail_ba_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tbl_ba_muat_detail_detail_ba_id_seq OWNER TO armasi;

--
-- Name: tbl_ba_muat_detail_detail_ba_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.tbl_ba_muat_detail_detail_ba_id_seq OWNED BY public.tbl_ba_muat_detail.detail_ba_id;


--
-- Name: tbl_ba_muat_trans; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_ba_muat_trans (
    no_ba text NOT NULL,
    tanggal date,
    supplier_kode text NOT NULL,
    awal text NOT NULL,
    tujuan text NOT NULL,
    tarif numeric,
    nama_transportir text,
    jenis text,
    no_pol text,
    realisasi numeric,
    ongkos text,
    no_bukti_tagihan text,
    kode_lama text,
    tarif_ori numeric,
    no_surat_jalan_induk text,
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean DEFAULT false,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_ba_muat_trans OWNER TO armasi;

--
-- Name: tbl_booking; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_booking (
    kode_book character varying(10) NOT NULL,
    customer_kode character varying(55),
    tgl_booking date,
    create_by character varying(10),
    create_date timestamp without time zone
);


ALTER TABLE public.tbl_booking OWNER TO armasi;

--
-- Name: tbl_detail_invoice; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_detail_invoice (
    detail_surat_jalan_id numeric,
    no_inv text NOT NULL,
    no_surat_jalan text NOT NULL,
    item_kode character varying(20) NOT NULL,
    volume numeric,
    harga numeric NOT NULL,
    tanggal_surat_jalan date,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_detail_invoice OWNER TO armasi;

--
-- Name: tbl_detail_surat_jalan_detail_surat_jalan_id_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.tbl_detail_surat_jalan_detail_surat_jalan_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tbl_detail_surat_jalan_detail_surat_jalan_id_seq OWNER TO armasi;

--
-- Name: tbl_detail_surat_jalan_detail_surat_jalan_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.tbl_detail_surat_jalan_detail_surat_jalan_id_seq OWNED BY public.tbl_detail_surat_jalan.detail_surat_jalan_id;


--
-- Name: tbl_detail_tarif_surat_jalan; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_detail_tarif_surat_jalan (
    surat_jalan text NOT NULL,
    supplier_kode text NOT NULL,
    awal text NOT NULL,
    tujuan text NOT NULL,
    tarif numeric,
    nama_transportir text,
    jenis text,
    no_pol text,
    realisasi numeric,
    ongkos text,
    no_bukti_tagihan text,
    kode_lama text,
    tarif_ori numeric,
    no_surat_jalan_induk text,
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean DEFAULT false,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_detail_tarif_surat_jalan OWNER TO armasi;

--
-- Name: tbl_do; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_do (
    no_do text NOT NULL,
    tanggal date NOT NULL,
    plan_kode text NOT NULL,
    created_by text,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_do OWNER TO armasi;

--
-- Name: tbl_feature; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_feature (
    feature_id integer NOT NULL,
    feature_name text NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    created_by character varying(20) DEFAULT 'admin'::character varying NOT NULL,
    last_updated_at timestamp without time zone DEFAULT now() NOT NULL,
    last_updated_by character varying(20) DEFAULT 'admin'::character varying NOT NULL
);


ALTER TABLE public.tbl_feature OWNER TO armasi;

--
-- Name: tbl_feature_feature_id_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.tbl_feature_feature_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tbl_feature_feature_id_seq OWNER TO armasi;

--
-- Name: tbl_feature_feature_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.tbl_feature_feature_id_seq OWNED BY public.tbl_feature.feature_id;


--
-- Name: tbl_gbj_stockblock; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_gbj_stockblock (
    plan_kode character(1) NOT NULL,
    subplant character varying(2) NOT NULL,
    order_id character varying(18) NOT NULL,
    customer_id text NOT NULL,
    order_status character varying(1) DEFAULT 'O'::character varying NOT NULL,
    order_target_date date NOT NULL,
    keterangan character varying(300),
    create_date timestamp without time zone NOT NULL,
    create_user character varying(20) NOT NULL,
    last_updated_at timestamp without time zone DEFAULT now() NOT NULL,
    last_updated_by character varying(20) NOT NULL
);


ALTER TABLE public.tbl_gbj_stockblock OWNER TO armasi;

--
-- Name: COLUMN tbl_gbj_stockblock.order_status; Type: COMMENT; Schema: public; Owner: armasi
--

COMMENT ON COLUMN public.tbl_gbj_stockblock.order_status IS 'O - in process
C - cancelled
S - Complete';


--
-- Name: tbl_invoice; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_invoice (
    no_inv text NOT NULL,
    customer_kode text NOT NULL,
    jenis_inv text,
    tanggal date,
    no_surat_jalan text,
    pembayaran text,
    no_faktur_pajak text,
    faktur_npwp text,
    faktur_nama text,
    faktur_alamat text,
    faktur_tanggal date,
    faktur_dibuat text,
    jumlah_tagihan numeric,
    discount numeric,
    ppn numeric,
    ongkos_angkut numeric,
    uang_muka numeric,
    total_penagihan numeric,
    create_by character varying(40),
    check_by character varying(40),
    approve_by character varying(40),
    modiby character varying(40),
    modidate date,
    jatuh_tempo numeric,
    tanggal_jatuhtempo date,
    harus_dibayar numeric,
    plan_kode text,
    keterangan text,
    kode_lama text,
    faktur_type text,
    type_inv character varying(50),
    terms_inv text,
    payment_inv text,
    valuta_kode character varying(50),
    status_tran character varying(1)
);


ALTER TABLE public.tbl_invoice OWNER TO armasi;

--
-- Name: tbl_iso; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_iso (
    iso_id integer NOT NULL,
    jenis_dokumen text,
    plan_kode character varying(50),
    no_iso text,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_iso OWNER TO armasi;

--
-- Name: tbl_iso_iso_id_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.tbl_iso_iso_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tbl_iso_iso_id_seq OWNER TO armasi;

--
-- Name: tbl_iso_iso_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.tbl_iso_iso_id_seq OWNED BY public.tbl_iso.iso_id;


--
-- Name: tbl_item_booking; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_item_booking (
    id integer NOT NULL,
    item_kode character varying(50) NOT NULL,
    kode_book character varying(10),
    item_nama character varying(200),
    quality character varying(20),
    quantity numeric,
    plant character(1),
    tgl_booking character varying(200),
    status character(1),
    create_by character varying(10),
    create_date timestamp without time zone
);


ALTER TABLE public.tbl_item_booking OWNER TO armasi;

--
-- Name: tbl_item_booking_id_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.tbl_item_booking_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tbl_item_booking_id_seq OWNER TO armasi;

--
-- Name: tbl_item_booking_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.tbl_item_booking_id_seq OWNED BY public.tbl_item_booking.id;


--
-- Name: tbl_jenis; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_jenis (
    jenis_kode character varying(50) NOT NULL,
    jenis_nama character varying(40),
    inactive boolean,
    modiby character varying(10),
    modidate date,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_jenis OWNER TO armasi;

--
-- Name: tbl_kode; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_kode (
    kode_id integer NOT NULL,
    nama_transaksi character varying,
    nama_kode character varying,
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_kode OWNER TO armasi;

--
-- Name: tbl_kode_kode_id_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.tbl_kode_kode_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tbl_kode_kode_id_seq OWNER TO armasi;

--
-- Name: tbl_kode_kode_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.tbl_kode_kode_id_seq OWNED BY public.tbl_kode.kode_id;


--
-- Name: tbl_konfirmasi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_konfirmasi (
    no_pol character varying(12),
    berat_timbangan numeric,
    jam_timbang character varying(10),
    tanggal date,
    jam timestamp without time zone,
    create_by character varying(15)
);


ALTER TABLE public.tbl_konfirmasi OWNER TO armasi;

--
-- Name: tbl_kurs; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_kurs (
    valuta_kode character varying(50) NOT NULL,
    tanggal date NOT NULL,
    nilai_kurs numeric,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_kurs OWNER TO armasi;

--
-- Name: tbl_lgc_gbj_detail; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_lgc_gbj_detail (
    id_detail integer NOT NULL,
    id_header integer NOT NULL,
    sub_plant character varying(3),
    jam_muat timestamp without time zone,
    jam_selesai timestamp without time zone,
    created_by character varying(20),
    created_on timestamp without time zone,
    modified_by character varying(20),
    modified_on timestamp without time zone
);


ALTER TABLE public.tbl_lgc_gbj_detail OWNER TO armasi;

--
-- Name: tbl_lgc_gbj_detail_id_detail_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.tbl_lgc_gbj_detail_id_detail_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tbl_lgc_gbj_detail_id_detail_seq OWNER TO armasi;

--
-- Name: tbl_lgc_gbj_detail_id_detail_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.tbl_lgc_gbj_detail_id_detail_seq OWNED BY public.tbl_lgc_gbj_detail.id_detail;


--
-- Name: tbl_lgc_gbj_header; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_lgc_gbj_header (
    id integer NOT NULL,
    nobarcode text,
    nopol character varying(20),
    supir character varying(30),
    nohp character varying(30),
    transporter text,
    customer text,
    orderby character varying(50),
    no_do text,
    no_bam text,
    no_sj text,
    jamdaftar timestamp without time zone,
    jam_masuk timestamp without time zone,
    jam_masuk_timbang timestamp without time zone,
    berat_masuk numeric,
    jam_keluar timestamp without time zone,
    berat_keluar numeric,
    jam_keluar_mobil timestamp without time zone,
    created_by character varying(20),
    created_on timestamp without time zone,
    modified_by character varying(20),
    modified_on timestamp without time zone,
    fstatus character varying(1),
    tgl_do timestamp without time zone
);


ALTER TABLE public.tbl_lgc_gbj_header OWNER TO armasi;

--
-- Name: tbl_lgc_gbj_header_id_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.tbl_lgc_gbj_header_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tbl_lgc_gbj_header_id_seq OWNER TO armasi;

--
-- Name: tbl_lgc_gbj_header_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.tbl_lgc_gbj_header_id_seq OWNED BY public.tbl_lgc_gbj_header.id;


--
-- Name: tbl_retur_produksi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_retur_produksi (
    retur_kode text NOT NULL,
    tanggal date,
    jenis_bahan text,
    create_by text,
    check_by text,
    approve_by text,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_retur_produksi OWNER TO armasi;

--
-- Name: tbl_satuan; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_satuan (
    satuan_id integer NOT NULL,
    satuan_kode character varying(20),
    status_tran character varying(1)
);


ALTER TABLE public.tbl_satuan OWNER TO armasi;

--
-- Name: tbl_satuan_satuan_id_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.tbl_satuan_satuan_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tbl_satuan_satuan_id_seq OWNER TO armasi;

--
-- Name: tbl_satuan_satuan_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.tbl_satuan_satuan_id_seq OWNED BY public.tbl_satuan.satuan_id;


--
-- Name: tbl_sp_downgrade_pallet14122018; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_downgrade_pallet14122018 (
    plan_kode character(1),
    no_downgrade character varying(18),
    tanggal date,
    pallet_no character varying(18),
    create_date timestamp without time zone,
    create_user character varying(15),
    item_kode_lama character varying(20),
    item_kode_baru character varying(20),
    approval boolean,
    approval_user character varying(18),
    date_approval timestamp without time zone,
    keterangan character varying(300),
    qty numeric,
    jenis_downgrade character varying(1),
    status_tran character varying(1)
);


ALTER TABLE public.tbl_sp_downgrade_pallet14122018 OWNER TO armasi;

--
-- Name: tbl_sp_hasilbj1204; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_hasilbj1204 (
    plan_kode character(1),
    seq_no character varying(18),
    pallet_no character varying(18),
    tanggal date,
    item_kode character varying(20),
    quality character varying(20),
    subplant character varying(2),
    shade character varying(4),
    size character varying(4),
    qty numeric,
    create_date date,
    create_user character varying(15),
    status_plt character varying(1),
    rkpterima_no character varying(20),
    rkpterima_tanggal date,
    rkpterima_user character varying(15),
    terima_no character varying(18),
    tanggal_terima date,
    terima_user character varying(15),
    status_item character varying(1),
    txn_no character varying(18),
    shift smallint,
    last_qty smallint,
    line character varying(1),
    regu character varying(1),
    plt_status character varying(20),
    keterangan text,
    kd_customer character varying(200),
    tanggal_pending date,
    last_update timestamp without time zone,
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    area character varying(10),
    lokasi character varying(15)
);


ALTER TABLE public.tbl_sp_hasilbj1204 OWNER TO armasi;

--
-- Name: tbl_sp_hasilbj12042018; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_hasilbj12042018 (
    plan_kode character(1),
    seq_no character varying(18),
    pallet_no character varying(18),
    tanggal date,
    item_kode character varying(20),
    quality character varying(20),
    subplant character varying(2),
    shade character varying(4),
    size character varying(4),
    qty numeric,
    create_date date,
    create_user character varying(15),
    status_plt character varying(1),
    rkpterima_no character varying(20),
    rkpterima_tanggal date,
    rkpterima_user character varying(15),
    terima_no character varying(18),
    tanggal_terima date,
    terima_user character varying(15),
    status_item character varying(1),
    txn_no character varying(18),
    shift smallint,
    last_qty smallint,
    line character varying(1),
    regu character varying(1),
    plt_status character varying(20),
    keterangan text,
    kd_customer character varying(200),
    tanggal_pending date,
    last_update timestamp without time zone,
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    area character varying(10),
    lokasi character varying(15)
);


ALTER TABLE public.tbl_sp_hasilbj12042018 OWNER TO armasi;

--
-- Name: tbl_sp_hasilbj2592018; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_hasilbj2592018 (
    plan_kode character(1),
    seq_no character varying(18),
    pallet_no character varying(18),
    tanggal date,
    item_kode character varying(20),
    quality character varying(20),
    subplant character varying(2),
    shade character varying(4),
    size character varying(4),
    qty numeric,
    create_date date,
    create_user character varying(15),
    status_plt character varying(1),
    rkpterima_no character varying(20),
    rkpterima_tanggal date,
    rkpterima_user character varying(15),
    terima_no character varying(18),
    tanggal_terima date,
    terima_user character varying(15),
    status_item character varying(1),
    txn_no character varying(18),
    shift smallint,
    last_qty smallint,
    line character varying(1),
    regu character varying(1),
    plt_status character varying(20),
    keterangan text,
    kd_customer character varying(200),
    tanggal_pending date,
    last_update timestamp without time zone,
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    area character varying(10),
    lokasi character varying(15),
    qa_approved boolean
);


ALTER TABLE public.tbl_sp_hasilbj2592018 OWNER TO armasi;

--
-- Name: tbl_sp_hasilbj_dg; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_hasilbj_dg (
    pallet_no character varying(18),
    item_kode character varying(20),
    quality character varying(20),
    shade character varying(4),
    size character varying(4)
);


ALTER TABLE public.tbl_sp_hasilbj_dg OWNER TO armasi;

--
-- Name: tbl_sp_hasilbjmut; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_hasilbjmut (
    plan_kode character(1),
    seq_no character varying(18),
    pallet_no character varying(18),
    tanggal date,
    item_kode character varying(20),
    quality character varying(20),
    subplant character varying(2),
    shade character varying(4),
    size character varying(4),
    qty numeric,
    create_date date,
    create_user character varying(15),
    status_plt character varying(1),
    rkpterima_no character varying(20),
    rkpterima_tanggal date,
    rkpterima_user character varying(15),
    terima_no character varying(18),
    tanggal_terima date,
    terima_user character varying(15),
    status_item character varying(1),
    txn_no character varying(18),
    shift smallint,
    last_qty smallint,
    line character varying(1),
    regu character varying(1),
    plt_status character varying(20),
    keterangan text,
    kd_customer character varying(200),
    tanggal_pending date,
    last_update timestamp without time zone,
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    area character varying(10),
    lokasi character varying(15),
    qa_approved boolean
);


ALTER TABLE public.tbl_sp_hasilbjmut OWNER TO armasi;

--
-- Name: tbl_sp_hasilbjtemp; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_hasilbjtemp (
    plan_kode character(1),
    seq_no character varying(18),
    pallet_no character varying(18),
    tanggal date,
    item_kode character varying(20),
    quality character varying(20),
    subplant character varying(2),
    shade character varying(4),
    size character varying(4),
    qty numeric,
    create_date date,
    create_user character varying(15),
    status_plt character varying(1),
    rkpterima_no character varying(20),
    rkpterima_tanggal date,
    rkpterima_user character varying(15),
    terima_no character varying(18),
    tanggal_terima date,
    terima_user character varying(15),
    status_item character varying(1),
    txn_no character varying(18),
    shift smallint,
    last_qty smallint,
    line character varying(1),
    regu character varying(1),
    plt_status character varying(20),
    keterangan text,
    kd_customer character varying(200),
    tanggal_pending date,
    last_update timestamp without time zone,
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    area character varying(10),
    lokasi character varying(15),
    qa_approved boolean
);


ALTER TABLE public.tbl_sp_hasilbjtemp OWNER TO armasi;

--
-- Name: tbl_sp_ket_dg_pallet; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_ket_dg_pallet (
    id_ket_pallet integer NOT NULL,
    keterangan character varying(100),
    plan_kode character varying(1),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_sp_ket_dg_pallet OWNER TO armasi;

--
-- Name: tbl_sp_ket_dg_pallet_id_ket_pallet_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.tbl_sp_ket_dg_pallet_id_ket_pallet_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tbl_sp_ket_dg_pallet_id_ket_pallet_seq OWNER TO armasi;

--
-- Name: tbl_sp_ket_dg_pallet_id_ket_pallet_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.tbl_sp_ket_dg_pallet_id_ket_pallet_seq OWNED BY public.tbl_sp_ket_dg_pallet.id_ket_pallet;


--
-- Name: tbl_sp_master_size; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_master_size (
    plan_kode character(1),
    size character varying(3),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_sp_master_size OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_agus_adj; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_agus_adj (
    kdbrg character varying,
    kua character varying,
    prd numeric,
    mtin numeric,
    mtout numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_agus_adj OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_agus_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_agus_jadi (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_agus_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_apr_adj; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_apr_adj (
    kdbrg character varying,
    kua character varying,
    prd numeric,
    mtin numeric,
    mtout numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_apr_adj OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_apr_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_apr_jadi (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_apr_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_balance_jan; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_balance_jan (
    groupmut character varying(20),
    kuamut character varying,
    msal numeric,
    mprd numeric,
    mmut numeric,
    mret numeric,
    kjul numeric,
    kmut numeric,
    kbrp numeric,
    kfoc numeric,
    ksmp numeric,
    sakh numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_balance_jan OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_brgmst; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_brgmst (
    noseq integer,
    groupmut character varying(20),
    kuamut character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_brgmst OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_des_adj; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_des_adj (
    kdbrg character varying,
    kua character varying,
    prd numeric,
    mtin numeric,
    mtout numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_des_adj OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_des_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_des_jadi (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_des_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_feb_adj; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_feb_adj (
    kdbrg character varying,
    kua character varying,
    prd numeric,
    mtin numeric,
    mtout numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_feb_adj OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_feb_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_feb_jadi (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_feb_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_foc; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_foc (
    nomut character varying(20),
    plt character varying(18),
    qt numeric,
    kdbrg character varying,
    sha character varying,
    size character varying,
    kua character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_foc OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_jan; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_jan (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_jan OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_jan_adj; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_jan_adj (
    kdbrg character varying,
    kua character varying,
    prd numeric,
    mtin numeric,
    mtout numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_jan_adj OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_jan_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_jan_jadi (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric,
    nama character varying(50)
);


ALTER TABLE public.tbl_sp_mutasi_pallet_jan_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_jan_nokd; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_jan_nokd (
    nmbrg character varying,
    kuamut character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_jan_nokd OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_jul_adj; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_jul_adj (
    kdbrg character varying,
    kua character varying,
    prd numeric,
    mtin numeric,
    mtout numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_jul_adj OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_jul_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_jul_jadi (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_jul_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_juli_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_juli_jadi (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_juli_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_jun_adj; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_jun_adj (
    kdbrg character varying,
    kua character varying,
    prd numeric,
    mtin numeric,
    mtout numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_jun_adj OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_jun_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_jun_jadi (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_jun_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_juni_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_juni_jadi (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_juni_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_mar_adj; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_mar_adj (
    kdbrg character varying,
    kua character varying,
    prd numeric,
    mtin numeric,
    mtout numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_mar_adj OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_mar_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_mar_jadi (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_mar_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_master_item; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_master_item (
    kd character varying,
    kua character varying,
    mtf character varying(20)
);


ALTER TABLE public.tbl_sp_mutasi_pallet_master_item OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_masterbrg; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_masterbrg (
    grp character varying(20),
    kua character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_masterbrg OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_may_adj; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_may_adj (
    kdbrg character varying,
    kua character varying,
    prd numeric,
    mtin numeric,
    mtout numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_may_adj OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_may_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_may_jadi (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_may_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_nov_adj; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_nov_adj (
    kdbrg character varying,
    kua character varying,
    prd numeric,
    mtin numeric,
    mtout numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_nov_adj OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_nov_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_nov_jadi (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_nov_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_now; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_now (
    io_plan_kode character varying(1),
    io_kd_lok character varying(8),
    io_no_pallet character varying(30),
    io_qty_pallet integer,
    io_tgl timestamp without time zone,
    plan_kode character(1),
    seq_no character varying(18),
    pallet_no character varying(18),
    tanggal date,
    item_kode character varying(20),
    quality character varying(20),
    subplant character varying(2),
    shade character varying(4),
    size character varying(4),
    qty numeric,
    create_date date,
    create_user character varying(15),
    status_plt character varying(1),
    rkpterima_no character varying(20),
    rkpterima_tanggal date,
    rkpterima_user character varying(15),
    terima_no character varying(18),
    tanggal_terima date,
    terima_user character varying(15),
    status_item character varying(1),
    txn_no character varying(18),
    shift smallint,
    last_qty smallint,
    line character varying(1),
    regu character varying(1),
    plt_status character varying(20),
    keterangan text,
    kd_customer character varying(200),
    tanggal_pending date,
    last_update timestamp without time zone,
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    area character varying(10),
    lokasi character varying(15),
    qa_approved boolean
);


ALTER TABLE public.tbl_sp_mutasi_pallet_now OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_okt_adj; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_okt_adj (
    kdbrg character varying,
    kua character varying,
    prd numeric,
    mtin numeric,
    mtout numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_okt_adj OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_okt_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_okt_jadi (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_okt_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_opname_des; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_opname_des (
    mtf character varying,
    kua character varying,
    qtyopn numeric,
    mtf2 character varying,
    kdbrg character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_opname_des OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_sep_adj; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_sep_adj (
    kdbrg character varying,
    kua character varying,
    prd numeric,
    mtin numeric,
    mtout numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_sep_adj OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_sep_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_sep_jadi (
    kdbrg character varying,
    kua character varying,
    saw numeric,
    sak numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_sep_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card (
    plan_kode character(1),
    no_mutasi character varying(20),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    item_kodemut character varying,
    sizemut character varying,
    shademut character varying,
    kuamut character varying,
    groupmut character varying(20)
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card13112018; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card13112018 (
    plan_kode character(1),
    no_mutasi character varying(20),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    item_kodemut character varying,
    sizemut character varying,
    shademut character varying,
    kuamut character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card13112018 OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card15112018; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card15112018 (
    plan_kode character(1),
    no_mutasi character varying(20),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    item_kodemut character varying,
    sizemut character varying,
    shademut character varying,
    kuamut character varying,
    groupmut character varying(20)
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card15112018 OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2017_saldo; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2017_saldo (
    groupmut character varying(20),
    kuamut character varying,
    qty numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2017_saldo OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018 (
    plan_kode character(1),
    no_mutasi character varying(20),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    item_kodemut character varying,
    sizemut character varying,
    shademut character varying,
    kuamut character varying,
    groupmut character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018 OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018_backup; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_backup (
    plan_kode character(1),
    no_mutasi character varying(20),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    item_kodemut character varying,
    sizemut character varying,
    shademut character varying,
    kuamut character varying,
    groupmut character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_backup OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018_master; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_master (
    mtfmst character varying,
    kuamst character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_master OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018_mbjkw4; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_mbjkw4 (
    plan_kode character(1),
    no_mutasi character varying(20),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    item_kodemut character varying,
    sizemut character varying,
    shademut character varying,
    kuamut character varying,
    groupmut character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_mbjkw4 OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018_mutasi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_mutasi (
    plan_kode character(1),
    no_mutasi character varying(20),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    item_kodemut character varying,
    sizemut character varying,
    shademut character varying,
    kuamut character varying,
    groupmut character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_mutasi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018_opname; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_opname (
    mtf character varying,
    kua character varying,
    qty numeric,
    mtfakh character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_opname OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018_pecah; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_pecah (
    motif character varying,
    kualitas character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_pecah OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018_pecah11; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_pecah11 (
    plan_kode character(1),
    no_mutasi character varying(20),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    item_kodemut character varying,
    sizemut character varying,
    shademut character varying,
    kuamut character varying,
    groupmut character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_pecah11 OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018_rekap; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_rekap (
    mtf character varying,
    kua character varying,
    qtyawal integer,
    qtytrx integer,
    qtyakhr integer,
    bln smallint,
    qtyhpp integer
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_rekap OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018_rekap_it; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_rekap_it (
    per integer,
    groupmut character varying,
    kuamut character varying,
    prod numeric,
    jual numeric,
    retur numeric,
    sample numeric,
    foc numeric,
    pecah numeric,
    mutasi numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_rekap_it OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018_rekap_jan; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_rekap_jan (
    groupmut character varying,
    kuamut character varying,
    prod numeric,
    pecah numeric,
    foc numeric,
    sample numeric,
    mutasi numeric,
    jual numeric,
    retur numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_rekap_jan OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018_saldo; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_saldo (
    groupmut character varying,
    kuamut character varying,
    adj numeric,
    bal numeric,
    bam numeric,
    brp numeric,
    mbj numeric,
    mlt numeric,
    obj numeric,
    seq numeric,
    smp numeric,
    pbp numeric,
    foc numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_saldo OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018_saldoakh; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_saldoakh (
    mtfakhr character varying,
    kuaahk character varying,
    salakhr integer,
    blnakh smallint
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_saldoakh OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018_sj; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_sj (
    no_surat_jalan text,
    tanggal date,
    item_kode character varying(20),
    item_nama character varying(200),
    quality character varying(15),
    category_nama character varying(200),
    volume numeric
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018_sj OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018backup04032019; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018backup04032019 (
    plan_kode character(1),
    no_mutasi character varying(20),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    item_kodemut character varying,
    sizemut character varying,
    shademut character varying,
    kuamut character varying,
    groupmut character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018backup04032019 OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_2018bc; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_2018bc (
    plan_kode character(1),
    no_mutasi character varying(20),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    item_kodemut character varying,
    sizemut character varying,
    shademut character varying,
    kuamut character varying,
    groupmut character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_2018bc OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_mltbackup; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_mltbackup (
    plan_kode character(1),
    no_mutasi character varying(20),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    item_kodemut character varying,
    sizemut character varying,
    shademut character varying,
    kuamut character varying,
    groupmut character varying(20)
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_mltbackup OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_now_saldo; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_now_saldo (
    motif text,
    kua character varying(20),
    qty bigint
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_now_saldo OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_card_prdbackup; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_card_prdbackup (
    plan_kode character(1),
    no_mutasi character varying(20),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    item_kodemut character varying,
    sizemut character varying,
    shademut character varying,
    kuamut character varying,
    groupmut character varying(20)
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_card_prdbackup OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_pallet_stock_cardbc; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_pallet_stock_cardbc (
    plan_kode character(1),
    no_mutasi character varying(20),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    item_kodemut character varying,
    sizemut character varying,
    shademut character varying,
    kuamut character varying
);


ALTER TABLE public.tbl_sp_mutasi_pallet_stock_cardbc OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_palletmut; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_palletmut (
    plan_kode character(1),
    no_mutasi character varying(18),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1),
    item_kode character varying,
    item_name character varying,
    size character varying,
    shade character varying,
    kua character varying
);


ALTER TABLE public.tbl_sp_mutasi_palletmut OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_palletmut2; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_palletmut2 (
    plan_kode character(1),
    no_mutasi character varying(18),
    tanggal date,
    pallet_no character varying(18),
    qty numeric,
    create_date timestamp without time zone,
    create_user character varying(15),
    status_mut character varying(1),
    status_print character varying(1),
    reff_txn character varying(18),
    keterangan character varying(200),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_sp_mutasi_palletmut2 OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_rekap_stock_now; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_rekap_stock_now (
    motif text,
    quality character varying(20),
    qty bigint
);


ALTER TABLE public.tbl_sp_mutasi_rekap_stock_now OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_retur; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_retur (
    nobuk text,
    tgl date,
    kdbrg text,
    retqty numeric,
    mtf text,
    kua text
);


ALTER TABLE public.tbl_sp_mutasi_retur OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_retur1; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_retur1 (
    nobuk text,
    tgl date,
    kdbrg text,
    retqty numeric,
    mtf text,
    kua text
);


ALTER TABLE public.tbl_sp_mutasi_retur1 OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_retur_feb2018; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_retur_feb2018 (
    nobuk text,
    tgl date,
    kdbrg text,
    retqty numeric,
    mtf text,
    kua text,
    itembrg character varying(50)
);


ALTER TABLE public.tbl_sp_mutasi_retur_feb2018 OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_retur_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_retur_jadi (
    nobuk text,
    tgl date,
    kdbrg text,
    retqty numeric,
    mtf text,
    kua text,
    itembrg character varying(50)
);


ALTER TABLE public.tbl_sp_mutasi_retur_jadi OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_retur_jan2018; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_retur_jan2018 (
    nobuk text,
    tgl date,
    kdbrg text,
    retqty numeric,
    mtf text,
    kua text,
    itembrg character varying(50)
);


ALTER TABLE public.tbl_sp_mutasi_retur_jan2018 OWNER TO armasi;

--
-- Name: tbl_sp_mutasi_saldo_awal; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_mutasi_saldo_awal (
    ket character varying(200),
    kua character varying,
    saw numeric
);


ALTER TABLE public.tbl_sp_mutasi_saldo_awal OWNER TO armasi;

--
-- Name: tbl_sp_opname; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_opname (
    plan_kode character(1) NOT NULL,
    no_txn character varying(18) NOT NULL,
    tanggal date,
    ket_so text,
    subplan_kode character(2) NOT NULL,
    kode_brg character varying(18) NOT NULL,
    qty_adj numeric NOT NULL,
    ket_det text,
    appr_sts character varying(1),
    appr_by character varying(20),
    appr_date timestamp without time zone,
    no_pallet character varying(18),
    create_date timestamp without time zone,
    create_user character varying(15)
);


ALTER TABLE public.tbl_sp_opname OWNER TO armasi;

--
-- Name: tbl_sp_permintaan_brp; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_permintaan_brp (
    plan_kode character(1) NOT NULL,
    no_pbp character varying(18) NOT NULL,
    tanggal date,
    pallet_no character varying(18) NOT NULL,
    create_date timestamp without time zone NOT NULL,
    create_user character varying(15),
    approval boolean DEFAULT false,
    date_approval timestamp without time zone,
    approval_user character varying(18),
    keterangan character varying(300),
    qty_awal numeric,
    qty_akhir numeric,
    status character varying(1),
    no_brp character varying(18),
    pallet_sortir character varying(18),
    sortir_date timestamp without time zone,
    qty_sortir numeric,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_sp_permintaan_brp OWNER TO armasi;

--
-- Name: tbl_sp_status_master; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_status_master (
    status_plt character varying(1) NOT NULL,
    remark_sts character varying(30),
    create_date timestamp without time zone NOT NULL,
    create_user character varying(15),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_sp_status_master OWNER TO armasi;

--
-- Name: tbl_sp_status_pallet; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sp_status_pallet (
    plan_kode character(1) NOT NULL,
    no_txn character varying(18) NOT NULL,
    tanggal date,
    pallet_no character varying(18) NOT NULL,
    status_plt character varying(1) NOT NULL,
    remark_txn character varying(30),
    create_date timestamp without time zone NOT NULL,
    create_user character varying(15),
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_sp_status_pallet OWNER TO armasi;

--
-- Name: tbl_stock_bulanan; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_stock_bulanan (
    item_kode text NOT NULL,
    tahun numeric NOT NULL,
    plan_kode numeric NOT NULL,
    d_bln_0 numeric DEFAULT 0 NOT NULL,
    k_bln_0 numeric DEFAULT 0 NOT NULL,
    sd_bln_0 numeric DEFAULT 0 NOT NULL,
    sk_bln_0 numeric DEFAULT 0 NOT NULL,
    d_bln_1 numeric DEFAULT 0 NOT NULL,
    k_bln_1 numeric DEFAULT 0 NOT NULL,
    d_bln_2 numeric DEFAULT 0 NOT NULL,
    k_bln_2 numeric DEFAULT 0 NOT NULL,
    d_bln_3 numeric DEFAULT 0 NOT NULL,
    k_bln_3 numeric DEFAULT 0 NOT NULL,
    d_bln_4 numeric DEFAULT 0 NOT NULL,
    k_bln_4 numeric DEFAULT 0 NOT NULL,
    d_bln_5 numeric DEFAULT 0 NOT NULL,
    k_bln_5 numeric DEFAULT 0 NOT NULL,
    d_bln_6 numeric DEFAULT 0 NOT NULL,
    k_bln_6 numeric DEFAULT 0 NOT NULL,
    d_bln_7 numeric DEFAULT 0 NOT NULL,
    k_bln_7 numeric DEFAULT 0 NOT NULL,
    d_bln_8 numeric DEFAULT 0 NOT NULL,
    k_bln_8 numeric DEFAULT 0 NOT NULL,
    d_bln_9 numeric DEFAULT 0 NOT NULL,
    k_bln_9 numeric DEFAULT 0 NOT NULL,
    d_bln_10 numeric DEFAULT 0 NOT NULL,
    k_bln_10 numeric DEFAULT 0 NOT NULL,
    d_bln_11 numeric DEFAULT 0 NOT NULL,
    k_bln_11 numeric DEFAULT 0 NOT NULL,
    d_bln_12 numeric DEFAULT 0 NOT NULL,
    k_bln_12 numeric DEFAULT 0 NOT NULL,
    sd_bln_1 numeric DEFAULT 0 NOT NULL,
    sk_bln_1 numeric DEFAULT 0 NOT NULL,
    sd_bln_2 numeric DEFAULT 0 NOT NULL,
    sk_bln_2 numeric DEFAULT 0 NOT NULL,
    sd_bln_3 numeric DEFAULT 0 NOT NULL,
    sk_bln_3 numeric DEFAULT 0 NOT NULL,
    sd_bln_4 numeric DEFAULT 0 NOT NULL,
    sk_bln_4 numeric DEFAULT 0 NOT NULL,
    sd_bln_5 numeric DEFAULT 0 NOT NULL,
    sk_bln_5 numeric DEFAULT 0 NOT NULL,
    sd_bln_6 numeric DEFAULT 0 NOT NULL,
    sk_bln_6 numeric DEFAULT 0 NOT NULL,
    sd_bln_7 numeric DEFAULT 0 NOT NULL,
    sk_bln_7 numeric DEFAULT 0 NOT NULL,
    sd_bln_8 numeric DEFAULT 0 NOT NULL,
    sk_bln_8 numeric DEFAULT 0 NOT NULL,
    sd_bln_9 numeric DEFAULT 0 NOT NULL,
    sk_bln_9 numeric DEFAULT 0 NOT NULL,
    sd_bln_10 numeric DEFAULT 0 NOT NULL,
    sk_bln_10 numeric DEFAULT 0 NOT NULL,
    sd_bln_11 numeric DEFAULT 0 NOT NULL,
    sk_bln_11 numeric DEFAULT 0 NOT NULL,
    sd_bln_12 numeric DEFAULT 0 NOT NULL,
    sk_bln_12 numeric DEFAULT 0 NOT NULL,
    kode_lama text,
    seq_key integer NOT NULL,
    size character varying(5),
    shading character varying(5),
    status_tran character varying(1)
);


ALTER TABLE public.tbl_stock_bulanan OWNER TO armasi;

--
-- Name: tbl_stock_bulanan_seq_key_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.tbl_stock_bulanan_seq_key_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tbl_stock_bulanan_seq_key_seq OWNER TO armasi;

--
-- Name: tbl_stock_bulanan_seq_key_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.tbl_stock_bulanan_seq_key_seq OWNED BY public.tbl_stock_bulanan.seq_key;


--
-- Name: tbl_sub_feature; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sub_feature (
    sub_f_id integer NOT NULL,
    sub_f_name text NOT NULL,
    feature_id integer NOT NULL,
    available_acl smallint DEFAULT ((B'111111'::"bit")::integer)::smallint NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    created_by character varying(20) DEFAULT 'admin'::character varying NOT NULL,
    last_updated_at timestamp without time zone DEFAULT now() NOT NULL,
    last_updated_by character varying(20) DEFAULT 'admin'::character varying NOT NULL
);


ALTER TABLE public.tbl_sub_feature OWNER TO armasi;

--
-- Name: tbl_sub_feature_sub_f_id_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.tbl_sub_feature_sub_f_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tbl_sub_feature_sub_f_id_seq OWNER TO armasi;

--
-- Name: tbl_sub_feature_sub_f_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.tbl_sub_feature_sub_f_id_seq OWNED BY public.tbl_sub_feature.sub_f_id;


--
-- Name: tbl_sub_plant_sec; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_sub_plant_sec (
    tss_user character varying(20) NOT NULL,
    tss_plant character(1) NOT NULL,
    tss_sub_plant character(1) NOT NULL,
    last_updated_at timestamp without time zone DEFAULT now() NOT NULL,
    last_updated_by character varying,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    created_by character varying(20) DEFAULT 'admin'::character varying
);


ALTER TABLE public.tbl_sub_plant_sec OWNER TO armasi;

--
-- Name: tbl_surat_jalan_jadi; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_surat_jalan_jadi (
    nosj character(20) NOT NULL,
    tgl date,
    noinv character(30),
    kodebrg character varying(18) NOT NULL,
    spec text,
    kua character(10),
    volume numeric,
    harga numeric,
    valuta character varying(5),
    shade character(10),
    size character(10)
);


ALTER TABLE public.tbl_surat_jalan_jadi OWNER TO armasi;

--
-- Name: tbl_tarif_angkutan_tarif_id_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.tbl_tarif_angkutan_tarif_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tbl_tarif_angkutan_tarif_id_seq OWNER TO armasi;

--
-- Name: tbl_tarif_angkutan_tarif_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.tbl_tarif_angkutan_tarif_id_seq OWNED BY public.tbl_tarif_angkutan.tarif_id;


--
-- Name: tbl_tarif_surat_jalan; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.tbl_tarif_surat_jalan (
    tanggal date,
    surat_jalan text NOT NULL,
    kode_lama text,
    update_tran timestamp without time zone,
    update_tran_user character varying(10),
    upload_date timestamp without time zone,
    upload_user character varying(10),
    status_transfer boolean DEFAULT false,
    status_tran character varying(1)
);


ALTER TABLE public.tbl_tarif_surat_jalan OWNER TO armasi;

--
-- Name: tbl_user_user_id_seq; Type: SEQUENCE; Schema: public; Owner: armasi
--

CREATE SEQUENCE public.tbl_user_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tbl_user_user_id_seq OWNER TO armasi;

--
-- Name: tbl_user_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: armasi
--

ALTER SEQUENCE public.tbl_user_user_id_seq OWNED BY public.tbl_user.user_id;


--
-- Name: txn_counters; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.txn_counters (
    plant_id character varying(2) NOT NULL,
    txn_id character varying(3) NOT NULL,
    period text NOT NULL,
    count integer DEFAULT 1 NOT NULL,
    last_updated_at timestamp without time zone DEFAULT now() NOT NULL,
    last_period timestamp without time zone NOT NULL,
    CONSTRAINT txn_counters_count_check CHECK ((count > 0))
);


ALTER TABLE public.txn_counters OWNER TO armasi;

--
-- Name: txn_counters_details; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.txn_counters_details (
    plant_id character varying(2) NOT NULL,
    txn_id character varying(3) NOT NULL,
    period_count integer NOT NULL,
    period_time timestamp without time zone NOT NULL,
    last_updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT txn_counters_details_period_count_check CHECK ((period_count > 0))
);


ALTER TABLE public.txn_counters_details OWNER TO armasi;

--
-- Name: view_username_by_userid; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.view_username_by_userid AS
 SELECT
        CASE
            WHEN (tbl_user.user_name IS NOT NULL) THEN tbl_user.user_name
            ELSE gen_user_adm.gua_kode
        END AS userid,
        CASE
            WHEN (tbl_user.user_name IS NOT NULL) THEN (((tbl_user.first_name)::text || ' '::text) || (tbl_user.last_name)::text)
            ELSE gen_user_adm.gua_nama
        END AS username
   FROM (public.tbl_user
     FULL JOIN public.gen_user_adm ON (((tbl_user.user_name)::text = (gen_user_adm.gua_kode)::text)));


ALTER TABLE public.view_username_by_userid OWNER TO armasi;

--
-- Name: vw_shipping_details; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.vw_shipping_details AS
SELECT
    NULL::text AS no_ba,
    NULL::integer AS detail_ba_id,
    NULL::text AS kode_lama,
    NULL::character varying(2) AS sub_plant,
    NULL::date AS tanggal,
    NULL::text AS customer_kode,
    NULL::text AS no_surat_jalan_rekap,
    NULL::character varying(40) AS create_by,
    NULL::text AS tujuan_surat_jalan_rekap,
    NULL::character varying(20) AS item_kode,
    NULL::character varying(5) AS itshade,
    NULL::character varying(3) AS itsize,
    NULL::numeric AS volume,
    NULL::text AS keterangan,
    NULL::text AS do_kode,
    NULL::character varying(10) AS detail_cat,
    NULL::numeric AS shipped_quantity;


ALTER TABLE public.vw_shipping_details OWNER TO postgres;

--
-- Name: vw_status_ba; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.vw_status_ba AS
 SELECT a.no_ba,
    a.qtyba,
    b.no_mutasi,
    b.qty
   FROM (( SELECT tbl_ba_muat_detail.no_ba,
            sum(tbl_ba_muat_detail.volume) AS qtyba
           FROM public.tbl_ba_muat_detail
          WHERE (tbl_ba_muat_detail.no_ba IN ( SELECT tbl_ba_muat.no_ba
                   FROM public.tbl_ba_muat
                  WHERE (tbl_ba_muat.kode_lama <> 'F'::text)))
          GROUP BY tbl_ba_muat_detail.no_ba) a
     JOIN ( SELECT c.no_mutasi,
            sum(c.qtymut) AS qty
           FROM ( SELECT tbl_sp_mutasi_pallet.no_mutasi,
                    (sum(tbl_sp_mutasi_pallet.qty) * ('-1'::integer)::numeric) AS qtymut
                   FROM public.tbl_sp_mutasi_pallet
                  WHERE ((tbl_sp_mutasi_pallet.no_mutasi)::text IN ( SELECT tbl_ba_muat.no_ba
                           FROM public.tbl_ba_muat
                          WHERE (tbl_ba_muat.kode_lama <> 'F'::text)))
                  GROUP BY tbl_sp_mutasi_pallet.no_mutasi
                UNION ALL
                 SELECT tbl_sp_mutasi_pallet.reff_txn,
                    (sum(tbl_sp_mutasi_pallet.qty) * ('-1'::integer)::numeric) AS qtymut
                   FROM public.tbl_sp_mutasi_pallet
                  WHERE ((tbl_sp_mutasi_pallet.reff_txn)::text IN ( SELECT tbl_ba_muat.no_ba
                           FROM public.tbl_ba_muat
                          WHERE (tbl_ba_muat.kode_lama <> 'F'::text)))
                  GROUP BY tbl_sp_mutasi_pallet.reff_txn) c
          GROUP BY c.no_mutasi) b ON ((a.no_ba = (b.no_mutasi)::text)))
  WHERE (a.qtyba = b.qty);


ALTER TABLE public.vw_status_ba OWNER TO armasi;

--
-- Name: vw_tbl_sp_mutasi_pallet_stock_card; Type: VIEW; Schema: public; Owner: armasi
--

CREATE VIEW public.vw_tbl_sp_mutasi_pallet_stock_card AS
 SELECT tbl_sp_mutasi_pallet_stock_card.plan_kode,
    tbl_sp_mutasi_pallet_stock_card.no_mutasi,
    tbl_sp_mutasi_pallet_stock_card.tanggal,
    tbl_sp_mutasi_pallet_stock_card.pallet_no,
    tbl_sp_mutasi_pallet_stock_card.qty,
    tbl_sp_mutasi_pallet_stock_card.create_date,
    tbl_sp_mutasi_pallet_stock_card.create_user,
    tbl_sp_mutasi_pallet_stock_card.status_mut,
    tbl_sp_mutasi_pallet_stock_card.status_print,
    tbl_sp_mutasi_pallet_stock_card.reff_txn,
    tbl_sp_mutasi_pallet_stock_card.keterangan,
    tbl_sp_mutasi_pallet_stock_card.update_tran,
    tbl_sp_mutasi_pallet_stock_card.update_tran_user,
    tbl_sp_mutasi_pallet_stock_card.upload_date,
    tbl_sp_mutasi_pallet_stock_card.upload_user,
    tbl_sp_mutasi_pallet_stock_card.status_transfer,
    tbl_sp_mutasi_pallet_stock_card.status_tran,
    tbl_sp_mutasi_pallet_stock_card.item_kodemut,
    tbl_sp_mutasi_pallet_stock_card.sizemut,
    tbl_sp_mutasi_pallet_stock_card.shademut,
    tbl_sp_mutasi_pallet_stock_card.kuamut,
    tbl_sp_mutasi_pallet_stock_card.groupmut
   FROM public.tbl_sp_mutasi_pallet_stock_card
UNION ALL
 SELECT tbl_sp_mutasi_pallet_stock_card_2018.plan_kode,
    tbl_sp_mutasi_pallet_stock_card_2018.no_mutasi,
    tbl_sp_mutasi_pallet_stock_card_2018.tanggal,
    tbl_sp_mutasi_pallet_stock_card_2018.pallet_no,
    tbl_sp_mutasi_pallet_stock_card_2018.qty,
    tbl_sp_mutasi_pallet_stock_card_2018.create_date,
    tbl_sp_mutasi_pallet_stock_card_2018.create_user,
    tbl_sp_mutasi_pallet_stock_card_2018.status_mut,
    tbl_sp_mutasi_pallet_stock_card_2018.status_print,
    tbl_sp_mutasi_pallet_stock_card_2018.reff_txn,
    tbl_sp_mutasi_pallet_stock_card_2018.keterangan,
    tbl_sp_mutasi_pallet_stock_card_2018.update_tran,
    tbl_sp_mutasi_pallet_stock_card_2018.update_tran_user,
    tbl_sp_mutasi_pallet_stock_card_2018.upload_date,
    tbl_sp_mutasi_pallet_stock_card_2018.upload_user,
    tbl_sp_mutasi_pallet_stock_card_2018.status_transfer,
    tbl_sp_mutasi_pallet_stock_card_2018.status_tran,
    tbl_sp_mutasi_pallet_stock_card_2018.item_kodemut,
    tbl_sp_mutasi_pallet_stock_card_2018.sizemut,
    tbl_sp_mutasi_pallet_stock_card_2018.shademut,
    tbl_sp_mutasi_pallet_stock_card_2018.kuamut,
    tbl_sp_mutasi_pallet_stock_card_2018.groupmut
   FROM public.tbl_sp_mutasi_pallet_stock_card_2018;


ALTER TABLE public.vw_tbl_sp_mutasi_pallet_stock_card OWNER TO armasi;

--
-- Name: whouse; Type: TABLE; Schema: public; Owner: armasi
--

CREATE TABLE public.whouse (
    warehouse_kode character varying(50) NOT NULL,
    plan_kode character varying(50) NOT NULL,
    jenis_warehouse character varying(10),
    warehouse_nama character varying(40),
    location text,
    area character varying(40),
    capacity numeric,
    satuan character varying(40),
    inactive boolean,
    modiby character varying(10),
    modidate date,
    note text,
    status_tran character varying(1)
);


ALTER TABLE public.whouse OWNER TO armasi;

--
-- Name: app_menu_id_seq; Type: SEQUENCE; Schema: qc; Owner: armasi_qc
--

CREATE SEQUENCE qc.app_menu_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE qc.app_menu_id_seq OWNER TO armasi_qc;

--
-- Name: app_menu; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.app_menu (
    am_id integer DEFAULT nextval('qc.app_menu_id_seq'::regclass) NOT NULL,
    am_label character varying(50),
    am_link text,
    am_parent integer,
    am_sort smallint,
    am_class character varying(100),
    am_level smallint,
    am_nama text,
    am_stats character varying(1)
);


ALTER TABLE qc.app_menu OWNER TO armasi_qc;

--
-- Name: app_priv; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.app_priv (
    user_id integer NOT NULL,
    menu_id integer NOT NULL,
    ap_view character varying(1),
    ap_add character varying(1),
    ap_edit character varying(1),
    ap_del character varying(1),
    ap_print character varying(1),
    ap_approve character varying(1)
);


ALTER TABLE qc.app_priv OWNER TO armasi_qc;

--
-- Name: app_user_id_seq; Type: SEQUENCE; Schema: qc; Owner: armasi_qc
--

CREATE SEQUENCE qc.app_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE qc.app_user_id_seq OWNER TO armasi_qc;

--
-- Name: app_user; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.app_user (
    user_id integer DEFAULT nextval('qc.app_user_id_seq'::regclass) NOT NULL,
    user_name character varying(20),
    first_name character varying(20),
    last_name character varying(20),
    jabatan_kode character varying(20),
    level_akses character varying(10),
    password character varying(100),
    alamat character varying(75),
    jenis_kelamin character varying(20),
    tanggal_lahir date,
    nip character varying(20),
    foto character varying(20),
    departemen_kode character varying(20),
    agama character varying(20),
    tanggal_masuk date,
    tanda_tangan character varying(40),
    plan_kode text,
    status character varying(1),
    jumlah numeric(2,0),
    tanggal date,
    jam time without time zone,
    jml_off numeric(2,0),
    expired_date date,
    user_gbb character varying(2),
    sub_plan character varying(3)
);


ALTER TABLE qc.app_user OWNER TO armasi_qc;

--
-- Name: prev_qcdaily; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.prev_qcdaily (
    pq_plant_kode character varying(1) NOT NULL,
    pq_line_kode character varying(2) NOT NULL,
    pq_motif text NOT NULL,
    pq_seri character varying(50),
    pq_shading character varying(50)
);


ALTER TABLE qc.prev_qcdaily OWNER TO armasi_qc;

--
-- Name: qc_air_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_air_detail (
    qih_id character varying(10) NOT NULL,
    qid_deep_wheel2 character varying(100) NOT NULL,
    qid_deep_wheel3 character varying(100),
    qid_data_mushola character varying(100),
    qid_glazing_line character varying(100),
    qid_kolam character varying(100),
    qid_pdam character varying(100)
);


ALTER TABLE qc.qc_air_detail OWNER TO armasi_qc;

--
-- Name: qc_air_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_air_header (
    qih_id character varying(10) NOT NULL,
    qih_sub_plant character varying(1) NOT NULL,
    qih_date timestamp without time zone,
    qih_user_create character varying(100),
    qih_date_create timestamp without time zone,
    qih_user_modify character varying(100),
    qih_date_modify timestamp without time zone,
    qih_rec_status character varying(1)
);


ALTER TABLE qc.qc_air_header OWNER TO armasi_qc;

--
-- Name: qc_alat_berat; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_alat_berat (
    qab_nama character varying(200) NOT NULL,
    qab_nomor character varying(20) NOT NULL
);


ALTER TABLE qc.qc_alat_berat OWNER TO armasi_qc;

--
-- Name: qc_alber_runhour; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_alber_runhour (
    qar_id character varying(10) NOT NULL,
    qar_date timestamp without time zone,
    qar_shift smallint,
    qar_ab_nama character varying(200),
    qar_ab_nomor character varying(20),
    qar_rec_stat character varying(1),
    qar_awal numeric,
    qar_akhir numeric,
    qar_remark text,
    qar_user_create character varying(100),
    qar_date_create timestamp without time zone,
    qar_user_modify character varying(100),
    qar_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_alber_runhour OWNER TO armasi_qc;

--
-- Name: qc_bm_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_bm_detail (
    qbh_id character varying(10) NOT NULL,
    qbd_box_unit character varying(4) NOT NULL,
    qbd_material_code character varying(21) NOT NULL,
    qbd_material_type character varying(1),
    qbd_formula numeric,
    qbd_dw numeric,
    qbd_mc numeric,
    qbd_ww numeric,
    qbd_remark text,
    qbd_value numeric
);


ALTER TABLE qc.qc_bm_detail OWNER TO armasi_qc;

--
-- Name: qc_bm_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_bm_header (
    qbh_sub_plant character varying(1) NOT NULL,
    qbh_id character varying(10) NOT NULL,
    qbh_date date,
    qbh_shift smallint,
    qbh_body_code text,
    qbh_volume numeric,
    qbh_user_create character varying(100),
    qbh_date_create timestamp without time zone,
    qbh_bm_no character varying(2),
    qbh_rec_status character varying(1),
    qbh_user_modify character varying(100),
    qbh_date_modify timestamp without time zone,
    qbh_user_pbd character varying(100),
    qbh_date_pbd date,
    qbh_kode_pbd character varying(50),
    qbh_to_ballmill character(3)
);


ALTER TABLE qc.qc_bm_header OWNER TO armasi_qc;

--
-- Name: qc_bm_unit; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_bm_unit (
    qbm_plant_code character varying(1) NOT NULL,
    qbm_kode character varying(2) NOT NULL,
    qbm_capacity numeric,
    qbm_desc text
);


ALTER TABLE qc.qc_bm_unit OWNER TO armasi_qc;

--
-- Name: qc_bm_wh_box_unit; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_bm_wh_box_unit (
    qbu_sub_plant character varying(2) NOT NULL,
    qbu_kode character varying(4) NOT NULL,
    qbu_desc text
);


ALTER TABLE qc.qc_bm_wh_box_unit OWNER TO armasi_qc;

--
-- Name: qc_box_unit; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_box_unit (
    qbu_sub_plant character varying(1) NOT NULL,
    qbu_kode character varying(4) NOT NULL,
    qbu_desc text
);


ALTER TABLE qc.qc_box_unit OWNER TO armasi_qc;

--
-- Name: qc_cb_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_cb_detail (
    qch_id character varying(10) NOT NULL,
    qcd_prep_group character varying(2) NOT NULL,
    qcd_prep_seq smallint NOT NULL,
    qcd_prep_remark text,
    qcd_silo_no character varying(2) NOT NULL,
    qcd_slip_no character varying(2) NOT NULL,
    qcd_prep_value text
);


ALTER TABLE qc.qc_cb_detail OWNER TO armasi_qc;

--
-- Name: qc_cb_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_cb_header (
    qch_sub_plant character varying(1) NOT NULL,
    qch_id character varying(10) NOT NULL,
    qch_date timestamp without time zone,
    qch_shift smallint,
    qch_rec_stat character varying(1),
    qch_bm_no character varying(2),
    qch_user_create character varying(100),
    qch_date_create timestamp without time zone,
    qch_user_modify character varying(100),
    qch_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_cb_header OWNER TO armasi_qc;

--
-- Name: qc_cb_prep_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_cb_prep_detail (
    qcpd_group character varying(2) NOT NULL,
    qcpd_seq smallint NOT NULL,
    qcpd_control_desc text,
    qcpd_um_id character varying(2)
);


ALTER TABLE qc.qc_cb_prep_detail OWNER TO armasi_qc;

--
-- Name: qc_cb_prep_master; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_cb_prep_master (
    qcpm_group character varying(2) NOT NULL,
    qcpm_desc text
);


ALTER TABLE qc.qc_cb_prep_master OWNER TO armasi_qc;

--
-- Name: qc_cb_prep_standard; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_cb_prep_standard (
    qcps_date date,
    qcps_group character varying(2) NOT NULL,
    qcps_seq smallint,
    qcps_min_val numeric,
    qcps_max_val numeric
);


ALTER TABLE qc.qc_cb_prep_standard OWNER TO armasi_qc;

--
-- Name: qc_cb_silo; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_cb_silo (
    qcs_sub_plant character varying(1) NOT NULL,
    qcs_code character varying(2) NOT NULL,
    qcs_desc text,
    qcs_cap numeric
);


ALTER TABLE qc.qc_cb_silo OWNER TO armasi_qc;

--
-- Name: qc_cb_slip_tank; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_cb_slip_tank (
    qct_sub_plant character varying(1) NOT NULL,
    qct_code character varying(2) NOT NULL,
    qct_desc text,
    qct_cap numeric
);


ALTER TABLE qc.qc_cb_slip_tank OWNER TO armasi_qc;

--
-- Name: qc_fg_fault_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_fg_fault_detail (
    fgf_id character varying(10) NOT NULL,
    fapr_id character varying(2) NOT NULL,
    eco_value numeric,
    rj_value numeric
);


ALTER TABLE qc.qc_fg_fault_detail OWNER TO armasi_qc;

--
-- Name: qc_fg_fault_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_fg_fault_header (
    fgf_sub_plant character varying(1) NOT NULL,
    fgf_id character varying(10) NOT NULL,
    fgf_date timestamp without time zone,
    fgf_kiln smallint,
    fgf_quality numeric,
    fgf_type text,
    fgf_status character varying(1),
    fgf_user_create character varying(100),
    fgf_date_create timestamp without time zone,
    fgf_user_modify character varying(100),
    fgf_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_fg_fault_header OWNER TO armasi_qc;

--
-- Name: qc_fg_fault_parameter; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_fg_fault_parameter (
    sub_plant character varying(1) NOT NULL,
    fapr_id character varying(2) NOT NULL,
    fapr_desc text,
    fapr_status character varying(1)
);


ALTER TABLE qc.qc_fg_fault_parameter OWNER TO armasi_qc;

--
-- Name: qc_fg_firing_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_fg_firing_detail (
    fh_id character varying(10) NOT NULL,
    fc_group character varying(100),
    fc_gdid integer NOT NULL,
    fhd_value text
);


ALTER TABLE qc.qc_fg_firing_detail OWNER TO armasi_qc;

--
-- Name: qc_fg_firing_group; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_fg_firing_group (
    fc_group character varying(2) NOT NULL,
    fc_desc text
);


ALTER TABLE qc.qc_fg_firing_group OWNER TO armasi_qc;

--
-- Name: qc_fg_firing_group_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_fg_firing_group_detail (
    fc_sub_plant character varying(1) NOT NULL,
    fc_group character varying(2) NOT NULL,
    fc_gdid integer NOT NULL,
    fc_gdparrent integer,
    fc_gdunit text,
    fc_gddesc text,
    fc_gdstatus character varying(1)
);


ALTER TABLE qc.qc_fg_firing_group_detail OWNER TO armasi_qc;

--
-- Name: qc_fg_firing_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_fg_firing_header (
    fh_sub_plant character varying(1) NOT NULL,
    fh_id character varying(10) NOT NULL,
    fh_date timestamp without time zone,
    fh_kiln smallint,
    fh_shift smallint,
    fh_status character varying(1),
    fh_user_create character varying(100),
    fh_date_create timestamp without time zone,
    fh_user_modify character varying(100),
    fh_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_fg_firing_header OWNER TO armasi_qc;

--
-- Name: qc_fg_kiln_bending_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_fg_kiln_bending_detail (
    kb_id character varying(10) NOT NULL,
    kbd_posisi integer NOT NULL,
    kbd_kg numeric,
    kbd_cm numeric
);


ALTER TABLE qc.qc_fg_kiln_bending_detail OWNER TO armasi_qc;

--
-- Name: qc_fg_kiln_bending_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_fg_kiln_bending_header (
    kb_sub_plant character varying(1) NOT NULL,
    kb_id character varying(10) NOT NULL,
    kb_date timestamp without time zone,
    kb_kiln smallint,
    kb_temp character varying(100),
    kb_speed character varying(10),
    kb_presi character varying(100),
    kb_desc text,
    kb_status character varying(1),
    kb_user_create character varying(100),
    kb_date_create timestamp without time zone,
    kb_user_modify character varying(100),
    kb_date_modify timestamp without time zone,
    kb_wa character varying(200),
    kb_ac character varying(200),
    kb_wm character varying(200),
    kb_tt character varying(200)
);


ALTER TABLE qc.qc_fg_kiln_bending_header OWNER TO armasi_qc;

--
-- Name: qc_fg_rg_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_fg_rg_detail (
    rg_id character varying(10) NOT NULL,
    rg_qly smallint NOT NULL,
    rg_per_2h integer NOT NULL,
    rg_shading text,
    rg_size text,
    rg_calibro text,
    rg_desc text,
    rg_defect_kode character varying(5)
);


ALTER TABLE qc.qc_fg_rg_detail OWNER TO armasi_qc;

--
-- Name: qc_fg_rg_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_fg_rg_header (
    rg_sub_plant character varying(1) NOT NULL,
    rg_id character varying(10) NOT NULL,
    rg_date timestamp without time zone,
    rg_line character varying(2),
    rg_shift smallint,
    rg_status character varying(1),
    rg_user_create character varying(100),
    rg_date_create timestamp without time zone,
    rg_user_modify character varying(100),
    rg_date_modify timestamp without time zone,
    rg_motif text
);


ALTER TABLE qc.qc_fg_rg_header OWNER TO armasi_qc;

--
-- Name: qc_fg_sorting_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_fg_sorting_detail (
    sp_id character varying(10) NOT NULL,
    code character varying(100),
    size character varying(100),
    export numeric,
    ekonomi numeric,
    reject numeric,
    keterangan text,
    rijek_palet numeric,
    rijek_buang numeric
);


ALTER TABLE qc.qc_fg_sorting_detail OWNER TO armasi_qc;

--
-- Name: qc_fg_sorting_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_fg_sorting_header (
    sp_sub_plant character varying(1) NOT NULL,
    sp_id character varying(10) NOT NULL,
    sp_date timestamp without time zone,
    sp_line text,
    sp_shift smallint,
    sp_status character varying(1),
    sp_user_create character varying(100),
    sp_date_create timestamp without time zone,
    sp_user_modify character varying(100),
    sp_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_fg_sorting_header OWNER TO armasi_qc;

--
-- Name: qc_fg_sorting_mesin; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_fg_sorting_mesin (
    sub_plant character varying(1) NOT NULL,
    mesin_id character varying(2) NOT NULL,
    mesin_desc text,
    mesin_status character varying(1)
);


ALTER TABLE qc.qc_fg_sorting_mesin OWNER TO armasi_qc;

--
-- Name: qc_ft_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_ft_header (
    qfh_sub_plant character varying(1) NOT NULL,
    qfh_id character varying(10) NOT NULL,
    qfh_date timestamp without time zone,
    qfh_rec_stat character varying(1),
    qfh_findings text,
    qfh_reported_to character varying(20),
    qfh_done_by character varying(20),
    qfh_user_create character varying(15),
    qfh_date_create timestamp without time zone
);


ALTER TABLE qc.qc_ft_header OWNER TO armasi_qc;

--
-- Name: qc_gas_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gas_detail (
    qgh_id character varying(10) NOT NULL,
    qgd_mesin character varying(2) NOT NULL,
    qgd_seq smallint NOT NULL,
    qgd_line character varying(2) NOT NULL,
    qgd_remark text,
    qgd_value character varying(100)
);


ALTER TABLE qc.qc_gas_detail OWNER TO armasi_qc;

--
-- Name: qc_gas_detail_produksi; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gas_detail_produksi (
    qgp_id character varying(10) NOT NULL,
    qgdp_mesin character varying(2) NOT NULL,
    qgdp_seq smallint NOT NULL,
    qgdp_line character varying(2) NOT NULL,
    qgdp_remark text,
    qgdp_value character varying(100)
);


ALTER TABLE qc.qc_gas_detail_produksi OWNER TO armasi_qc;

--
-- Name: qc_gas_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gas_header (
    qgh_id character varying(10) NOT NULL,
    qgh_sub_plant character varying(1),
    qgh_date timestamp without time zone,
    qgh_shift smallint,
    qgh_rec_stat character varying(1),
    qgd_user_create character varying(100),
    qgd_date_create timestamp without time zone,
    qgd_user_modify character varying(100),
    qgd_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_gas_header OWNER TO armasi_qc;

--
-- Name: qc_gas_prep; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gas_prep (
    qgp_sub_plant character varying(1) NOT NULL,
    qgp_mesin_code character varying(2) NOT NULL,
    qgp_mesin_no character varying(2) NOT NULL,
    qgp_line character varying(2)
);


ALTER TABLE qc.qc_gas_prep OWNER TO armasi_qc;

--
-- Name: qc_gas_prep_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gas_prep_detail (
    qgpd_mesin_code character varying(2) NOT NULL,
    qgpd_seq smallint NOT NULL,
    qgpd_desc text,
    qgpd_um_id character varying(2)
);


ALTER TABLE qc.qc_gas_prep_detail OWNER TO armasi_qc;

--
-- Name: qc_gas_prep_detail_2; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gas_prep_detail_2 (
    qgpd2_mesin_code character varying(2) NOT NULL,
    qgpd2_seq smallint NOT NULL,
    qgpd2_desc text,
    qgpd2_um_id character varying(2)
);


ALTER TABLE qc.qc_gas_prep_detail_2 OWNER TO armasi_qc;

--
-- Name: qc_gas_produksi; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gas_produksi (
    qgp_id character varying(10) NOT NULL,
    qgp_sub_plant character varying(1),
    qgp_date timestamp without time zone,
    qgp_shift smallint,
    qgp_rec_stat character varying(1),
    qgp_user_create character varying(100),
    qgp_date_create timestamp without time zone,
    qgp_user_modify character varying(100),
    qgp_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_gas_produksi OWNER TO armasi_qc;

--
-- Name: qc_gen_plant_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gen_plant_detail (
    qpd_plant_code character varying(2) NOT NULL,
    qpd_feeder_cap numeric,
    qpd_ball_mill_body numeric,
    qpd_ball_mill_glaze numeric,
    qpd_silo numeric,
    qpd_slip_thank numeric,
    qpd_press numeric,
    qpd_horizontal_dryer numeric
);


ALTER TABLE qc.qc_gen_plant_detail OWNER TO armasi_qc;

--
-- Name: qc_gen_um; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gen_um (
    qgu_id character varying(2) NOT NULL,
    qgu_code character varying(15),
    qgu_desc text
);


ALTER TABLE qc.qc_gen_um OWNER TO armasi_qc;

--
-- Name: qc_genset_detail_test; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_genset_detail_test (
    qgh_id character varying(10) NOT NULL,
    qgh_genset character varying(100) NOT NULL,
    qgh_no_urut character varying
);


ALTER TABLE qc.qc_genset_detail_test OWNER TO armasi_qc;

--
-- Name: qc_genset_header_test; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_genset_header_test (
    qgh_user_modify character varying,
    qgh_user_create character varying(100),
    qgh_sub_plant character varying(1) NOT NULL,
    qgh_id character varying(10) NOT NULL,
    qgh_date_create timestamp without time zone,
    qgh_date timestamp without time zone,
    qgh_rec_status character varying(1),
    qgh_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_genset_header_test OWNER TO armasi_qc;

--
-- Name: qc_genset_runhour_detail_test; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_genset_runhour_detail_test (
    qgh_id character varying(10) NOT NULL,
    qgh_run_hour character varying,
    qgh_warming character varying,
    qgh_meter_solar character varying,
    qgh_no_urut character varying
);


ALTER TABLE qc.qc_genset_runhour_detail_test OWNER TO armasi_qc;

--
-- Name: qc_genset_runhour_header_test; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_genset_runhour_header_test (
    qgh_id character varying(10) NOT NULL,
    qgh_sub_plant character varying(1) NOT NULL,
    qgh_date_create timestamp without time zone,
    qgh_date timestamp without time zone,
    qgh_user_create character varying(100),
    qgh_user_modify character varying(100),
    qgh_rec_status character varying(1),
    qgh_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_genset_runhour_header_test OWNER TO armasi_qc;

--
-- Name: qc_gl_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gl_detail (
    qgh_id character varying(10) NOT NULL,
    qgd_motif character varying(200) NOT NULL,
    qgd_hasil numeric,
    qgd_reject numeric,
    qgd_hambatan character varying(5)
);


ALTER TABLE qc.qc_gl_detail OWNER TO armasi_qc;

--
-- Name: qc_gl_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gl_header (
    qgh_id character varying(10) NOT NULL,
    qgh_subplant character varying(1),
    qgh_date timestamp without time zone,
    qgh_shift smallint,
    qgh_rec_stat character varying(1),
    qgh_absensi character varying(300),
    qgh_keterangan text,
    qgh_user_create character varying(100),
    qgh_date_create timestamp without time zone,
    qgh_user_modify character varying(100),
    qgh_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_gl_header OWNER TO armasi_qc;

--
-- Name: qc_gp_bmg; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gp_bmg (
    qgb_sub_plant character varying(1) NOT NULL,
    qgb_code character varying(2) NOT NULL,
    qgb_desc text,
    qgb_cap numeric
);


ALTER TABLE qc.qc_gp_bmg OWNER TO armasi_qc;

--
-- Name: qc_gp_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gp_detail (
    qgh_id character varying(10) NOT NULL,
    qgd_prep_group character varying(2) NOT NULL,
    qgd_prep_seq smallint NOT NULL,
    qgd_prep_value character varying(200),
    qgd_prep_remark text,
    qgd_standard_id integer
);


ALTER TABLE qc.qc_gp_detail OWNER TO armasi_qc;

--
-- Name: qc_gp_detail_master; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gp_detail_master (
    qgdm_group character varying(2) NOT NULL,
    qgdm_seq smallint NOT NULL,
    qgdm_control_desc text,
    qgdm_um_id character varying(2)
);


ALTER TABLE qc.qc_gp_detail_master OWNER TO armasi_qc;

--
-- Name: qc_gp_group_master; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gp_group_master (
    qggm_group character varying(2) NOT NULL,
    qggm_desc text
);


ALTER TABLE qc.qc_gp_group_master OWNER TO armasi_qc;

--
-- Name: qc_gp_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gp_header (
    qgh_id character varying(10) NOT NULL,
    qgh_sub_plant character varying(1),
    qgh_date timestamp without time zone,
    qgh_rec_stat character varying(1),
    qgh_glaze_code character varying(200),
    qgh_bmg_no character varying(2),
    qgd_user_create character varying(100),
    qgd_date_create timestamp without time zone,
    qgh_category character varying(1),
    qgh_shift smallint,
    qgd_user_mofify character varying(100),
    qgd_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_gp_header OWNER TO armasi_qc;

--
-- Name: qc_gp_standard_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gp_standard_detail (
    qgsd_std_id integer NOT NULL,
    qgsd_group character varying(2),
    qgsd_seq smallint,
    qgsd_min_val numeric,
    qgsd_max_val numeric
);


ALTER TABLE qc.qc_gp_standard_detail OWNER TO armasi_qc;

--
-- Name: qc_gp_standard_header_qgsh_std_id_seq; Type: SEQUENCE; Schema: qc; Owner: armasi_qc
--

CREATE SEQUENCE qc.qc_gp_standard_header_qgsh_std_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE qc.qc_gp_standard_header_qgsh_std_id_seq OWNER TO armasi_qc;

--
-- Name: qc_gp_standard_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_gp_standard_header (
    qgsh_std_id integer DEFAULT nextval('qc.qc_gp_standard_header_qgsh_std_id_seq'::regclass) NOT NULL,
    qgsh_glaze_code character varying(20) NOT NULL,
    qgsh_date date,
    qgsh_category character varying(1)
);


ALTER TABLE qc.qc_gp_standard_header OWNER TO armasi_qc;

--
-- Name: qc_ic_in_appr; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_ic_in_appr (
    appr_uname character varying(20) NOT NULL,
    appr_jab character varying(20),
    appr_user_create character varying(100),
    appr_date_create timestamp without time zone
);


ALTER TABLE qc.qc_ic_in_appr OWNER TO armasi_qc;

--
-- Name: qc_ic_kebasahan_data; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_ic_kebasahan_data (
    ic_id character varying(10) NOT NULL,
    ic_sub_plant character varying(1),
    ic_date timestamp without time zone,
    ic_no_kendaraan character varying(15),
    ic_kd_material character varying(100),
    ic_nm_material text,
    ic_sub_kontraktor character varying(100),
    ic_no_box character varying(50),
    ic_kadar_air character varying(20),
    ic_keterangan text,
    ic_rec_stat character varying(1),
    ic_user_create character varying(100),
    ic_date_create timestamp without time zone,
    ic_user_modify character varying(100),
    ic_date_modify timestamp without time zone,
    ic_lw character varying(20),
    ic_visco character varying(20),
    ic_residu character varying(20),
    ic_hasil character varying(1),
    ic_ap_kabag_user character varying(20),
    ic_ap_kabag_date timestamp without time zone,
    ic_ap_pm_user character varying(20),
    ic_ap_pm_date timestamp without time zone,
    ic_idmb character varying(10),
    ic_idmk character varying(10),
    ic_ap_kabag_sts character varying(1),
    ic_ap_kabag_note text,
    ic_ap_pm_sts character varying(1),
    ic_ap_pm_note text,
    ic_no_sj character varying(255),
    ic_no_po character varying(20),
    ic_bpb_kode character varying(20),
    pojs_user character varying(20),
    pojs_date timestamp without time zone,
    jenis character varying(10)
);


ALTER TABLE qc.qc_ic_kebasahan_data OWNER TO armasi_qc;

--
-- Name: qc_ic_kebasahan_data_test; Type: TABLE; Schema: qc; Owner: armasi
--

CREATE TABLE qc.qc_ic_kebasahan_data_test (
    ic_id character varying(10),
    ic_sub_plant character varying(1),
    ic_date timestamp without time zone,
    ic_no_kendaraan character varying(15),
    ic_kd_material character varying(100),
    ic_nm_material text,
    ic_sub_kontraktor character varying(100),
    ic_no_box character varying(50),
    ic_kadar_air character varying(20),
    ic_keterangan text,
    ic_rec_stat character varying(1),
    ic_user_create character varying(100),
    ic_date_create timestamp without time zone,
    ic_user_modify character varying(100),
    ic_date_modify timestamp without time zone,
    ic_lw character varying(20),
    ic_visco character varying(20),
    ic_residu character varying(20),
    ic_hasil character varying(1),
    ic_ap_kabag_user character varying(20),
    ic_ap_kabag_date timestamp without time zone,
    ic_ap_pm_user character varying(20),
    ic_ap_pm_date timestamp without time zone,
    ic_idmb character varying(10),
    ic_idmk character varying(10),
    ic_ap_kabag_sts character varying(1),
    ic_ap_kabag_note text,
    ic_ap_pm_sts character varying(1),
    ic_ap_pm_note text,
    ic_no_sj character varying(255),
    ic_no_po character varying(20),
    ic_bpb_kode character varying(20),
    pojs_user character varying(20),
    pojs_date timestamp without time zone,
    jenis character varying(10)
);


ALTER TABLE qc.qc_ic_kebasahan_data_test OWNER TO armasi;

--
-- Name: qc_ic_mb_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_ic_mb_detail (
    ic_id character varying(10) NOT NULL,
    icd_group integer NOT NULL,
    icd_seq integer NOT NULL,
    icd_value text
);


ALTER TABLE qc.qc_ic_mb_detail OWNER TO armasi_qc;

--
-- Name: qc_ic_mb_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_ic_mb_header (
    ic_sub_plant character varying(1) NOT NULL,
    ic_id character varying(10) NOT NULL,
    ic_date timestamp without time zone,
    ic_idmasuk character varying(10) NOT NULL,
    ic_rec_stat character varying(1),
    ic_user_create character varying(100),
    ic_date_create timestamp without time zone,
    ic_user_modify character varying(100),
    ic_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_ic_mb_header OWNER TO armasi_qc;

--
-- Name: qc_ic_mb_parameter_olddd; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_ic_mb_parameter_olddd (
    qcpd_group smallint NOT NULL,
    qcpd_seq smallint NOT NULL,
    qcpd_desc character varying(150),
    qcpd_sat character varying(20),
    qcpd_std character(100)
);


ALTER TABLE qc.qc_ic_mb_parameter_olddd OWNER TO armasi_qc;

--
-- Name: qc_ic_parameter; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_ic_parameter (
    pm_id integer NOT NULL,
    pm_groupid smallint NOT NULL,
    pm_groupname character varying(200),
    pm_seq smallint NOT NULL,
    pm_desc text,
    pm_std text,
    pm_sat character varying(100),
    pm_urut smallint NOT NULL,
    pm_status character varying(1)
);


ALTER TABLE qc.qc_ic_parameter OWNER TO armasi_qc;

--
-- Name: qc_ic_parameter_pm_id_seq; Type: SEQUENCE; Schema: qc; Owner: armasi_qc
--

CREATE SEQUENCE qc.qc_ic_parameter_pm_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE qc.qc_ic_parameter_pm_id_seq OWNER TO armasi_qc;

--
-- Name: qc_ic_parameter_pm_id_seq; Type: SEQUENCE OWNED BY; Schema: qc; Owner: armasi_qc
--

ALTER SEQUENCE qc.qc_ic_parameter_pm_id_seq OWNED BY qc.qc_ic_parameter.pm_id;


--
-- Name: qc_ic_spesifikasimutu; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_ic_spesifikasimutu (
    ic_kd_material character varying(200) NOT NULL,
    ic_nm_material text NOT NULL,
    ic_kd_group smallint NOT NULL,
    ic_kd_seq smallint NOT NULL,
    ic_std text,
    ic_status character varying(1),
    ic_user_create character varying(100),
    ic_date_create timestamp without time zone
);


ALTER TABLE qc.qc_ic_spesifikasimutu OWNER TO armasi_qc;

--
-- Name: qc_ic_teskimia_data; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_ic_teskimia_data (
    ic_sub_plant character varying(1) NOT NULL,
    ic_id character varying(10) NOT NULL,
    ic_date timestamp without time zone,
    ic_idmasuk character varying(10),
    no_lot character varying(20),
    berat character varying(20),
    glossy character varying(1),
    flatness character varying(1),
    pinhole character varying(1),
    keterangan text,
    kesimpulan text,
    ic_rec_stat character varying(1),
    ic_user_create character varying(100),
    ic_date_create timestamp without time zone,
    ic_user_modify character varying(100),
    ic_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_ic_teskimia_data OWNER TO armasi_qc;

--
-- Name: qc_kiln_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_kiln_detail (
    kl_id character varying(10) NOT NULL,
    kl_group character varying(2) NOT NULL,
    kld_id character varying(2) NOT NULL,
    kl_d_value text
);


ALTER TABLE qc.qc_kiln_detail OWNER TO armasi_qc;

--
-- Name: qc_kiln_group; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_kiln_group (
    kl_group character varying(2) NOT NULL,
    kl_desc text
);


ALTER TABLE qc.qc_kiln_group OWNER TO armasi_qc;

--
-- Name: qc_kiln_group_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_kiln_group_detail (
    sub_plant character varying(1) NOT NULL,
    kl_group character varying(2) NOT NULL,
    kld_id character varying(2) NOT NULL,
    kld_desc text,
    kld_status character varying(1)
);


ALTER TABLE qc.qc_kiln_group_detail OWNER TO armasi_qc;

--
-- Name: qc_kiln_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_kiln_header (
    kl_sub_plant character varying(1) NOT NULL,
    kl_id character varying(10) NOT NULL,
    id_kiln character varying(10) NOT NULL,
    kl_date timestamp without time zone,
    kl_time timestamp without time zone,
    kl_speed text,
    kl_code text,
    kl_presure text,
    kl_user_create character varying(200),
    kl_date_create timestamp without time zone,
    kl_user_modify character varying(200),
    kl_date_modify timestamp without time zone,
    kl_status character varying(1)
);


ALTER TABLE qc.qc_kiln_header OWNER TO armasi_qc;

--
-- Name: qc_kiln_header_OLD; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc."qc_kiln_header_OLD" (
    kl_sub_plant character varying(1) NOT NULL,
    kl_id character varying(10) NOT NULL,
    kl_number smallint,
    kl_date timestamp without time zone,
    kl_time timestamp without time zone,
    kl_speed text,
    kl_code text,
    kl_presure text,
    kl_user_create character varying(200),
    kl_date_create timestamp without time zone,
    kl_user_modify character varying(200),
    kl_date_modify timestamp without time zone,
    kl_status character varying(1)
);


ALTER TABLE qc."qc_kiln_header_OLD" OWNER TO armasi_qc;

--
-- Name: qc_kiln_mesin; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_kiln_mesin (
    sub_plant character varying(1) NOT NULL,
    id_kiln character varying(10) NOT NULL,
    desc_kiln character varying(30)
);


ALTER TABLE qc.qc_kiln_mesin OWNER TO armasi_qc;

--
-- Name: qc_kpi_dept; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_kpi_dept (
    iddept smallint NOT NULL,
    nmdept character varying(50) NOT NULL
);


ALTER TABLE qc.qc_kpi_dept OWNER TO armasi_qc;

--
-- Name: qc_kpi_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_kpi_detail (
    idh smallint NOT NULL,
    idd integer NOT NULL,
    parent_id integer,
    kpi_desc character varying(200),
    bobot integer,
    satuan character varying(15),
    sasaran_target character varying(225),
    sasaran_periode character varying(100)
);


ALTER TABLE qc.qc_kpi_detail OWNER TO armasi_qc;

--
-- Name: qc_kpi_divisi; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_kpi_divisi (
    iddept smallint NOT NULL,
    iddivisi smallint NOT NULL,
    nmdivisi character varying(50) NOT NULL
);


ALTER TABLE qc.qc_kpi_divisi OWNER TO armasi_qc;

--
-- Name: qc_kpi_parameter; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_kpi_parameter (
    kpi_id integer NOT NULL,
    kpi_parent integer NOT NULL,
    kpi_dept smallint NOT NULL,
    kpi_divisi smallint NOT NULL,
    kpi_cat smallint NOT NULL,
    kpi_desc text NOT NULL,
    kpi_bobot character varying(15),
    kpi_satuan character varying(20),
    kpi_target character varying(50),
    kpi_periode character varying(25)
);


ALTER TABLE qc.qc_kpi_parameter OWNER TO armasi_qc;

--
-- Name: qc_line_unit; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_line_unit (
    qlu_plant_code character varying(1) NOT NULL,
    qlu_kode character varying(2) NOT NULL,
    qlu_capacity numeric,
    qlu_desc text
);


ALTER TABLE qc.qc_line_unit OWNER TO armasi_qc;

--
-- Name: qc_listrik_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_listrik_detail (
    qlh_id character varying(10) NOT NULL,
    qld_group character varying(100) NOT NULL,
    qld_r character varying(100),
    qld_s character varying(100),
    qld_t character varying(100),
    qld_v character varying(100),
    qld_watt_hour character varying(100)
);


ALTER TABLE qc.qc_listrik_detail OWNER TO armasi_qc;

--
-- Name: qc_listrik_gardu_detail_test; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_listrik_gardu_detail_test (
    qlh_id character varying(10) NOT NULL,
    qlh_group character varying(100) NOT NULL,
    qlh_value character varying(100)
);


ALTER TABLE qc.qc_listrik_gardu_detail_test OWNER TO armasi_qc;

--
-- Name: qc_listrik_gardu_header_test; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_listrik_gardu_header_test (
    qlh_id character varying(10) NOT NULL,
    qlh_sub_plant character varying(1) NOT NULL,
    qlh_date timestamp without time zone,
    qlh_user_create character varying(100),
    qlh_user_modify character varying(100),
    qlh_date_create timestamp without time zone,
    qlh_date_modify timestamp without time zone,
    qlh_rec_status character(1)
);


ALTER TABLE qc.qc_listrik_gardu_header_test OWNER TO armasi_qc;

--
-- Name: qc_listrik_gardu_setting_test; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_listrik_gardu_setting_test (
    id character varying(10) NOT NULL,
    name character varying(100) NOT NULL,
    parent character varying(10),
    is_active character(1),
    sort smallint
);


ALTER TABLE qc.qc_listrik_gardu_setting_test OWNER TO armasi_qc;

--
-- Name: qc_listrik_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_listrik_header (
    qlh_id character varying(10) NOT NULL,
    qlh_sub_plant character varying(1) NOT NULL,
    qlh_date timestamp without time zone,
    qlh_rec_status character varying(1),
    qlh_cap_bank_1 character varying(100),
    qlh_cap_bank_2 character varying(100),
    qlh_cap_bank_3 character varying(100),
    qlh_user_create character varying(100),
    qlh_date_create timestamp without time zone,
    qlh_user_modify character varying(100),
    qlh_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_listrik_header OWNER TO armasi_qc;

--
-- Name: qc_master_sparepart; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_master_sparepart (
    kdasset character varying(10) NOT NULL,
    item_kode character varying(50) NOT NULL,
    item_nama text,
    item_satuan character varying(50)
);


ALTER TABLE qc.qc_master_sparepart OWNER TO armasi_qc;

--
-- Name: qc_md_defect; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_md_defect (
    qmd_kode character varying(5) NOT NULL,
    qmd_nama text
);


ALTER TABLE qc.qc_md_defect OWNER TO armasi_qc;

--
-- Name: qc_md_hambatan; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_md_hambatan (
    qmh_code character varying(5) NOT NULL,
    qmh_nama character varying(200) NOT NULL
);


ALTER TABLE qc.qc_md_hambatan OWNER TO armasi_qc;

--
-- Name: qc_md_line; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_md_line (
    qml_plant_code character varying(1) NOT NULL,
    qml_kode character varying(2) NOT NULL,
    qml_nama text
);


ALTER TABLE qc.qc_md_line OWNER TO armasi_qc;

--
-- Name: qc_md_motif; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_md_motif (
    qmm_nama text NOT NULL,
    qmm_size character varying(100)
);


ALTER TABLE qc.qc_md_motif OWNER TO armasi_qc;

--
-- Name: qc_md_subkon; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_md_subkon (
    subkon_id character varying(20) NOT NULL,
    subkon_name character varying(200),
    subkon_desc text,
    subkon_status character varying(1),
    subkon_user_create character varying(100),
    subkon_date_create timestamp without time zone,
    subkon_user_modify character varying(100),
    subkon_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_md_subkon OWNER TO armasi_qc;

--
-- Name: qc_mesin_kg; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_mesin_kg (
    qmk_sub_plant character varying(1) NOT NULL,
    qmk_mesin_code character varying(2) NOT NULL,
    qmk_mesin_no character varying(2) NOT NULL,
    qmk_line character varying(2),
    qmk_ukuran character varying(10) NOT NULL,
    qmk_berat numeric
);


ALTER TABLE qc.qc_mesin_kg OWNER TO armasi_qc;

--
-- Name: qc_mesin_unit; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_mesin_unit (
    qmu_code character varying(2) NOT NULL,
    qmu_desc text,
    qmu_seq smallint
);


ALTER TABLE qc.qc_mesin_unit OWNER TO armasi_qc;

--
-- Name: qc_pd_cm_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_cm_detail (
    cmh_id character varying(10) NOT NULL,
    cm_group character varying(2) NOT NULL,
    cd1_id character varying(2) NOT NULL,
    cd2_id character varying(2) NOT NULL,
    cmd_value text
);


ALTER TABLE qc.qc_pd_cm_detail OWNER TO armasi_qc;

--
-- Name: qc_pd_cm_group; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_cm_group (
    cm_group character varying(2) NOT NULL,
    cm_desc text
);


ALTER TABLE qc.qc_pd_cm_group OWNER TO armasi_qc;

--
-- Name: qc_pd_cm_group_d1; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_cm_group_d1 (
    cm_group character varying(2) NOT NULL,
    cd1_id character varying(2) NOT NULL,
    cd1_desc text
);


ALTER TABLE qc.qc_pd_cm_group_d1 OWNER TO armasi_qc;

--
-- Name: qc_pd_cm_group_d2; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_cm_group_d2 (
    sub_plant character varying(1) NOT NULL,
    cm_group character varying(2) NOT NULL,
    cd1_id character varying(2) NOT NULL,
    cd2_id character varying(2) NOT NULL,
    cd2_desc text,
    cd2_status character varying(1),
    cd2_type character varying(6)
);


ALTER TABLE qc.qc_pd_cm_group_d2 OWNER TO armasi_qc;

--
-- Name: qc_pd_cm_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_cm_header (
    cmh_sub_plant character varying(1) NOT NULL,
    cmh_id character varying(10) NOT NULL,
    cmh_date timestamp without time zone,
    cmh_press character varying(3),
    cmh_user_create character varying(200),
    cmh_date_create timestamp without time zone,
    cmh_user_modify character varying(200),
    cmh_date_modify timestamp without time zone,
    cmh_status character varying(1)
);


ALTER TABLE qc.qc_pd_cm_header OWNER TO armasi_qc;

--
-- Name: qc_pd_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_detail (
    qph_id character varying(10) NOT NULL,
    qpd_hd_no character varying(2),
    qpd_mould_no character varying(2),
    qpd_pd_group character varying(2) NOT NULL,
    qpd_pd_seq smallint NOT NULL,
    qpd_pd_value numeric,
    qpd_pd_valmax numeric,
    qpd_pd_remark text
);


ALTER TABLE qc.qc_pd_detail OWNER TO armasi_qc;

--
-- Name: qc_pd_detail_old; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_detail_old (
    qph_id character varying(10) NOT NULL,
    qpd_hd_no character varying(2),
    qpd_mould_no character varying(2),
    qpd_pd_group character varying(2) NOT NULL,
    qpd_pd_seq smallint NOT NULL,
    qpd_pd_value numeric,
    qpd_pd_remark text,
    "qpd_pd_valMax" numeric
);


ALTER TABLE qc.qc_pd_detail_old OWNER TO armasi_qc;

--
-- Name: qc_pd_group; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_group (
    qpg_group character varying(2) NOT NULL,
    qpg_desc text
);


ALTER TABLE qc.qc_pd_group OWNER TO armasi_qc;

--
-- Name: qc_pd_group_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_group_detail (
    qpgd_subplant character varying(1) NOT NULL,
    qpgd_group character varying(2) NOT NULL,
    qpgd_seq smallint NOT NULL,
    qpgd_control_desc text,
    qpgd_um_id character varying(2),
    qpgd_isactive character varying(1),
    qpgd_standar character varying(100)
);


ALTER TABLE qc.qc_pd_group_detail OWNER TO armasi_qc;

--
-- Name: qc_pd_group_detail2; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_group_detail2 (
    qpgd_group character varying(2) NOT NULL,
    qpgd_seq smallint NOT NULL,
    qpgd_control_desc text,
    qpgd_um_id character varying(2),
    qpgd_isactive character varying(1),
    qpgd_standar character varying(100),
    qpgd_subplant character varying(1) NOT NULL
);


ALTER TABLE qc.qc_pd_group_detail2 OWNER TO armasi_qc;

--
-- Name: qc_pd_hd; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_hd (
    qph_sub_plant character varying(1) NOT NULL,
    qph_code character varying(2) NOT NULL,
    qph_desc text,
    qph_cap numeric
);


ALTER TABLE qc.qc_pd_hd OWNER TO armasi_qc;

--
-- Name: qc_pd_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_header (
    qph_sub_plant character varying(1) NOT NULL,
    qph_id character varying(10) NOT NULL,
    qph_date timestamp without time zone,
    qph_rec_stat character varying(1),
    qph_no_line character varying(2),
    qph_user_create character varying(100),
    qph_date_create timestamp without time zone,
    qph_shift smallint,
    qph_user_modify character varying(100),
    qph_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_pd_header OWNER TO armasi_qc;

--
-- Name: qc_pd_hp_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_hp_detail (
    hph_id character varying(10) NOT NULL,
    hpd_date_start timestamp without time zone,
    hpd_date_stop timestamp without time zone,
    hpd_value text
);


ALTER TABLE qc.qc_pd_hp_detail OWNER TO armasi_qc;

--
-- Name: qc_pd_hp_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_hp_header (
    hph_sub_plant character varying(1) NOT NULL,
    hph_id character varying(10) NOT NULL,
    hph_date timestamp without time zone,
    hph_press character varying(3),
    hph_line character varying(1),
    hph_shift character varying(1),
    hph_user_create character varying(200),
    hph_date_create timestamp without time zone,
    hph_user_modify character varying(200),
    hph_date_modify timestamp without time zone,
    hph_status character varying(1)
);


ALTER TABLE qc.qc_pd_hp_header OWNER TO armasi_qc;

--
-- Name: qc_pd_hsl_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_hsl_detail (
    qpdh_id character varying(10) NOT NULL,
    qcpdm_group character varying(2) NOT NULL,
    qcpdd_seq smallint NOT NULL,
    qpp_press_no character varying(2),
    qpdh_pd_value text
);


ALTER TABLE qc.qc_pd_hsl_detail OWNER TO armasi_qc;

--
-- Name: qc_pd_hsl_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_hsl_header (
    qpdh_sub_plant character varying(1) NOT NULL,
    qpdh_date timestamp without time zone,
    qpdh_user_create character varying(200),
    qpdh_date_create timestamp without time zone,
    qpdh_shift smallint,
    qpdh_user_modify character varying(200),
    qpdh_date_modify timestamp without time zone,
    qpdh_id character varying(10) NOT NULL,
    qpdh_status character varying(1)
);


ALTER TABLE qc.qc_pd_hsl_header OWNER TO armasi_qc;

--
-- Name: qc_pd_mouldset; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_mouldset (
    qpm_sub_plant character varying(1) NOT NULL,
    qpm_press_code character varying(2) NOT NULL,
    qpm_code character varying(2) NOT NULL,
    qpm_desc text
);


ALTER TABLE qc.qc_pd_mouldset OWNER TO armasi_qc;

--
-- Name: qc_pd_op_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_op_detail (
    op_id character varying(10) NOT NULL,
    op_mould smallint NOT NULL,
    op_urut numeric NOT NULL,
    op_value text
);


ALTER TABLE qc.qc_pd_op_detail OWNER TO armasi_qc;

--
-- Name: qc_pd_op_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_op_header (
    op_sub_plant character varying(1) NOT NULL,
    op_id character varying(10) NOT NULL,
    op_date timestamp without time zone,
    op_shift smallint,
    op_press smallint,
    op_tekanan text,
    op_rec_stat character varying(1),
    op_format character varying(10) NOT NULL,
    op_ukuran character varying(10),
    op_user_create character varying(100),
    op_date_create timestamp without time zone,
    op_user_modify character varying(100),
    op_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_pd_op_header OWNER TO armasi_qc;

--
-- Name: qc_pd_outpress; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_outpress (
    qpo_sub_plant character varying(1) NOT NULL,
    qpo_id character varying(10) NOT NULL,
    qpo_date timestamp without time zone,
    qpo_rec_stat character varying(1),
    qpo_press_no character varying(2),
    qpo_mould_no character varying(2),
    qpo_th_1 smallint,
    qpo_th_2 smallint,
    qpo_th_3 smallint,
    qpo_th_4 smallint,
    qpo_weight numeric,
    qpo_pd_remark text,
    qpo_user_create character varying(20),
    qpo_date_create timestamp without time zone
);


ALTER TABLE qc.qc_pd_outpress OWNER TO armasi_qc;

--
-- Name: qc_pd_prep_group; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_prep_group (
    qcpdm_group character varying(2) NOT NULL,
    qcpdm_desc text
);


ALTER TABLE qc.qc_pd_prep_group OWNER TO armasi_qc;

--
-- Name: qc_pd_prep_group_detil; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_prep_group_detil (
    qcpdm_group character varying(2) NOT NULL,
    qcpdd_seq smallint NOT NULL,
    qcpdd_control_desc text
);


ALTER TABLE qc.qc_pd_prep_group_detil OWNER TO armasi_qc;

--
-- Name: qc_pd_press; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_press (
    qpp_sub_plant character varying(1) NOT NULL,
    qpp_code character varying(2) NOT NULL,
    qpp_desc text,
    qpp_cap numeric
);


ALTER TABLE qc.qc_pd_press OWNER TO armasi_qc;

--
-- Name: qc_pd_sd; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_sd (
    qps_sub_plant character varying(1) NOT NULL,
    qps_code character varying(2) NOT NULL,
    qps_desc text,
    qps_cap numeric
);


ALTER TABLE qc.qc_pd_sd OWNER TO armasi_qc;

--
-- Name: qc_pd_size_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_size_detail (
    op_id character varying(10) NOT NULL,
    op_mould smallint NOT NULL,
    op_urut numeric NOT NULL,
    op_value text
);


ALTER TABLE qc.qc_pd_size_detail OWNER TO armasi_qc;

--
-- Name: qc_pd_size_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_size_header (
    op_sub_plant character varying(1) NOT NULL,
    op_id character varying(10) NOT NULL,
    op_date timestamp without time zone,
    op_shift smallint,
    op_press smallint,
    op_tekanan text,
    op_rec_stat character varying(1),
    op_format character varying(10) NOT NULL,
    op_ukuran character varying(10),
    op_user_create character varying(100),
    op_date_create timestamp without time zone,
    op_user_modify character varying(100),
    op_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_pd_size_header OWNER TO armasi_qc;

--
-- Name: qc_pd_standard; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_standard (
    qps_group character varying(2) NOT NULL,
    qps_seq smallint,
    qps_min_val numeric,
    qps_max_val numeric
);


ALTER TABLE qc.qc_pd_standard OWNER TO armasi_qc;

--
-- Name: qc_pd_thickness_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_thickness_detail (
    op_id character varying(10) NOT NULL,
    op_mould smallint NOT NULL,
    op_urut numeric NOT NULL,
    op_value text
);


ALTER TABLE qc.qc_pd_thickness_detail OWNER TO armasi_qc;

--
-- Name: qc_pd_thickness_header; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_pd_thickness_header (
    op_sub_plant character varying(1) NOT NULL,
    op_id character varying(10) NOT NULL,
    op_date timestamp without time zone,
    op_shift smallint,
    op_press smallint,
    op_tekanan text,
    op_rec_stat character varying(1),
    op_format character varying(10) NOT NULL,
    op_ukuran character varying(10),
    op_user_create character varying(100),
    op_date_create timestamp without time zone,
    op_user_modify character varying(100),
    op_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_pd_thickness_header OWNER TO armasi_qc;

--
-- Name: qc_sp_monitoring; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_sp_monitoring (
    qsm_sub_plant character varying(1) NOT NULL,
    qsm_id character varying(10) NOT NULL,
    qsm_date timestamp without time zone,
    qsm_rec_status character varying(1),
    qsm_user_create character varying(100),
    qsm_date_create timestamp without time zone,
    qsm_user_modify character varying(100),
    qsm_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_sp_monitoring OWNER TO armasi_qc;

--
-- Name: qc_sp_monitoring_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_sp_monitoring_detail (
    qsm_id character varying(10) NOT NULL,
    qsmd_sett_group character varying(2) NOT NULL,
    qsmd_sett_seq smallint NOT NULL,
    qsmd_sett_value numeric,
    qsmd_sett_remark text
);


ALTER TABLE qc.qc_sp_monitoring_detail OWNER TO armasi_qc;

--
-- Name: qc_sp_monitoring_stop; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_sp_monitoring_stop (
    qsms_id character varying(10) NOT NULL,
    qsms_sub_plant character varying(1) NOT NULL,
    qsms_date timestamp without time zone,
    qsms_rec_status character varying(1),
    qsms_keterangan text,
    qsms_user_create character varying(100),
    qsms_date_create timestamp without time zone,
    qsms_user_modify character varying(100),
    qsms_date_modify timestamp without time zone
);


ALTER TABLE qc.qc_sp_monitoring_stop OWNER TO armasi_qc;

--
-- Name: qc_sp_sett_detail; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_sp_sett_detail (
    qssd_group character varying(2) NOT NULL,
    qssd_seq smallint NOT NULL,
    qssd_monitoring_desc character varying(50),
    qssd_um_id character varying(2)
);


ALTER TABLE qc.qc_sp_sett_detail OWNER TO armasi_qc;

--
-- Name: qc_sp_sett_master; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_sp_sett_master (
    qss_group character varying(2) NOT NULL,
    qss_desc text
);


ALTER TABLE qc.qc_sp_sett_master OWNER TO armasi_qc;

--
-- Name: qc_subplan; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qc_subplan (
    plan_kode character varying(50) NOT NULL,
    sub_plan character varying(2) NOT NULL,
    keterangan text
);


ALTER TABLE qc.qc_subplan OWNER TO armasi_qc;

--
-- Name: qcdaily_eco; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qcdaily_eco (
    qec_id character varying(10) NOT NULL,
    qec_sub_plant character varying(1) NOT NULL,
    qec_line character varying(2) NOT NULL,
    qec_date timestamp without time zone NOT NULL,
    qec_motif text NOT NULL,
    qec_seri character varying(50),
    qec_shading character varying(50),
    qec_rec_status character varying(1),
    qec_defect_kode character varying(5) NOT NULL,
    qec_m2 numeric,
    qec_keterangan text
);


ALTER TABLE qc.qcdaily_eco OWNER TO armasi_qc;

--
-- Name: qcdaily_exp; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qcdaily_exp (
    qex_id character varying(10) NOT NULL,
    qex_sub_plant character varying(1) NOT NULL,
    qex_line character varying(2) NOT NULL,
    qex_date timestamp without time zone NOT NULL,
    qex_motif text NOT NULL,
    qex_seri character varying(50),
    qex_shading character varying(50),
    qex_rec_status character varying(1),
    qex_exp numeric,
    qex_eco numeric,
    qex_kw numeric,
    qex_user_create character varying(100),
    qex_date_create timestamp without time zone,
    qex_user_modify character varying(100),
    qex_date_modify timestamp without time zone
);


ALTER TABLE qc.qcdaily_exp OWNER TO armasi_qc;

--
-- Name: qcdaily_kw; Type: TABLE; Schema: qc; Owner: armasi_qc
--

CREATE TABLE qc.qcdaily_kw (
    qkw_id character varying(10) NOT NULL,
    qkw_sub_plant character varying(1) NOT NULL,
    qkw_line character varying(2) NOT NULL,
    qkw_date timestamp without time zone NOT NULL,
    qkw_motif text NOT NULL,
    qkw_seri character varying(50),
    qkw_shading character varying(50),
    qkw_rec_status character varying(1),
    qkw_defect_kode character varying(5) NOT NULL,
    qkw_m2 numeric,
    qkw_keterangan text
);


ALTER TABLE qc.qcdaily_kw OWNER TO armasi_qc;

--
-- Name: app_menu; Type: TABLE; Schema: taman; Owner: armasi_wmm
--

CREATE TABLE taman.app_menu (
    am_id integer NOT NULL,
    am_label character varying(50),
    am_link text,
    am_parent integer,
    am_sort smallint,
    am_class character varying(100),
    am_level smallint,
    am_nama text,
    am_status character varying(1)
);


ALTER TABLE taman.app_menu OWNER TO armasi_wmm;

--
-- Name: app_menu_am_id_seq; Type: SEQUENCE; Schema: taman; Owner: armasi_wmm
--

CREATE SEQUENCE taman.app_menu_am_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE taman.app_menu_am_id_seq OWNER TO armasi_wmm;

--
-- Name: app_menu_am_id_seq; Type: SEQUENCE OWNED BY; Schema: taman; Owner: armasi_wmm
--

ALTER SEQUENCE taman.app_menu_am_id_seq OWNED BY taman.app_menu.am_id;


--
-- Name: app_menu_front; Type: TABLE; Schema: taman; Owner: armasi_wmm
--

CREATE TABLE taman.app_menu_front (
    am_id integer NOT NULL,
    am_label character varying(50),
    am_link text,
    am_parent integer,
    am_sort smallint,
    am_class character varying(100),
    am_level smallint,
    am_nama text,
    am_status character varying(1)
);


ALTER TABLE taman.app_menu_front OWNER TO armasi_wmm;

--
-- Name: app_menu_front_am_id_seq; Type: SEQUENCE; Schema: taman; Owner: armasi_wmm
--

CREATE SEQUENCE taman.app_menu_front_am_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE taman.app_menu_front_am_id_seq OWNER TO armasi_wmm;

--
-- Name: app_menu_front_am_id_seq; Type: SEQUENCE OWNED BY; Schema: taman; Owner: armasi_wmm
--

ALTER SEQUENCE taman.app_menu_front_am_id_seq OWNED BY taman.app_menu_front.am_id;


--
-- Name: app_priv; Type: TABLE; Schema: taman; Owner: armasi_wmm
--

CREATE TABLE taman.app_priv (
    user_id integer NOT NULL,
    menu_id integer NOT NULL,
    ap_view character varying(1),
    ap_add character varying(1),
    ap_edit character varying(1),
    ap_del character varying(1),
    ap_print character varying(1),
    ap_approve character varying(1)
);


ALTER TABLE taman.app_priv OWNER TO armasi_wmm;

--
-- Name: app_priv_front; Type: TABLE; Schema: taman; Owner: armasi_wmm
--

CREATE TABLE taman.app_priv_front (
    user_id integer NOT NULL,
    menu_id integer NOT NULL,
    ap_view character varying(1),
    ap_add character varying(1),
    ap_edit character varying(1),
    ap_del character varying(1)
);


ALTER TABLE taman.app_priv_front OWNER TO armasi_wmm;

--
-- Name: app_user; Type: TABLE; Schema: taman; Owner: armasi_wmm
--

CREATE TABLE taman.app_user (
    user_id integer NOT NULL,
    user_name character varying(20),
    first_name character varying(20),
    last_name character varying(20),
    user_pass character varying(100),
    created_by character varying(20),
    created_on timestamp without time zone,
    modified_by character varying(20),
    modified_on timestamp without time zone
);


ALTER TABLE taman.app_user OWNER TO armasi_wmm;

--
-- Name: app_user_front; Type: TABLE; Schema: taman; Owner: armasi_wmm
--

CREATE TABLE taman.app_user_front (
    user_id integer NOT NULL,
    user_name character varying(20),
    first_name character varying(20),
    last_name character varying(20),
    user_pass character varying(100),
    user_dept character varying(10)[],
    user_lokasi character varying(10)[],
    created_by character varying(20),
    created_on timestamp without time zone,
    modified_by character varying(20),
    modified_on timestamp without time zone,
    user_subplant character varying(1)[],
    leader_name character varying(20)
);


ALTER TABLE taman.app_user_front OWNER TO armasi_wmm;

--
-- Name: app_user_front_user_id_seq; Type: SEQUENCE; Schema: taman; Owner: armasi_wmm
--

CREATE SEQUENCE taman.app_user_front_user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE taman.app_user_front_user_id_seq OWNER TO armasi_wmm;

--
-- Name: app_user_front_user_id_seq; Type: SEQUENCE OWNED BY; Schema: taman; Owner: armasi_wmm
--

ALTER SEQUENCE taman.app_user_front_user_id_seq OWNED BY taman.app_user_front.user_id;


--
-- Name: app_user_user_id_seq; Type: SEQUENCE; Schema: taman; Owner: armasi_wmm
--

CREATE SEQUENCE taman.app_user_user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE taman.app_user_user_id_seq OWNER TO armasi_wmm;

--
-- Name: app_user_user_id_seq; Type: SEQUENCE OWNED BY; Schema: taman; Owner: armasi_wmm
--

ALTER SEQUENCE taman.app_user_user_id_seq OWNED BY taman.app_user.user_id;


--
-- Name: md_lokasi; Type: TABLE; Schema: taman; Owner: armasi_wmm
--

CREATE TABLE taman.md_lokasi (
    lokasi_id character varying(10) NOT NULL,
    lokasi_nama character varying(250) NOT NULL,
    lokasi_desc character varying(250),
    lokasi_status character varying(1),
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone
);


ALTER TABLE taman.md_lokasi OWNER TO armasi_wmm;

--
-- Name: md_rincian_kerja_rincian_id_seq; Type: SEQUENCE; Schema: taman; Owner: armasi_wmm
--

CREATE SEQUENCE taman.md_rincian_kerja_rincian_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    MAXVALUE 2147483647
    CACHE 1;


ALTER TABLE taman.md_rincian_kerja_rincian_id_seq OWNER TO armasi_wmm;

--
-- Name: md_rincian_kerja; Type: TABLE; Schema: taman; Owner: armasi_wmm
--

CREATE TABLE taman.md_rincian_kerja (
    rincian_id integer DEFAULT nextval('taman.md_rincian_kerja_rincian_id_seq'::regclass) NOT NULL,
    rincian_nama character varying(350) NOT NULL,
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone
);


ALTER TABLE taman.md_rincian_kerja OWNER TO armasi_wmm;

--
-- Name: md_subplant; Type: TABLE; Schema: taman; Owner: armasi_wmm
--

CREATE TABLE taman.md_subplant (
    subplant character varying(1) NOT NULL,
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone
);


ALTER TABLE taman.md_subplant OWNER TO armasi_wmm;

--
-- Name: tb_kerja_detail; Type: TABLE; Schema: taman; Owner: armasi_taman
--

CREATE TABLE taman.tb_kerja_detail (
    rk_id character varying(12) NOT NULL,
    rkd_id integer NOT NULL,
    rincian_id integer NOT NULL,
    rkd_jam character varying(5),
    rkd_sebelum character varying(500),
    rkd_sesudah character varying(500)
);


ALTER TABLE taman.tb_kerja_detail OWNER TO armasi_taman;

--
-- Name: tb_kerja_header; Type: TABLE; Schema: taman; Owner: armasi_taman
--

CREATE TABLE taman.tb_kerja_header (
    rk_id character varying(12) NOT NULL,
    rk_date date,
    rk_pic character varying(20),
    rk_lokasi character varying(10)[],
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone,
    rk_status character varying(1)
);


ALTER TABLE taman.tb_kerja_header OWNER TO armasi_taman;

--
-- Name: app_menu; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.app_menu (
    am_id integer NOT NULL,
    am_label character varying(50),
    am_link text,
    am_parent integer,
    am_sort smallint,
    am_class character varying(100),
    am_level smallint,
    am_nama text,
    am_status character varying(1)
);


ALTER TABLE wmm.app_menu OWNER TO armasi_wmm;

--
-- Name: app_menu_am_id_seq; Type: SEQUENCE; Schema: wmm; Owner: armasi_wmm
--

CREATE SEQUENCE wmm.app_menu_am_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE wmm.app_menu_am_id_seq OWNER TO armasi_wmm;

--
-- Name: app_menu_am_id_seq; Type: SEQUENCE OWNED BY; Schema: wmm; Owner: armasi_wmm
--

ALTER SEQUENCE wmm.app_menu_am_id_seq OWNED BY wmm.app_menu.am_id;


--
-- Name: app_menu_front; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.app_menu_front (
    am_id integer NOT NULL,
    am_label character varying(50),
    am_link text,
    am_parent integer,
    am_sort smallint,
    am_class character varying(100),
    am_level smallint,
    am_nama text,
    am_status character varying(1)
);


ALTER TABLE wmm.app_menu_front OWNER TO armasi_wmm;

--
-- Name: app_menu_front_am_id_seq; Type: SEQUENCE; Schema: wmm; Owner: armasi_wmm
--

CREATE SEQUENCE wmm.app_menu_front_am_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE wmm.app_menu_front_am_id_seq OWNER TO armasi_wmm;

--
-- Name: app_menu_front_am_id_seq; Type: SEQUENCE OWNED BY; Schema: wmm; Owner: armasi_wmm
--

ALTER SEQUENCE wmm.app_menu_front_am_id_seq OWNED BY wmm.app_menu_front.am_id;


--
-- Name: app_priv; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.app_priv (
    user_id integer NOT NULL,
    menu_id integer NOT NULL,
    ap_view character varying(1),
    ap_add character varying(1),
    ap_edit character varying(1),
    ap_del character varying(1),
    ap_print character varying(1),
    ap_approve character varying(1)
);


ALTER TABLE wmm.app_priv OWNER TO armasi_wmm;

--
-- Name: app_priv_front; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.app_priv_front (
    user_id integer NOT NULL,
    menu_id integer NOT NULL,
    ap_view character varying(1),
    ap_add character varying(1),
    ap_edit character varying(1),
    ap_del character varying(1)
);


ALTER TABLE wmm.app_priv_front OWNER TO armasi_wmm;

--
-- Name: app_user; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.app_user (
    user_id integer NOT NULL,
    user_name character varying(20),
    first_name character varying(20),
    last_name character varying(20),
    user_pass character varying(100),
    created_by character varying(20),
    created_on timestamp without time zone,
    modified_by character varying(20),
    modified_on timestamp without time zone
);


ALTER TABLE wmm.app_user OWNER TO armasi_wmm;

--
-- Name: app_user_front; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.app_user_front (
    user_id integer NOT NULL,
    user_name character varying(20),
    first_name character varying(20),
    last_name character varying(20),
    user_pass character varying(100),
    user_dept character varying(10)[],
    user_lokasi character varying(10)[],
    created_by character varying(20),
    created_on timestamp without time zone,
    modified_by character varying(20),
    modified_on timestamp without time zone,
    user_subplant character varying(1)[]
);


ALTER TABLE wmm.app_user_front OWNER TO armasi_wmm;

--
-- Name: app_user_front_user_id_seq; Type: SEQUENCE; Schema: wmm; Owner: armasi_wmm
--

CREATE SEQUENCE wmm.app_user_front_user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE wmm.app_user_front_user_id_seq OWNER TO armasi_wmm;

--
-- Name: app_user_front_user_id_seq; Type: SEQUENCE OWNED BY; Schema: wmm; Owner: armasi_wmm
--

ALTER SEQUENCE wmm.app_user_front_user_id_seq OWNED BY wmm.app_user_front.user_id;


--
-- Name: app_user_user_id_seq; Type: SEQUENCE; Schema: wmm; Owner: armasi_wmm
--

CREATE SEQUENCE wmm.app_user_user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE wmm.app_user_user_id_seq OWNER TO armasi_wmm;

--
-- Name: app_user_user_id_seq; Type: SEQUENCE OWNED BY; Schema: wmm; Owner: armasi_wmm
--

ALTER SEQUENCE wmm.app_user_user_id_seq OWNED BY wmm.app_user.user_id;


--
-- Name: md_departemen; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.md_departemen (
    dept_id character varying(50) NOT NULL,
    dept_nama character varying(250) NOT NULL,
    dept_status character varying(1) NOT NULL,
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone
);


ALTER TABLE wmm.md_departemen OWNER TO armasi_wmm;

--
-- Name: md_jenis_temuan; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.md_jenis_temuan (
    mdt_id character varying(30) NOT NULL,
    mdt_nama character varying(250) NOT NULL,
    mdt_waktu integer NOT NULL,
    mdt_penalti_awal integer NOT NULL,
    mdt_penalti_lanjutan integer NOT NULL,
    mdt_desc text,
    mdt_status character varying(1),
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone
);


ALTER TABLE wmm.md_jenis_temuan OWNER TO armasi_wmm;

--
-- Name: md_kategori; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.md_kategori (
    kat_kode character varying(50) NOT NULL,
    kat_nama character varying(225),
    kat_keterangan text,
    kat_status character varying(1),
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone
);


ALTER TABLE wmm.md_kategori OWNER TO armasi_wmm;

--
-- Name: md_lokasi; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.md_lokasi (
    lokasi_id character varying(10) NOT NULL,
    lokasi_nama character varying(250) NOT NULL,
    lokasi_desc character varying(250),
    lokasi_status character varying(1),
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone
);


ALTER TABLE wmm.md_lokasi OWNER TO armasi_wmm;

--
-- Name: md_status; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.md_status (
    status_id character varying(12) NOT NULL,
    status_nama character varying(200) NOT NULL,
    status_order smallint
);


ALTER TABLE wmm.md_status OWNER TO armasi_wmm;

--
-- Name: md_sub_kategori; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.md_sub_kategori (
    subkat_id character varying(10) NOT NULL,
    subkat_nama character varying(250) NOT NULL,
    subkat_desc character varying(250),
    subkat_status character varying(1),
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone
);


ALTER TABLE wmm.md_sub_kategori OWNER TO armasi_wmm;

--
-- Name: md_subplant; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.md_subplant (
    subplant character varying(1) NOT NULL,
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone
);


ALTER TABLE wmm.md_subplant OWNER TO armasi_wmm;

--
-- Name: tb_document; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.tb_document (
    doc_id integer NOT NULL,
    doc_kat character varying(20) NOT NULL,
    doc_subkat character varying(20) NOT NULL,
    doc_dept character varying(20) NOT NULL,
    doc_nama text NOT NULL,
    doc_desc text,
    doc_urut smallint,
    doc_status character varying(1),
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone
);


ALTER TABLE wmm.tb_document OWNER TO armasi_wmm;

--
-- Name: tb_document_detail; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.tb_document_detail (
    file_id integer NOT NULL,
    doc_id integer NOT NULL,
    file_revisi integer NOT NULL,
    file_nama text,
    file_status character varying(1),
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone,
    tgl_upload timestamp without time zone,
    tgl_terbit date
);


ALTER TABLE wmm.tb_document_detail OWNER TO armasi_wmm;

--
-- Name: tb_document_detail_file_id_seq; Type: SEQUENCE; Schema: wmm; Owner: armasi_wmm
--

CREATE SEQUENCE wmm.tb_document_detail_file_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE wmm.tb_document_detail_file_id_seq OWNER TO armasi_wmm;

--
-- Name: tb_document_detail_file_id_seq; Type: SEQUENCE OWNED BY; Schema: wmm; Owner: armasi_wmm
--

ALTER SEQUENCE wmm.tb_document_detail_file_id_seq OWNED BY wmm.tb_document_detail.file_id;


--
-- Name: tb_document_doc_id_seq; Type: SEQUENCE; Schema: wmm; Owner: armasi_wmm
--

CREATE SEQUENCE wmm.tb_document_doc_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE wmm.tb_document_doc_id_seq OWNER TO armasi_wmm;

--
-- Name: tb_document_doc_id_seq; Type: SEQUENCE OWNED BY; Schema: wmm; Owner: armasi_wmm
--

ALTER SEQUENCE wmm.tb_document_doc_id_seq OWNED BY wmm.tb_document.doc_id;


--
-- Name: tb_penalti_user; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.tb_penalti_user (
    temuan_id character varying(15) NOT NULL,
    user_name character varying(20) NOT NULL
);


ALTER TABLE wmm.tb_penalti_user OWNER TO armasi_wmm;

--
-- Name: tb_tanggapan; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.tb_tanggapan (
    tem_id character varying(12) NOT NULL,
    tgp_id character varying(12) NOT NULL,
    tgp_date timestamp without time zone,
    tgp_desc text,
    tgp_validasi_sts character varying(2),
    tgp_validasi_date timestamp without time zone,
    tgp_validasi_note text,
    tgp_validasi_user character varying(20),
    tgp_status character varying(1),
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone
);


ALTER TABLE wmm.tb_tanggapan OWNER TO armasi_wmm;

--
-- Name: tb_temuan_detail; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.tb_temuan_detail (
    h_id character varying(15) NOT NULL,
    d_id smallint NOT NULL,
    nama_file text
);


ALTER TABLE wmm.tb_temuan_detail OWNER TO armasi_wmm;

--
-- Name: tb_temuan_header; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.tb_temuan_header (
    tem_id character varying(12) NOT NULL,
    tem_date timestamp without time zone,
    tem_dept character varying(10) NOT NULL,
    tem_jenis character varying(10) NOT NULL,
    tem_waktu integer,
    tem_poin_awal integer,
    tem_poin_akhir integer,
    tem_desc text,
    tem_lokasi text,
    tem_validasi_sts character varying(2),
    tem_validasi_date timestamp without time zone,
    tem_validasi_user character varying(20),
    tem_status character varying(1),
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone,
    tem_subplant character varying(1)
);


ALTER TABLE wmm.tb_temuan_header OWNER TO armasi_wmm;

--
-- Name: tb_user_poin; Type: TABLE; Schema: wmm; Owner: armasi_wmm
--

CREATE TABLE wmm.tb_user_poin (
    user_name character varying(30) NOT NULL,
    tahun integer NOT NULL,
    poin_awal integer NOT NULL,
    created_by character varying(100),
    created_on timestamp without time zone,
    modified_by character varying(100),
    modified_on timestamp without time zone
);


ALTER TABLE wmm.tb_user_poin OWNER TO armasi_wmm;

--
-- Name: bd_mc_param bmp_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bd_mc_param ALTER COLUMN bmp_id SET DEFAULT nextval('public.bd_mc_param_bmp_id_seq'::regclass);


--
-- Name: bd_qc_mc bqm_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bd_qc_mc ALTER COLUMN bqm_id SET DEFAULT nextval('public.bd_qc_mc_bqm_id_seq'::regclass);


--
-- Name: gl_qc_app gqa_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gl_qc_app ALTER COLUMN gqa_id SET DEFAULT nextval('public.gl_qc_app_gqa_id_seq'::regclass);


--
-- Name: gl_qc_param gqp_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gl_qc_param ALTER COLUMN gqp_id SET DEFAULT nextval('public.gl_qc_param_gqp_id_seq'::regclass);


--
-- Name: pallet_event_types id; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.pallet_event_types ALTER COLUMN id SET DEFAULT nextval('public.pallet_event_types_id_seq'::regclass);


--
-- Name: tbl_ba_muat_detail detail_ba_id; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_ba_muat_detail ALTER COLUMN detail_ba_id SET DEFAULT nextval('public.tbl_ba_muat_detail_detail_ba_id_seq'::regclass);


--
-- Name: tbl_detail_surat_jalan detail_surat_jalan_id; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_detail_surat_jalan ALTER COLUMN detail_surat_jalan_id SET DEFAULT nextval('public.tbl_detail_surat_jalan_detail_surat_jalan_id_seq'::regclass);


--
-- Name: tbl_feature feature_id; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_feature ALTER COLUMN feature_id SET DEFAULT nextval('public.tbl_feature_feature_id_seq'::regclass);


--
-- Name: tbl_iso iso_id; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_iso ALTER COLUMN iso_id SET DEFAULT nextval('public.tbl_iso_iso_id_seq'::regclass);


--
-- Name: tbl_item_booking id; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_item_booking ALTER COLUMN id SET DEFAULT nextval('public.tbl_item_booking_id_seq'::regclass);


--
-- Name: tbl_kode kode_id; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_kode ALTER COLUMN kode_id SET DEFAULT nextval('public.tbl_kode_kode_id_seq'::regclass);


--
-- Name: tbl_lgc_gbj_detail id_detail; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_lgc_gbj_detail ALTER COLUMN id_detail SET DEFAULT nextval('public.tbl_lgc_gbj_detail_id_detail_seq'::regclass);


--
-- Name: tbl_lgc_gbj_header id; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_lgc_gbj_header ALTER COLUMN id SET DEFAULT nextval('public.tbl_lgc_gbj_header_id_seq'::regclass);


--
-- Name: tbl_satuan satuan_id; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_satuan ALTER COLUMN satuan_id SET DEFAULT nextval('public.tbl_satuan_satuan_id_seq'::regclass);


--
-- Name: tbl_sp_ket_dg_pallet id_ket_pallet; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sp_ket_dg_pallet ALTER COLUMN id_ket_pallet SET DEFAULT nextval('public.tbl_sp_ket_dg_pallet_id_ket_pallet_seq'::regclass);


--
-- Name: tbl_stock_bulanan seq_key; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_stock_bulanan ALTER COLUMN seq_key SET DEFAULT nextval('public.tbl_stock_bulanan_seq_key_seq'::regclass);


--
-- Name: tbl_sub_feature sub_f_id; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sub_feature ALTER COLUMN sub_f_id SET DEFAULT nextval('public.tbl_sub_feature_sub_f_id_seq'::regclass);


--
-- Name: tbl_tarif_angkutan tarif_id; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_tarif_angkutan ALTER COLUMN tarif_id SET DEFAULT nextval('public.tbl_tarif_angkutan_tarif_id_seq'::regclass);


--
-- Name: tbl_user user_id; Type: DEFAULT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_user ALTER COLUMN user_id SET DEFAULT nextval('public.tbl_user_user_id_seq'::regclass);


--
-- Name: qc_ic_parameter pm_id; Type: DEFAULT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_ic_parameter ALTER COLUMN pm_id SET DEFAULT nextval('qc.qc_ic_parameter_pm_id_seq'::regclass);


--
-- Name: app_menu am_id; Type: DEFAULT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.app_menu ALTER COLUMN am_id SET DEFAULT nextval('taman.app_menu_am_id_seq'::regclass);


--
-- Name: app_menu_front am_id; Type: DEFAULT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.app_menu_front ALTER COLUMN am_id SET DEFAULT nextval('taman.app_menu_front_am_id_seq'::regclass);


--
-- Name: app_user user_id; Type: DEFAULT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.app_user ALTER COLUMN user_id SET DEFAULT nextval('taman.app_user_user_id_seq'::regclass);


--
-- Name: app_user_front user_id; Type: DEFAULT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.app_user_front ALTER COLUMN user_id SET DEFAULT nextval('taman.app_user_front_user_id_seq'::regclass);


--
-- Name: app_menu am_id; Type: DEFAULT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.app_menu ALTER COLUMN am_id SET DEFAULT nextval('wmm.app_menu_am_id_seq'::regclass);


--
-- Name: app_menu_front am_id; Type: DEFAULT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.app_menu_front ALTER COLUMN am_id SET DEFAULT nextval('wmm.app_menu_front_am_id_seq'::regclass);


--
-- Name: app_user user_id; Type: DEFAULT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.app_user ALTER COLUMN user_id SET DEFAULT nextval('wmm.app_user_user_id_seq'::regclass);


--
-- Name: app_user_front user_id; Type: DEFAULT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.app_user_front ALTER COLUMN user_id SET DEFAULT nextval('wmm.app_user_front_user_id_seq'::regclass);


--
-- Name: tb_document doc_id; Type: DEFAULT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.tb_document ALTER COLUMN doc_id SET DEFAULT nextval('wmm.tb_document_doc_id_seq'::regclass);


--
-- Name: tb_document_detail file_id; Type: DEFAULT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.tb_document_detail ALTER COLUMN file_id SET DEFAULT nextval('wmm.tb_document_detail_file_id_seq'::regclass);


--
-- Name: item item_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.item
    ADD CONSTRAINT item_pkey PRIMARY KEY (item_kode);


--
-- Name: rimpil_by_motif_size_shading; Type: MATERIALIZED VIEW; Schema: public; Owner: armasi
--

CREATE MATERIALIZED VIEW public.rimpil_by_motif_size_shading AS
 SELECT t2.production_subplant,
    t2.motif_id,
    t2.motif_dimension,
    t2.motif_name,
    t2.quality,
    t2.size,
    t2.shading,
        CASE
            WHEN ((t2.size)::text = 'KK'::text) THEN true
            WHEN ((t2.current_quantity)::numeric < (100)::numeric) THEN true
            ELSE false
        END AS is_rimpil
   FROM ( SELECT hasilbj.subplant AS production_subplant,
            category.category_nama AS motif_dimension,
            item.item_kode AS motif_id,
            item.item_nama AS motif_name,
            item.quality,
            hasilbj.size,
            hasilbj.shade AS shading,
            category.jumlah_m2 AS single_pallet_quantity,
            sum(hasilbj.last_qty) AS current_quantity
           FROM ((public.tbl_sp_hasilbj hasilbj
             JOIN public.item ON (((hasilbj.item_kode)::text = (item.item_kode)::text)))
             JOIN public.category ON ((substr((item.item_kode)::text, 1, 2) = (category.category_kode)::text)))
          WHERE ((hasilbj.last_qty > 0) AND ((hasilbj.status_plt)::text = 'R'::text))
          GROUP BY hasilbj.subplant, category.category_nama, item.item_kode, item.quality, hasilbj.size, hasilbj.shade, category.jumlah_m2) t2
  WITH NO DATA;


ALTER TABLE public.rimpil_by_motif_size_shading OWNER TO armasi;

--
-- Name: pallets_with_location_age_and_rimpil; Type: MATERIALIZED VIEW; Schema: public; Owner: armasi
--

CREATE MATERIALIZED VIEW public.pallets_with_location_age_and_rimpil AS
 SELECT pallets.location_subplant,
    pallets.location_area_name,
    pallets.location_area_no,
    pallets.location_row_no AS location_line_no,
    pallets.location_id,
    pallets.pallet_no,
        CASE
            WHEN ((pallets.subplant)::text = '4'::text) THEN '4A'::character varying
            WHEN ((pallets.subplant)::text = '5'::text) THEN '5A'::character varying
            ELSE pallets.subplant
        END AS production_subplant,
    pallets.item_kode AS motif_id,
    t3.motif_dimension,
    t3.motif_name,
    t3.quality,
    pallets.size,
    pallets.shade AS shading,
    pallets.create_date AS creation_date,
    pallets.regu AS creator_group,
    pallets.shift AS creator_shift,
    pallets.line,
    pallets.last_qty AS current_quantity,
    (CURRENT_DATE - pallets.create_date) AS pallet_age,
        CASE
            WHEN ((CURRENT_DATE - pallets.create_date) <= 5) THEN 'Very Fast'::text
            WHEN (((CURRENT_DATE - pallets.create_date) > 5) AND ((CURRENT_DATE - pallets.create_date) <= 60)) THEN 'Fast'::text
            WHEN (((CURRENT_DATE - pallets.create_date) > 60) AND ((CURRENT_DATE - pallets.create_date) <= 270)) THEN 'Medium'::text
            WHEN (((CURRENT_DATE - pallets.create_date) > 270) AND ((CURRENT_DATE - pallets.create_date) <= 360)) THEN 'Slow'::text
            ELSE 'Dead Stock'::text
        END AS pallet_age_category,
        CASE
            WHEN ((CURRENT_DATE - pallets.create_date) <= 30) THEN 'A. 1-30 days'::text
            WHEN (((CURRENT_DATE - pallets.create_date) >= 31) AND ((CURRENT_DATE - pallets.create_date) <= 60)) THEN 'B. 31-60 days'::text
            WHEN (((CURRENT_DATE - pallets.create_date) >= 61) AND ((CURRENT_DATE - pallets.create_date) <= 90)) THEN 'C. 61-90 days'::text
            WHEN (((CURRENT_DATE - pallets.create_date) >= 91) AND ((CURRENT_DATE - pallets.create_date) <= 120)) THEN 'D. 91-120 days'::text
            WHEN (((CURRENT_DATE - pallets.create_date) >= 121) AND ((CURRENT_DATE - pallets.create_date) <= 150)) THEN 'E. 121-150 days'::text
            WHEN (((CURRENT_DATE - pallets.create_date) >= 151) AND ((CURRENT_DATE - pallets.create_date) <= 180)) THEN 'F. 151-180 days'::text
            WHEN (((CURRENT_DATE - pallets.create_date) >= 181) AND ((CURRENT_DATE - pallets.create_date) <= 210)) THEN 'G. 181-210 days'::text
            WHEN (((CURRENT_DATE - pallets.create_date) >= 211) AND ((CURRENT_DATE - pallets.create_date) <= 240)) THEN 'H. 211-240 days'::text
            ELSE 'I. >240 days'::text
        END AS pallet_month_category,
    t3.is_rimpil,
    pallets.is_blocked,
    pallets.status_plt AS pallet_status
   FROM (( SELECT pallets_with_location.plan_kode,
            pallets_with_location.seq_no,
            pallets_with_location.pallet_no,
            pallets_with_location.tanggal,
            pallets_with_location.item_kode,
            pallets_with_location.quality,
            pallets_with_location.subplant,
            pallets_with_location.shade,
            pallets_with_location.size,
            pallets_with_location.qty,
            pallets_with_location.create_date,
            pallets_with_location.create_user,
            pallets_with_location.status_plt,
            pallets_with_location.rkpterima_no,
            pallets_with_location.rkpterima_tanggal,
            pallets_with_location.rkpterima_user,
            pallets_with_location.terima_no,
            pallets_with_location.tanggal_terima,
            pallets_with_location.terima_user,
            pallets_with_location.status_item,
            pallets_with_location.txn_no,
            pallets_with_location.shift,
            pallets_with_location.last_qty,
            pallets_with_location.line,
            pallets_with_location.regu,
            pallets_with_location.plt_status,
            pallets_with_location.keterangan,
            pallets_with_location.kd_customer,
            pallets_with_location.tanggal_pending,
            pallets_with_location.last_update,
            pallets_with_location.update_tran,
            pallets_with_location.update_tran_user,
            pallets_with_location.upload_date,
            pallets_with_location.upload_user,
            pallets_with_location.status_transfer,
            pallets_with_location.status_tran,
            pallets_with_location.area,
            pallets_with_location.lokasi,
            pallets_with_location.qa_approved,
            pallets_with_location.location_subplant,
            pallets_with_location.location_no,
            pallets_with_location.location_since,
            pallets_with_location.location_area_no,
            pallets_with_location.location_area_name,
            pallets_with_location.location_row_no,
            pallets_with_location.location_id,
            ((pallets_with_location.status_plt)::text = 'B'::text) AS is_blocked
           FROM public.pallets_with_location
          WHERE ((pallets_with_location.last_qty > 0) AND ((pallets_with_location.status_plt)::text = ANY (ARRAY[('R'::character varying)::text, ('B'::character varying)::text, ('K'::character varying)::text])))) pallets
     JOIN public.rimpil_by_motif_size_shading t3 ON ((((
        CASE
            WHEN ((pallets.subplant)::text = '4'::text) THEN '4A'::character varying
            WHEN ((pallets.subplant)::text = '5'::text) THEN '5A'::character varying
            ELSE pallets.subplant
        END)::text = (t3.production_subplant)::text) AND ((pallets.item_kode)::text = (t3.motif_id)::text) AND ((pallets.size)::text = (t3.size)::text) AND ((pallets.shade)::text = (t3.shading)::text))))
  ORDER BY pallets.pallet_no
  WITH NO DATA;


ALTER TABLE public.pallets_with_location_age_and_rimpil OWNER TO armasi;

--
-- Name: pallets_without_location_age_and_rimpil; Type: MATERIALIZED VIEW; Schema: public; Owner: armasi
--

CREATE MATERIALIZED VIEW public.pallets_without_location_age_and_rimpil AS
 SELECT pallets.pallet_no,
        CASE
            WHEN ((pallets.subplant)::text = '4'::text) THEN '4A'::character varying
            WHEN ((pallets.subplant)::text = '5'::text) THEN '5A'::character varying
            ELSE pallets.subplant
        END AS production_subplant,
    pallets.item_kode AS motif_id,
    t3.motif_dimension,
    t3.motif_name,
    t3.quality,
    pallets.size,
    pallets.shade AS shading,
    pallets.create_date AS creation_date,
    pallets.regu AS creator_group,
    pallets.shift AS creator_shift,
    pallets.line,
    pallets.last_qty AS current_quantity,
    (CURRENT_DATE - pallets.create_date) AS pallet_age,
        CASE
            WHEN ((CURRENT_DATE - pallets.create_date) <= 5) THEN 'Very Fast'::text
            WHEN (((CURRENT_DATE - pallets.create_date) > 5) AND ((CURRENT_DATE - pallets.create_date) <= 60)) THEN 'Fast'::text
            WHEN (((CURRENT_DATE - pallets.create_date) > 60) AND ((CURRENT_DATE - pallets.create_date) <= 270)) THEN 'Medium'::text
            WHEN (((CURRENT_DATE - pallets.create_date) > 270) AND ((CURRENT_DATE - pallets.create_date) <= 360)) THEN 'Slow'::text
            ELSE 'Dead Stock'::text
        END AS pallet_age_category,
        CASE
            WHEN ((CURRENT_DATE - pallets.create_date) <= 30) THEN '1 - 30'::text
            WHEN (((CURRENT_DATE - pallets.create_date) >= 31) AND ((CURRENT_DATE - pallets.create_date) <= 60)) THEN '31 - 60'::text
            WHEN (((CURRENT_DATE - pallets.create_date) >= 61) AND ((CURRENT_DATE - pallets.create_date) <= 90)) THEN '61 - 90'::text
            WHEN (((CURRENT_DATE - pallets.create_date) >= 91) AND ((CURRENT_DATE - pallets.create_date) <= 120)) THEN '91 - 90'::text
            ELSE '> 120'::text
        END AS pallet_month_category,
    t3.is_rimpil,
    pallets.is_blocked,
    pallets.status_plt AS pallet_status
   FROM (( SELECT tbl_sp_hasilbj.plan_kode,
            tbl_sp_hasilbj.seq_no,
            tbl_sp_hasilbj.pallet_no,
            tbl_sp_hasilbj.tanggal,
            tbl_sp_hasilbj.item_kode,
            tbl_sp_hasilbj.quality,
            tbl_sp_hasilbj.subplant,
            tbl_sp_hasilbj.shade,
            tbl_sp_hasilbj.size,
            tbl_sp_hasilbj.qty,
            tbl_sp_hasilbj.create_date,
            tbl_sp_hasilbj.create_user,
            tbl_sp_hasilbj.status_plt,
            tbl_sp_hasilbj.rkpterima_no,
            tbl_sp_hasilbj.rkpterima_tanggal,
            tbl_sp_hasilbj.rkpterima_user,
            tbl_sp_hasilbj.terima_no,
            tbl_sp_hasilbj.tanggal_terima,
            tbl_sp_hasilbj.terima_user,
            tbl_sp_hasilbj.status_item,
            tbl_sp_hasilbj.txn_no,
            tbl_sp_hasilbj.shift,
            tbl_sp_hasilbj.last_qty,
            tbl_sp_hasilbj.line,
            tbl_sp_hasilbj.regu,
            tbl_sp_hasilbj.plt_status,
            tbl_sp_hasilbj.keterangan,
            tbl_sp_hasilbj.kd_customer,
            tbl_sp_hasilbj.tanggal_pending,
            tbl_sp_hasilbj.last_update,
            tbl_sp_hasilbj.update_tran,
            tbl_sp_hasilbj.update_tran_user,
            tbl_sp_hasilbj.upload_date,
            tbl_sp_hasilbj.upload_user,
            tbl_sp_hasilbj.status_transfer,
            tbl_sp_hasilbj.status_tran,
            tbl_sp_hasilbj.area,
            tbl_sp_hasilbj.lokasi,
            tbl_sp_hasilbj.qa_approved,
            tbl_sp_hasilbj.block_ref_id,
            ((tbl_sp_hasilbj.status_plt)::text = 'B'::text) AS is_blocked
           FROM public.tbl_sp_hasilbj
          WHERE ((tbl_sp_hasilbj.last_qty > 0) AND ((tbl_sp_hasilbj.status_plt)::text = ANY (ARRAY[('R'::character varying)::text, ('B'::character varying)::text, ('K'::character varying)::text])))) pallets
     JOIN public.rimpil_by_motif_size_shading t3 ON ((((
        CASE
            WHEN ((pallets.subplant)::text = '4'::text) THEN '4A'::character varying
            WHEN ((pallets.subplant)::text = '5'::text) THEN '5A'::character varying
            ELSE pallets.subplant
        END)::text = (t3.production_subplant)::text) AND ((pallets.item_kode)::text = (t3.motif_id)::text) AND ((pallets.size)::text = (t3.size)::text) AND ((pallets.shade)::text = (t3.shading)::text))))
  WHERE (NOT (EXISTS ( SELECT inv_opname.io_plan_kode,
            inv_opname.io_kd_lok,
            inv_opname.io_no_pallet,
            inv_opname.io_qty_pallet,
            inv_opname.io_tgl
           FROM public.inv_opname
          WHERE ((inv_opname.io_no_pallet)::text = (pallets.pallet_no)::text))))
  ORDER BY pallets.pallet_no
  WITH NO DATA;


ALTER TABLE public.pallets_without_location_age_and_rimpil OWNER TO armasi;

--
-- Name: inactive_armasi_users inactive_armasi_users_pkey; Type: CONSTRAINT; Schema: audit; Owner: armasi
--

ALTER TABLE ONLY audit.inactive_armasi_users
    ADD CONSTRAINT inactive_armasi_users_pkey PRIMARY KEY (year, month, username);


--
-- Name: user_login user_login_pkey; Type: CONSTRAINT; Schema: audit; Owner: armasi
--

ALTER TABLE ONLY audit.user_login
    ADD CONSTRAINT user_login_pkey PRIMARY KEY (username, event_time);


--
-- Name: meta_mv_refresh meta_mv_refresh_pkey; Type: CONSTRAINT; Schema: db_maintenance; Owner: armasi
--

ALTER TABLE ONLY db_maintenance.meta_mv_refresh
    ADD CONSTRAINT meta_mv_refresh_pkey PRIMARY KEY (mv_name);


--
-- Name: mutation_records mutation_records_pkey; Type: CONSTRAINT; Schema: gbj_report; Owner: armasi
--

ALTER TABLE ONLY gbj_report.mutation_records
    ADD CONSTRAINT mutation_records_pkey PRIMARY KEY (pallet_no, mutation_type, mutation_id, mutation_time);


--
-- Name: app_user app_user_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.app_user
    ADD CONSTRAINT app_user_pkey PRIMARY KEY (user_id);


--
-- Name: app_user app_user_user_name_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.app_user
    ADD CONSTRAINT app_user_user_name_key UNIQUE (user_name);


--
-- Name: assets_master_main assets_master_main_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.assets_master_main
    ADD CONSTRAINT assets_master_main_key PRIMARY KEY (amm_code);


--
-- Name: assets_master_main assets_master_main_unik; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.assets_master_main
    ADD CONSTRAINT assets_master_main_unik UNIQUE (amm_desc);


--
-- Name: assets_master_maintenance_detail assets_master_maintenance_detail_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.assets_master_maintenance_detail
    ADD CONSTRAINT assets_master_maintenance_detail_key PRIMARY KEY (amm_code, amms_code, item_code);


--
-- Name: assets_master_maintenance assets_master_maintenance_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.assets_master_maintenance
    ADD CONSTRAINT assets_master_maintenance_key PRIMARY KEY (amm_code, amms_code);


--
-- Name: assets_master_part assets_master_part_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.assets_master_part
    ADD CONSTRAINT assets_master_part_key PRIMARY KEY (amp_code, amp_part);


--
-- Name: assets_master_sparepart assets_master_sparepart_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.assets_master_sparepart
    ADD CONSTRAINT assets_master_sparepart_key PRIMARY KEY (amsp_code, amsp_sparepart_code);


--
-- Name: assets_master_sparepart_old assets_master_sparepart_old_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.assets_master_sparepart_old
    ADD CONSTRAINT assets_master_sparepart_old_key PRIMARY KEY (amsp_code, amsp_sparepart_code);


--
-- Name: assets_master_spesification assets_master_spesification_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.assets_master_spesification
    ADD CONSTRAINT assets_master_spesification_key PRIMARY KEY (ams_code);


--
-- Name: sett_assets_category sett_assets_category_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.sett_assets_category
    ADD CONSTRAINT sett_assets_category_key PRIMARY KEY (sac_code);


--
-- Name: sett_assets_group sett_assets_group_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.sett_assets_group
    ADD CONSTRAINT sett_assets_group_key PRIMARY KEY (sag_code);


--
-- Name: sett_ceklist_asset sett_ceklist_asset_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.sett_ceklist_asset
    ADD CONSTRAINT sett_ceklist_asset_pkey PRIMARY KEY (ceklist_code, asset_code);


--
-- Name: sett_ceklist_detail sett_ceklist_detail_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.sett_ceklist_detail
    ADD CONSTRAINT sett_ceklist_detail_pkey PRIMARY KEY (ceklist_code, cd_code);


--
-- Name: sett_ceklist sett_ceklist_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.sett_ceklist
    ADD CONSTRAINT sett_ceklist_pkey PRIMARY KEY (ceklist_code);


--
-- Name: sett_ceklist sett_ceklist_uniq; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.sett_ceklist
    ADD CONSTRAINT sett_ceklist_uniq UNIQUE (ceklist_name);


--
-- Name: sett_employee sett_employee_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.sett_employee
    ADD CONSTRAINT sett_employee_key PRIMARY KEY (se_code);


--
-- Name: sett_location sett_location_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.sett_location
    ADD CONSTRAINT sett_location_key PRIMARY KEY (sl_code);


--
-- Name: sett_maintenance_type sett_maintenance_type_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.sett_maintenance_type
    ADD CONSTRAINT sett_maintenance_type_key PRIMARY KEY (smt_work_type, smt_code);


--
-- Name: sett_manufacture sett_manufacture_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.sett_manufacture
    ADD CONSTRAINT sett_manufacture_key PRIMARY KEY (sm_code);


--
-- Name: sett_sub_location sett_sub_location_key; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.sett_sub_location
    ADD CONSTRAINT sett_sub_location_key PRIMARY KEY (ssl_location_code, ssl_code);


--
-- Name: sparepart_temp sparepart_temp_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.sparepart_temp
    ADD CONSTRAINT sparepart_temp_pkey PRIMARY KEY (amsp_code, amsp_sparepart_code);


--
-- Name: tbl_ceklist_detail tbl_ceklist_detail_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_ceklist_detail
    ADD CONSTRAINT tbl_ceklist_detail_pkey PRIMARY KEY (asset_code, tanggal, cd_code);


--
-- Name: tbl_ceklist tbl_ceklist_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_ceklist
    ADD CONSTRAINT tbl_ceklist_pkey PRIMARY KEY (asset_code, tanggal);


--
-- Name: tbl_downtime tbl_downtime_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_downtime
    ADD CONSTRAINT tbl_downtime_pkey PRIMARY KEY (dt_code);


--
-- Name: tbl_dtl_spkmr tbl_dtl_spkmr_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_dtl_spkmr
    ADD CONSTRAINT tbl_dtl_spkmr_pkey PRIMARY KEY (no_mr, no_mr_dtl);


--
-- Name: tbl_hours_asset tbl_hours_asset_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_hours_asset
    ADD CONSTRAINT tbl_hours_asset_pkey PRIMARY KEY (amm_code, tanggal);


--
-- Name: tbl_km_asset tbl_km_asset_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_km_asset
    ADD CONSTRAINT tbl_km_asset_pkey PRIMARY KEY (amm_code, tanggal);


--
-- Name: tbl_mr_detail tbl_mr_detail_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_mr_detail
    ADD CONSTRAINT tbl_mr_detail_pkey PRIMARY KEY (mr_code, item_code);


--
-- Name: tbl_mr tbl_mr_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_mr
    ADD CONSTRAINT tbl_mr_pkey PRIMARY KEY (mr_code);


--
-- Name: tbl_mreqitem tbl_mreqitem_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi
--

ALTER TABLE ONLY man.tbl_mreqitem
    ADD CONSTRAINT tbl_mreqitem_pkey PRIMARY KEY (mrequest_kode, item_kode, notes);


--
-- Name: tbl_mrequest tbl_mrequest_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_mrequest
    ADD CONSTRAINT tbl_mrequest_pkey PRIMARY KEY (mrequest_kode);


--
-- Name: tbl_mrspk tbl_mrspk_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_mrspk
    ADD CONSTRAINT tbl_mrspk_pkey PRIMARY KEY (spkmr_code);


--
-- Name: tbl_psp_detail tbl_psp_detail_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_psp_detail
    ADD CONSTRAINT tbl_psp_detail_pkey PRIMARY KEY (psp_code, item_code);


--
-- Name: tbl_psp tbl_psp_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_psp
    ADD CONSTRAINT tbl_psp_pkey PRIMARY KEY (psp_code);


--
-- Name: tbl_mrspk_detail tbl_spkmr_detail_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_mrspk_detail
    ADD CONSTRAINT tbl_spkmr_detail_pkey PRIMARY KEY (spkmr_code, item_name);


--
-- Name: tbl_spkmr tbl_spkmr_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_spkmr
    ADD CONSTRAINT tbl_spkmr_pkey PRIMARY KEY (no_mr);


--
-- Name: tbl_wo_detail tbl_wo_detail_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_wo_detail
    ADD CONSTRAINT tbl_wo_detail_pkey PRIMARY KEY (wo_code, item_code);


--
-- Name: tbl_wo tbl_wo_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_wo
    ADD CONSTRAINT tbl_wo_pkey PRIMARY KEY (wo_code);


--
-- Name: tbl_wr tbl_wr_pkey; Type: CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_wr
    ADD CONSTRAINT tbl_wr_pkey PRIMARY KEY (wr_code);


--
-- Name: category category_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.category
    ADD CONSTRAINT category_pkey PRIMARY KEY (category_kode);


--
-- Name: country country_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.country
    ADD CONSTRAINT country_pkey PRIMARY KEY (country_kode);


--
-- Name: currency currency_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.currency
    ADD CONSTRAINT currency_pkey PRIMARY KEY (valuta_kode);


--
-- Name: departemen departemen_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.departemen
    ADD CONSTRAINT departemen_pkey PRIMARY KEY (departemen_kode);


--
-- Name: pallet_event_types event_name_uniq; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.pallet_event_types
    ADD CONSTRAINT event_name_uniq UNIQUE (event_name);


--
-- Name: gen_user_adm gen_user_adm_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.gen_user_adm
    ADD CONSTRAINT gen_user_adm_pkey PRIMARY KEY (gua_kode);


--
-- Name: glcategory glcategory_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.glcategory
    ADD CONSTRAINT glcategory_pkey PRIMARY KEY (glcategory);


--
-- Name: glmaster glmaster_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.glmaster
    ADD CONSTRAINT glmaster_pkey PRIMARY KEY (gl_account);


--
-- Name: inv_master_area_detail inv_master_area_detail_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inv_master_area_detail
    ADD CONSTRAINT inv_master_area_detail_pkey PRIMARY KEY (plan_kode, kd_area, kd_baris);


--
-- Name: inv_master_area inv_master_area_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.inv_master_area
    ADD CONSTRAINT inv_master_area_pkey PRIMARY KEY (plan_kode, kd_area);


--
-- Name: inv_master_lok_pallet inv_master_lok_pallet_iml_kd_lok_key; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.inv_master_lok_pallet
    ADD CONSTRAINT inv_master_lok_pallet_iml_kd_lok_key UNIQUE (iml_kd_lok);


--
-- Name: inv_master_lok_pallet inv_master_lok_pallet_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.inv_master_lok_pallet
    ADD CONSTRAINT inv_master_lok_pallet_pkey PRIMARY KEY (iml_plan_kode, iml_kd_lok);


--
-- Name: inv_opname_hist inv_opname_hist_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.inv_opname_hist
    ADD CONSTRAINT inv_opname_hist_pkey PRIMARY KEY (ioh_kd_lok, ioh_kd_lok_old, ioh_no_pallet, ioh_tgl);


--
-- Name: inv_opname inv_opname_io_no_pallet_key; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.inv_opname
    ADD CONSTRAINT inv_opname_io_no_pallet_key UNIQUE (io_no_pallet);


--
-- Name: inv_opname inv_opname_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.inv_opname
    ADD CONSTRAINT inv_opname_pkey PRIMARY KEY (io_kd_lok, io_no_pallet);


--
-- Name: inventory_checking_details inventory_checking_details_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.inventory_checking_details
    ADD CONSTRAINT inventory_checking_details_pkey PRIMARY KEY (ref_no, pallet_no);


--
-- Name: inventory_checking inventory_checking_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.inventory_checking
    ADD CONSTRAINT inventory_checking_pkey PRIMARY KEY (ref_no);


--
-- Name: item_gbj_stockblock item_gbj_stockblock_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi_qc
--

ALTER TABLE ONLY public.item_gbj_stockblock
    ADD CONSTRAINT item_gbj_stockblock_pkey PRIMARY KEY (order_id, pallet_no);


--
-- Name: item_locker item_locker_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.item_locker
    ADD CONSTRAINT item_locker_pkey PRIMARY KEY (warehouse_kode, item_kode);


--
-- Name: item_opname item_opname_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.item_opname
    ADD CONSTRAINT item_opname_pkey PRIMARY KEY (kode_opname, item_kode);


--
-- Name: item_retur_produksi item_retur_produksi_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.item_retur_produksi
    ADD CONSTRAINT item_retur_produksi_pkey PRIMARY KEY (retur_kode, item_kode, keterangan, pallet_no);


--
-- Name: tbl_lgc_gbj_detail lgc_gbj_detail_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_lgc_gbj_detail
    ADD CONSTRAINT lgc_gbj_detail_pkey PRIMARY KEY (id_detail);


--
-- Name: pallet_event_types pallet_event_types_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.pallet_event_types
    ADD CONSTRAINT pallet_event_types_pkey PRIMARY KEY (id);


--
-- Name: pallet_events pallet_events_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.pallet_events
    ADD CONSTRAINT pallet_events_pkey PRIMARY KEY (pallet_no, event_time);


--
-- Name: plan plan_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.plan
    ADD CONSTRAINT plan_pkey PRIMARY KEY (plan_kode);


--
-- Name: region region_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.region
    ADD CONSTRAINT region_pkey PRIMARY KEY (region_kode);


--
-- Name: subplan subplan_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.subplan
    ADD CONSTRAINT subplan_pkey PRIMARY KEY (plan_kode, sub_plan);


--
-- Name: supplier supplier_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.supplier
    ADD CONSTRAINT supplier_pkey PRIMARY KEY (supplier_kode);


--
-- Name: t_brg_type t_brg_type_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.t_brg_type
    ADD CONSTRAINT t_brg_type_pkey PRIMARY KEY (type_kode);


--
-- Name: t_brg_warna t_brg_warna_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.t_brg_warna
    ADD CONSTRAINT t_brg_warna_pkey PRIMARY KEY (warna_kode);


--
-- Name: tbl_autority tbl_autority_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_autority
    ADD CONSTRAINT tbl_autority_pkey PRIMARY KEY (user_id, sub_f_id);


--
-- Name: tbl_ba_muat_detail tbl_ba_muat_detail_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_ba_muat_detail
    ADD CONSTRAINT tbl_ba_muat_detail_pkey PRIMARY KEY (no_ba, detail_ba_id);


--
-- Name: tbl_ba_muat tbl_ba_muat_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_ba_muat
    ADD CONSTRAINT tbl_ba_muat_pkey PRIMARY KEY (no_ba);


--
-- Name: tbl_ba_muat_trans tbl_ba_muat_trans_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_ba_muat_trans
    ADD CONSTRAINT tbl_ba_muat_trans_pkey PRIMARY KEY (no_ba);


--
-- Name: tbl_booking tbl_booking_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_booking
    ADD CONSTRAINT tbl_booking_pkey PRIMARY KEY (kode_book);


--
-- Name: tbl_customer tbl_customer_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_customer
    ADD CONSTRAINT tbl_customer_pkey PRIMARY KEY (customer_kode);


--
-- Name: tbl_detail_invoice tbl_detail_invoice_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_detail_invoice
    ADD CONSTRAINT tbl_detail_invoice_pkey PRIMARY KEY (no_inv, no_surat_jalan, item_kode, harga);


--
-- Name: tbl_detail_surat_jalan tbl_detail_surat_jalan_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_detail_surat_jalan
    ADD CONSTRAINT tbl_detail_surat_jalan_pkey PRIMARY KEY (no_surat_jalan, detail_surat_jalan_id);


--
-- Name: tbl_detail_tarif_surat_jalan tbl_detail_tarif_surat_jalan_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_detail_tarif_surat_jalan
    ADD CONSTRAINT tbl_detail_tarif_surat_jalan_pkey PRIMARY KEY (surat_jalan, supplier_kode, awal, tujuan);


--
-- Name: tbl_do tbl_do_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_do
    ADD CONSTRAINT tbl_do_pkey PRIMARY KEY (no_do);


--
-- Name: tbl_feature tbl_feature_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_feature
    ADD CONSTRAINT tbl_feature_pkey PRIMARY KEY (feature_id);


--
-- Name: tbl_gbj_stockblock tbl_gbj_stockblock_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_gbj_stockblock
    ADD CONSTRAINT tbl_gbj_stockblock_pkey PRIMARY KEY (order_id);


--
-- Name: tbl_invoice tbl_invoice_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_invoice
    ADD CONSTRAINT tbl_invoice_pkey PRIMARY KEY (no_inv);


--
-- Name: tbl_item_booking tbl_item_booking_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_item_booking
    ADD CONSTRAINT tbl_item_booking_pkey PRIMARY KEY (id);


--
-- Name: tbl_jenis tbl_jenis_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_jenis
    ADD CONSTRAINT tbl_jenis_pkey PRIMARY KEY (jenis_kode);


--
-- Name: tbl_kode tbl_kode_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_kode
    ADD CONSTRAINT tbl_kode_pkey PRIMARY KEY (kode_id);


--
-- Name: tbl_level tbl_level_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_level
    ADD CONSTRAINT tbl_level_pkey PRIMARY KEY (level_kode);


--
-- Name: tbl_lgc_gbj_header tbl_lgc_gbj_header_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_lgc_gbj_header
    ADD CONSTRAINT tbl_lgc_gbj_header_pkey PRIMARY KEY (id);


--
-- Name: tbl_retur_produksi tbl_retur_produksi_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_retur_produksi
    ADD CONSTRAINT tbl_retur_produksi_pkey PRIMARY KEY (retur_kode);


--
-- Name: tbl_satuan tbl_satuan_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_satuan
    ADD CONSTRAINT tbl_satuan_pkey PRIMARY KEY (satuan_id);


--
-- Name: tbl_sp_downgrade_pallet tbl_sp_downgrade_pallet_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sp_downgrade_pallet
    ADD CONSTRAINT tbl_sp_downgrade_pallet_pkey PRIMARY KEY (plan_kode, no_downgrade, pallet_no);


--
-- Name: tbl_sp_hasilbj tbl_sp_hasilbj_pallet_no_key; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sp_hasilbj
    ADD CONSTRAINT tbl_sp_hasilbj_pallet_no_key UNIQUE (pallet_no);


--
-- Name: tbl_sp_hasilbj tbl_sp_hasilbj_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sp_hasilbj
    ADD CONSTRAINT tbl_sp_hasilbj_pkey PRIMARY KEY (plan_kode, pallet_no);


--
-- Name: tbl_sp_ket_dg_pallet tbl_sp_ket_dg_pallet_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sp_ket_dg_pallet
    ADD CONSTRAINT tbl_sp_ket_dg_pallet_pkey PRIMARY KEY (id_ket_pallet);


--
-- Name: tbl_sp_mutasi_pallet tbl_sp_mutasi_pallet_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sp_mutasi_pallet
    ADD CONSTRAINT tbl_sp_mutasi_pallet_pkey PRIMARY KEY (plan_kode, no_mutasi, pallet_no, status_mut);


--
-- Name: tbl_sp_permintaan_brp tbl_sp_permintaan_brp_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sp_permintaan_brp
    ADD CONSTRAINT tbl_sp_permintaan_brp_pkey PRIMARY KEY (plan_kode, no_pbp, pallet_no);


--
-- Name: tbl_sp_status_master tbl_sp_status_master_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sp_status_master
    ADD CONSTRAINT tbl_sp_status_master_pkey PRIMARY KEY (status_plt);


--
-- Name: tbl_sp_status_pallet tbl_sp_status_pallet_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sp_status_pallet
    ADD CONSTRAINT tbl_sp_status_pallet_pkey PRIMARY KEY (plan_kode, no_txn, pallet_no);


--
-- Name: tbl_stock_bulanan tbl_stock_bulanan_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_stock_bulanan
    ADD CONSTRAINT tbl_stock_bulanan_pkey PRIMARY KEY (item_kode, tahun, plan_kode, seq_key);


--
-- Name: tbl_sub_feature tbl_sub_feature_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sub_feature
    ADD CONSTRAINT tbl_sub_feature_pkey PRIMARY KEY (sub_f_id);


--
-- Name: tbl_sub_plant_sec tbl_sub_plant_sec_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sub_plant_sec
    ADD CONSTRAINT tbl_sub_plant_sec_pkey PRIMARY KEY (tss_user, tss_plant, tss_sub_plant);


--
-- Name: tbl_surat_jalan tbl_surat_jalan_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_surat_jalan
    ADD CONSTRAINT tbl_surat_jalan_pkey PRIMARY KEY (no_surat_jalan);


--
-- Name: tbl_tarif_surat_jalan tbl_tarif_surat_jalan_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_tarif_surat_jalan
    ADD CONSTRAINT tbl_tarif_surat_jalan_pkey PRIMARY KEY (surat_jalan);


--
-- Name: tbl_toleransi_barang_pecah tbl_toleransi_barang_pecah_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_toleransi_barang_pecah
    ADD CONSTRAINT tbl_toleransi_barang_pecah_pkey PRIMARY KEY (plan_kode);


--
-- Name: tbl_user tbl_user_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_user
    ADD CONSTRAINT tbl_user_pkey PRIMARY KEY (user_id);


--
-- Name: tbl_user tbl_user_user_name_key; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_user
    ADD CONSTRAINT tbl_user_user_name_key UNIQUE (user_name);


--
-- Name: txn_counters_details txn_counters_details_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.txn_counters_details
    ADD CONSTRAINT txn_counters_details_pkey PRIMARY KEY (plant_id, txn_id, period_time);


--
-- Name: txn_counters txn_counters_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.txn_counters
    ADD CONSTRAINT txn_counters_pkey PRIMARY KEY (plant_id, txn_id);


--
-- Name: whouse whouse_pkey; Type: CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.whouse
    ADD CONSTRAINT whouse_pkey PRIMARY KEY (warehouse_kode);


--
-- Name: app_menu app_menu_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.app_menu
    ADD CONSTRAINT app_menu_pkey PRIMARY KEY (am_id);


--
-- Name: app_priv app_priv_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.app_priv
    ADD CONSTRAINT app_priv_pkey PRIMARY KEY (user_id, menu_id);


--
-- Name: qc_fg_fault_parameter fg_fault_parameter_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_fg_fault_parameter
    ADD CONSTRAINT fg_fault_parameter_pkey PRIMARY KEY (sub_plant, fapr_id);


--
-- Name: prev_qcdaily prev_qcdaily_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.prev_qcdaily
    ADD CONSTRAINT prev_qcdaily_pkey PRIMARY KEY (pq_plant_kode, pq_line_kode);


--
-- Name: qc_air_detail qc_air_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_air_detail
    ADD CONSTRAINT qc_air_detail_pkey PRIMARY KEY (qih_id);


--
-- Name: qc_air_header qc_air_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_air_header
    ADD CONSTRAINT qc_air_header_pkey PRIMARY KEY (qih_id);


--
-- Name: qc_alat_berat qc_alat_berat_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_alat_berat
    ADD CONSTRAINT qc_alat_berat_pkey PRIMARY KEY (qab_nama, qab_nomor);


--
-- Name: qc_alber_runhour qc_alber_runhour_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_alber_runhour
    ADD CONSTRAINT qc_alber_runhour_pkey PRIMARY KEY (qar_id);


--
-- Name: qc_alber_runhour qc_alber_runhour_uniq; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_alber_runhour
    ADD CONSTRAINT qc_alber_runhour_uniq UNIQUE (qar_date, qar_shift, qar_ab_nama, qar_ab_nomor);


--
-- Name: qc_bm_detail qc_bm_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_bm_detail
    ADD CONSTRAINT qc_bm_detail_pkey PRIMARY KEY (qbh_id, qbd_box_unit, qbd_material_code);


--
-- Name: qc_bm_header qc_bm_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_bm_header
    ADD CONSTRAINT qc_bm_header_pkey PRIMARY KEY (qbh_sub_plant, qbh_id);


--
-- Name: qc_bm_unit qc_bm_unit_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_bm_unit
    ADD CONSTRAINT qc_bm_unit_pkey PRIMARY KEY (qbm_plant_code, qbm_kode);


--
-- Name: qc_box_unit qc_box_unit_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_box_unit
    ADD CONSTRAINT qc_box_unit_pkey PRIMARY KEY (qbu_sub_plant, qbu_kode);


--
-- Name: qc_cb_detail qc_cb_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_cb_detail
    ADD CONSTRAINT qc_cb_detail_pkey PRIMARY KEY (qch_id, qcd_prep_group, qcd_prep_seq, qcd_slip_no, qcd_silo_no);


--
-- Name: qc_cb_header qc_cb_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_cb_header
    ADD CONSTRAINT qc_cb_header_pkey PRIMARY KEY (qch_id);


--
-- Name: qc_cb_silo qc_cb_silo_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_cb_silo
    ADD CONSTRAINT qc_cb_silo_pkey PRIMARY KEY (qcs_sub_plant, qcs_code);


--
-- Name: qc_cb_slip_tank qc_cb_slip_tank_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_cb_slip_tank
    ADD CONSTRAINT qc_cb_slip_tank_pkey PRIMARY KEY (qct_sub_plant, qct_code);


--
-- Name: qc_fg_fault_detail qc_fg_fault_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_fg_fault_detail
    ADD CONSTRAINT qc_fg_fault_detail_pkey PRIMARY KEY (fgf_id, fapr_id);


--
-- Name: qc_fg_fault_header qc_fg_fault_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_fg_fault_header
    ADD CONSTRAINT qc_fg_fault_header_pkey PRIMARY KEY (fgf_sub_plant, fgf_id);


--
-- Name: qc_fg_firing_group_detail qc_fg_firing_group_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_fg_firing_group_detail
    ADD CONSTRAINT qc_fg_firing_group_detail_pkey PRIMARY KEY (fc_sub_plant, fc_group, fc_gdid);


--
-- Name: qc_fg_firing_header qc_fg_firing_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_fg_firing_header
    ADD CONSTRAINT qc_fg_firing_header_pkey PRIMARY KEY (fh_sub_plant, fh_id);


--
-- Name: qc_fg_kiln_bending_header qc_fg_kiln_bending_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_fg_kiln_bending_header
    ADD CONSTRAINT qc_fg_kiln_bending_header_pkey PRIMARY KEY (kb_sub_plant, kb_id);


--
-- Name: qc_fg_rg_detail qc_fg_rg_detail_uniq; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_fg_rg_detail
    ADD CONSTRAINT qc_fg_rg_detail_uniq UNIQUE (rg_id, rg_qly, rg_defect_kode);


--
-- Name: qc_fg_rg_header qc_fg_rg_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_fg_rg_header
    ADD CONSTRAINT qc_fg_rg_header_pkey PRIMARY KEY (rg_sub_plant, rg_id);


--
-- Name: qc_fg_sorting_header qc_fg_sorting_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_fg_sorting_header
    ADD CONSTRAINT qc_fg_sorting_header_pkey PRIMARY KEY (sp_sub_plant, sp_id);


--
-- Name: qc_fg_sorting_mesin qc_fg_sorting_mesin_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_fg_sorting_mesin
    ADD CONSTRAINT qc_fg_sorting_mesin_pkey PRIMARY KEY (sub_plant, mesin_id);


--
-- Name: qc_gas_detail qc_gas_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gas_detail
    ADD CONSTRAINT qc_gas_detail_pkey PRIMARY KEY (qgh_id, qgd_mesin, qgd_seq, qgd_line);


--
-- Name: qc_gas_detail_produksi qc_gas_detail_produksi_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gas_detail_produksi
    ADD CONSTRAINT qc_gas_detail_produksi_pkey PRIMARY KEY (qgp_id, qgdp_mesin, qgdp_seq, qgdp_line);


--
-- Name: qc_gas_header qc_gas_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gas_header
    ADD CONSTRAINT qc_gas_header_pkey PRIMARY KEY (qgh_id);


--
-- Name: qc_gas_prep_detail_2 qc_gas_prep_detail_2_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gas_prep_detail_2
    ADD CONSTRAINT qc_gas_prep_detail_2_pkey PRIMARY KEY (qgpd2_mesin_code, qgpd2_seq);


--
-- Name: qc_gas_prep_detail qc_gas_prep_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gas_prep_detail
    ADD CONSTRAINT qc_gas_prep_detail_pkey PRIMARY KEY (qgpd_mesin_code, qgpd_seq);


--
-- Name: qc_gas_prep qc_gas_prep_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gas_prep
    ADD CONSTRAINT qc_gas_prep_pkey PRIMARY KEY (qgp_sub_plant, qgp_mesin_code, qgp_mesin_no);


--
-- Name: qc_gas_produksi qc_gas_produksi_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gas_produksi
    ADD CONSTRAINT qc_gas_produksi_pkey PRIMARY KEY (qgp_id);


--
-- Name: qc_gen_um qc_gen_um_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gen_um
    ADD CONSTRAINT qc_gen_um_pkey PRIMARY KEY (qgu_id);


--
-- Name: qc_genset_detail_test qc_genset_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_genset_detail_test
    ADD CONSTRAINT qc_genset_detail_pkey PRIMARY KEY (qgh_id);


--
-- Name: qc_genset_header_test qc_genset_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_genset_header_test
    ADD CONSTRAINT qc_genset_header_pkey PRIMARY KEY (qgh_id);


--
-- Name: qc_genset_runhour_detail_test qc_genset_runhour_detail_test_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_genset_runhour_detail_test
    ADD CONSTRAINT qc_genset_runhour_detail_test_pkey PRIMARY KEY (qgh_id);


--
-- Name: qc_gl_detail qc_gl_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gl_detail
    ADD CONSTRAINT qc_gl_detail_pkey PRIMARY KEY (qgh_id, qgd_motif);


--
-- Name: qc_gl_header qc_gl_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gl_header
    ADD CONSTRAINT qc_gl_header_pkey PRIMARY KEY (qgh_id);


--
-- Name: qc_gp_bmg qc_gp_bmg_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gp_bmg
    ADD CONSTRAINT qc_gp_bmg_pkey PRIMARY KEY (qgb_sub_plant, qgb_code);


--
-- Name: qc_gp_detail_master qc_gp_detail_master_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gp_detail_master
    ADD CONSTRAINT qc_gp_detail_master_pkey PRIMARY KEY (qgdm_group, qgdm_seq);


--
-- Name: qc_gp_detail qc_gp_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gp_detail
    ADD CONSTRAINT qc_gp_detail_pkey PRIMARY KEY (qgh_id, qgd_prep_group, qgd_prep_seq);


--
-- Name: qc_gp_group_master qc_gp_group_master_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gp_group_master
    ADD CONSTRAINT qc_gp_group_master_pkey PRIMARY KEY (qggm_group);


--
-- Name: qc_gp_header qc_gp_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_gp_header
    ADD CONSTRAINT qc_gp_header_pkey PRIMARY KEY (qgh_id);


--
-- Name: qc_ic_in_appr qc_ic_in_appr_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_ic_in_appr
    ADD CONSTRAINT qc_ic_in_appr_pkey PRIMARY KEY (appr_uname);


--
-- Name: qc_ic_kebasahan_data qc_ic_kebasahan_data_new_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_ic_kebasahan_data
    ADD CONSTRAINT qc_ic_kebasahan_data_new_pkey PRIMARY KEY (ic_id);


--
-- Name: qc_ic_mb_detail qc_ic_mb_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_ic_mb_detail
    ADD CONSTRAINT qc_ic_mb_detail_pkey PRIMARY KEY (ic_id, icd_group, icd_seq);


--
-- Name: qc_ic_mb_header qc_ic_mb_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_ic_mb_header
    ADD CONSTRAINT qc_ic_mb_header_pkey PRIMARY KEY (ic_id);


--
-- Name: qc_ic_mb_parameter_olddd qc_ic_mb_parameter_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_ic_mb_parameter_olddd
    ADD CONSTRAINT qc_ic_mb_parameter_pkey PRIMARY KEY (qcpd_group, qcpd_seq);


--
-- Name: qc_ic_parameter qc_ic_parameter_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_ic_parameter
    ADD CONSTRAINT qc_ic_parameter_pkey PRIMARY KEY (pm_id);


--
-- Name: qc_ic_spesifikasimutu qc_ic_spesifikasimutu_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_ic_spesifikasimutu
    ADD CONSTRAINT qc_ic_spesifikasimutu_pkey PRIMARY KEY (ic_kd_material, ic_kd_group, ic_kd_seq);


--
-- Name: qc_ic_teskimia_data qc_ic_teskimia_data_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_ic_teskimia_data
    ADD CONSTRAINT qc_ic_teskimia_data_pkey PRIMARY KEY (ic_id);


--
-- Name: qc_kiln_mesin qc_kiln_mesin_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_kiln_mesin
    ADD CONSTRAINT qc_kiln_mesin_pkey PRIMARY KEY (sub_plant, id_kiln);


--
-- Name: qc_kiln_header qc_kl_header_copy_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_kiln_header
    ADD CONSTRAINT qc_kl_header_copy_pkey PRIMARY KEY (kl_sub_plant, kl_id);


--
-- Name: qc_kiln_header_OLD qc_kl_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc."qc_kiln_header_OLD"
    ADD CONSTRAINT qc_kl_header_pkey PRIMARY KEY (kl_sub_plant, kl_id);


--
-- Name: qc_kiln_group_detail qc_klin_group_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_kiln_group_detail
    ADD CONSTRAINT qc_klin_group_detail_pkey PRIMARY KEY (sub_plant, kl_group, kld_id);


--
-- Name: qc_kiln_group qc_klin_group_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_kiln_group
    ADD CONSTRAINT qc_klin_group_pkey PRIMARY KEY (kl_group);


--
-- Name: qc_kpi_dept qc_kpi_dept_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_kpi_dept
    ADD CONSTRAINT qc_kpi_dept_pkey PRIMARY KEY (iddept);


--
-- Name: qc_kpi_detail qc_kpi_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_kpi_detail
    ADD CONSTRAINT qc_kpi_detail_pkey PRIMARY KEY (idh, idd);


--
-- Name: qc_kpi_divisi qc_kpi_divisi_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_kpi_divisi
    ADD CONSTRAINT qc_kpi_divisi_pkey PRIMARY KEY (iddept, iddivisi);


--
-- Name: qc_kpi_parameter qc_kpi_parameter_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_kpi_parameter
    ADD CONSTRAINT qc_kpi_parameter_pkey PRIMARY KEY (kpi_id);


--
-- Name: qc_line_unit qc_line_unit_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_line_unit
    ADD CONSTRAINT qc_line_unit_pkey PRIMARY KEY (qlu_plant_code, qlu_kode);


--
-- Name: qc_listrik_detail qc_listrik_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_listrik_detail
    ADD CONSTRAINT qc_listrik_detail_pkey PRIMARY KEY (qlh_id, qld_group);


--
-- Name: qc_listrik_gardu_detail_test qc_listrik_gardu_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_listrik_gardu_detail_test
    ADD CONSTRAINT qc_listrik_gardu_detail_pkey PRIMARY KEY (qlh_id, qlh_group);


--
-- Name: qc_listrik_gardu_header_test qc_listrik_gardu_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_listrik_gardu_header_test
    ADD CONSTRAINT qc_listrik_gardu_header_pkey PRIMARY KEY (qlh_id);


--
-- Name: qc_listrik_gardu_setting_test qc_listrik_gardu_setting_test_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_listrik_gardu_setting_test
    ADD CONSTRAINT qc_listrik_gardu_setting_test_pkey PRIMARY KEY (name);


--
-- Name: qc_listrik_header qc_listrik_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_listrik_header
    ADD CONSTRAINT qc_listrik_header_pkey PRIMARY KEY (qlh_id);


--
-- Name: qc_master_sparepart qc_master_sparepart_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_master_sparepart
    ADD CONSTRAINT qc_master_sparepart_pkey PRIMARY KEY (kdasset, item_kode);


--
-- Name: qc_md_defect qc_md_defect_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_md_defect
    ADD CONSTRAINT qc_md_defect_pkey PRIMARY KEY (qmd_kode);


--
-- Name: qc_md_hambatan qc_md_hambatan_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_md_hambatan
    ADD CONSTRAINT qc_md_hambatan_pkey PRIMARY KEY (qmh_code);


--
-- Name: qc_md_line qc_md_line_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_md_line
    ADD CONSTRAINT qc_md_line_pkey PRIMARY KEY (qml_plant_code, qml_kode);


--
-- Name: qc_md_motif qc_md_motif_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_md_motif
    ADD CONSTRAINT qc_md_motif_pkey PRIMARY KEY (qmm_nama);


--
-- Name: qc_md_subkon qc_md_subkon_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_md_subkon
    ADD CONSTRAINT qc_md_subkon_pkey PRIMARY KEY (subkon_id);


--
-- Name: qc_mesin_kg qc_mesin_kg_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_mesin_kg
    ADD CONSTRAINT qc_mesin_kg_pkey PRIMARY KEY (qmk_sub_plant, qmk_mesin_code, qmk_mesin_no, qmk_ukuran);


--
-- Name: qc_mesin_unit qc_mesin_unit_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_mesin_unit
    ADD CONSTRAINT qc_mesin_unit_pkey PRIMARY KEY (qmu_code);


--
-- Name: qc_pd_cm_group_d1 qc_pd_cm_group_d1_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_cm_group_d1
    ADD CONSTRAINT qc_pd_cm_group_d1_pkey PRIMARY KEY (cm_group, cd1_id);


--
-- Name: qc_pd_cm_group_d2 qc_pd_cm_group_d2_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_cm_group_d2
    ADD CONSTRAINT qc_pd_cm_group_d2_pkey PRIMARY KEY (sub_plant, cm_group, cd1_id, cd2_id);


--
-- Name: qc_pd_cm_group qc_pd_cm_group_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_cm_group
    ADD CONSTRAINT qc_pd_cm_group_pkey PRIMARY KEY (cm_group);


--
-- Name: qc_pd_cm_header qc_pd_cm_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_cm_header
    ADD CONSTRAINT qc_pd_cm_header_pkey PRIMARY KEY (cmh_id);


--
-- Name: qc_pd_detail qc_pd_detail2_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_detail
    ADD CONSTRAINT qc_pd_detail2_pkey PRIMARY KEY (qph_id, qpd_pd_group, qpd_pd_seq);


--
-- Name: qc_pd_detail_old qc_pd_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_detail_old
    ADD CONSTRAINT qc_pd_detail_pkey PRIMARY KEY (qph_id, qpd_pd_group, qpd_pd_seq);


--
-- Name: qc_pd_group_detail2 qc_pd_group_detail2_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_group_detail2
    ADD CONSTRAINT qc_pd_group_detail2_pkey PRIMARY KEY (qpgd_group, qpgd_seq);


--
-- Name: qc_pd_group_detail qc_pd_group_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_group_detail
    ADD CONSTRAINT qc_pd_group_detail_pkey PRIMARY KEY (qpgd_subplant, qpgd_group, qpgd_seq);


--
-- Name: qc_pd_group qc_pd_group_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_group
    ADD CONSTRAINT qc_pd_group_pkey PRIMARY KEY (qpg_group);


--
-- Name: qc_pd_hd qc_pd_hd_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_hd
    ADD CONSTRAINT qc_pd_hd_pkey PRIMARY KEY (qph_sub_plant, qph_code);


--
-- Name: qc_pd_header qc_pd_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_header
    ADD CONSTRAINT qc_pd_header_pkey PRIMARY KEY (qph_id);


--
-- Name: qc_pd_hp_header qc_pd_hp_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_hp_header
    ADD CONSTRAINT qc_pd_hp_header_pkey PRIMARY KEY (hph_id);


--
-- Name: qc_pd_hsl_header qc_pd_hsl_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_hsl_header
    ADD CONSTRAINT qc_pd_hsl_header_pkey PRIMARY KEY (qpdh_id);


--
-- Name: qc_pd_mouldset qc_pd_mouldset_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_mouldset
    ADD CONSTRAINT qc_pd_mouldset_pkey PRIMARY KEY (qpm_sub_plant, qpm_press_code, qpm_code);


--
-- Name: qc_pd_op_detail qc_pd_op_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_op_detail
    ADD CONSTRAINT qc_pd_op_detail_pkey PRIMARY KEY (op_id, op_mould, op_urut);


--
-- Name: qc_pd_op_header qc_pd_op_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_op_header
    ADD CONSTRAINT qc_pd_op_header_pkey PRIMARY KEY (op_id);


--
-- Name: qc_pd_prep_group_detil qc_pd_prep_detil_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_prep_group_detil
    ADD CONSTRAINT qc_pd_prep_detil_pkey PRIMARY KEY (qcpdd_seq, qcpdm_group);


--
-- Name: qc_pd_prep_group qc_pd_prep_master_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_prep_group
    ADD CONSTRAINT qc_pd_prep_master_pkey PRIMARY KEY (qcpdm_group);


--
-- Name: qc_pd_press qc_pd_press_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_press
    ADD CONSTRAINT qc_pd_press_pkey PRIMARY KEY (qpp_sub_plant, qpp_code);


--
-- Name: qc_pd_sd qc_pd_sd_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_sd
    ADD CONSTRAINT qc_pd_sd_pkey PRIMARY KEY (qps_sub_plant, qps_code);


--
-- Name: qc_pd_size_detail qc_pd_size_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_size_detail
    ADD CONSTRAINT qc_pd_size_detail_pkey PRIMARY KEY (op_id, op_mould, op_urut);


--
-- Name: qc_pd_size_header qc_pd_size_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_size_header
    ADD CONSTRAINT qc_pd_size_header_pkey PRIMARY KEY (op_id);


--
-- Name: qc_pd_thickness_detail qc_pd_thickness_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_thickness_detail
    ADD CONSTRAINT qc_pd_thickness_detail_pkey PRIMARY KEY (op_id, op_mould, op_urut);


--
-- Name: qc_pd_thickness_header qc_pd_thickness_header_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_pd_thickness_header
    ADD CONSTRAINT qc_pd_thickness_header_pkey PRIMARY KEY (op_id);


--
-- Name: qc_sp_monitoring_detail qc_sp_monitoring_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_sp_monitoring_detail
    ADD CONSTRAINT qc_sp_monitoring_detail_pkey PRIMARY KEY (qsm_id, qsmd_sett_group, qsmd_sett_seq);


--
-- Name: qc_sp_monitoring qc_sp_monitoring_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_sp_monitoring
    ADD CONSTRAINT qc_sp_monitoring_pkey PRIMARY KEY (qsm_id);


--
-- Name: qc_sp_monitoring_stop qc_sp_monitoring_stop_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_sp_monitoring_stop
    ADD CONSTRAINT qc_sp_monitoring_stop_pkey PRIMARY KEY (qsms_id);


--
-- Name: qc_sp_sett_detail qc_sp_sett_detail_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_sp_sett_detail
    ADD CONSTRAINT qc_sp_sett_detail_pkey PRIMARY KEY (qssd_group, qssd_seq);


--
-- Name: qc_sp_sett_master qc_sp_sett_master_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_sp_sett_master
    ADD CONSTRAINT qc_sp_sett_master_pkey PRIMARY KEY (qss_group);


--
-- Name: qc_subplan qc_subplan_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_subplan
    ADD CONSTRAINT qc_subplan_pkey PRIMARY KEY (plan_kode, sub_plan);


--
-- Name: qcdaily_eco qcdaily_eco_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qcdaily_eco
    ADD CONSTRAINT qcdaily_eco_pkey PRIMARY KEY (qec_id, qec_defect_kode);


--
-- Name: qcdaily_eco qcdaily_eco_uniq; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qcdaily_eco
    ADD CONSTRAINT qcdaily_eco_uniq UNIQUE (qec_sub_plant, qec_line, qec_date, qec_defect_kode);


--
-- Name: qcdaily_exp qcdaily_exp_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qcdaily_exp
    ADD CONSTRAINT qcdaily_exp_pkey PRIMARY KEY (qex_id);


--
-- Name: qcdaily_exp qcdaily_exp_uniq; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qcdaily_exp
    ADD CONSTRAINT qcdaily_exp_uniq UNIQUE (qex_sub_plant, qex_line, qex_date);


--
-- Name: qcdaily_kw qcdaily_kw_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qcdaily_kw
    ADD CONSTRAINT qcdaily_kw_pkey PRIMARY KEY (qkw_id, qkw_defect_kode);


--
-- Name: qcdaily_kw qcdaily_kw_uniq; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qcdaily_kw
    ADD CONSTRAINT qcdaily_kw_uniq UNIQUE (qkw_sub_plant, qkw_line, qkw_date, qkw_defect_kode);


--
-- Name: qc_cb_prep_detail qcpd_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_cb_prep_detail
    ADD CONSTRAINT qcpd_pkey PRIMARY KEY (qcpd_group, qcpd_seq);


--
-- Name: qc_cb_prep_master qcpm_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_cb_prep_master
    ADD CONSTRAINT qcpm_pkey PRIMARY KEY (qcpm_group);


--
-- Name: qc_genset_runhour_header_test qgh_genset_runhour_header_test_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qc_genset_runhour_header_test
    ADD CONSTRAINT qgh_genset_runhour_header_test_pkey PRIMARY KEY (qgh_id);


--
-- Name: app_user tbl_user_pkey; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.app_user
    ADD CONSTRAINT tbl_user_pkey PRIMARY KEY (user_id);


--
-- Name: app_user tbl_user_user_name_key; Type: CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.app_user
    ADD CONSTRAINT tbl_user_user_name_key UNIQUE (user_name);


--
-- Name: app_menu_front app_menu_front_pkey; Type: CONSTRAINT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.app_menu_front
    ADD CONSTRAINT app_menu_front_pkey PRIMARY KEY (am_id);


--
-- Name: app_menu app_menu_pkey; Type: CONSTRAINT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.app_menu
    ADD CONSTRAINT app_menu_pkey PRIMARY KEY (am_id);


--
-- Name: app_priv_front app_priv_front_pkey; Type: CONSTRAINT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.app_priv_front
    ADD CONSTRAINT app_priv_front_pkey PRIMARY KEY (user_id, menu_id);


--
-- Name: app_priv app_priv_pkey; Type: CONSTRAINT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.app_priv
    ADD CONSTRAINT app_priv_pkey PRIMARY KEY (user_id, menu_id);


--
-- Name: app_user_front app_user_front_pkey; Type: CONSTRAINT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.app_user_front
    ADD CONSTRAINT app_user_front_pkey PRIMARY KEY (user_id);


--
-- Name: app_user_front app_user_front_user_name_key; Type: CONSTRAINT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.app_user_front
    ADD CONSTRAINT app_user_front_user_name_key UNIQUE (user_name);


--
-- Name: app_user app_user_pkey; Type: CONSTRAINT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.app_user
    ADD CONSTRAINT app_user_pkey PRIMARY KEY (user_id);


--
-- Name: app_user app_user_user_name_key; Type: CONSTRAINT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.app_user
    ADD CONSTRAINT app_user_user_name_key UNIQUE (user_name);


--
-- Name: md_lokasi md_lokasi_pkey; Type: CONSTRAINT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.md_lokasi
    ADD CONSTRAINT md_lokasi_pkey PRIMARY KEY (lokasi_id);


--
-- Name: md_rincian_kerja md_rincian_kerja_pkey; Type: CONSTRAINT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.md_rincian_kerja
    ADD CONSTRAINT md_rincian_kerja_pkey PRIMARY KEY (rincian_id);


--
-- Name: md_subplant md_subplant_pkey; Type: CONSTRAINT; Schema: taman; Owner: armasi_wmm
--

ALTER TABLE ONLY taman.md_subplant
    ADD CONSTRAINT md_subplant_pkey PRIMARY KEY (subplant);


--
-- Name: tb_kerja_detail tb_kerja_detail_pkey; Type: CONSTRAINT; Schema: taman; Owner: armasi_taman
--

ALTER TABLE ONLY taman.tb_kerja_detail
    ADD CONSTRAINT tb_kerja_detail_pkey PRIMARY KEY (rk_id, rkd_id);


--
-- Name: tb_kerja_header tb_kerja_header_pkey; Type: CONSTRAINT; Schema: taman; Owner: armasi_taman
--

ALTER TABLE ONLY taman.tb_kerja_header
    ADD CONSTRAINT tb_kerja_header_pkey PRIMARY KEY (rk_id);


--
-- Name: app_menu_front app_menu_front_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.app_menu_front
    ADD CONSTRAINT app_menu_front_pkey PRIMARY KEY (am_id);


--
-- Name: app_menu app_menu_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.app_menu
    ADD CONSTRAINT app_menu_pkey PRIMARY KEY (am_id);


--
-- Name: app_priv_front app_priv_front_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.app_priv_front
    ADD CONSTRAINT app_priv_front_pkey PRIMARY KEY (user_id, menu_id);


--
-- Name: app_priv app_priv_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.app_priv
    ADD CONSTRAINT app_priv_pkey PRIMARY KEY (user_id, menu_id);


--
-- Name: app_user_front app_user_front_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.app_user_front
    ADD CONSTRAINT app_user_front_pkey PRIMARY KEY (user_id);


--
-- Name: app_user_front app_user_front_user_name_key; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.app_user_front
    ADD CONSTRAINT app_user_front_user_name_key UNIQUE (user_name);


--
-- Name: app_user app_user_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.app_user
    ADD CONSTRAINT app_user_pkey PRIMARY KEY (user_id);


--
-- Name: app_user app_user_user_name_key; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.app_user
    ADD CONSTRAINT app_user_user_name_key UNIQUE (user_name);


--
-- Name: md_departemen md_departemen_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.md_departemen
    ADD CONSTRAINT md_departemen_pkey PRIMARY KEY (dept_id);


--
-- Name: md_jenis_temuan md_jenis_temuan_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.md_jenis_temuan
    ADD CONSTRAINT md_jenis_temuan_pkey PRIMARY KEY (mdt_id);


--
-- Name: md_kategori md_kategori_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.md_kategori
    ADD CONSTRAINT md_kategori_pkey PRIMARY KEY (kat_kode);


--
-- Name: md_lokasi md_lokasi_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.md_lokasi
    ADD CONSTRAINT md_lokasi_pkey PRIMARY KEY (lokasi_id);


--
-- Name: md_status md_status_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.md_status
    ADD CONSTRAINT md_status_pkey PRIMARY KEY (status_id);


--
-- Name: md_sub_kategori md_sub_kategori_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.md_sub_kategori
    ADD CONSTRAINT md_sub_kategori_pkey PRIMARY KEY (subkat_id);


--
-- Name: md_subplant md_subplant_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.md_subplant
    ADD CONSTRAINT md_subplant_pkey PRIMARY KEY (subplant);


--
-- Name: tb_document_detail tb_document_detail_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.tb_document_detail
    ADD CONSTRAINT tb_document_detail_pkey PRIMARY KEY (file_id);


--
-- Name: tb_document tb_document_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.tb_document
    ADD CONSTRAINT tb_document_pkey PRIMARY KEY (doc_id);


--
-- Name: tb_penalti_user tb_penalti_user_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.tb_penalti_user
    ADD CONSTRAINT tb_penalti_user_pkey PRIMARY KEY (temuan_id, user_name);


--
-- Name: tb_tanggapan tb_tanggapan_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.tb_tanggapan
    ADD CONSTRAINT tb_tanggapan_pkey PRIMARY KEY (tem_id, tgp_id);


--
-- Name: tb_temuan_detail tb_temuan_detail_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.tb_temuan_detail
    ADD CONSTRAINT tb_temuan_detail_pkey PRIMARY KEY (h_id, d_id);


--
-- Name: tb_temuan_header tb_temuan_header_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.tb_temuan_header
    ADD CONSTRAINT tb_temuan_header_pkey PRIMARY KEY (tem_id);


--
-- Name: tb_user_poin tb_user_poin_pkey; Type: CONSTRAINT; Schema: wmm; Owner: armasi_wmm
--

ALTER TABLE ONLY wmm.tb_user_poin
    ADD CONSTRAINT tb_user_poin_pkey PRIMARY KEY (user_name, tahun);


--
-- Name: idx_user_login_username; Type: INDEX; Schema: audit; Owner: armasi
--

CREATE INDEX idx_user_login_username ON audit.user_login USING btree (username);


--
-- Name: idx_date_mutation_summary; Type: INDEX; Schema: gbj_report; Owner: armasi
--

CREATE INDEX idx_date_mutation_summary ON gbj_report.summary_mutation_by_motif_size_shading USING btree (mutation_date);


--
-- Name: idx_subplant_mutation_summary; Type: INDEX; Schema: gbj_report; Owner: armasi
--

CREATE INDEX idx_subplant_mutation_summary ON gbj_report.summary_mutation_by_motif_size_shading USING btree (subplant);


--
-- Name: summary_mutation_by_motif_siz_mutation_date_subplant_motif__idx; Type: INDEX; Schema: gbj_report; Owner: armasi
--

CREATE UNIQUE INDEX summary_mutation_by_motif_siz_mutation_date_subplant_motif__idx ON gbj_report.summary_mutation_by_motif_size_shading USING btree (mutation_date, subplant, motif_id, size, shading);


--
-- Name: idx_category1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_category1 ON public.category USING btree (category_kode);


--
-- Name: idx_category2; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_category2 ON public.category USING btree (jenis_kode);


--
-- Name: idx_country1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_country1 ON public.country USING btree (country_kode);


--
-- Name: idx_currency1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_currency1 ON public.currency USING btree (valuta_kode);


--
-- Name: idx_departemen1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_departemen1 ON public.departemen USING btree (plan_kode);


--
-- Name: idx_departemen2; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_departemen2 ON public.departemen USING btree (departemen_kode);


--
-- Name: idx_glcategory1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_glcategory1 ON public.glcategory USING btree (glcategory);


--
-- Name: idx_glmaster1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_glmaster1 ON public.glmaster USING btree (glcategory);


--
-- Name: idx_glmaster2; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_glmaster2 ON public.glmaster USING btree (gl_account);


--
-- Name: idx_item1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_item1 ON public.item USING btree (category_kode);


--
-- Name: idx_item2; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_item2 ON public.item USING btree (item_kode);


--
-- Name: idx_item_locker1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_item_locker1 ON public.item_locker USING btree (warehouse_kode);


--
-- Name: idx_item_locker2; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_item_locker2 ON public.item_locker USING btree (item_kode);


--
-- Name: idx_item_opname; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_item_opname ON public.item_opname USING btree (item_kode);


--
-- Name: idx_no_surat_jalan; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_no_surat_jalan ON public.tbl_surat_jalan USING btree (no_surat_jalan);


--
-- Name: idx_plan1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_plan1 ON public.plan USING btree (plan_kode);


--
-- Name: idx_region1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_region1 ON public.region USING btree (region_kode);


--
-- Name: idx_region2; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_region2 ON public.region USING btree (country_kode);


--
-- Name: idx_supplier1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_supplier1 ON public.supplier USING btree (supplier_kode);


--
-- Name: idx_supplier2; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_supplier2 ON public.supplier USING btree (region_kode);


--
-- Name: idx_supplier3; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_supplier3 ON public.supplier USING btree (gl_account);


--
-- Name: idx_tbl_customer1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_tbl_customer1 ON public.tbl_customer USING btree (customer_kode);


--
-- Name: idx_tbl_detail_invoice1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_tbl_detail_invoice1 ON public.tbl_detail_invoice USING btree (no_inv);


--
-- Name: idx_tbl_detail_surat_jalan1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_tbl_detail_surat_jalan1 ON public.tbl_detail_surat_jalan USING btree (no_surat_jalan);


--
-- Name: idx_tbl_invoice1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_tbl_invoice1 ON public.tbl_invoice USING btree (no_inv);


--
-- Name: idx_tbl_invoice2; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_tbl_invoice2 ON public.tbl_invoice USING btree (customer_kode);


--
-- Name: idx_tbl_jenis1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_tbl_jenis1 ON public.tbl_jenis USING btree (jenis_kode);


--
-- Name: idx_tbl_kurs1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_tbl_kurs1 ON public.tbl_kurs USING btree (valuta_kode);


--
-- Name: idx_tbl_sp_mutasi_pallet_pallet_no; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_tbl_sp_mutasi_pallet_pallet_no ON public.tbl_sp_mutasi_pallet USING hash (pallet_no);


--
-- Name: idx_tbl_tarif_surat_jalan1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_tbl_tarif_surat_jalan1 ON public.tbl_tarif_surat_jalan USING btree (surat_jalan);


--
-- Name: idx_whouse1; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX idx_whouse1 ON public.whouse USING btree (plan_kode);


--
-- Name: idx_whouse2; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX idx_whouse2 ON public.whouse USING btree (warehouse_kode);


--
-- Name: ind_tbl_sp_hasil_bj_size; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX ind_tbl_sp_hasil_bj_size ON public.tbl_sp_hasilbj USING btree (size);


--
-- Name: ind_tbl_sp_hasilbj; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX ind_tbl_sp_hasilbj ON public.tbl_sp_hasilbj USING btree (plan_kode, seq_no, pallet_no);


--
-- Name: item_kode; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX item_kode ON public.item USING btree (item_kode);


--
-- Name: new_location_idx; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX new_location_idx ON public.inv_opname_hist USING btree (ioh_kd_lok);


--
-- Name: old_location_idx; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX old_location_idx ON public.inv_opname_hist USING btree (ioh_kd_lok_old);


--
-- Name: pallets_no_pallet_mutation_summary_by_quantity; Type: INDEX; Schema: public; Owner: armasi
--

CREATE UNIQUE INDEX pallets_no_pallet_mutation_summary_by_quantity ON public.pallets_mutation_summary_by_quantity USING btree (pallet_no);


--
-- Name: sub_f_id_tbl_sub_feature_key; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX sub_f_id_tbl_sub_feature_key ON public.tbl_sub_feature USING btree (sub_f_id);


--
-- Name: tbl_do_no_do_key; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX tbl_do_no_do_key ON public.tbl_do USING btree (no_do);


--
-- Name: user_updater_idx; Type: INDEX; Schema: public; Owner: armasi
--

CREATE INDEX user_updater_idx ON public.inv_opname_hist USING btree (ioh_userid);


--
-- Name: pallets_with_location_rimpil _RETURN; Type: RULE; Schema: public; Owner: armasi
--

CREATE OR REPLACE VIEW public.pallets_with_location_rimpil AS
 SELECT ('2'::text || (ima.plan_kode)::text) AS location_subplant,
    ima.ket_area AS location_area_name,
    ima.kd_area AS location_area_no,
    iml.iml_no_baris AS location_line_no,
    iml.iml_no_lok AS location_cell_no,
    io.io_kd_lok AS location_id,
    hasilbj.pallet_no,
    hasilbj.subplant AS production_subplant,
    hasilbj.item_kode AS motif_id,
    t3.motif_dimension,
    t3.motif_name,
    t3.quality,
    hasilbj.size,
    hasilbj.shade AS shading,
    hasilbj.create_date AS creation_date,
    hasilbj.regu AS creator_group,
    hasilbj.shift AS creator_shift,
    hasilbj.line,
    hasilbj.last_qty AS current_quantity,
    t3.is_rimpil
   FROM ((((public.tbl_sp_hasilbj hasilbj
     JOIN public.inv_opname io ON (((hasilbj.pallet_no)::text = (io.io_no_pallet)::text)))
     JOIN public.inv_master_lok_pallet iml ON (((io.io_kd_lok)::text = (iml.iml_kd_lok)::text)))
     JOIN public.inv_master_area ima ON ((((iml.iml_plan_kode)::text = (ima.plan_kode)::text) AND ((iml.iml_kd_area)::text = (ima.kd_area)::text))))
     JOIN ( SELECT t2.production_subplant,
            t2.motif_id,
            t2.motif_dimension,
            t2.motif_name,
            t2.quality,
            t2.size,
            t2.shading,
                CASE
                    WHEN ((((t2.motif_dimension)::text = '40 X 40'::text) AND (t2.current_quantity <= 156)) OR (((t2.motif_dimension)::text = '25 X 40'::text) AND (t2.current_quantity <= 198)) OR (((t2.motif_dimension)::text = '20 X 25'::text) AND (t2.current_quantity <= 196)) OR (((t2.motif_dimension)::text = '25 X 50'::text) AND (t2.current_quantity <= 168)) OR (((t2.motif_dimension)::text = '50 X 50'::text) AND (t2.current_quantity <= 176)) OR (((t2.motif_dimension)::text = '20 X 20'::text) AND (t2.current_quantity <= 200)) OR (((t2.motif_dimension)::text = '25 X 25'::text) AND (t2.current_quantity <= 196)) OR (((t2.motif_dimension)::text = '30 X 30'::text) AND (t2.current_quantity <= 198))) THEN true
                    ELSE false
                END AS is_rimpil
           FROM ( SELECT hasilbj_1.subplant AS production_subplant,
                    category.category_nama AS motif_dimension,
                    item.item_kode AS motif_id,
                    item.item_nama AS motif_name,
                    item.quality,
                    hasilbj_1.size,
                    hasilbj_1.shade AS shading,
                    sum(hasilbj_1.last_qty) AS current_quantity
                   FROM (((public.tbl_sp_hasilbj hasilbj_1
                     JOIN public.inv_opname io_1 ON (((hasilbj_1.pallet_no)::text = (io_1.io_no_pallet)::text)))
                     JOIN public.item ON (((hasilbj_1.item_kode)::text = (item.item_kode)::text)))
                     JOIN public.category ON ((substr((item.item_kode)::text, 1, 2) = (category.category_kode)::text)))
                  WHERE ((hasilbj_1.last_qty > 0) AND (io_1.io_kd_lok IS NOT NULL) AND ((hasilbj_1.status_plt)::text = 'R'::text))
                  GROUP BY hasilbj_1.subplant, category.category_nama, item.item_kode, item.quality, hasilbj_1.size, hasilbj_1.shade) t2) t3 ON ((((hasilbj.subplant)::text = (t3.production_subplant)::text) AND ((hasilbj.item_kode)::text = (t3.motif_id)::text) AND ((hasilbj.size)::text = (t3.size)::text) AND ((hasilbj.shade)::text = (t3.shading)::text))))
  WHERE (hasilbj.last_qty > 0)
  ORDER BY hasilbj.subplant, hasilbj.pallet_no;


--
-- Name: vw_shipping_details _RETURN; Type: RULE; Schema: public; Owner: postgres
--

CREATE OR REPLACE VIEW public.vw_shipping_details AS
 SELECT tbm.no_ba,
    tbmd.detail_ba_id,
        CASE
            WHEN (tbm.kode_lama = 'P'::text) THEN tbmd.kode_lama
            ELSE tbm.kode_lama
        END AS kode_lama,
    tbmd.sub_plant,
    tbm.tanggal,
    tbm.customer_kode,
    tbm.no_surat_jalan_rekap,
    tbm.create_by,
    tbm.tujuan_surat_jalan_rekap,
    tbmd.item_kode,
    tbmd.itshade,
    tbmd.itsize,
    tbmd.volume,
    tbmd.keterangan,
    tbmd.do_kode,
    tbmd.detail_cat,
    COALESCE(sum(abs(mut.qty)), (0)::numeric) AS shipped_quantity
   FROM ((public.tbl_ba_muat tbm
     JOIN public.tbl_ba_muat_detail tbmd ON ((tbm.no_ba = tbmd.no_ba)))
     LEFT JOIN ( SELECT m.pallet_no,
            hasilbj.subplant,
            hasilbj.item_kode,
            m.no_mutasi,
            m.qty,
            m.reff_txn,
            hasilbj.size,
            hasilbj.shade,
            m.status_mut
           FROM (public.tbl_sp_mutasi_pallet m
             JOIN public.tbl_sp_hasilbj hasilbj ON (((m.pallet_no)::text = (hasilbj.pallet_no)::text)))) mut ON (((((mut.no_mutasi)::text = tbmd.no_ba) OR ((mut.reff_txn)::text = tbmd.no_ba)) AND ((mut.item_kode)::text = (tbmd.item_kode)::text) AND ((mut.subplant)::text = (tbmd.sub_plant)::text) AND ((((tbmd.detail_cat)::text = 'SALES'::text) AND ((mut.status_mut)::text = 'S'::text)) OR (((tbmd.detail_cat)::text = 'FOC'::text) AND ((mut.status_mut)::text = 'F'::text)) OR (((tbmd.detail_cat)::text = 'RIMPIL'::text) AND ((mut.status_mut)::text = 'R'::text)) OR (((tbmd.detail_cat)::text = 'SAMPLE'::text) AND ((mut.status_mut)::text = 'L'::text))) AND
        CASE
            WHEN ((COALESCE(tbmd.itsize, ''::character varying))::text <> ''::text) THEN ((tbmd.itsize)::text = (mut.size)::text)
            ELSE true
        END AND
        CASE
            WHEN ((COALESCE(tbmd.itshade, ''::character varying))::text <> ''::text) THEN ((tbmd.itshade)::text = (mut.shade)::text)
            ELSE true
        END)))
  WHERE (tbm.plan_kode = ((public.get_plant_code())::character varying)::text)
  GROUP BY tbmd.detail_ba_id, tbm.no_ba, tbmd.item_kode, tbmd.volume, tbmd.keterangan, tbmd.itsize, tbmd.itshade, tbmd.detail_cat, tbmd.sub_plant, tbmd.kode_lama, tbmd.do_kode;


--
-- Name: summary_sku_available_for_sales _RETURN; Type: RULE; Schema: public; Owner: armasi
--

CREATE OR REPLACE VIEW public.summary_sku_available_for_sales AS
 SELECT pallets_with_location.subplant AS production_subplant,
    pallets_with_location.location_subplant,
    pallets_with_location.location_id,
    item.category_kode AS motif_group_id,
    btrim(regexp_replace((item.spesification)::text, '(ECO|ECONOMY|EKONOMI|ECONOMI|EXP|EXPORT)\s*'::text, ''::text, 'g'::text)) AS motif_group_name,
    item.item_kode AS motif_id,
    item.item_nama AS motif_name,
    category.category_nama AS motif_dimension,
    item.color,
        CASE
            WHEN ((item.quality)::text = 'EXPORT'::text) THEN 'EXP'::character varying
            WHEN (((item.quality)::text = 'ECONOMY'::text) OR ((item.quality)::text = 'EKONOMI'::text)) THEN 'ECO'::character varying
            ELSE item.quality
        END AS quality,
    pallets_with_location.size,
    pallets_with_location.shade AS shading,
    COALESCE(rimpil.is_rimpil, false) AS is_rimpil,
    count(pallets_with_location.pallet_no) AS pallet_count,
    sum(pallets_with_location.last_qty) AS current_quantity
   FROM (((public.pallets_with_location
     JOIN public.item ON (((pallets_with_location.item_kode)::text = (item.item_kode)::text)))
     JOIN public.category ON (("left"((pallets_with_location.item_kode)::text, 2) = (category.category_kode)::text)))
     LEFT JOIN public.rimpil_by_motif_size_shading rimpil ON ((((
        CASE
            WHEN ((pallets_with_location.subplant)::text = ANY (ARRAY[('4'::character varying)::text, ('5'::character varying)::text])) THEN (((pallets_with_location.subplant)::text || 'A'::text))::character varying
            ELSE pallets_with_location.subplant
        END)::text = (rimpil.production_subplant)::text) AND ((pallets_with_location.item_kode)::text = (rimpil.motif_id)::text) AND ((pallets_with_location.size)::text = (rimpil.size)::text) AND ((pallets_with_location.shade)::text = (rimpil.shading)::text))))
  WHERE (((pallets_with_location.status_plt)::text = 'R'::text) AND (pallets_with_location.last_qty > 0))
  GROUP BY pallets_with_location.subplant, pallets_with_location.location_id, pallets_with_location.location_subplant, item.category_kode, (btrim(regexp_replace((item.spesification)::text, '(ECO|ECONOMY|EKONOMI|ECONOMI|EXP|EXPORT)\s*'::text, ''::text, 'g'::text))), item.color, item.quality, item.item_kode, item.item_nama, category.category_nama, pallets_with_location.size, pallets_with_location.shade, rimpil.is_rimpil;


--
-- Name: tbl_user audit_user_login; Type: TRIGGER; Schema: public; Owner: armasi
--

CREATE TRIGGER audit_user_login AFTER UPDATE OF monthly_login_count, monthly_logout_count, last_activity ON public.tbl_user FOR EACH ROW EXECUTE PROCEDURE public.tg_tbl_user_audit_login();


--
-- Name: tbl_sp_mutasi_pallet tbl_sp_mutasi_pallet_auto_set_create_date; Type: TRIGGER; Schema: public; Owner: armasi
--

CREATE TRIGGER tbl_sp_mutasi_pallet_auto_set_create_date BEFORE INSERT OR UPDATE ON public.tbl_sp_mutasi_pallet FOR EACH ROW EXECUTE PROCEDURE public.tbl_sp_mutasi_pallet_auto_set_create_date();

ALTER TABLE public.tbl_sp_mutasi_pallet DISABLE TRIGGER tbl_sp_mutasi_pallet_auto_set_create_date;


--
-- Name: tbl_sp_hasilbj tg_auto_remove_hasilbj; Type: TRIGGER; Schema: public; Owner: armasi
--

CREATE TRIGGER tg_auto_remove_hasilbj AFTER UPDATE ON public.tbl_sp_hasilbj FOR EACH ROW EXECUTE PROCEDURE public.auto_remove_location();


--
-- Name: tbl_sp_mutasi_pallet tg_auto_transit_hasilbj; Type: TRIGGER; Schema: public; Owner: armasi
--

CREATE TRIGGER tg_auto_transit_hasilbj AFTER INSERT ON public.tbl_sp_mutasi_pallet FOR EACH ROW EXECUTE PROCEDURE public.auto_transit_hasilbj();


--
-- Name: tbl_sp_downgrade_pallet tg_mutation_report_downgrade; Type: TRIGGER; Schema: public; Owner: armasi
--

CREATE TRIGGER tg_mutation_report_downgrade AFTER UPDATE ON public.tbl_sp_downgrade_pallet FOR EACH ROW EXECUTE PROCEDURE public.fn_mutation_report_downgrade();


--
-- Name: tbl_sp_mutasi_pallet tg_mutation_report_record; Type: TRIGGER; Schema: public; Owner: armasi
--

CREATE TRIGGER tg_mutation_report_record AFTER INSERT OR DELETE OR UPDATE ON public.tbl_sp_mutasi_pallet FOR EACH ROW EXECUTE PROCEDURE public.fn_mutation_report_record();


--
-- Name: txn_counters tg_record_txn_counter_update; Type: TRIGGER; Schema: public; Owner: armasi
--

CREATE TRIGGER tg_record_txn_counter_update AFTER INSERT OR UPDATE ON public.txn_counters FOR EACH ROW EXECUTE PROCEDURE public.fn_record_txn_counter_update();


--
-- Name: tbl_sp_permintaan_brp tg_tbl_sp_permintaan_brp_no_double_request; Type: TRIGGER; Schema: public; Owner: armasi
--

CREATE TRIGGER tg_tbl_sp_permintaan_brp_no_double_request BEFORE INSERT OR UPDATE ON public.tbl_sp_permintaan_brp FOR EACH ROW EXECUTE PROCEDURE public.fn_tbl_sp_permintaan_brp_no_double_request();

ALTER TABLE public.tbl_sp_permintaan_brp DISABLE TRIGGER tg_tbl_sp_permintaan_brp_no_double_request;


--
-- Name: tbl_surat_jalan tg_tbl_surat_jalan_auto_set_updated_at; Type: TRIGGER; Schema: public; Owner: armasi
--

CREATE TRIGGER tg_tbl_surat_jalan_auto_set_updated_at BEFORE INSERT OR UPDATE ON public.tbl_surat_jalan FOR EACH ROW EXECUTE PROCEDURE public.fn_tbl_surat_jalan_auto_set_updated_at();


--
-- Name: tbl_sp_hasilbj tg_update_itemkode; Type: TRIGGER; Schema: public; Owner: armasi
--

CREATE TRIGGER tg_update_itemkode BEFORE UPDATE ON public.tbl_sp_hasilbj FOR EACH ROW EXECUTE PROCEDURE public.update_itemkode();


--
-- Name: tbl_sp_hasilbj tg_validate_hasilbj; Type: TRIGGER; Schema: public; Owner: armasi
--

CREATE TRIGGER tg_validate_hasilbj BEFORE INSERT OR UPDATE ON public.tbl_sp_hasilbj FOR EACH ROW EXECUTE PROCEDURE public.validate_hasilbj();

ALTER TABLE public.tbl_sp_hasilbj DISABLE TRIGGER tg_validate_hasilbj;


--
-- Name: inv_opname tr_insert_to_hist; Type: TRIGGER; Schema: public; Owner: armasi
--

CREATE TRIGGER tr_insert_to_hist AFTER INSERT ON public.inv_opname FOR EACH ROW EXECUTE PROCEDURE public.fn_insert_to_hist();

ALTER TABLE public.inv_opname DISABLE TRIGGER tr_insert_to_hist;


--
-- Name: tbl_mreqitem $1; Type: FK CONSTRAINT; Schema: man; Owner: armasi
--

ALTER TABLE ONLY man.tbl_mreqitem
    ADD CONSTRAINT "$1" FOREIGN KEY (mrequest_kode) REFERENCES man.tbl_mrequest(mrequest_kode) ON UPDATE CASCADE;


--
-- Name: tbl_mr_detail tbl_mr_detail__tbl_mr; Type: FK CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_mr_detail
    ADD CONSTRAINT tbl_mr_detail__tbl_mr FOREIGN KEY (mr_code) REFERENCES man.tbl_mr(mr_code) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tbl_psp_detail tbl_psp_detail__tbl_psp; Type: FK CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_psp_detail
    ADD CONSTRAINT tbl_psp_detail__tbl_psp FOREIGN KEY (psp_code) REFERENCES man.tbl_psp(psp_code) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tbl_mrspk_detail tbl_spkmr_detail__tbl_spkmr; Type: FK CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_mrspk_detail
    ADD CONSTRAINT tbl_spkmr_detail__tbl_spkmr FOREIGN KEY (spkmr_code) REFERENCES man.tbl_mrspk(spkmr_code) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tbl_wo_detail tbl_wo_detail__tbl_wo; Type: FK CONSTRAINT; Schema: man; Owner: armasi_man
--

ALTER TABLE ONLY man.tbl_wo_detail
    ADD CONSTRAINT tbl_wo_detail__tbl_wo FOREIGN KEY (wo_code) REFERENCES man.tbl_wo(wo_code) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: category $1; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.category
    ADD CONSTRAINT "$1" FOREIGN KEY (jenis_kode) REFERENCES public.tbl_jenis(jenis_kode) ON UPDATE CASCADE;


--
-- Name: item $1; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.item
    ADD CONSTRAINT "$1" FOREIGN KEY (category_kode) REFERENCES public.category(category_kode) ON UPDATE CASCADE;


--
-- Name: departemen $1; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.departemen
    ADD CONSTRAINT "$1" FOREIGN KEY (plan_kode) REFERENCES public.plan(plan_kode) ON UPDATE CASCADE;


--
-- Name: tbl_ba_muat_detail $1; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_ba_muat_detail
    ADD CONSTRAINT "$1" FOREIGN KEY (no_ba) REFERENCES public.tbl_ba_muat(no_ba) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tbl_detail_surat_jalan $1; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_detail_surat_jalan
    ADD CONSTRAINT "$1" FOREIGN KEY (no_surat_jalan) REFERENCES public.tbl_surat_jalan(no_surat_jalan) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: whouse $1; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.whouse
    ADD CONSTRAINT "$1" FOREIGN KEY (plan_kode) REFERENCES public.plan(plan_kode) ON UPDATE CASCADE;


--
-- Name: tbl_detail_tarif_surat_jalan $1; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_detail_tarif_surat_jalan
    ADD CONSTRAINT "$1" FOREIGN KEY (surat_jalan) REFERENCES public.tbl_tarif_surat_jalan(surat_jalan) ON UPDATE CASCADE;


--
-- Name: tbl_invoice $1; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_invoice
    ADD CONSTRAINT "$1" FOREIGN KEY (customer_kode) REFERENCES public.tbl_customer(customer_kode);


--
-- Name: tbl_detail_invoice $1; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_detail_invoice
    ADD CONSTRAINT "$1" FOREIGN KEY (no_inv) REFERENCES public.tbl_invoice(no_inv);


--
-- Name: item_locker $1; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.item_locker
    ADD CONSTRAINT "$1" FOREIGN KEY (warehouse_kode) REFERENCES public.whouse(warehouse_kode) ON UPDATE CASCADE;


--
-- Name: inv_master_area_detail inv_master_area_detail_plan_kode_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inv_master_area_detail
    ADD CONSTRAINT inv_master_area_detail_plan_kode_fkey FOREIGN KEY (plan_kode, kd_area) REFERENCES public.inv_master_area(plan_kode, kd_area) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: inv_master_lok_pallet inv_master_lok_pallet_iml_plan_kode_fkey; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.inv_master_lok_pallet
    ADD CONSTRAINT inv_master_lok_pallet_iml_plan_kode_fkey FOREIGN KEY (iml_plan_kode, iml_kd_area, iml_no_baris) REFERENCES public.inv_master_area_detail(plan_kode, kd_area, kd_baris) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: inv_opname inv_opname_io_no_pallet_fkey; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.inv_opname
    ADD CONSTRAINT inv_opname_io_no_pallet_fkey FOREIGN KEY (io_no_pallet) REFERENCES public.tbl_sp_hasilbj(pallet_no) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: inv_opname inv_opname_io_plan_kode_fkey; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.inv_opname
    ADD CONSTRAINT inv_opname_io_plan_kode_fkey FOREIGN KEY (io_plan_kode, io_kd_lok) REFERENCES public.inv_master_lok_pallet(iml_plan_kode, iml_kd_lok) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: inventory_checking_details inventory_checking_details_ref_no_fkey; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.inventory_checking_details
    ADD CONSTRAINT inventory_checking_details_ref_no_fkey FOREIGN KEY (ref_no) REFERENCES public.inventory_checking(ref_no);


--
-- Name: item_gbj_stockblock item_tbl_gbj_stockblock; Type: FK CONSTRAINT; Schema: public; Owner: armasi_qc
--

ALTER TABLE ONLY public.item_gbj_stockblock
    ADD CONSTRAINT item_tbl_gbj_stockblock FOREIGN KEY (order_id) REFERENCES public.tbl_gbj_stockblock(order_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: pallet_events pallet_events_event_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.pallet_events
    ADD CONSTRAINT pallet_events_event_id_fkey FOREIGN KEY (event_id) REFERENCES public.pallet_event_types(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tbl_autority tbl_autority_sub_f_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_autority
    ADD CONSTRAINT tbl_autority_sub_f_id_fkey FOREIGN KEY (sub_f_id) REFERENCES public.tbl_sub_feature(sub_f_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tbl_autority tbl_autority_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_autority
    ADD CONSTRAINT tbl_autority_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.tbl_user(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tbl_sp_downgrade_pallet tbl_sp_downgrade_pallet_item_kode_lama_fkey; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sp_downgrade_pallet
    ADD CONSTRAINT tbl_sp_downgrade_pallet_item_kode_lama_fkey FOREIGN KEY (item_kode_lama) REFERENCES public.item(item_kode) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: tbl_sp_hasilbj tbl_sp_hasilbj_plan_plan_kode_fk; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sp_hasilbj
    ADD CONSTRAINT tbl_sp_hasilbj_plan_plan_kode_fk FOREIGN KEY (plan_kode) REFERENCES public.plan(plan_kode) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: tbl_sp_mutasi_pallet tbl_sp_mutasi_pallet_plan_kode_fkey; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sp_mutasi_pallet
    ADD CONSTRAINT tbl_sp_mutasi_pallet_plan_kode_fkey FOREIGN KEY (plan_kode, pallet_no) REFERENCES public.tbl_sp_hasilbj(plan_kode, pallet_no);


--
-- Name: tbl_sub_feature tbl_sub_feature_feature_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sub_feature
    ADD CONSTRAINT tbl_sub_feature_feature_id_fkey FOREIGN KEY (feature_id) REFERENCES public.tbl_feature(feature_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tbl_sub_plant_sec tbl_sub_plant_sec_tss_plant_fkey; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sub_plant_sec
    ADD CONSTRAINT tbl_sub_plant_sec_tss_plant_fkey FOREIGN KEY (tss_plant) REFERENCES public.plan(plan_kode);


--
-- Name: tbl_sub_plant_sec tbl_sub_plant_sec_tss_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.tbl_sub_plant_sec
    ADD CONSTRAINT tbl_sub_plant_sec_tss_user_fkey FOREIGN KEY (tss_user) REFERENCES public.tbl_user(user_name) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: txn_counters_details txn_counters_details_plant_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: armasi
--

ALTER TABLE ONLY public.txn_counters_details
    ADD CONSTRAINT txn_counters_details_plant_id_fkey FOREIGN KEY (plant_id, txn_id) REFERENCES public.txn_counters(plant_id, txn_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: qcdaily_eco qcdaily_eco__qc_md_defect; Type: FK CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qcdaily_eco
    ADD CONSTRAINT qcdaily_eco__qc_md_defect FOREIGN KEY (qec_defect_kode) REFERENCES qc.qc_md_defect(qmd_kode) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: qcdaily_eco qcdaily_eco__qc_md_motif; Type: FK CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qcdaily_eco
    ADD CONSTRAINT qcdaily_eco__qc_md_motif FOREIGN KEY (qec_motif) REFERENCES qc.qc_md_motif(qmm_nama) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: qcdaily_exp qcdaily_exp__qc_md_motif; Type: FK CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qcdaily_exp
    ADD CONSTRAINT qcdaily_exp__qc_md_motif FOREIGN KEY (qex_motif) REFERENCES qc.qc_md_motif(qmm_nama) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: qcdaily_kw qcdaily_kw__qc_md_defect; Type: FK CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qcdaily_kw
    ADD CONSTRAINT qcdaily_kw__qc_md_defect FOREIGN KEY (qkw_defect_kode) REFERENCES qc.qc_md_defect(qmd_kode) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: qcdaily_kw qcdaily_kw__qc_md_motif; Type: FK CONSTRAINT; Schema: qc; Owner: armasi_qc
--

ALTER TABLE ONLY qc.qcdaily_kw
    ADD CONSTRAINT qcdaily_kw__qc_md_motif FOREIGN KEY (qkw_motif) REFERENCES qc.qc_md_motif(qmm_nama) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: FUNCTION dblink_connect_u(text); Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON FUNCTION public.dblink_connect_u(text) FROM PUBLIC;


--
-- Name: FUNCTION dblink_connect_u(text, text); Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON FUNCTION public.dblink_connect_u(text, text) FROM PUBLIC;


--
-- Name: SEQUENCE tbl_detail_surat_jalan_detail_surat_jalan_id_seq; Type: ACL; Schema: public; Owner: armasi
--

REVOKE ALL ON SEQUENCE public.tbl_detail_surat_jalan_detail_surat_jalan_id_seq FROM armasi;
GRANT SELECT,UPDATE ON SEQUENCE public.tbl_detail_surat_jalan_detail_surat_jalan_id_seq TO armasi;


--
-- Name: SEQUENCE tbl_feature_feature_id_seq; Type: ACL; Schema: public; Owner: armasi
--

REVOKE ALL ON SEQUENCE public.tbl_feature_feature_id_seq FROM armasi;
GRANT SELECT,UPDATE ON SEQUENCE public.tbl_feature_feature_id_seq TO armasi;


--
-- Name: SEQUENCE tbl_iso_iso_id_seq; Type: ACL; Schema: public; Owner: armasi
--

REVOKE ALL ON SEQUENCE public.tbl_iso_iso_id_seq FROM armasi;
GRANT SELECT,UPDATE ON SEQUENCE public.tbl_iso_iso_id_seq TO armasi;


--
-- Name: SEQUENCE tbl_kode_kode_id_seq; Type: ACL; Schema: public; Owner: armasi
--

REVOKE ALL ON SEQUENCE public.tbl_kode_kode_id_seq FROM armasi;
GRANT SELECT,UPDATE ON SEQUENCE public.tbl_kode_kode_id_seq TO armasi;


--
-- Name: SEQUENCE tbl_satuan_satuan_id_seq; Type: ACL; Schema: public; Owner: armasi
--

REVOKE ALL ON SEQUENCE public.tbl_satuan_satuan_id_seq FROM armasi;
GRANT SELECT,UPDATE ON SEQUENCE public.tbl_satuan_satuan_id_seq TO armasi;


--
-- Name: SEQUENCE tbl_sub_feature_sub_f_id_seq; Type: ACL; Schema: public; Owner: armasi
--

REVOKE ALL ON SEQUENCE public.tbl_sub_feature_sub_f_id_seq FROM armasi;
GRANT SELECT,UPDATE ON SEQUENCE public.tbl_sub_feature_sub_f_id_seq TO armasi;


--
-- Name: SEQUENCE tbl_tarif_angkutan_tarif_id_seq; Type: ACL; Schema: public; Owner: armasi
--

REVOKE ALL ON SEQUENCE public.tbl_tarif_angkutan_tarif_id_seq FROM armasi;
GRANT SELECT,UPDATE ON SEQUENCE public.tbl_tarif_angkutan_tarif_id_seq TO armasi;


--
-- PostgreSQL database dump complete
--

