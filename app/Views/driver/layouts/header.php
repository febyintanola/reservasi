<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="<?= base_url('css/theme.css') ?>" rel="stylesheet"/>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="flex bg-white min-h-screen">
        <?php
        $hdrFotoUrl = 'https://via.placeholder.com/32';
        $uid = session('user_id');
        if ($uid){
            try{
                $profileModel = model('App\\Models\\UserProfileModel');
                $p = $profileModel->where('user_id',$uid)->first();
                if (!empty($p['foto_url'])){
                    $hdrFotoUrl=$p['foto_url'];
                }
            }catch (\Throwable $e){
                //ignore
            }
        }
?>
<!-- Main Content Area -->
 <div class="flex-1 flex flex-col">
    <header class="flex item-center justify-between px-4 py-3 border-b border-white bg-white">
        <div class="flex-1 flex justify-center">
            <div class="text-black text-base font-normal">RuMa</div>
        </div>
        <a href="<?= base_url('/driver/profile')?>" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center ml-auto overflow-hidden">
            <img src="<?=esc($hdrFotoUrl) ?>" alt="Profil" class="w=8 h-8 object-cover"/>
    </a>
    </header>
    <main class="flex-1 p-6 overflow-y-auto">
        <?= $this->renderSection('content') ?>
    </main>
    </div>
</body>
<script>
    //toggle sidebar
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    toggleBtn.addEventListener('click', () =>{
        sidebar.classList.toggle('-ml-64');
    });
    </script>
</html>


    


