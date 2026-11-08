# Bảng hợp đồng giữa Backend và Giao diện (CT428)

> Phiên bản 0.1 · 08/11/2026 · Người giữ: **Duy** · Người dùng: **Lộc** (giao diện), **Quang** (backend phụ, tài liệu)
> Đặt tại `docs/hop-dong-api.md`.

**Ký hiệu trạng thái:** ✅ đã có trong code · 🟡 chốt trong tài liệu này, code làm theo ngày ghi · 📝 bản nháp, người phụ trách xác nhận.

## 0. Cách dùng

- Đây là **nguồn duy nhất** cho route, tên trường form, biến view và mẫu JSON. Code và giao diện đều phải khớp file này.
- **Lộc:** dựng form và view đúng tên trường, tên biến ở đây. Backend chưa xong thì dùng dữ liệu giả có **cùng cấu trúc**.
- **Quang:** dựa vào file này để viết tìm kiếm, hồ sơ, rating; xác nhận hoặc sửa Mục 6 trong ngày 3 (11/11).
- **Quy tắc đổi:** đổi route, tên trường, biến view hoặc schema thì (1) sửa file này, (2) ghi vào Mục 8, (3) báo cả nhóm **trong cùng ngày**. Không đổi âm thầm.
- **Lịch giao backend (theo kế hoạch của Duy):**

| Hạng mục | Ngày giao |
|---|---|
| Đăng ký, đăng nhập, đăng xuất | đã có mã, đang kiểm chứng |
| Model dự án | ngày 3 (11/11) |
| Dự án của tôi (`mine`) | ngày 4 (12/11) |
| Chi tiết, sửa, hủy, xóa dự án | ngày 5 (13/11) |
| Gửi, sửa, rút đề xuất | ngày 8 đến 9 (16 đến 17/11) |
| Chọn đề xuất, hoàn thành dự án | ngày 10 (18/11) |

## 1. Quy ước chung

### 1.1. Form ghi dữ liệu (POST)

Mọi form ghi dữ liệu đều có:

```html
<form action="<?= url('controller/action') ?>" method="post" data-ajax novalidate>
  <input type="hidden" name="csrf" value="<?= Csrf::token() ?>">
  <div class="form-alert d-none" role="alert"></div>
  ...
</form>
```

`app.js` gửi form bằng `fetch`, đọc JSON, hiện thông báo và tự chuyển trang nếu có `redirect`.

### 1.2. Mẫu JSON phản hồi

Thành công:

```json
{ "success": true, "message": "Đăng dự án thành công!", "redirect": "/CT428-Freelance-Platform/public/project/mine" }
```

Lỗi kiểm tra dữ liệu (HTTP 422), lỗi theo từng trường nằm trong `errors`, **khóa là tên trường form**:

```json
{ "success": false, "message": "Vui lòng kiểm tra lại thông tin.", "errors": { "tieude": "Tiêu đề phải từ 5 đến 150 ký tự." } }
```

Lỗi khác (không có `errors`):

```json
{ "success": false, "message": "Email hoặc mật khẩu không đúng." }
```

| Mã HTTP | Khi nào | `message` |
|---|---|---|
| 200 | Thành công | tùy thao tác |
| 401 | Chưa đăng nhập (có kèm `redirect` về trang đăng nhập) | Bạn cần đăng nhập. |
| 403 | Sai vai trò hoặc không phải chủ sở hữu | Bạn không có quyền thực hiện thao tác này. |
| 404 | Không tồn tại hoặc không được xem | Không tìm thấy. |
| 405 | Không phải POST | Phương thức không hợp lệ. |
| 409 | Trùng dữ liệu hoặc trạng thái không cho phép thao tác | tùy trường hợp, ví dụ Email này đã được đăng ký. |
| 419 | Thiếu hoặc sai token CSRF | Phiên làm việc hết hạn, vui lòng tải lại trang. |
| 422 | Dữ liệu không hợp lệ | Vui lòng kiểm tra lại thông tin. |
| 500 | Lỗi hệ thống | Có lỗi hệ thống, vui lòng thử lại. |

### 1.3. Đường dẫn và tham số

