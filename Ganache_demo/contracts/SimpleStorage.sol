// SPDX-License-Identifier: MIT
pragma solidity 0.8.19;

contract SimpleStorage {
    // Lưu tổng lượt xem của từng profile
    mapping(uint256 => uint256) public profileViews;

    // Lưu vết: ProfileID => (ViewerID => đã xem hay chưa)
    mapping(uint256 => mapping(uint256 => bool)) public hasViewed;

    // Hàm tăng lượt xem có kiểm tra tính độc nhất
    function incrementView(uint256 _profileId, uint256 _viewerId) public {
        // Chỉ tăng nếu:
        // 1. Người xem khác người được xem (_profileId != _viewerId)
        // 2. Người xem này chưa từng xem profile này trước đây (!hasViewed)
        if (_profileId != _viewerId && !hasViewed[_profileId][_viewerId]) {
            profileViews[_profileId] += 1;
            hasViewed[_profileId][_viewerId] = true;
        }
    }

    // Giữ lại hàm cũ để tránh lỗi tương thích (không làm gì)
    function get() public view returns (uint256) {
        return 0; 
    }
}
