<?php $__env->startSection('page-title', 'Sửa phim'); ?>
<?php $__env->startSection('page-subtitle', 'Cập nhật thông tin phim, poster, trailer và phân loại'); ?>

<?php
    $selectedGenres = old('the_loai_id', $selectedGenreIds ?? []);
    $selectedGenres = is_array($selectedGenres) ? $selectedGenres : [];

    $posterUrl = function (?string $poster): string {
        if (empty($poster)) {
            return asset('assets/images/LOGO copy.png');
        }

        $poster = ltrim($poster, '/');

        if (\Illuminate\Support\Str::startsWith($poster, ['http://', 'https://'])) {
            return $poster;
        }

        if (\Illuminate\Support\Str::startsWith($poster, 'storage/')) {
            return asset($poster);
        }

        if (\Illuminate\Support\Str::startsWith($poster, 'movies/')) {
            return asset('storage/' . $poster);
        }

        return asset('storage/movies/' . $poster);
    };

    $currentPoster = $posterUrl($phim->poster);
?>

<?php $__env->startSection('content'); ?>
<form action="<?php echo e(route('admin.phims.update', $phim)); ?>" method="POST" enctype="multipart/form-data" class="movie-form-page">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <?php echo $__env->make('admin.partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <section class="movie-form-hero">
        <div class="movie-form-copy">
            <span class="movie-kicker">
                <i class="fa-solid fa-pen-to-square"></i>
                Cập nhật phim #<?php echo e($phim->id); ?>

            </span>
            <h2><?php echo e($phim->ten_phim); ?></h2>
            <p>
                Chỉnh sửa thông tin phim, thay poster, cập nhật trailer và phân loại để dữ liệu hiển thị
                nhất quán trên trang người dùng và khu vực đặt vé.
            </p>
        </div>

        <div class="movie-form-hero-actions">
            <a href="<?php echo e(route('admin.phims.show', $phim)); ?>" class="movie-action-btn is-soft">
                <i class="fa-solid fa-eye"></i>
                Xem chi tiết
            </a>
            <a href="<?php echo e(route('admin.phims.index')); ?>" class="movie-action-btn is-ghost">
                <i class="fa-solid fa-arrow-left"></i>
                Danh sách phim
            </a>
            <button type="submit" class="movie-action-btn is-primary">
                <i class="fa-solid fa-floppy-disk"></i>
                Cập nhật phim
            </button>
        </div>
    </section>

    <div class="movie-form-layout">
        <main class="movie-form-main">
            <section class="movie-form-panel">
                <div class="movie-form-panel-head">
                    <span><i class="fa-solid fa-circle-info"></i></span>
                    <div>
                        <h3>Thông tin chính</h3>
                        <p>Cập nhật tên phim, đội ngũ sản xuất, thời lượng và ngôn ngữ hiển thị.</p>
                    </div>
                </div>

                <div class="movie-form-grid">
                    <label class="movie-form-field is-wide">
                        <span>Tên phim <b>*</b></span>
                        <input
                            type="text"
                            name="ten_phim"
                            value="<?php echo e(old('ten_phim', $phim->ten_phim)); ?>"
                            placeholder="Nhập tên phim"
                            class="<?php echo e($errors->has('ten_phim') ? 'is-invalid' : ''); ?>"
                            autocomplete="off"
                        >
                        <?php $__errorArgs = ['ten_phim'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <small><?php echo e($message); ?></small>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>

                    <label class="movie-form-field">
                        <span>Đạo diễn <b>*</b></span>
                        <input
                            type="text"
                            name="dao_dien"
                            value="<?php echo e(old('dao_dien', $phim->dao_dien)); ?>"
                            placeholder="Tên đạo diễn"
                            class="<?php echo e($errors->has('dao_dien') ? 'is-invalid' : ''); ?>"
                        >
                        <?php $__errorArgs = ['dao_dien'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <small><?php echo e($message); ?></small>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>

                    <label class="movie-form-field">
                        <span>Ngôn ngữ <b>*</b></span>
                        <input
                            type="text"
                            name="ngon_ngu"
                            value="<?php echo e(old('ngon_ngu', $phim->ngon_ngu)); ?>"
                            placeholder="Tiếng Việt / Phụ đề Anh"
                            class="<?php echo e($errors->has('ngon_ngu') ? 'is-invalid' : ''); ?>"
                        >
                        <?php $__errorArgs = ['ngon_ngu'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <small><?php echo e($message); ?></small>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>

                    <label class="movie-form-field is-wide">
                        <span>Diễn viên <b>*</b></span>
                        <input
                            type="text"
                            name="dien_vien"
                            value="<?php echo e(old('dien_vien', $phim->dien_vien)); ?>"
                            placeholder="Nhập các diễn viên chính, cách nhau bằng dấu phẩy"
                            class="<?php echo e($errors->has('dien_vien') ? 'is-invalid' : ''); ?>"
                        >
                        <?php $__errorArgs = ['dien_vien'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <small><?php echo e($message); ?></small>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>

                    <label class="movie-form-field">
                        <span>Thời lượng <b>*</b></span>
                        <div class="movie-input-with-suffix">
                            <input
                                type="number"
                                name="thoi_luong"
                                value="<?php echo e(old('thoi_luong', $phim->thoi_luong)); ?>"
                                placeholder="120"
                                min="1"
                                class="<?php echo e($errors->has('thoi_luong') ? 'is-invalid' : ''); ?>"
                            >
                            <em>phút</em>
                        </div>
                        <?php $__errorArgs = ['thoi_luong'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <small><?php echo e($message); ?></small>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>

                    <label class="movie-form-field">
                        <span>Giới hạn tuổi <b>*</b></span>
                        <input
                            type="text"
                            name="gioi_han_tuoi"
                            value="<?php echo e(old('gioi_han_tuoi', $phim->gioi_han_tuoi)); ?>"
                            placeholder="P, T13, T16, T18"
                            class="<?php echo e($errors->has('gioi_han_tuoi') ? 'is-invalid' : ''); ?>"
                        >
                        <?php $__errorArgs = ['gioi_han_tuoi'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <small><?php echo e($message); ?></small>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>
                </div>
            </section>

            <section class="movie-form-panel">
                <div class="movie-form-panel-head">
                    <span><i class="fa-solid fa-tags"></i></span>
                    <div>
                        <h3>Phân loại phim</h3>
                        <p>Quốc gia và thể loại giúp khách lọc phim nhanh hơn.</p>
                    </div>
                </div>

                <div class="movie-form-grid">
                    <label class="movie-form-field">
                        <span>Quốc gia <b>*</b></span>
                        <select name="quoc_gia_id" class="<?php echo e($errors->has('quoc_gia_id') ? 'is-invalid' : ''); ?>">
                            <option value="">Chọn quốc gia</option>
                            <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($country->id); ?>" <?php if(old('quoc_gia_id', $phim->quoc_gia_id) == $country->id): echo 'selected'; endif; ?>>
                                    <?php echo e($country->ten_quoc_gia); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['quoc_gia_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <small><?php echo e($message); ?></small>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>

                    <div class="movie-form-field">
                        <span>Thể loại đã chọn</span>
                        <div class="movie-selected-count">
                            <i class="fa-solid fa-check"></i>
                            <strong id="genreCount"><?php echo e(count($selectedGenres)); ?></strong>
                            <em>thể loại</em>
                        </div>
                    </div>
                </div>

                <div class="movie-genre-picker <?php echo e($errors->has('the_loai_id') ? 'is-invalid' : ''); ?>">
                    <?php $__empty_1 = true; $__currentLoopData = $genres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $genre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <label class="movie-genre-option">
                            <input
                                type="checkbox"
                                name="the_loai_id[]"
                                value="<?php echo e($genre->id); ?>"
                                <?php if(in_array($genre->id, $selectedGenres)): echo 'checked'; endif; ?>
                            >
                            <span>
                                <i class="fa-solid fa-check"></i>
                                <?php echo e($genre->ten_the_loai); ?>

                            </span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="movie-form-empty">
                            <i class="fa-solid fa-tags"></i>
                            <strong>Chưa có thể loại nào</strong>
                            <a href="<?php echo e(route('admin.the-loais.create')); ?>">Tạo thể loại ngay</a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php $__errorArgs = ['the_loai_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="movie-form-error"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </section>

            <section class="movie-form-panel">
                <div class="movie-form-panel-head">
                    <span><i class="fa-solid fa-align-left"></i></span>
                    <div>
                        <h3>Nội dung phim</h3>
                        <p>Cập nhật mô tả đang dùng ở trang chi tiết người dùng.</p>
                    </div>
                </div>

                <label class="movie-form-field">
                    <span>Mô tả phim <b>*</b></span>
                    <textarea
                        name="mo_ta"
                        rows="8"
                        placeholder="Nhập mô tả phim, bối cảnh, điểm nhấn và lý do nên xem..."
                        class="<?php echo e($errors->has('mo_ta') ? 'is-invalid' : ''); ?>"
                    ><?php echo e(old('mo_ta', $phim->mo_ta)); ?></textarea>
                    <?php $__errorArgs = ['mo_ta'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <small><?php echo e($message); ?></small>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </label>
            </section>
        </main>

        <aside class="movie-form-side">
            <section class="movie-form-panel movie-media-panel">
                <div class="movie-form-panel-head">
                    <span><i class="fa-solid fa-image"></i></span>
                    <div>
                        <h3>Poster phim</h3>
                        <p>Chọn ảnh mới nếu muốn thay poster hiện tại. Tối đa 2MB.</p>
                    </div>
                </div>

                <label for="poster" class="movie-poster-uploader <?php echo e($errors->has('poster') ? 'is-invalid' : ''); ?>">
                    <input id="poster" type="file" name="poster" accept="image/*">
                    <img id="posterPreview" src="<?php echo e($currentPoster); ?>" alt="Poster hiện tại của <?php echo e($phim->ten_phim); ?>" <?php if(! $phim->poster): ?> hidden <?php endif; ?>>
                    <span id="posterPlaceholder" <?php if($phim->poster): ?> hidden <?php endif; ?>>
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <strong>Tải poster mới</strong>
                        <small>Bấm để chọn ảnh dọc mới</small>
                    </span>
                </label>

                <div class="movie-file-note" id="posterFileName">
                    <?php echo e($phim->poster ? 'Poster hiện tại: ' . basename($phim->poster) : 'Chưa có poster'); ?>

                </div>

                <?php $__errorArgs = ['poster'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="movie-form-error"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </section>

            <section class="movie-form-panel movie-media-panel">
                <div class="movie-form-panel-head">
                    <span><i class="fa-brands fa-youtube"></i></span>
                    <div>
                        <h3>Trailer</h3>
                        <p>Dán link YouTube để kiểm tra preview trước khi cập nhật.</p>
                    </div>
                </div>

                <label class="movie-form-field">
                    <span>Trailer URL <b>*</b></span>
                    <input
                        type="url"
                        id="trailer"
                        name="trailer"
                        value="<?php echo e(old('trailer', $phim->trailer)); ?>"
                        placeholder="https://www.youtube.com/watch?v=..."
                        class="<?php echo e($errors->has('trailer') ? 'is-invalid' : ''); ?>"
                    >
                    <?php $__errorArgs = ['trailer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <small><?php echo e($message); ?></small>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <small id="trailerHint" class="movie-live-hint">Hỗ trợ youtube.com, youtu.be và YouTube Shorts.</small>
                </label>

                <div id="trailerBox" class="movie-trailer-preview" hidden>
                    <iframe id="trailerPreview" title="Xem trước trailer" src="" allowfullscreen></iframe>
                </div>
            </section>

            <section class="movie-form-tip">
                <i class="fa-solid fa-lightbulb"></i>
                <div>
                    <strong>Lưu ý khi cập nhật</strong>
                    <span>Nếu không chọn poster mới, hệ thống sẽ giữ nguyên poster hiện tại của phim.</span>
                </div>
            </section>
        </aside>
    </div>

    <div class="movie-form-savebar">
        <div>
            <strong>Cập nhật thay đổi?</strong>
            <span>Kiểm tra lại poster, trailer và thể loại trước khi lưu.</span>
        </div>
        <div class="movie-form-save-actions">
            <a href="<?php echo e(route('admin.phims.show', $phim)); ?>" class="movie-action-btn is-ghost">
                <i class="fa-solid fa-xmark"></i>
                Hủy
            </a>
            <button type="submit" class="movie-action-btn is-primary">
                <i class="fa-solid fa-floppy-disk"></i>
                Cập nhật phim
            </button>
        </div>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const posterInput = document.getElementById('poster');
        const posterPreview = document.getElementById('posterPreview');
        const posterPlaceholder = document.getElementById('posterPlaceholder');
        const posterFileName = document.getElementById('posterFileName');
        const genreCount = document.getElementById('genreCount');
        const trailerInput = document.getElementById('trailer');
        const trailerBox = document.getElementById('trailerBox');
        const trailerPreview = document.getElementById('trailerPreview');
        const trailerHint = document.getElementById('trailerHint');

        function updateGenreCount() {
            if (!genreCount) {
                return;
            }

            genreCount.textContent = document.querySelectorAll('input[name="the_loai_id[]"]:checked').length;
        }

        function getYoutubeEmbed(url) {
            if (!url) {
                return null;
            }

            try {
                const parsed = new URL(url);
                const host = parsed.hostname.replace(/^www\./, '');

                if (host === 'youtube.com' && parsed.searchParams.get('v')) {
                    return 'https://www.youtube.com/embed/' + parsed.searchParams.get('v');
                }

                if (host === 'youtu.be') {
                    const id = parsed.pathname.split('/').filter(Boolean)[0];
                    return id ? 'https://www.youtube.com/embed/' + id : null;
                }

                if (host === 'youtube.com' && parsed.pathname.includes('/shorts/')) {
                    const id = parsed.pathname.split('/shorts/')[1]?.split('/')[0];
                    return id ? 'https://www.youtube.com/embed/' + id : null;
                }
            } catch (error) {
                return null;
            }

            return null;
        }

        function updateTrailerPreview() {
            const embed = getYoutubeEmbed(trailerInput?.value.trim());

            if (!trailerInput || !trailerBox || !trailerPreview || !trailerHint) {
                return;
            }

            if (!trailerInput.value.trim()) {
                trailerPreview.src = '';
                trailerBox.hidden = true;
                trailerHint.textContent = 'Hỗ trợ youtube.com, youtu.be và YouTube Shorts.';
                trailerHint.classList.remove('is-error');
                return;
            }

            if (!embed) {
                trailerPreview.src = '';
                trailerBox.hidden = true;
                trailerHint.textContent = 'Link trailer chưa đúng định dạng YouTube.';
                trailerHint.classList.add('is-error');
                return;
            }

            trailerPreview.src = embed;
            trailerBox.hidden = false;
            trailerHint.textContent = 'Trailer hợp lệ, có thể xem trước bên dưới.';
            trailerHint.classList.remove('is-error');
        }

        document.querySelectorAll('input[name="the_loai_id[]"]').forEach(function(input) {
            input.addEventListener('change', updateGenreCount);
        });

        posterInput?.addEventListener('change', function(event) {
            const file = event.target.files?.[0];

            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function(loadEvent) {
                posterPreview.src = loadEvent.target.result;
                posterPreview.hidden = false;
                posterPlaceholder.hidden = true;
                posterFileName.textContent = 'Poster mới: ' + file.name;
            };
            reader.readAsDataURL(file);
        });

        trailerInput?.addEventListener('input', updateTrailerPreview);
        updateGenreCount();
        updateTrailerPreview();
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\DATN\WD-11-Cinehome-cinema\resources\views/admin/phims/edit.blade.php ENDPATH**/ ?>