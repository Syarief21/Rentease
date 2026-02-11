<?php
// backend/session.php

class Session {

    /**
     * Memulai atau melanjutkan session
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {

            // Deteksi HTTPS (convert ke boolean aman)
            $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

            // PHP < 7.3 TIDAK support array options
            session_set_cookie_params(
                86400,     // lifetime 1 hari
                '/',       // path
                '',        // domain
                $is_https, // secure
                true       // httponly
            );

            session_start();

            // Tambahkan SameSite manual (PHP < 7.3)
            if (PHP_VERSION_ID < 70300) {
                header(
                    'Set-Cookie: ' . session_name() . '=' . session_id() .
                    '; Path=/' .
                    ($is_https ? '; Secure' : '') .
                    '; HttpOnly; SameSite=Lax'
                );
            }
        }
    }

    /**
     * Simpan data session
     */
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Ambil data session
     */
    public static function get($key) {
        self::start();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * Cek login
     */
    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Destroy session (logout)
     */
    public static function destroy() {
        self::start();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}
