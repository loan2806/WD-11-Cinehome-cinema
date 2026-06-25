# TÀI LIỆU CHỨC NĂNG QUẢN LÝ THÔNG BÁO ĐẨY
## Hệ thống quản lý rạp chiếu phim CineHome

---

## 1. USE CASE

### UC-01: Xem danh sách thông báo đẩy
- **Tác nhân**: Quản trị viên (Admin), Quản lý hệ thống (Root Controller)
- **Mô tả**: Xem danh sách các thông báo đẩy đã tạo trong hệ thống
- **Tiền điều kiện**: Người dùng đã đăng nhập và có quyền `quan_ly_thong_bao_day`
- **Luồng sự kiện**:
  1. Người dùng truy cập menu "Thông báo đẩy"
  2. Hệ thống hiển thị danh sách thông báo
  3. Người dùng có thể tìm kiếm theo tiêu đề, lọc theo loại và trạng thái
- **Luồng ngoại lệ**: Không có thông báo nào → Hiển thị thông báo "Chưa có thông báo đẩy nào"

### UC-02: Tạo thông báo đẩy mới
- **Tác nhân**: Quản trị viên (Admin), Quản lý hệ thống (Root Controller)
- **Mô tả**: Tạo mới thông báo đẩy và gửi đến người dùng được chọn
- **Tiền điều kiện**: Người dùng đã đăng nhập và có quyền `quan_ly_thong_bao_day`
- **Luồng sự kiện**:
  1. Người dùng nhấn "Tạo thông báo mới"
  2. Hệ thống hiển thị form tạo thông báo
  3. Người dùng nhập: tiêu đề, nội dung, loại, đối tượng nhận
  4. Hệ thống kiểm tra dữ liệu hợp lệ
  5. Hệ thống lưu thông báo, ghi nhận người tạo, gửi đến người nhận
  6. Hệ thống ghi nhật ký hoạt động
- **Luồng ngoại lệ**:
  - Dữ liệu không hợp lệ → Hiển thị lỗi validation
  - Lỗi hệ thống → Hiển thị thông báo lỗi

### UC-03: Xem chi tiết thông báo đẩy
- **Tác nhân**: Quản trị viên (Admin), Quản lý hệ thống (Root Controller)
- **Mô tả**: Xem chi tiết thông báo đẩy bao gồm thông tin người nhận
- **Tiền điều kiện**: Thông báo đẩy tồn tại trong hệ thống
- **Luồng sự kiện**:
  1. Người dùng nhấn vào tiêu đề thông báo
  2. Hệ thống hiển thị chi tiết thông báo
  3. Hiển thị danh sách người nhận cụ thể (nếu có)
- **Luồng ngoại lệ**: Thông báo không tồn tại → Hiển thị trang 404

### UC-04: Xóa thông báo đẩy
- **Tác nhân**: Quản trị viên (Admin), Quản lý hệ thống (Root Controller)
- **Mô tả**: Xóa thông báo đẩy đã tạo
- **Tiền điều kiện**:
  - Người dùng có quyền xóa (Quản trị viên hoặc Quản lý hệ thống)
  - Thông báo tồn tại trong hệ thống
- **Luồng sự kiện**:
  1. Người dùng nhấn nút xóa
  2. Hệ thống hiển thị hộp xác nhận
  3. Người dùng xác nhận xóa
  4. Hệ thống xóa thông báo và các bản ghi liên quan
  5. Hệ thống ghi nhật ký hoạt động
- **Luồng ngoại lệ**:
  - Không có quyền xóa → Hiển thị thông báo lỗi
  - Hủy xác nhận → Không thực hiện xóa

---

## 2. USER STORY

