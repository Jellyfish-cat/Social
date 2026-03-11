<?php

return [

    'accepted' => ':attribute phải được chấp nhận.',
    'accepted_if' => ':attribute phải được chấp nhận khi :other là :value.',
    'active_url' => ':attribute phải là một URL hợp lệ.',
    'after' => ':attribute phải là ngày sau :date.',
    'after_or_equal' => ':attribute phải là ngày sau hoặc bằng :date.',
    'alpha' => ':attribute chỉ được chứa chữ cái.',
    'alpha_dash' => ':attribute chỉ được chứa chữ cái, số, dấu gạch ngang và gạch dưới.',
    'alpha_num' => ':attribute chỉ được chứa chữ cái và số.',
    'any_of' => ':attribute không hợp lệ.',
    'array' => ':attribute phải là một mảng.',
    'ascii' => ':attribute chỉ được chứa ký tự ASCII.',
    'before' => ':attribute phải là ngày trước :date.',
    'before_or_equal' => ':attribute phải là ngày trước hoặc bằng :date.',

    'between' => [
        'array' => ':attribute phải có từ :min đến :max phần tử.',
        'file' => ':attribute phải có dung lượng từ :min đến :max KB.',
        'numeric' => ':attribute phải nằm trong khoảng :min đến :max.',
        'string' => ':attribute phải có từ :min đến :max ký tự.',
    ],

    'boolean' => ':attribute phải là true hoặc false.',
    'can' => ':attribute chứa giá trị không được phép.',
    'confirmed' => 'Xác nhận :attribute không khớp.',
    'contains' => ':attribute thiếu giá trị bắt buộc.',
    'current_password' => 'Mật khẩu hiện tại không đúng.',
    'date' => ':attribute phải là ngày hợp lệ.',
    'date_equals' => ':attribute phải là ngày bằng :date.',
    'date_format' => ':attribute phải đúng định dạng :format.',
    'decimal' => ':attribute phải có :decimal chữ số thập phân.',
    'declined' => ':attribute phải bị từ chối.',
    'declined_if' => ':attribute phải bị từ chối khi :other là :value.',
    'different' => ':attribute và :other phải khác nhau.',
    'digits' => ':attribute phải gồm :digits chữ số.',
    'digits_between' => ':attribute phải có từ :min đến :max chữ số.',
    'dimensions' => ':attribute có kích thước ảnh không hợp lệ.',
    'distinct' => ':attribute có giá trị trùng lặp.',
    'doesnt_contain' => ':attribute không được chứa các giá trị: :values.',
    'doesnt_end_with' => ':attribute không được kết thúc bằng: :values.',
    'doesnt_start_with' => ':attribute không được bắt đầu bằng: :values.',
    'email' => ':attribute phải là địa chỉ email hợp lệ.',
    'encoding' => ':attribute phải được mã hóa bằng :encoding.',
    'ends_with' => ':attribute phải kết thúc bằng một trong các giá trị: :values.',
    'enum' => ':attribute đã chọn không hợp lệ.',
    'exists' => ':attribute đã chọn không tồn tại.',
    'extensions' => ':attribute phải có phần mở rộng: :values.',
    'file' => ':attribute phải là một tệp.',
    'filled' => ':attribute phải có giá trị.',

    'gt' => [
        'array' => ':attribute phải có nhiều hơn :value phần tử.',
        'file' => ':attribute phải lớn hơn :value KB.',
        'numeric' => ':attribute phải lớn hơn :value.',
        'string' => ':attribute phải nhiều hơn :value ký tự.',
    ],

    'gte' => [
        'array' => ':attribute phải có ít nhất :value phần tử.',
        'file' => ':attribute phải lớn hơn hoặc bằng :value KB.',
        'numeric' => ':attribute phải lớn hơn hoặc bằng :value.',
        'string' => ':attribute phải lớn hơn hoặc bằng :value ký tự.',
    ],

    'hex_color' => ':attribute phải là mã màu hex hợp lệ.',
    'image' => ':attribute phải là hình ảnh.',
    'in' => ':attribute đã chọn không hợp lệ.',
    'in_array' => ':attribute phải tồn tại trong :other.',
    'in_array_keys' => ':attribute phải chứa ít nhất một khóa: :values.',
    'integer' => ':attribute phải là số nguyên.',
    'ip' => ':attribute phải là địa chỉ IP hợp lệ.',
    'ipv4' => ':attribute phải là địa chỉ IPv4 hợp lệ.',
    'ipv6' => ':attribute phải là địa chỉ IPv6 hợp lệ.',
    'json' => ':attribute phải là chuỗi JSON hợp lệ.',
    'list' => ':attribute phải là danh sách.',
    'lowercase' => ':attribute phải viết thường.',

    'lt' => [
        'array' => ':attribute phải có ít hơn :value phần tử.',
        'file' => ':attribute phải nhỏ hơn :value KB.',
        'numeric' => ':attribute phải nhỏ hơn :value.',
        'string' => ':attribute phải ít hơn :value ký tự.',
    ],

    'lte' => [
        'array' => ':attribute không được có quá :value phần tử.',
        'file' => ':attribute phải nhỏ hơn hoặc bằng :value KB.',
        'numeric' => ':attribute phải nhỏ hơn hoặc bằng :value.',
        'string' => ':attribute phải nhỏ hơn hoặc bằng :value ký tự.',
    ],

    'mac_address' => ':attribute phải là địa chỉ MAC hợp lệ.',

    'max' => [
        'array' => ':attribute không được có quá :max phần tử.',
        'file' => ':attribute không được lớn hơn :max KB.',
        'numeric' => ':attribute không được lớn hơn :max.',
        'string' => ':attribute không được nhiều hơn :max ký tự.',
    ],

    'max_digits' => ':attribute không được nhiều hơn :max chữ số.',
    'mimes' => ':attribute phải là tệp loại: :values.',
    'mimetypes' => ':attribute phải là tệp loại: :values.',

    'min' => [
        'array' => ':attribute phải có ít nhất :min phần tử.',
        'file' => ':attribute phải có dung lượng ít nhất :min KB.',
        'numeric' => ':attribute phải lớn hơn hoặc bằng :min.',
        'string' => ':attribute phải có ít nhất :min ký tự.',
    ],

    'min_digits' => ':attribute phải có ít nhất :min chữ số.',
    'missing' => ':attribute phải bị thiếu.',
    'missing_if' => ':attribute phải bị thiếu khi :other là :value.',
    'missing_unless' => ':attribute phải bị thiếu trừ khi :other là :value.',
    'missing_with' => ':attribute phải bị thiếu khi :values tồn tại.',
    'missing_with_all' => ':attribute phải bị thiếu khi tất cả :values tồn tại.',
    'multiple_of' => ':attribute phải là bội số của :value.',
    'not_in' => ':attribute đã chọn không hợp lệ.',
    'not_regex' => 'Định dạng :attribute không hợp lệ.',
    'numeric' => ':attribute phải là số.',

    'password' => [
        'letters' => ':attribute phải chứa ít nhất một chữ cái.',
        'mixed' => ':attribute phải chứa ít nhất một chữ hoa và một chữ thường.',
        'numbers' => ':attribute phải chứa ít nhất một số.',
        'symbols' => ':attribute phải chứa ít nhất một ký tự đặc biệt.',
        'uncompromised' => ':attribute đã xuất hiện trong rò rỉ dữ liệu. Hãy chọn :attribute khác.',
    ],

    'present' => ':attribute phải tồn tại.',
    'present_if' => ':attribute phải tồn tại khi :other là :value.',
    'present_unless' => ':attribute phải tồn tại trừ khi :other là :value.',
    'present_with' => ':attribute phải tồn tại khi :values tồn tại.',
    'present_with_all' => ':attribute phải tồn tại khi tất cả :values tồn tại.',

    'prohibited' => ':attribute bị cấm.',
    'regex' => 'Định dạng :attribute không hợp lệ.',
    'required' => ':attribute là bắt buộc.',
    'required_if' => ':attribute là bắt buộc khi :other là :value.',
    'required_unless' => ':attribute là bắt buộc trừ khi :other nằm trong :values.',
    'required_with' => ':attribute là bắt buộc khi :values tồn tại.',
    'required_without' => ':attribute là bắt buộc khi :values không tồn tại.',

    'same' => ':attribute phải giống :other.',

    'size' => [
        'array' => ':attribute phải chứa :size phần tử.',
        'file' => ':attribute phải có dung lượng :size KB.',
        'numeric' => ':attribute phải bằng :size.',
        'string' => ':attribute phải có :size ký tự.',
    ],

    'string' => ':attribute phải là chuỗi.',
    'timezone' => ':attribute phải là múi giờ hợp lệ.',
    'unique' => ':attribute đã tồn tại.',
    'uploaded' => ':attribute tải lên thất bại.',
    'url' => ':attribute phải là URL hợp lệ.',
    'uuid' => ':attribute phải là UUID hợp lệ.',

    'attributes' => [
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'name' => 'Tên',
    ],

];