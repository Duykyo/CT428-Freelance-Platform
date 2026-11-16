# Đặc tả chức năng - Nền tảng Kết nối Việc làm Tự do (CT428)

Người soạn: Quang. Phiên bản nháp 2. Căn cứ: database/schema.sql và docs/hop-dong-api.md (v0.1 của Duy). Nếu hai file có chỗ khác nhau thì theo hợp đồng.

## 1. Vai trò

| Vai trò | Giá trị trong DB (NguoiDung.VaiTro) | Được làm |
|---|---|---|
| Khách hàng | khachhang | Đăng, sửa, hủy, xóa dự án; xem đề xuất, chọn freelancer; đánh dấu hoàn thành; đánh giá freelancer |
| Freelancer | freelancer | Tìm dự án; gửi, sửa, rút đề xuất; quản lý hồ sơ và kỹ năng; đánh giá khách hàng; nhận đánh giá |

Mọi người dùng: đăng ký, đăng nhập, đăng xuất, xem và sửa hồ sơ của chính mình, đổi mật khẩu.

## 2. Dữ liệu chính

- NguoiDung: ID, HoTen (tối đa 100), Email (duy nhất, tối đa 150), MatKhau (lưu dạng băm), VaiTro, KyNang (tối đa 500 ký tự, các kỹ năng cách nhau bằng dấu phẩy, có thể trống), NgayTao.
- DuAn: ID, TieuDe (tối đa 150), MoTa, NganSach (số nguyên, VNĐ), HanNhanDeXuat (ngày), ID_KhachHang, TrangThai, KyNang (kỹ năng yêu cầu, tối đa 500 ký tự), NgayTao.
- DeXuat: ID, ID_DuAn, ID_Freelancer, ChiPhiDeXuat, ThoiGianThucHien (số ngày), NoiDung, TrangThai, NgayTao.
- DanhGia: ID, ID_DuAn, ID_NguoiDanhGia, ID_NguoiDuocDanhGia, Diem (1 đến 5), NhanXet (tối đa 500 ký tự), NgayTao.

## 3. Vòng đời dự án (DuAn.TrangThai)

| Mã | Ý nghĩa |
|---|---|
| mo | Đang nhận đề xuất (mặc định khi tạo) |
| dang_thuc_hien | Khách hàng đã chọn một đề xuất |
| hoan_thanh | Công việc xong, được đánh giá |
| da_huy | Khách hàng hủy dự án |

Chuyển trạng thái (chỉ chủ dự án thực hiện):

| Từ | Sang | Cách thực hiện | Điều kiện |
|---|---|---|---|
| mo | dang_thuc_hien | proposal/select | Đề xuất được chọn đang cho_duyet |
| mo | da_huy | project/cancel | |
| dang_thuc_hien | hoan_thanh | project/complete | |
| dang_thuc_hien | da_huy | project/cancel | Các đề xuất giữ nguyên trạng thái |
| hoan_thanh, da_huy | (kết thúc) | không đổi được | |

Quy tắc đi kèm:
- Sửa dự án: chỉ khi mo.
- Xóa dự án: chỉ khi mo hoặc da_huy; các đề xuất của dự án bị xóa theo.

## 4. Vòng đời đề xuất (DeXuat.TrangThai)

| Mã | Ý nghĩa |
|---|---|
| cho_duyet | Freelancer vừa gửi, chờ khách hàng (mặc định) |
| duoc_chon | Khách hàng đã chọn đề xuất này |
| tu_choi | Đề xuất không được chọn |

Quy tắc:
- Chỉ freelancer gửi được đề xuất; khách hàng gọi thì bị 403.
- Mỗi freelancer chỉ một đề xuất cho mỗi dự án (UNIQUE ID_DuAn + ID_Freelancer); gửi lần hai bị 409.
- Chỉ gửi khi dự án mo và còn hạn nhận đề xuất.
- Chọn đề xuất chạy trong một giao dịch: đề xuất được chọn thành duoc_chon, các đề xuất khác của dự án thành tu_choi, dự án thành dang_thuc_hien.
- Sửa và rút đề xuất: chỉ khi đề xuất cho_duyet và dự án mo. Rút đề xuất là xóa bản ghi (không có trạng thái "đã rút").

## 5. Quy tắc nghiệp vụ

