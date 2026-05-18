<style>
    .page-loader {
        position: fixed;
        inset: 0;
        background: radial-gradient(circle at center, rgba(217, 154, 50, 0.18), transparent 35%),
            linear-gradient(135deg, #080808, #1a0b04, #2b1208);
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.6s ease, visibility 0.6s ease;
    }

    .page-loader.hide {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .loader-content {
        text-align: center;
        animation: loaderFadeUp 0.8s ease both;
    }

    .loader-logo-wrap {
        width: 120px;
        height: 120px;
        margin: 0 auto 18px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .loader-logo {
        width: 78px;
        height: 78px;
        object-fit: contain;
        background: #ffffff;
        border-radius: 22px;
        padding: 6px;
        position: relative;
        z-index: 2;
        animation: logoPulse 1.8s ease-in-out infinite;
    }

    .loader-ring {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 3px solid transparent;
        border-top-color: #d99a32;
        border-right-color: #8a4a21;
        animation: spinRing 1.2s linear infinite;
    }

    .loader-ring::after {
        content: "";
        position: absolute;
        inset: 10px;
        border-radius: 50%;
        border: 2px solid transparent;
        border-bottom-color: #f4c56a;
        border-left-color: #5a2d14;
        animation: spinRingReverse 1.6s linear infinite;
    }

    .loader-title {
        font-size: 34px;
        font-weight: 950;
        color: #ffffff;
        margin-bottom: 6px;
        letter-spacing: 1px;
        font-family: sans-serif;
    }

    .loader-title span {
        color: #d99a32;
    }

    .loader-text {
        color: #d9c4aa;
        font-size: 15px;
        margin-bottom: 18px;
        font-family: sans-serif;
    }

    .loader-progress {
        width: 240px;
        height: 6px;
        background: rgba(255, 255, 255, 0.12);
        border-radius: 999px;
        overflow: hidden;
        margin: 0 auto;
    }

    .loader-progress-bar {
        width: 45%;
        height: 100%;
        background: linear-gradient(90deg, #5a2d14, #d99a32, #f4c56a);
        border-radius: 999px;
        animation: loadingMove 1.3s ease-in-out infinite;
    }

    @keyframes spinRing {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes spinRingReverse {
        from { transform: rotate(360deg); }
        to { transform: rotate(0deg); }
    }

    @keyframes logoPulse {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 rgba(217, 154, 50, 0); }
        50% { transform: scale(1.06); box-shadow: 0 0 28px rgba(217, 154, 50, 0.45); }
    }

    @keyframes loadingMove {
        0% { transform: translateX(-120%); }
        100% { transform: translateX(260%); }
    }

    @keyframes loaderFadeUp {
        from { opacity: 0; transform: translateY(22px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div id="page-loader" class="page-loader">
    <div class="loader-content">
        <div class="loader-logo-wrap">
            <img src="{{ asset('assets/images/logo.png') }}" alt="CineHome Logo" class="loader-logo" width="100px" >
            <div class="loader-ring"></div>
        </div>

        <h2 class="loader-title">
            Cine<span>Home</span>
        </h2>

        <p class="loader-text">Đang chuẩn bị trải nghiệm điện ảnh...</p>

        <div class="loader-progress">
            <div class="loader-progress-bar"></div>
        </div>
    </div>
</div>

<script>
    // Đảm bảo preloader biến mất ngay cả khi Vite/app.js lỗi
    (function() {
        var loader = document.getElementById("page-loader");
        if (loader) {
            window.addEventListener("load", function() {
                setTimeout(function() {
                    loader.classList.add("hide");
                }, 700);
            });
            // Fallback: Tự động ẩn sau 3 giây nếu window.load không kích hoạt (do lỗi script khác)
            setTimeout(function() {
                if (!loader.classList.contains("hide")) {
                    loader.classList.add("hide");
                }
            }, 3000);
        }
    })();
</script>