| ID | Vai trò | Nhu cầu | Mục tiêu | Tiêu chí chấp nhận |
|---|---|---|---|---|
| US-01 | Admin | Quản lý thông báo đẩy | Tạo và gửi thông báo đến người dùng | - Nhập tiêu đề, nội dung, loại, đối tượng nhận<br>- Kiểm tra dữ liệu hợp lệ<br>- Gửi thông báo thành công |
| US-02 | Admin | Xem danh sách thông báo | Theo dõi các thông báo đã gửi | - Hiển thị tiêu đề, loại, người tạo, ngày tạo, trạng thái<br>- Tìm kiếm theo tiêu đề<br>- Lọc theo loại và trạng thái |
| US-03 | Admin | Xem chi tiết thông báo | Xem thông tin đầy đủ của thông báo | - Hiển thị tất cả thông tin thông báo<br>- Hiển thị danh sách người nhận (nếu là người dùng cụ thể) |
| US-04 | Admin/Root | Xóa thông báo | Xóa thông báo không cần thiết | - Chỉ Root/Admin được xóa<br>- Hiển thị xác nhận trước khi xóa<br>- Ghi nhật ký khi xóa |
| US-05 | Admin | Theo dõi hoạt động | Xem lịch sử thao tác | - Ghi nhật ký khi tạo, gửi, xóa<br>- Có thể tra cứu trong nhật ký hệ thống |

---

## 3. LUỒNG NGHIỆP VỤ

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   Đăng nhập     │────▶│   Truy cập      │────▶│   Kiểm tra       │
│   (Admin/Root)  │     │   menu TBĐ       │     │   phân quyền     │
└─────────────────┘     └─────────────────┘     └─────────────────┘
                                                        │
                                        ┌───────────────┴───────────────┐
                                        │                                   │
                                        ▼                                   ▼
                              ┌─────────────────┐              ┌─────────────────┐
                              │   Có quyền      │              │   Không có       │
                              │   truy cập      │              │   quyền          │
                              └─────────────────┘              └─────────────────┘
                                        │                                   │
                                        ▼                                   ▼
                              ┌─────────────────┐              ┌─────────────────┐
                              │   Hiển thị      │              │   Hiển thị       │
                              │   danh sách TBĐ  │              │   thông báo lỗi  │
                              └─────────────────┘              └─────────────────┘
                                        │
                        ┌───────────────┼───────────────┐
                        │               │               │
                        ▼               ▼               ▼
                ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
                │   Tạo mới    │ │   Xem chi    │ │   Xóa        │
                │   thông báo  │ │   tiết TBĐ   │ │   thông báo  │
                └──────────────┘ └──────────────┘ └──────────────┘
                        │               │               │
                        ▼               ▼               ▼
                ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
                │   Nhập form  │ │   Hiển thị   │ │   Xác nhận   │
                │   validation │ │   chi tiết    │ │   xóa        │
                └──────────────┘ └──────────────┘ └──────────────┘
                        │               │               │
                        ▼               │               │
                ┌──────────────┐         │               │
                │   Lưu DB     │         │               │
                │   Gửi TBĐ    │         │               │
                │   Ghi nhật ký│         │               │
                └──────────────┘         │               │
                                          │               │
                                          │               ▼
                                          │       ┌──────────────┐
                                          │       │   Xóa DB     │
                                          │       │   Ghi nhật ký│
                                          │       └──────────────┘
                                          │               │
                                          └───────────────┘
                                                      │
                                                      ▼
                                            ┌──────────────┐
                                            │   Thành công  │
                                            └──────────────┘
```

---

## 4. LUỒNG XỬ LÝ CHI TIẾT

### 4.1 Luồng tạo thông báo đẩy

```
┌─────────────────────────────────────────────────────────────────┐
│                        Tạo thông báo đẩy                          │
└─────────────────────────────────────────────────────────────────┘

[Bước 1] Người dùng truy cập /admin/thong-bao-push/create
    │
    ▼
[Bước 2] Hệ thống kiểm tra quyền quan_ly_thong_bao_day
    │ - Có quyền → Tiếp tục
    │ - Không có → Chuyển hướng về dashboard
    ▼
[Bước 3] Hiển thị form tạo thông báo
    │ - Tiêu đề (required, max:255)
    │ - Nội dung (required, text)
    │ - Loại (info/success/warning/error)
    │ - Đối tượng nhận (all/khach_hang/nhan_vien/quan_tri_vien/nguoi_dung_cu_the)
    │ - Người dùng cụ thể (nếu chọn nguoi_dung_cu_the)
    ▼
