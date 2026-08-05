# Mạng Xã Hội (Social Network App)

## Giới thiệu
Đây là dự án Mạng Xã Hội được phát triển nhằm cung cấp nền tảng kết nối, chia sẻ bài viết, tương tác thời gian thực (Real-time) và tích hợp hệ thống trí tuệ nhân tạo (AI Recommender) để đề xuất nội dung bài viết và kết bạn thông minh dựa trên sở thích và hành vi của người dùng.

## Công nghệ sử dụng
- **Backend:** PHP 8.2+, Laravel 12
- **Real-time & WebSockets:** Laravel Reverb
- **Search Engine:** Meilisearch & Laravel Scout
- **AI & Data Service:** Python (FastAPI, Scikit-learn, Pandas, TF-IDF, Collaborative Filtering)
- **Database:** MySQL
- **Frontend:** JavaScript (ES6+), Blade Template, TailwindCSS, Vite
- **Background Tasks:** Laravel Queue Worker

## Chức năng
- **Xác thực & Tài khoản:** Đăng ký, đăng nhập, quản lý trang cá nhân, thiết lập hồ sơ.
- **Tương tác Bài viết:** Đăng bài viết, cập nhật bài viết, thả tim (Like), lưu bài viết yêu thích (Favorite), bình luận (Comment).
- **Hệ thống Đề xuất AI:** Đề xuất bài viết và gợi ý người dùng (kết bạn/theo dõi) thông minh bằng dịch vụ Python AI.
- **Tìm kiếm Thông minh:** Tìm kiếm nhanh chóng bài viết, người dùng, chủ đề với Meilisearch & sắp xếp thứ tự bởi AI.
- **Nhắn tin Real-time:** Nhắn tin cá nhân và nhắn tin nhóm thời gian thực qua WebSockets (Laravel Reverb).
- **Thông báo Real-time:** Thông báo khi có tương tác mới (thích, bình luận, follow).
- **Theo dõi & Chia sẻ:** Theo dõi người dùng khác (Follow/Unfollow), chia sẻ bài viết.
- **Quản trị hệ thống (Admin & Moderator Dashboard):** Quản lý người dùng, bài viết, chủ đề (Topic), xử lý báo cáo vi phạm (Reports) và xem nhật ký hoạt động (Activity Logs).
