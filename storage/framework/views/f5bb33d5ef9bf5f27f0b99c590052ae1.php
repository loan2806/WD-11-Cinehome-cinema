<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'CineHome')); ?></title>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="<?php echo e(asset('assets/css/user-home.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/user-home.css'))); ?>">

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body>
        <?php echo e($slot); ?>


        <script src="<?php echo e(asset('assets/js/user-home.js')); ?>?v=<?php echo e(filemtime(public_path('assets/js/user-home.js'))); ?>"></script>
    </body>
</html>
<?php /**PATH E:\laragon\www\Cinema\WD-11-Cinehome-cinema\resources\views/layouts/guest.blade.php ENDPATH**/ ?>