[Bước 4] Người dùng nhập dữ liệu và submit form
    ▼
[Bước 5] Hệ thống validate dữ liệu
    │ - Tiêu đề: required, string, max:255
    │ - Nội dung: required, string
    │ - Loại: required, in:[info,success,warning,error]
    │ - Đối tượng nhận: required, in:[all,khach_hang,nhan_vien,quan_tri_vien,nguoi_dung_cu_the]
    │ - Người dùng cụ thể: exists:nguoi_dungs,id (nếu chọn)
    ▼
[Bước 6] Validation pass → Tiếp tục
    Validation fail → Trả về form với lỗi
    ▼
[Bước 7] Bắt đầu Transaction
    ▼
[Bước 8] Tạo ThongBaoPush mới
    │ - tieu_de, noi_dung, loai, doi_tuong_nhan
    │ - nguoi_tao_id = auth()->id()
    │ - trang_thai = 'da_gui'
    │ - thoi_gian_gui = now()
    ▼
[Bước 9] Gửi thông báo đến người nhận
    │ - all → Lấy tất cả NguoiDung
    │ - khach_hang → Lấy vai_tro = 'user'
    │ - nhan_vien → Lấy vai_tro = 'nhan_vien'
    │ - quan_tri_vien → Lấy vai_tro = 'admin' hoặc 'quan_ly_he_thong'
    │ - nguoi_dung_cu_the → Lấy theo ID
    │ → Tạo ThongBaoPushNguoiDung cho mỗi người
    ▼
[Bước 10] Commit Transaction
    ▼
[Bước 11] Ghi nhật ký hoạt động
    │ - hanh_dong: 'Tạo thông báo đẩy'
    │ - chuc_nang: 'Quản lý thông báo đẩy'
    │ - mo_ta: "Tạo thông báo: {tieu_de}"
    │ - thuoc_tinh: {loai, doi_tuong_nhan}
    ▼
[Bước 12] Gửi thông báo admin (AdminNotificationService::push)
    ▼
[Bước 13] Redirect về danh sách với thông báo thành công
```

### 4.2 Luồng xóa thông báo đẩy

```
┌─────────────────────────────────────────────────────────────────┐
│                        Xóa thông báo đẩy                          │
└─────────────────────────────────────────────────────────────────┘

[Bước 1] Người dùng nhấn nút xóa trên danh sách hoặc trang chi tiết
    ▼
[Bước 2] Hệ thống kiểm tra vai trò
    │ - Quản trị viên → Cho phép xóa
    │ - Quản lý hệ thống → Cho phép xóa
    │ - Khác → Từ chối với thông báo lỗi
    ▼
[Bước 3] Hiển thị hộp xác nhận JavaScript
    │ - "Bạn có chắc chắn muốn xóa thông báo này không?"
    ▼
[Bước 4] Người dùng xác nhận → Submit form DELETE
    ▼
[Bước 5] Xóa các bản ghi trung gian (ThongBaoPushNguoiDung)
    ▼
[Bước 6] Xóa thông báo đẩy (ThongBaoPush)
    ▼
[Bước 7] Ghi nhật ký hoạt động
    │ - hanh_dong: 'Xóa thông báo đẩy'
    │ - chuc_nang: 'Quản lý thông báo đẩy'
    │ - mo_ta: "Xóa thông báo: {tieu_de}"
    │ - thuoc_tinh: {id, tieu_de}
    ▼
[Bước 8] Gửi thông báo admin
    ▼
