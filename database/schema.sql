CREATE DATABASE IF NOT EXISTS freelance_platform
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE freelance_platform;

CREATE TABLE NguoiDung (
  ID        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  HoTen     VARCHAR(100) NOT NULL,
  Email     VARCHAR(150) NOT NULL,
  MatKhau   VARCHAR(255) NOT NULL,
  VaiTro    ENUM('khachhang','freelancer') NOT NULL,
  KyNang    VARCHAR(500) NULL,
  NgayTao   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_nguoidung_email (Email)
) ENGINE=InnoDB;

CREATE TABLE DuAn (
  ID            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  TieuDe        VARCHAR(150) NOT NULL,
  MoTa          TEXT NOT NULL,
  NganSach      DECIMAL(15,0) NOT NULL,
  HanNhanDeXuat DATE NOT NULL,
  ID_KhachHang  INT UNSIGNED NOT NULL,
  TrangThai     ENUM('mo','dang_thuc_hien','hoan_thanh','da_huy') NOT NULL DEFAULT 'mo',
  KyNang        VARCHAR(500) NULL,
  NgayTao       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_duan_khachhang FOREIGN KEY (ID_KhachHang)
    REFERENCES NguoiDung(ID) ON DELETE CASCADE,
  INDEX idx_duan_trangthai (TrangThai),
  INDEX idx_duan_ngansach (NganSach),
  FULLTEXT KEY ft_duan_tim (TieuDe, MoTa)
) ENGINE=InnoDB;

CREATE TABLE DeXuat (
  ID              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ID_DuAn         INT UNSIGNED NOT NULL,
  ID_Freelancer   INT UNSIGNED NOT NULL,
  ChiPhiDeXuat    DECIMAL(15,0) NOT NULL,
  ThoiGianThucHien SMALLINT UNSIGNED NOT NULL,
  NoiDung         TEXT NOT NULL,
  TrangThai       ENUM('cho_duyet','duoc_chon','tu_choi') NOT NULL DEFAULT 'cho_duyet',
  NgayTao         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_dexuat_duan FOREIGN KEY (ID_DuAn)
    REFERENCES DuAn(ID) ON DELETE CASCADE,
  CONSTRAINT fk_dexuat_freelancer FOREIGN KEY (ID_Freelancer)
    REFERENCES NguoiDung(ID) ON DELETE CASCADE,
  UNIQUE KEY uq_moi_freelancer_mot_de_xuat (ID_DuAn, ID_Freelancer)
) ENGINE=InnoDB;

CREATE TABLE DanhGia (
  ID                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ID_DuAn            INT UNSIGNED NOT NULL,
  ID_NguoiDanhGia    INT UNSIGNED NOT NULL,
  ID_NguoiDuocDanhGia INT UNSIGNED NOT NULL,
  Diem               TINYINT UNSIGNED NOT NULL,
  NhanXet            VARCHAR(500) NULL,
  NgayTao            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_diem CHECK (Diem BETWEEN 1 AND 5),
  CONSTRAINT fk_dg_duan FOREIGN KEY (ID_DuAn) REFERENCES DuAn(ID) ON DELETE CASCADE,
  CONSTRAINT fk_dg_nguoidanhgia FOREIGN KEY (ID_NguoiDanhGia) REFERENCES NguoiDung(ID) ON DELETE CASCADE,
  CONSTRAINT fk_dg_nguoiduocdg FOREIGN KEY (ID_NguoiDuocDanhGia) REFERENCES NguoiDung(ID) ON DELETE CASCADE,
  UNIQUE KEY uq_mot_danhgia (ID_DuAn, ID_NguoiDanhGia)
) ENGINE=InnoDB;