1. Hạn nhận đề xuất: còn hạn khi HanNhanDeXuat lớn hơn hoặc bằng ngày hiện tại. Quá hạn thì không nhận đề xuất mới và không hiện trong kết quả tìm kiếm; trạng thái dự án không tự đổi.
2. Ai được chọn freelancer: chỉ chủ dự án, chọn một đề xuất cho mỗi dự án.
3. Ngân sách là số nguyên lớn hơn 0 (VNĐ).
4. Đánh giá (rating):
   - Chỉ đánh giá khi dự án hoan_thanh.
   - Người đánh giá là chủ dự án hoặc freelancer có đề xuất duoc_chon của dự án đó; bên được đánh giá do server tự xác định.
   - Mỗi người đánh giá một lần cho mỗi dự án (UNIQUE ID_DuAn + ID_NguoiDanhGia); trùng thì 409.
   - Không tự đánh giá chính mình.
   - Điểm là số nguyên từ 1 đến 5; nhận xét tối đa 500 ký tự, được để trống.
   - Hồ sơ hiển thị điểm trung bình và số lượt đánh giá mà người đó nhận được (chưa có thì số lượt là 0).
5. Hồ sơ: mỗi người chỉ sửa hồ sơ của chính mình, chỉ sửa họ tên và kỹ năng (tối đa 500 ký tự). Email không đổi được.
6. Đổi mật khẩu: phải nhập đúng mật khẩu cũ; mật khẩu mới từ 8 ký tự; xác nhận phải trùng mật khẩu mới. Mật khẩu chỉ lưu dạng băm, không trả trong JSON.
7. Quyền truy cập và mã lỗi: chưa đăng nhập 401; sai vai trò hoặc không phải chủ sở hữu 403; không tồn tại hoặc không được xem 404; trùng dữ liệu hoặc sai trạng thái 409; thiếu CSRF 419; dữ liệu sai 422. Form POST có CSRF.

## 6. Tìm kiếm dự án

Route project/search (GET, trả JSON), dành cho freelancer. Chỉ trả dự án có TrangThai = mo và còn hạn.

| Tham số | Ý nghĩa |
|---|---|
| q | Từ khóa, tìm trong TieuDe và MoTa |
| min, max | Khoảng ngân sách (số nguyên) |
| kynang | Kỹ năng yêu cầu của dự án |
| sort | newest (mới nhất), budget_desc (ngân sách giảm), budget_asc (ngân sách tăng), deadline (hạn gần nhất) |
| page | Số trang, bắt đầu từ 1; mỗi trang 10 mục |

Phản hồi: items, page, totalPages, total (theo hợp đồng Mục 6).

## 7. Luồng người dùng

Khách hàng:
1. Đăng ký (chọn vai trò khách hàng), đăng nhập.
2. Đăng dự án (tiêu đề, mô tả, ngân sách, hạn nhận đề xuất, kỹ năng); xem Dự án của tôi.
3. Xem danh sách đề xuất của dự án, chọn một freelancer.
4. Khi xong, đánh dấu hoàn thành (hoặc hủy dự án khi cần).
5. Đánh giá freelancer (điểm 1 đến 5, nhận xét).

Freelancer:
1. Đăng ký (chọn vai trò freelancer), đăng nhập, cập nhật hồ sơ và kỹ năng.
2. Tìm dự án theo từ khóa, ngân sách, kỹ năng, sắp xếp.
3. Xem chi tiết dự án, gửi đề xuất (chi phí, thời gian thực hiện, nội dung).
4. Theo dõi Đề xuất của tôi; sửa hoặc rút khi còn cho_duyet.
5. Khi được chọn và dự án hoàn thành: đánh giá khách hàng, xem điểm đánh giá trung bình trong hồ sơ.

## 8. Việc cần chốt với Duy

- Rating hai chiều: hợp đồng v0.1 cho cả khách hàng và freelancer đánh giá nhau; kế hoạch ban đầu của Quang chỉ có khách hàng đánh giá freelancer. Cần Duy chốt.
- Có trang xem hồ sơ của người khác (ví dụ khách hàng xem hồ sơ freelancer) không; hợp đồng hiện mới có hồ sơ của chính mình (profile/index).
- Lọc nhiều kỹ năng: dự án cần khớp tất cả hay chỉ một kỹ năng.