- Mọi đường dẫn dùng `url('controller/action')`, không viết cứng tên thư mục.
- Tham số trên URL: `?id=<số nguyên dương>`. Trong form POST, `id` là trường ẩn.
- Tên trường form viết thường, không dấu, không gạch ngang (ví dụ `tieude`, `ngansach`).

### 1.4. Phân trang

Tham số `page` (mặc định 1), **10 mục mỗi trang**. View nhận `$page`, `$totalPages`, `$total`. API JSON trả `page`, `totalPages`, `total`.

### 1.5. Định dạng dữ liệu

| Dữ liệu | Lưu và gửi | Hiển thị (do view) |
|---|---|---|
| Ngày | `YYYY-MM-DD` (ô `<input type="date">` gửi đúng dạng này) | `dd/mm/yyyy` |
| Tiền (VNĐ) | số nguyên, không dấu chấm | `number_format($x, 0, ',', '.')` rồi thêm ` ₫` |
| Chuỗi | UTF-8 | luôn qua `e()`; JS dùng `textContent` |

### 1.6. Trang lỗi khi mở bằng GET

- Không có quyền: 403. Không tồn tại hoặc không được xem: 404.
- Giao diện: `app/views/errors/403.php`, `404.php` (Lộc làm ở L2.5). Trước đó backend hiển thị dòng chữ đơn giản.

### 1.7. Quy tắc phân quyền

Mọi route ghi dữ liệu kiểm tra theo thứ tự: POST và CSRF, đã đăng nhập (401), đúng vai trò hoặc đúng chủ sở hữu (403), dữ liệu hợp lệ (422), trạng thái cho phép (409).

## 2. Mã trạng thái dùng chung

### 2.1. Dự án (`DuAn.TrangThai`)

| Mã | Nhãn hiển thị | Màu | Ý nghĩa |
|---|---|---|---|
| `mo` | Đang nhận đề xuất | xanh lá | Dự án mới đăng, freelancer có thể gửi đề xuất |
| `dang_thuc_hien` | Đang thực hiện | xanh dương | Khách hàng đã chọn một đề xuất |
| `hoan_thanh` | Hoàn thành | xanh ngọc đậm | Công việc xong, được đánh giá |
| `da_huy` | Đã hủy | xám | Khách hàng hủy dự án |

### 2.2. Đề xuất (`DeXuat.TrangThai`)

| Mã | Nhãn hiển thị | Màu |
|---|---|---|
| `cho_duyet` | Chờ duyệt | vàng |
| `duoc_chon` | Được chọn | xanh lá |
| `tu_choi` | Bị từ chối | xám |

### 2.3. Quy tắc chuyển trạng thái (🟡 Duy xác nhận)

| Từ | Sang | Cách thực hiện | Ai | Điều kiện |
|---|---|---|---|---|
| `mo` | `dang_thuc_hien` | `proposal/select` | Chủ dự án | Đề xuất được chọn đang `cho_duyet` |
| `mo` | `da_huy` | `project/cancel` | Chủ dự án | |
| `dang_thuc_hien` | `hoan_thanh` | `project/complete` | Chủ dự án | |
| `dang_thuc_hien` | `da_huy` | `project/cancel` | Chủ dự án | Các đề xuất giữ nguyên trạng thái |
| `hoan_thanh`, `da_huy` | (kết thúc) | không đổi được | | |

Quy tắc đi kèm:

- **Xóa dự án** (`project/delete`): chỉ khi `mo` hoặc `da_huy`. Các đề xuất của dự án bị xóa theo.
- **Sửa dự án** (`project/update`): chỉ khi `mo`.
- **Chọn đề xuất:** chạy trong một giao dịch: đề xuất được chọn thành `duoc_chon`, các đề xuất khác của dự án thành `tu_choi`, dự án thành `dang_thuc_hien`.
- **Sửa đề xuất** và **rút đề xuất:** chỉ khi đề xuất `cho_duyet` và dự án `mo`. **Rút đề xuất là xóa bản ghi** (không có trạng thái "đã rút").
- **Còn hạn nhận đề xuất:** `HanNhanDeXuat` lớn hơn hoặc bằng ngày hiện tại.

## 3. Xác thực

