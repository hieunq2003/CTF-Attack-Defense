#!/usr/bin/env python3

import logging
from typing import Optional

import pymysql
import requests
from ctf_gameserver import checkerlib

log = logging.getLogger(__name__)


DB_USER = "ctf"
DB_PASSWORD = "ctfpass"
DB_NAME = "sis_db"
DB_PORT = 3306


class SisChecker(checkerlib.BaseChecker):
    """
    Checker cho dịch vụ Student Information System (SIS) chạy trong container teamX-server.

    - place_flag: sinh flag từ gameserver, ghi vào bảng flag_storage (tick, flag)
    - check_service: gọi HTTP tới web SIS xem có sống không
    - check_flag: đọc flag_storage theo tick, so sánh với flag gameserver
    """

    # ------------------------- helper -------------------------
    
    def _db_connect(self) -> pymysql.connections.Connection:
        """
        Kết nối MariaDB trong container teamX-server tương ứng team hiện tại.
        team 1 -> team1-server
        team 2 -> team2-server
        team 3 -> team3-server
        """
        # CÁCH ỔN ĐỊNH: dùng tên container để Docker DNS tự resolve
        host = f"team{self.team}-server"
        log.info("Connecting to DB %s (team=%s)", host, self.team)

        conn = pymysql.connect(
            host=host,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME,
            port=DB_PORT,
            charset="utf8mb4",
            autocommit=True,
            connect_timeout=3,
            read_timeout=3,
            write_timeout=3,
        )
        return conn


    # ------------------------- place_flag -------------------------

    def place_flag(self, tick: int) -> checkerlib.CheckResult:
        """
        Được gọi mỗi tick để ĐẶT flag mới.

        Bước:
        1) Hỏi gameserver xin flag của tick này: checkerlib.get_flag(tick)
        2) Ghi (tick, flag) vào bảng flag_storage của DB team tương ứng
        3) Ghi thêm flagid (ở đây ta dùng tick luôn) để gameserver nhớ
        """
        flag = checkerlib.get_flag(tick)
        log.info("Placing flag for team=%s tick=%s : %s", self.team, tick, flag)

        try:
            conn = self._db_connect()
            with conn.cursor() as cur:
                # Nếu tick đã tồn tại thì update flag luôn cho chắc
                cur.execute(
                    """
                    INSERT INTO flag_storage (tick, flag)
                    VALUES (%s, %s)
                    ON DUPLICATE KEY UPDATE flag = VALUES(flag)
                    """,
                    (tick, flag),
                )
            # Không cần conn.commit() vì autocommit=True
        except Exception as exc:  # pylint: disable=broad-except
            log.exception("place_flag DB error (team=%s tick=%s): %s", self.team, tick, exc)
            return checkerlib.CheckResult.DOWN
        finally:
            try:
                conn.close()  # type: ignore[name-defined]
            except Exception:
                pass

        # Lưu “flagid” cho tick này, ở đây ta dùng tick luôn cho đơn giản
        checkerlib.set_flagid(str(tick))
        return checkerlib.CheckResult.OK

    # ------------------------- check_service -------------------------
    def check_service(self) -> checkerlib.CheckResult:
        """
        Kiểm tra web SIS còn sống không:
        - GET http://teamX-server/
        - status_code phải 200
        """
        url = f"http://team{self.team}-server/"
        log.info("Checking service health at %s", url)

        try:
            resp = requests.get(url, timeout=5)
        except Exception as exc:  # pylint: disable=broad-except
            log.warning("HTTP error when checking %s: %s", url, exc)
            return checkerlib.CheckResult.DOWN

        if resp.status_code != 200:
            log.warning("Unexpected HTTP status %s for %s", resp.status_code, url)
            return checkerlib.CheckResult.DOWN

        # Tạm thời: chỉ cần 200 là OK
        return checkerlib.CheckResult.OK

    
    # ------------------------- check_flag -------------------------

    def check_flag(self, tick: int) -> checkerlib.CheckResult:
        """
        Lấy flag “đúng” từ gameserver rồi so sánh với flag trong DB:
        - Nếu không tìm thấy row tick -> FLAG_NOT_FOUND
        - Nếu có nhưng flag khác -> FLAG_NOT_FOUND (coi như bị mất / sửa)
        - Nếu giống -> OK
        """
        expected_flag = checkerlib.get_flag(tick)
        log.info("Checking flag for team=%s tick=%s", self.team, tick)

        try:
            conn = self._db_connect()
            with conn.cursor() as cur:
                cur.execute("SELECT flag FROM flag_storage WHERE tick = %s", (tick,))
                row = cur.fetchone()
        except Exception as exc:  # pylint: disable=broad-except
            log.exception("check_flag DB error (team=%s tick=%s): %s", self.team, tick, exc)
            return checkerlib.CheckResult.DOWN
        finally:
            try:
                conn.close()  # type: ignore[name-defined]
            except Exception:
                pass

        if row is None:
            log.warning("No flag for tick=%s in DB (team=%s)", tick, self.team)
            return checkerlib.CheckResult.FLAG_NOT_FOUND

        stored_flag = row[0]
        if stored_flag != expected_flag:
            log.warning(
                "Flag mismatch for tick=%s (team=%s): stored=%r expected=%r",
                tick,
                self.team,
                stored_flag,
                expected_flag,
            )
            return checkerlib.CheckResult.FLAG_NOT_FOUND

        return checkerlib.CheckResult.OK


if __name__ == "__main__":
    checkerlib.run_check(SisChecker)