[Bước 9] Redirect về danh sách với thông báo thành công
```

---

## 5. CÁC TRƯỜNG HỢP KIỂM THỬ (TEST CASES)

### 5.1 Test Case - Danh sách thông báo

| TC-ID | Mục tiêu | Bước | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|
| TC-001 | Hiển thị danh sách | 1. Đăng nhập Admin<br>2. Truy cập /admin/thong-bao-push | Hiển thị danh sách thông báo | Pass |
| TC-002 | Danh sách rỗng | 1. Không có thông báo nào<br>2. Truy cập danh sách | Hiển thị "Chưa có thông báo đẩy nào" | Pass |
| TC-003 | Tìm kiếm theo tiêu đề | 1. Nhập từ khóa vào ô search<br>2. Nhấn Lọc | Hiển thị thông báo có tiêu đề chứa từ khóa | Pass |
| TC-004 | Lọc theo loại Info | 1. Chọn loại "Info"<br>2. Nhấn Lọc | Hiển thị các thông báo loại Info | Pass |
| TC-005 | Lọc theo trạng thái | 1. Chọn trạng thái "Đã gửi"<br>2. Nhấn Lọc | Hiển thị các thông báo đã gửi | Pass |
| TC-006 | Reset bộ lọc | 1. Áp dụng bộ lọc<br>2. Nhấn Reset | Hiển thị tất cả thông báo | Pass |

### 5.2 Test Case - Tạo thông báo

| TC-ID | Mục tiêu | Bước | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|
| TC-007 | Tạo thành công | 1. Nhập đầy đủ thông tin<br>2. Chọn đối tượng "Tất cả"<br>3. Nhấn Tạo | Thông báo tạo thành công, gửi đến tất cả user | Pass |
| TC-008 | Thiếu tiêu đề | 1. Để trống tiêu đề<br>2. Nhấn Tạo | Hiển thị lỗi "Tiêu đề không được để trống" | Pass |
| TC-009 | Thiếu nội dung | 1. Để trống nội dung<br>2. Nhấn Tạo | Hiển thị lỗi "Nội dung không được để trống" | Pass |
| TC-010 | Loại không hợp lệ | 1. Chọn loại không hợp lệ<br>2. Nhấn Tạo | Hiển thị lỗi validation | Pass |
| TC-011 | Đối tượng không hợp lệ | 1. Không chọn đối tượng<br>2. Nhấn Tạo | Hiển thị lỗi validation | Pass |
| TC-012 | Gửi đến khách hàng | 1. Chọn đối tượng "Khách hàng"<br>2. Nhấn Tạo | Thông báo gửi đến vai_tro = 'user' | Pass |
| TC-013 | Gửi đến nhân viên | 1. Chọn đối tượng "Nhân viên"<br>2. Nhấn Tạo | Thông báo gửi đến vai_tro = 'nhan_vien' | Pass |
| TC-014 | Gửi đến quản trị | 1. Chọn đối tượng "Quản trị viên"<br>2. Nhấn Tạo | Thông báo gửi đến vai_tro = 'admin'/'quan_ly_he_thong' | Pass |
| TC-015 | Gửi người dùng cụ thể | 1. Chọn "Người dùng cụ thể"<br>2. Chọn người dùng<br>3. Nhấn Tạo | Thông báo gửi đến người dùng được chọn | Pass |

### 5.3 Test Case - Xem chi tiết

| TC-ID | Mục tiêu | Bước | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|
| TC-016 | Xem chi tiết | 1. Nhấn vào tiêu đề thông báo | Hiển thị đầy đủ thông tin thông báo | Pass |
| TC-017 | Xem danh sách người nhận | 1. Xem chi tiết TBĐ người dùng cụ thể | Hiển thị danh sách người nhận | Pass |
| TC-018 | Không có người nhận | 1. Xem chi tiết TBĐ all/khach_hang | Không hiển thị danh sách người nhận | Pass |

### 5.4 Test Case - Xóa thông báo

| TC-ID | Mục tiêu | Bước | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|
| TC-019 | Xóa thành công (Admin) | 1. Đăng nhập Admin<br>2. Nhấn xóa<br>3. Xác nhận | Thông báo bị xóa, quay về danh sách | Pass |
| TC-020 | Xóa thành công (Root) | 1. Đăng nhập Root<br>2. Nhấn xóa<br>3. Xác nhận | Thông báo bị xóa, quay về danh sách | Pass |
| TC-021 | Không có quyền xóa | 1. Đăng nhập Nhân viên<br>2. Truy cập trang<br>3. Nhấn xóa | Hiển thị lỗi "Không có quyền xóa" | Pass |
| TC-022 | Hủy xác nhận | 1. Nhấn xóa<br>2. Hủy xác nhận | Không xóa thông báo | Pass |
| TC-023 | Xóa có danh sách người nhận | 1. Xóa TBĐ có người nhận cụ thể | Xóa cả bản ghi trung gian | Pass |

### 5.5 Test Case - Phân quyền

| TC-ID | Mục tiêu | Bước | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|
| TC-024 | Truy cập có quyền | 1. Đăng nhập Admin có quyền<br>2. Truy cập /admin/thong-bao-push | Hiển thị danh sách | Pass |
| TC-025 | Truy cập không có quyền | 1. Đăng nhập không có quyền<br>2. Truy cập /admin/thong-bao-push | Chuyển hướng về dashboard | Pass |
| TC-026 | Tạo không có quyền | 1. Đăng nhập không có quyền<br>2. Truy cập /admin/thong-bao-push/create | Chuyển hướng về dashboard | Pass |

### 5.6 Test Case - Ghi nhật ký

| TC-ID | Mục tiêu | Bước | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|
| TC-027 | Ghi nhật ký tạo | 1. Tạo thông báo mới<br>2. Xem nhật ký | Có bản ghi "Tạo thông báo đẩy" | Pass |
| TC-028 | Ghi nhật ký xóa | 1. Xóa thông báo<br>2. Xem nhật ký | Có bản ghi "Xóa thông báo đẩy" | Pass |
| TC-029 | Thông tin nhật ký | 1. Thực hiện thao tác<br>2. Xem chi tiết nhật ký | Hiển thị đầy đủ: IP, User Agent, thời gian | Pass |

---

## 6. THIẾT KẾ CƠ SỞ DỮ LIỆU

### 6.1 Sơ đồ ERD (Entity Relationship Diagram)

```
┌─────────────────────────────────────────────────────────────────┐
│                         nguoi_dungs                              │
├─────────────────────────────────────────────────────────────────┤
│ PK  id (BIGINT)                                                 │
│     ho_ten (VARCHAR)                                            │
│     email (VARCHAR)                                             │
│     mat_khau (VARCHAR)                                          │
│     vai_tro (ENUM: user, nhan_vien, admin, quan_ly_he_thong)   │
│     trang_thai_hoat_dong (BOOLEAN)                              │
│     ...                                                         │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         │ 1:N
                         │