| Route | Method | Ai | Trạng thái |
|---|---|---|---|
| `auth/login` | GET | khách vãng lai | ✅ view `auth/login` |
| `auth/register` | GET | khách vãng lai | ✅ view `auth/register` |
| `auth/doRegister` | POST | khách vãng lai | ✅ |
| `auth/doLogin` | POST | khách vãng lai | ✅ |
| `auth/logout` | POST | đã đăng nhập | ✅ |

Người đã đăng nhập mở `auth/login` hoặc `auth/register` sẽ được chuyển về trang chủ theo vai trò (xem 3.4).

### 3.1. `auth/doRegister`

| Trường | Bắt buộc | Quy tắc | Khóa lỗi |
|---|---|---|---|
| `hoten` | có | 2 đến 100 ký tự | `hoten` |
| `email` | có | đúng định dạng email, chưa đăng ký | `email` (trùng email trả 409) |
| `matkhau` | có | từ 8 ký tự | `matkhau` |
| `xacnhan` | có | trùng `matkhau` | `xacnhan` |
| `vaitro` | có | `khachhang` hoặc `freelancer` | `vaitro` |
| `kynang` | không | tối đa 500 ký tự, cách nhau bằng dấu phẩy | `kynang` |

Thành công: `redirect` về `auth/login`.

### 3.2. `auth/doLogin`

| Trường | Bắt buộc | Ghi chú |
|---|---|---|
| `email` | có | |
| `matkhau` | có | |

Sai email hoặc mật khẩu: 401, `message` chung "Email hoặc mật khẩu không đúng." (không báo email nào tồn tại).

### 3.3. `auth/logout`

Chỉ cần `csrf`. Thành công: `redirect` về `auth/login`.

### 3.4. Trang đích sau đăng nhập (`redirect`)

| Vai trò | Hiện tại ✅ | Từ ngày 4 (12/11) 🟡 |
|---|---|---|
| `khachhang` | `project/create` | `project/mine` |
| `freelancer` | `project/index` | `project/index` |

### 3.5. Thông tin người dùng đang đăng nhập

`current_user()` trả `['id' => int, 'name' => string, 'email' => string, 'role' => 'khachhang'|'freelancer']`. View dùng để hiện tên và rẽ nhánh theo vai trò.

## 4. Dự án (phía khách hàng)

| Route | Method | Ai | Mục đích | Trạng thái |
|---|---|---|---|---|
| `project/create` | GET | khách hàng | Form đăng dự án | ✅ |
| `project/store` | POST | khách hàng | Lưu dự án mới | ✅ |
| `project/mine` | GET | khách hàng | Danh sách dự án của tôi | 🟡 ngày 4 |
| `project/show` | GET | đã đăng nhập | Chi tiết dự án | 🟡 ngày 5 |
| `project/edit` | GET | chủ dự án | Form sửa | 🟡 ngày 5 |
| `project/update` | POST | chủ dự án | Lưu chỉnh sửa | 🟡 ngày 5 |
| `project/cancel` | POST | chủ dự án | Hủy dự án | 🟡 ngày 5 |
| `project/delete` | POST | chủ dự án | Xóa dự án | 🟡 ngày 5 |
| `project/complete` | POST | chủ dự án | Đánh dấu hoàn thành | 🟡 ngày 10 |
| `project/index` | GET | freelancer | Trang tìm dự án (giữ chỗ, xem Mục 6) | ✅ |

### 4.1. Trường form của `project/store` và `project/update`

| Trường | Bắt buộc | Quy tắc | Khóa lỗi |
|---|---|---|---|
| `id` | chỉ với `update` | id dự án (trường ẩn) | |
| `tieude` | có | 5 đến 150 ký tự | `tieude` |
| `mota` | có | từ 20 ký tự | `mota` |
| `ngansach` | có | số nguyên lớn hơn 0 (VNĐ) | `ngansach` |
| `han` | có | ngày `YYYY-MM-DD`, từ hôm nay trở đi | `han` |
| `kynang` | không | tối đa 500 ký tự | `kynang` |

### 4.2. Trường của `project/cancel`, `project/delete`, `project/complete`

Chỉ có `id` (trường ẩn) và `csrf`. Trạng thái không cho phép: 409, `message` "Dự án không ở trạng thái cho phép thao tác này." Thành công: `redirect` về `project/mine`.

