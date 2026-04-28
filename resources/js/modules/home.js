window.startLoading = function () {
    const bar = document.getElementById("loading-bar");
    if (bar) bar.style.width = "30%";
    setTimeout(() => { if (bar) bar.style.width = "60%"; }, 200);
    setTimeout(() => { if (bar) bar.style.width = "85%"; }, 500);
}
window.finishLoading = function () {
    const bar = document.getElementById("loading-bar");
    if (bar) bar.style.width = "100%";
    setTimeout(() => {
        if (bar) bar.style.width = "0%";
    }, 300);
}

// Cuộn lên đầu trang khi reload (chỉ áp dụng cho trang chủ)
if (window.location.pathname === '/') {
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    window.scrollTo(0, 0);
}

document.addEventListener("DOMContentLoaded", () => {


    document.querySelectorAll('.video-link').forEach(link => {
        const video = document.createElement('video');
        video.src = link.href + "#t=0.5";
        video.crossOrigin = "anonymous";
        video.muted = true;

        video.addEventListener('loadeddata', () => {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);

            const base64Image = canvas.toDataURL('image/jpeg');
            link.setAttribute('data-thumb', base64Image);
        });
    });

    const videoOptions = {
        root: null,
        threshold: 0.6
    };

    const videoObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target;

            if (entry.isIntersecting) {
                video.play().catch(() => { });
            } else {
                video.pause();
                video.currentTime = 0;
            }
        });
    }, videoOptions);

    document.querySelectorAll('.feed-video').forEach(v => {
        videoObserver.observe(v);
    });

    if (window.Fancybox) {
        Fancybox.bind("[data-fancybox^='gallery-']", {
            Compact: false,
            Animated: true,
            Thumbs: { autoStart: true },
            Html: {
                video: {
                    autoplay: true,
                    controls: true,
                    format: "mp4"
                }
            }
        });
    }

});