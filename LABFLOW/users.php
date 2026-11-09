<?php
require_once __DIR__ . '/config/layout.php';
require_admin();

ui_open('Kelola User', 'users', '', ['users.css'], '<h1>Kelola User</h1>');
?>

            <div class="page-head">
                <div>
                    <h1>Kelola User</h1>
                    <p>Buat dan atur akun yang dapat masuk ke LabFlow.</p>
                </div>
            </div>

            <div class="users-grid">

                <!-- ============ FORM ============ -->
                <div class="card" id="formCard">
                    <div class="card-head">
                        <h2><i data-lucide="user-plus" id="formIcon"></i> <span id="formTitle">Tambah Akun Baru</span></h2>
                        <span class="badge" id="formBadge">NEW USER</span>
                    </div>
                    <p class="desc">Admin mengelola user. Koordinator mengelola lab &amp; stok. User hanya dapat melihat data.</p>

                    <form id="userForm" autocomplete="off">
                        <input type="hidden" id="userId" value="">

                        <div class="field">
                            <label for="fullName">Nama Lengkap</label>
                            <input type="text" class="input" id="fullName" required maxlength="100" placeholder="Contoh: Pak Budi (Kepala Lab)">
                        </div>

                        <div class="field">
                            <label for="userName">Username</label>
                            <input type="text" class="input" id="userName" required maxlength="30" placeholder="huruf kecil, angka, titik / underscore">
                        </div>

                        <div class="field">
                            <label for="userPass">Password <span id="pwHint" class="hint"></span></label>
                            <div class="pw-wrap">
                                <input type="password" class="input" id="userPass" placeholder="Minimal 6 karakter" autocomplete="new-password">
                                <button type="button" id="pwToggle" class="pw-toggle" aria-label="Tampilkan password"><i data-lucide="eye"></i></button>
                            </div>
                        </div>

                        <div class="field">
                            <span class="label">Hak Akses</span>
                            <div class="role-options">
                                <label class="role-card"><input type="radio" name="role" value="user" checked>
                                    <span><i data-lucide="eye"></i><strong>User</strong><small>Melihat data lab &amp; mengirim laporan ke Koordinator/Admin.</small></span></label>
                                <label class="role-card"><input type="radio" name="role" value="coordinator">
                                    <span><i data-lucide="clipboard-check"></i><strong>Koordinator</strong><small>Kelola lab, PC &amp; stok.</small></span></label>
                                <label class="role-card"><input type="radio" name="role" value="admin">
                                    <span><i data-lucide="shield-check"></i><strong>Admin</strong><small>Kelola user + semua akses.</small></span></label>
                            </div>
                        </div>

                        <div class="field">
                            <label for="userActive">Status Akun</label>
                            <select class="select" id="userActive">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif (tidak bisa login)</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="btnSave"><i data-lucide="user-plus"></i> <span>Buat Akun</span></button>
                            <button type="button" class="btn btn-secondary" id="btnCancel"><i data-lucide="rotate-ccw"></i> Reset</button>
                        </div>
                    </form>
                    <div class="form-footer">Dibuat oleh: <?= e(current_user_name()) ?> (<?= e(role_label($_SESSION['role'])) ?>).</div>
                </div>

                <!-- ============ DAFTAR ============ -->
                <div class="card">
                    <div class="card-head"><h2><i data-lucide="users"></i> Daftar Akun</h2></div>
                    <div class="metrics">
                        <div class="metric"><strong id="mAll">0</strong><span>Total User</span></div>
                        <div class="metric blue"><strong id="mAdmin">0</strong><span>Admin</span></div>
                        <div class="metric"><strong id="mCoord">0</strong><span>Koordinator</span></div>
                    </div>
                    <div class="input-icon" style="margin-top:16px">
                        <i data-lucide="search"></i>
                        <input type="search" id="userSearch" class="input" placeholder="Cari nama / username..." autocomplete="off">
                    </div>
                    <div id="userList" class="user-list">
                        <?php for ($i = 0; $i < 3; $i++): ?>
                        <div class="user-row"><div class="avatar skeleton"></div><div class="user-info"><strong class="skeleton">Memuat nama</strong><span class="skeleton" style="margin-top:4px">@memuat</span></div></div>
                        <?php endfor; ?>
                    </div>
                </div>

            </div>

<?php
ui_end_content();
ui_scripts('', ['users.js']);
