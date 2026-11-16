# Đặc tả chức năng - Nền tảng Kết nối Việc làm Tự do (CT428)

Người soạn: Quang. Phiên bản nháp 1. Căn cứ: database/schema.sql.

## 1. Vai trò

| Vai trò | Giá trị trong DB (NguoiDung.VaiTro) | Được làm |
|---|---|---|
| Khách hàng | khachhang | Đăng dự án, xem đề xuất, chọn freelancer, hoàn thành hoặc hủy dự án, đánh giá freelancer |
| Freelancer | freelancer | Tìm dự án, gửi đề xuất, quản lý hồ sơ và kỹ năng, nhận đánh giá |

Mọi người dùng: đăng ký, đăng nhập, đăng xuất, xem và sửa hồ sơ của chính mình, đổi mật khẩu.

## 2. Dữ liệu chính

- NguoiDung: ID, HoTen (tối đa 100), Email (duy nhất, tối đa 150), MatKhau (lưu dạng băm), VaiTro, KyNang (chuỗi tối đa 500 ký tự, có thể trống), NgayTao.
- DuAn: ID, TieuDe (tối đa 150), MoTa, NganSach (số nguyên), HanNhanDeXuat (ngày), ID_KhachHang, TrangThai, KyNang (kỹ năng yêu cầu, tối đa 500 ký tự), NgayTao.
- DeXuat: ID, ID_DuAn, ID_Freelancer, ChiPhiDeXuat, ThoiGianThucHien (số ngày), NoiDung, TrangThai, NgayTao.
- DanhGia: ID, ID_DuAn, ID_NguoiDanhGia, ID_NguoiDuocDanhGia, Diem (1 đến 5), NhanXet (tối đa 500 ký tự), NgayTao.

## 3. Vòng đời dự án (DuAn.TrangThai)

| Mã | Ý nghĩa |
|---|---|
| mo | Dự án đang mở, nhận đề xuất (mặc định khi tạo) |
| dang_thuc_hien | Khách hàng đã chọn freelancer, đang làm |
| hoan_thanh | Dự án đã xong |
| da_huy | Dự án bị hủy |

Chuyển trạng thái:
- mo -> dang_thuc_hien: khi khách hàng chọn một đề xuất.
- mo -> da_huy: khách hàng hủy khi chưa chọn ai.
- dang_thuc_hien -> hoan_thanh: khách hàng xác nhận hoàn thành.
- dang_thuc_hien -> da_huy: [CẦN DUY XÁC NHẬN: có cho hủy khi đã giao việc không?]

Chỉ khách hàng chủ dự án mới được đổi trạng thái dự án của mình.

## 4. Vòng đời đề xuất (DeXuat.TrangThai)

| Mã | Ý nghĩa |
|---|---|
| cho_duyet | Freelancer vừa gửi, chờ khách hàng xem (mặc định) |
| duoc_chon | Khách hàng đã chọn đề xuất này |
| tu_choi | Đề xuất không được chọn |

Quy tắc:
- Mỗi freelancer chỉ gửi một đề xuất cho một dự án (ràng buộc UNIQUE ID_DuAn + ID_Freelancer).
- Khi một đề xuất chuyển sang duoc_chon thì dự án chuyển sang dang_thuc_hien, các đề xuất còn lại của dự án đó chuyển sang tu_choi. [CẦN DUY XÁC NHẬN]
- Chỉ gửi đề xuất khi dự án ở trạng thái mo và chưa quá HanNhanDeXuat.
- Khách hàng không được gửi đề xuất vào dự án của chính mình.

## 5. Quy tắc nghiệp vụ

1. Hạn nhận đề xuất: sau ngày HanNhanDeXuat không nhận đề xuất mới. Dự án quá hạn không hiện trong kết quả tìm kiếm. [CẦN DUY XÁC NHẬN: quá hạn có tự chuyển trạng thái không, hay chỉ ẩn?]
2. Ai được chọn freelancer: chỉ khách hàng chủ dự án, và chỉ chọn một đề xuất cho mỗi dự án.
3. Ngân sách là số nguyên lớn hơn 0.
4. Đánh giá (rating):
   - Chỉ đánh giá khi dự án ở trạng thái hoan_thanh.
   - Người đánh giá là khách hàng chủ dự án; người được đánh giá là freelancer có đề xuất duoc_chon của dự án đó.
   - Mỗi dự án chỉ có một lượt đánh giá (UNIQUE ID_DuAn + ID_NguoiDanhGia).
   - Không tự đánh giá chính mình.
   - Điểm là số nguyên từ 1 đến 5; nhận xét tối đa 500 ký tự, được để trống.
   - Hồ sơ freelancer hiển thị điểm trung bình và số lượt đánh giá (chưa có thì số lượt là 0).
5. Hồ sơ: mỗi người chỉ sửa hồ sơ của chính mình (họ tên, kỹ năng). Email không đổi được. [CẦN DUY XÁC NHẬN]
6. Đổi mật khẩu: phải nhập đúng mật khẩu cũ; mật khẩu mới có độ dài tối thiểu [CẦN DUY XÁC NHẬN: bao nhiêu ký tự].
7. Quyền truy cập: chưa đăng nhập thì không ghi được dữ liệu; sai quyền trả 403; không thấy dữ liệu trả 404; form POST có CSRF.

## 6. Tìm kiếm dự án

Chỉ trả dự án có TrangThai = mo và HanNhanDeXuat chưa quá hạn.

| Tham số | Ý nghĩa |
|---|---|
| q | Từ khóa, tìm trong TieuDe và MoTa |
| min, max | Khoảng ngân sách |
| skill | Kỹ năng yêu cầu của dự án |
| sort | moi_nhat, ngan_sach_tang, ngan_sach_giam, han_gan_nhat |
| page | Số trang (bắt đầu từ 1) |

Kết quả phân trang. Tên tham số là đề xuất, chốt trong bảng hợp đồng.

## 7. Luồng người dùng

Khách hàng:
1. Đăng ký (chọn vai trò khách hàng), đăng nhập.
2. Đăng dự án (tiêu đề, mô tả, ngân sách, hạn nhận đề xuất, kỹ năng).
3. Xem danh sách đề xuất của dự án, chọn một freelancer.
4. Khi xong, xác nhận hoàn thành.
5. Đánh giá freelancer (điểm 1 đến 5, nhận xét).

Freelancer:
1. Đăng ký (chọn vai trò freelancer), đăng nhập, cập nhật hồ sơ và kỹ năng.
2. Tìm dự án theo từ khóa, ngân sách, kỹ năng, sắp xếp.
3. Xem chi tiết dự án, gửi đề xuất (chi phí, thời gian thực hiện, nội dung).
4. Theo dõi trạng thái đề xuất; khi được chọn thì thực hiện dự án.
5. Xem điểm đánh giá trung bình trong hồ sơ.

## 8. Việc cần chốt với Duy

- Hủy dự án khi đang thực hiện.
- Khi chọn một đề xuất, các đề xuất còn lại tự chuyển tu_choi.
- Quá hạn nhận đề xuất: chỉ ẩn hay đổi trạng thái.
- Có cho sửa hoặc rút đề xuất sau khi gửi không.
- Độ dài tối thiểu của mật khẩu; có cho đổi email không.
- KyNang đang là một chuỗi (VARCHAR 500): quy ước phân cách (ví dụ dấu phẩy) và cách lọc theo kỹ năng.