### 4.3. Biến truyền cho view

**`projects/mine`**

| Biến | Kiểu | Ghi chú |
|---|---|---|
| `$title` | string | |
| `$projects` | mảng | mỗi phần tử có các khóa ở bảng dưới |
| `$page`, `$totalPages`, `$total` | int | |
| `$trangThai` | string | bộ lọc hiện tại: rỗng (tất cả), `mo`, `dang_thuc_hien`, `hoan_thanh`, `da_huy` |

Bộ lọc gửi bằng GET: `project/mine?trangthai=mo&page=2`.

Mỗi phần tử của `$projects`: `ID`, `TieuDe`, `NganSach`, `HanNhanDeXuat`, `TrangThai`, `KyNang`, `NgayTao`, `SoDeXuat` (số đề xuất của dự án).

**`projects/show`**

| Biến | Kiểu | Ghi chú |
|---|---|---|
| `$project` | mảng | `ID`, `TieuDe`, `MoTa`, `NganSach`, `HanNhanDeXuat`, `ID_KhachHang`, `TrangThai`, `KyNang`, `NgayTao`, `TenKhachHang` |
| `$isOwner` | bool | người đang xem là chủ dự án |
| `$proposals` | mảng | danh sách đề xuất, **chỉ có khi `$isOwner`**, ngược lại là `[]` |
| `$myProposal` | mảng hoặc `null` | đề xuất của freelancer đang xem (nếu có) |
| `$canPropose` | bool | freelancer được gửi đề xuất (dự án `mo`, còn hạn, chưa gửi) |

Mỗi phần tử của `$proposals`: `ID`, `ID_Freelancer`, `ChiPhiDeXuat`, `ThoiGianThucHien`, `NoiDung`, `TrangThai`, `NgayTao`, `TenFreelancer`, `KyNangFreelancer`. Khi có rating, thêm `DiemTB` và `SoLuotDanhGia` (📝 Quang).

**`projects/edit`:** `$title`, `$project` (cùng khóa như trên).

### 4.4. Ai xem được `project/show`

- **Chủ dự án:** luôn xem được.
- **Freelancer:** xem được khi dự án `mo`, hoặc khi freelancer đã có đề xuất cho dự án đó.
- Trường hợp còn lại (dự án không tồn tại, hoặc không được xem): 404.

### 4.5. Nút nào hiện khi nào (chủ dự án, trang `show`)

| Nút | Hiện khi |
|---|---|
| Sửa | `TrangThai = mo` |
| Xóa | `TrangThai = mo` hoặc `da_huy` |
| Hủy dự án | `TrangThai = mo` hoặc `dang_thuc_hien` |
| Chọn đề xuất (trên từng đề xuất) | dự án `mo` và đề xuất `cho_duyet` |
| Đánh dấu hoàn thành | `TrangThai = dang_thuc_hien` |

Mọi nút ghi dữ liệu có hộp thoại xác nhận (`data-confirm`). Backend vẫn kiểm tra lại, nên nút hiện sai chỉ gây lỗi 409 chứ không làm hỏng dữ liệu.

## 5. Đề xuất

| Route | Method | Ai | Mục đích | Trạng thái |
|---|---|---|---|---|
| `proposal/store` | POST | freelancer | Gửi đề xuất | 🟡 ngày 9 |
| `proposal/mine` | GET | freelancer | Đề xuất của tôi | 🟡 ngày 9 |
| `proposal/update` | POST | chủ đề xuất | Sửa đề xuất | 🟡 ngày 9 |
| `proposal/withdraw` | POST | chủ đề xuất | Rút đề xuất (xóa) | 🟡 ngày 9 |
| `proposal/select` | POST | chủ dự án | Chọn đề xuất | 🟡 ngày 10 |

### 5.1. Trường form

| Trường | `store` | `update` | Quy tắc | Khóa lỗi |
|---|---|---|---|---|
| `id_duan` | có | | id dự án (trường ẩn) | |
| `id` | | có | id đề xuất (trường ẩn) | |
| `chiphi` | có | có | số nguyên lớn hơn 0 (VNĐ) | `chiphi` |
| `thoigian` | có | có | số nguyên từ 1 đến 365 (ngày) | `thoigian` |
| `noidung` | có | có | từ 20 ký tự | `noidung` |