┌────────────────────────▼────────────────────────────────────────┐
│                      thong_bao_pushs                             │
├─────────────────────────────────────────────────────────────────┤
│ PK  id (BIGINT)                                                 │
│     tieu_de (VARCHAR 255)                                       │
│     noi_dung (TEXT)                                             │
│     loai (ENUM: info, success, warning, error)                  │
│     doi_tuong_nhan (ENUM: all, khach_hang, nhan_vien,           │
│                     quan_tri_vien, nguoi_dung_cu_the)           │
│ FK  nguoi_tao_id (BIGINT) → nguoi_dungs.id                      │
│     trang_thai (ENUM: da_gui, chua_gui)                         │
│     thoi_gian_gui (TIMESTAMP NULLABLE)                           │
│     created_at (TIMESTAMP)                                      │
│     updated_at (TIMESTAMP)                                      │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         │ 1:N
                         │
┌────────────────────────▼────────────────────────────────────────┐
│                   thong_bao_push_nguoi_dungs                      │
├─────────────────────────────────────────────────────────────────┤
│ PK  id (BIGINT)                                                 │
│ FK  thong_bao_push_id (BIGINT) → thong_bao_pushs.id             │
│ FK  nguoi_dung_id (BIGINT) → nguoi_dungs.id                     │
│     da_doc (BOOLEAN DEFAULT false)                               │
│     doc_luc (TIMESTAMP NULLABLE)                                 │
│     created_at (TIMESTAMP)                                      │
│     updated_at (TIMESTAMP)                                      │
│     UNIQUE(thong_bao_push_id, nguoi_dung_id)                    │
└─────────────────────────────────────────────────────────────────┘
```

### 6.2 Mô tả bảng chi tiết

#### Bảng: `thong_bao_pushs`

| Tên cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|
| id | BIGINT | PK, AUTO_INCREMENT | Mã định danh thông báo |
| tieu_de | VARCHAR(255) | NOT NULL | Tiêu đề thông báo |
| noi_dung | TEXT | NOT NULL | Nội dung thông báo |
| loai | ENUM | DEFAULT 'info' | Loại: info, success, warning, error |
| doi_tuong_nhan | ENUM | DEFAULT 'all' | Đối tượng nhận |
| nguoi_tao_id | BIGINT | FK → nguoi_dungs.id | Người tạo thông báo |
| trang_thai | ENUM | DEFAULT 'chua_gui' | Trạng thái gửi |
| thoi_gian_gui | TIMESTAMP | NULLABLE | Thời gian gửi |
| created_at | TIMESTAMP | - | Thời gian tạo |
| updated_at | TIMESTAMP | - | Thời gian cập nhật |

#### Bảng: `thong_bao_push_nguoi_dungs`

| Tên cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|---|---|---|---|
| id | BIGINT | PK, AUTO_INCREMENT | Mã định danh bản ghi |
| thong_bao_push_id | BIGINT | FK → thong_bao_pushs.id | Thông báo đẩy |
| nguoi_dung_id | BIGINT | FK → nguoi_dungs.id | Người dùng nhận |
| da_doc | BOOLEAN | DEFAULT false | Đã đọc hay chưa |
| doc_luc | TIMESTAMP | NULLABLE | Thời điểm đọc |
| created_at | TIMESTAMP | - | Thời gian tạo |
| updated_at | TIMESTAMP | - | Thời gian cập nhật |

**Unique Constraint**: (thong_bao_push_id, nguoi_dung_id)

---

## 7. CẤU TRÚC CODE

### 7.1 Migration
- File: `database/migrations/2026_06_25_000001_tao_bang_thong_bao_pushs.php`
- Tạo 2 bảng: `thong_bao_pushs` và `thong_bao_push_nguoi_dungs`

### 7.2 Models

#### ThongBaoPush
```php
namespace App\Models;

