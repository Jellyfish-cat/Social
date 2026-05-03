<?php

namespace App\Providers;
use Carbon\Carbon;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\Paginator;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Post::class => PostPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
            Carbon::setLocale(Session::get('locale'));
        }
        Paginator::useBootstrap();

        // Tùy chỉnh Email Xác thực Đăng ký
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Xác nhận địa chỉ Email - Gunpla Social')
                ->greeting('Chào bạn!')
                ->line('Chào mừng bạn đến với Gunpla Social - Cộng đồng người chơi mô hình Việt Nam.')
                ->line('Chỉ còn một bước nữa thôi, hãy nhấn vào nút bên dưới để kích hoạt tài khoản của bạn.')
                ->action('Kích hoạt tài khoản', $url)
                ->line('Liên kết này sẽ hết hạn sau 60 phút.')
                ->line('Nếu bạn không tạo tài khoản, bạn không cần thực hiện thêm hành động nào.')
                ->salutation('Trân trọng, Đội ngũ Admin Gunpla Social');
        });

        // Tùy chỉnh Email Đặt lại mật khẩu
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Yêu cầu đặt lại mật khẩu - Gunpla Social')
                ->greeting('Xin chào!')
                ->line('Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản Gunpla Social của bạn.')
                ->line('Nếu bạn thực sự muốn thay đổi mật khẩu, hãy nhấn vào nút bên dưới:')
                ->action('Đặt lại mật khẩu', $url)
                ->line('Liên kết này có hiệu lực trong vòng 60 phút.')
                ->line('Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này để bảo mật tài khoản.')
                ->salutation('Thân mến, Gunpla Social Team');
        });
    }
}