`proposal/withdraw` và `proposal/select` chỉ có `id` (id đề xuất) và `csrf`. Dự án được chọn do server tự suy ra từ đề xuất, không nhận từ client.

### 5.2. Quy tắc nghiệp vụ khi gửi đề xuất

- Chỉ freelancer gửi được (khách hàng gọi trả 403).
- Mỗi freelancer **một đề xuất cho mỗi dự án**. Gửi lần hai: 409, `message` "Bạn đã gửi đề xuất cho dự án này."
- Dự án phải `mo` và còn hạn. Nếu không: 409, `message` "Dự án đã đóng hoặc hết hạn nhận đề xuất."

### 5.3. Phản hồi của `proposal/select`

Thành công: `message` "Đã chọn đề xuất.", `redirect` về `project/show?id=<id dự án>`. Chọn lần hai hoặc đề xuất không còn `cho_duyet`: 409.

### 5.4. Biến truyền cho view

**`proposals/mine`:** `$title`, `$proposals`, `$page`, `$totalPages`, `$total`. Mỗi phần tử: `ID`, `ID_DuAn`, `TieuDe` (tên dự án), `TrangThaiDuAn`, `HanNhanDeXuat`, `ChiPhiDeXuat`, `ThoiGianThucHien`, `NoiDung`, `TrangThai`, `NgayTao`.

Nút **Sửa** và **Rút** chỉ hiện khi `TrangThai = cho_duyet` và `TrangThaiDuAn = mo`.

## 6. Phần của Quang (📝 bản nháp, Quang xác nhận hoặc sửa)

| Route | Method | Ai | Trường / tham số |
|---|---|---|---|
| `project/search` | GET (JSON) | freelancer | `q`, `min`, `max`, `kynang`, `sort`, `page` |
| `profile/index` | GET | đã đăng nhập | |
| `profile/update` | POST | đã đăng nhập | `hoten`, `kynang` |
| `profile/password` | POST | đã đăng nhập | `matkhaucu`, `matkhaumoi`, `xacnhan` |
| `review/store` | POST | chủ dự án hoặc freelancer được chọn | `id_duan`, `diem` (1 đến 5), `nhanxet` (tối đa 500 ký tự) |

**`project/search`:** điều kiện cố định `TrangThai = mo` và còn hạn. `sort` nhận `newest`, `budget_desc`, `budget_asc`, `deadline`. Phản hồi:

```json
{
  "success": true,
  "items": [
    { "ID": 12, "TieuDe": "Thiết kế website", "NganSach": 5000000, "HanNhanDeXuat": "2026-12-01",
      "KyNang": "php, mysql", "TenKhachHang": "Nguyễn Văn An", "SoDeXuat": 3 }
  ],
  "page": 1, "totalPages": 4, "total": 37
}
```

**`profile/index`:** `$title`, `$profile` (`ID`, `HoTen`, `Email`, `VaiTro`, `KyNang`, `NgayTao`), `$rating` (`DiemTB`, `SoLuot`).

**`review/store`:** dự án phải `hoan_thanh`; người đánh giá là chủ dự án hoặc freelancer có đề xuất `duoc_chon`; bên được đánh giá do server tự xác định; mỗi người đánh giá một lần cho mỗi dự án (trùng: 409). Trang `projects/show` thêm biến `$canReview` và `$reviewed` (bool).

## 7. Khác biệt tạm thời giữa code hiện tại và hợp đồng

- Hiện tại `doLogin` và `project/store` còn chuyển về `project/create`. Từ ngày 4 sẽ đổi sang `project/mine` (Mục 3.4 và 4).
- Trang 403 và 404 hiện chỉ là dòng chữ; sẽ dùng view của Lộc khi có (Mục 1.6).
- Các route đánh dấu 🟡 chưa có mã. Lộc cứ làm theo hợp đồng bằng dữ liệu giả cùng cấu trúc.

## 8. Nhật ký thay đổi

| Ngày | Phiên bản | Thay đổi | Người sửa |
|---|---|---|---|
| 08/11/2026 | 0.1 | Bản đầu: xác thực, dự án khách hàng, đề xuất, mã trạng thái; mục của Quang ở dạng nháp | Duy |
