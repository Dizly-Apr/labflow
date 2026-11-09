<?php
require_once __DIR__ . '/config/layout.php';
require_login();

$au = avatar_url($_SESSION['avatar'] ?? null);
$initial = strtoupper(function_exists('mb_substr') ? mb_substr(current_user_name(), 0, 1) : substr(current_user_name(), 0, 1));

ui_open('Profil', '', '', ['users.css'], '<h1>Profil Saya</h1>');
?>

            <div class="page-head">
                <div>
                    <h1>Profil Saya</h1>
                    <p>Informasi akun, foto profil &amp; ganti password.</p>
                </div>
            </div>

            <div class="users-grid">

                <!-- ============ GANTI PASSWORD ============ -->
                <div class="card">
                    <div class="card-head">
                        <h2><i data-lucide="key-round"></i> Ganti Password</h2>
                        <span class="badge badge-blue">SECURITY</span>
                    </div>
                    <p class="desc">Gunakan password yang sulit ditebak, minimal 6 karakter.</p>

                    <form id="pwForm" autocomplete="off">
                        <div class="field">
                            <label for="curPass">Password saat ini</label>
                            <div class="pw-wrap">
                                <input type="password" class="input" id="curPass" required autocomplete="current-password">
                                <button type="button" class="pw-toggle" data-toggle="curPass" aria-label="Tampilkan password"><i data-lucide="eye"></i></button>
                            </div>
                        </div>
                        <div class="field">
                            <label for="newPass">Password baru</label>
                            <div class="pw-wrap">
                                <input type="password" class="input" id="newPass" required minlength="6" autocomplete="new-password">
                                <button type="button" class="pw-toggle" data-toggle="newPass" aria-label="Tampilkan password"><i data-lucide="eye"></i></button>
                            </div>
                            <div class="strength"><i id="strengthBar"></i></div>
                            <div class="strength-label" id="strengthLabel"></div>
                        </div>
                        <div class="field">
                            <label for="newPass2">Ulangi password baru</label>
                            <div class="pw-wrap">
                                <input type="password" class="input" id="newPass2" required minlength="6" autocomplete="new-password">
                                <button type="button" class="pw-toggle" data-toggle="newPass2" aria-label="Tampilkan password"><i data-lucide="eye"></i></button>
                            </div>
                            <div class="strength-label" id="matchLabel"></div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="btnPw"><i data-lucide="save"></i> Simpan Password</button>
                        </div>
                    </form>
                </div>

                <!-- ============ AKUN ============ -->
                <div class="card">
                    <div class="card-head"><h2><i data-lucide="circle-user-round"></i> Akun</h2></div>

                    <div class="photo-box">
                        <div class="avatar-lg" id="avatarPreview" role="button" tabindex="0" aria-label="Ganti foto profil">
                            <?php if ($au): ?><img src="<?= e($au) ?>" alt="Foto profil"><?php else: ?><?= e($initial) ?><?php endif; ?>
                            <span class="cam"><i data-lucide="camera"></i></span>
                        </div>
                        <div class="photo-actions">
                            <input type="file" id="avatarFile" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
                            <button type="button" class="btn btn-secondary" id="btnPick"><i data-lucide="image-up"></i> Pilih Foto</button>
                            <?php if ($au): ?><button type="button" class="btn btn-ghost btn-sm" id="btnRemove" style="color:var(--red)"><i data-lucide="trash-2"></i> Hapus foto</button><?php endif; ?>
                            <div class="photo-hint">JPG, PNG, WEBP, atau GIF &middot; maks. 15 MB.<br>Setelah memilih, Anda bisa menggeser &amp; memperbesar foto.</div>
                        </div>
                    </div>

                    <div class="me-row">
                        <div class="user-info">
                            <strong><?= e(current_user_name()) ?></strong>
                            <span>@<?= e($_SESSION['username']) ?></span>
                        </div>
                        <span class="role-pill role-<?= e($_SESSION['role']) ?>"><?= e(role_label($_SESSION['role'])) ?></span>
                    </div>
                </div>
            </div>

<?php ui_end_content(); ?>

<!-- Editor foto profil: geser & zoom sebelum disimpan -->
<div class="modal-backdrop" id="cropOverlay" data-static hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="cropTitle" style="max-width:400px">
        <div class="modal-head">
            <div><h3 id="cropTitle">Atur Foto Profil</h3><p>Geser foto untuk memposisikan, gunakan slider atau scroll mouse untuk zoom.</p></div>
            <button type="button" class="modal-close" id="cropClose" aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>
        <canvas id="cropCanvas" width="320" height="320"></canvas>
        <div class="crop-zoom">
            <button type="button" class="icon-btn" id="zoomOut" aria-label="Perkecil"><i data-lucide="minus"></i></button>
            <input type="range" id="zoomRange" min="1" max="4" step="0.01" value="1" aria-label="Zoom">
            <button type="button" class="icon-btn" id="zoomIn" aria-label="Perbesar"><i data-lucide="plus"></i></button>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" id="cropReset">Reset</button>
            <button type="button" class="btn btn-secondary" id="cropCancel">Batal</button>
            <button type="button" class="btn btn-primary" id="cropSave"><i data-lucide="check"></i> Simpan Foto</button>
        </div>
    </div>
</div>

<?php ui_scripts('', ['profile.js']);
