<?php
// Deteksi halaman aktif supaya menu yang sedang dibuka bisa di-highlight
$__current_page = basename($_SERVER['PHP_SELF']);
function __navActive($page, $current) {
    return $page === $current ? 'active' : '';
}
?>
<style>
    /* Beri ruang di bawah supaya konten tidak tertutup bottom nav */
    .desktop-wrapper { padding-bottom: 90px; }

    .bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        max-width: 480px;
        margin: 0 auto;
        background-color: #FFFFFF;
        display: flex;
        align-items: stretch;
        justify-content: space-around;
        padding: 8px 6px calc(8px + env(safe-area-inset-bottom, 0px));
        box-shadow: 0 -6px 24px rgba(0, 102, 255, 0.12);
        border-radius: 24px 24px 0 0;
        z-index: 999;
    }

    .bottom-nav a {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        text-decoration: none;
        color: #94A3B8;
        font-size: 11px;
        font-weight: 600;
        padding: 8px 4px;
        border-radius: 14px;
        transition: all 0.2s ease;
    }

    .bottom-nav a i {
        font-size: 18px;
        line-height: 1;
    }

    .bottom-nav a:hover {
        color: #0066FF;
    }

    .bottom-nav a.active {
        color: #0066FF;
        background-color: #E0F2FE;
    }

    @media (max-width: 480px) {
        .bottom-nav {
            max-width: 100%;
            border-radius: 20px 20px 0 0;
        }
    }
</style>

<div class="bottom-nav">
    <a href="../../index.php" class="<?php echo __navActive('index.php', $__current_page); ?>">
        <i class="fa-solid fa-house"></i><span>Home</span>
    </a>
    <a href="riwayat.php" class="<?php echo __navActive('riwayat.php', $__current_page); ?>">
        <i class="fa-solid fa-clock-rotate-left"></i><span>Riwayat</span>
    </a>
    <a href="chat.php" class="<?php echo __navActive('chat.php', $__current_page); ?>">
        <i class="fa-solid fa-bell"></i><span>Notifikasi</span>
    </a>
    <a href="rating.php" class="<?php echo __navActive('rating.php', $__current_page); ?>">
        <i class="fa-solid fa-star"></i><span>Rating</span>
    </a>
</div>