class ThongBaoPush extends Model
{
    protected $fillable = [
        'tieu_de', 'noi_dung', 'loai', 'doi_tuong_nhan',
        'nguoi_tao_id', 'trang_thai', 'thoi_gian_gui',
    ];

    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_tao_id');
    }

    public function nguoiDungs(): HasMany
    {
        return $this->hasMany(ThongBaoPushNguoiDung::class, 'thong_bao_push_id');
    }
}
```

#### ThongBaoPushNguoiDung
```php
namespace App\Models;

class ThongBaoPushNguoiDung extends Model
{
    protected $table = 'thong_bao_push_nguoi_dungs';

    protected $fillable = [
        'thong_bao_push_id', 'nguoi_dung_id', 'da_doc', 'doc_luc',
    ];

    public function thongBaoPush(): BelongsTo
    {
        return $this->belongsTo(ThongBaoPush::class, 'thong_bao_push_id');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }
}
```

### 7.3 Controller

#### ThongBaoPushController
- **Namespace**: `App\Http\Controllers\Admin\ThongBaoPushController`
- **Methods**:
  - `index(Request $request)` - Danh sách với tìm kiếm và lọc
  - `create()` - Form tạo mới
  - `store(StoreThongBaoPushRequest $request)` - Lưu và gửi thông báo
  - `show(ThongBaoPush $thongBaoPush)` - Xem chi tiết
  - `destroy(Request $request, ThongBaoPush $thongBaoPush)` - Xóa thông báo
  - `getUsersByRole(Request $request)` - AJAX lấy người dùng theo vai trò
  - `guiThongBao(...)` - Gửi thông báo đến người nhận

### 7.4 Form Request
- File: `app/Http/Requests/Admin/StoreThongBaoPushRequest.php`
- Validation rules:
  - `tieu_de`: required, string, max:255
  - `noi_dung`: required, string
  - `loai`: required, in:[info,success,warning,error]
  - `doi_tuong_nhan`: required, in:[all,khach_hang,nhan_vien,quan_tri_vien,nguoi_dung_cu_the]
  - `nguoi_dung_cu_the`: nullable, integer, exists:nguoi_dungs,id

### 7.5 Routes

```php
Route::middleware(['permission:quan_ly_cau_hinh_he_thong'])->group(function () {
    Route::resource('thong-bao-push', \App\Http\Controllers\Admin\ThongBaoPushController::class)
        ->names('thong-bao-push');
    Route::get('/thong-bao-push/users-by-role', [\App\Http\Controllers\Admin\ThongBaoPushController::class, 'getUsersByRole'])
        ->name('thong-bao-push.users-by-role');
});
```

| Method | URI | Tên route | Quyền |
|---|---|---|---|
| GET | /admin/thong-bao-push | admin.thong-bao-push.index | quan_ly_cau_hinh_he_thong |
| GET | /admin/thong-bao-push/create | admin.thong-bao-push.create | quan_ly_cau_hinh_he_thong |
| POST | /admin/thong-bao-push | admin.thong-bao-push.store | quan_ly_cau_hinh_he_thong |
| GET | /admin/thong-bao-push/{id} | admin.thong-bao-push.show | quan_ly_cau_hinh_he_thong |
| DELETE | /admin/thong-bao-push/{id} | admin.thong-bao-push.destroy | quan_ly_cau_hinh_he_thong |
| GET | /admin/thong-bao-push/users-by-role | admin.thong-bao-push.users-by-role | quan_ly_cau_hinh_he_thong |

### 7.6 Views

| View file | Mô tả |
|---|---|
| admin/thong-bao-push/index.blade.php | Danh sách thông báo đẩy |
| admin/thong-bao-push/create.blade.php | Form tạo thông báo mới |
| admin/thong-bao-push/show.blade.php | Chi tiết thông báo đẩy |

### 7.7 Phân quyền (Spatie Permission)

**Permission mới**:
- `quan_ly_thong_bao_day` - Quản lý thông báo đẩy

**Roles được cấp quyền**:
- Quản trị viên: Tất cả quyền (đã có)
- Quản lý hệ thống: Thêm `quan_ly_thong_bao_day`

---

## 8. LƯU Ý TRIỂN KHAI

### 8.1 Các bước cài đặt

1. Chạy migration:
   ```bash
   php artisan migrate
   ```

2. Chạy seeder để cấp quyền:
   ```bash
   php artisan db:seed --class=VaiTroAndQuyenSeeder
   ```

3. Xóa cache permission:
   ```bash
   php artisan permission:cache-clear
   ```

4. Build assets (nếu cần):
   ```bash
   npm run build
   ```

### 8.2 Cấu hình cần lưu ý

- Đảm bảo bảng `nguoi_dungs` tồn tại với đầy đủ các vai trò
- Kiểm tra Spatie Permission đã được cài đặt và cấu hình
- Đảm bảo `AdminNotificationService` hoạt động để gửi thông báo admin

### 8.3 Mở rộng tương lai

- Tích hợp Firebase Cloud Messaging (FCM) cho push notification thực
- Thêm lịch gửi thông báo (scheduled notifications)
- Thêm template thông báo
- Thêm phân loại người dùng theo nhóm tùy chỉnh
- Thêm thống kê: tỷ lệ đọc, tỷ lệ click

---

## 9. TÀI LIỆU THAM KHẢO

- Laravel 12 Documentation: https://laravel.com/docs/12.x
- Spatie Permission: https://spatie.be/docs/laravel-permission/v6/introduction
- Bootstrap 5: https://getbootstrap.com/docs/5.3/
- Tailwind CSS: https://tailwindcss.com/docs
